<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once __DIR__.'/../../api/Simpla.php';
require_once __DIR__ . '/../../scorings/BoostraPTI.php';

class ScoristaNewCron_mt extends Simpla
{
    private const LOG_FILE = 'ScoristaNewCron_mt.txt';

	public const LOCKER_NAME = self::class;
	public const WORKERS_COUNT = 5;
	public const SLEEP_SECONDS = 5;
	public const SUSPEND_SECONDS = 5;
	
	private $worker_id  = '';
	private $is_reverse = false;
	private $suspended  = [];

    public function __construct()
    {
    	parent::__construct();
    }

    public function run()
    {
		$db = $this->db;
		$const = 'constant';
        $sc_types = [
            $this->scorings::TYPE_SCORISTA => 'NEW_SCORISTA',
        ];
		$case_block = array_map(fn($idx) => "WHEN GET_LOCK('{$const('static::LOCKER_NAME')}_{$idx}', 0) THEN '{$idx}'"
											. " WHEN GET_LOCK('{$const('static::LOCKER_NAME')}_!{$idx}', 0) THEN '!{$idx}'"
								, array_keys(array_fill(1, static::WORKERS_COUNT, 0)));

		while(true) {
			$db->query("SELECT IS_USED_LOCK('{$const('static::LOCKER_NAME')}_EXIT') stop_all_workers");
			if($db->result('stop_all_workers')) {
				break;
			}
			$this->suspended = array_filter($this->suspended, function($_service) use ($db) {
				if($_service['last_check']->diff(new DateTime)->s >= static::SUSPEND_SECONDS) {
					$db->query("DO RELEASE_LOCK('{$_service['locker_id']}')");
					return false;
				}
				return true;
			});
			if($this->worker_id) {
				$query = "SELECT GET_LOCK('{$const('static::LOCKER_NAME')}_{$this->worker_id}', 0) lock_is_valid";
				$db->query($query);
				if(!$db->result('lock_is_valid')) {
					$this->worker_id = '';
				}
			}
			if(!$this->worker_id) {
				$query = $db->placehold('SELECT CASE ' . implode(' ', $case_block) . ' END locker');
				$db->query($query);
				if(!($this->worker_id = $db->result('locker'))) {
					break;
				}
				$this->is_reverse = strpos($this->worker_id, '!') !== false;
				$this->logging(__METHOD__, '', '', 'Начало работы крона ' . $this->worker_id, self::LOG_FILE);
			}
			
			$suspended = [0, ...array_map(fn($item) => $item['id'], $this->suspended)];
            $scoring = $this->scorings->get_scoring_mt($sc_types, $suspended, $this->is_reverse);
			
			if($scoring) {
				//run service
                $this->scorings->update_scoring($scoring->id, array(
                    'status' => $this->scorings::STATUS_PROCESS,
                    'start_date' => date('Y-m-d H:i:s')
                ));
                $result = $this->handleNewScoring($scoring);
                if (!$result) {
					$this->scorings->update_scoring($scoring->id, ['status' => $this->scorings::STATUS_NEW]);
                    $this->suspended[] = [
                        'id' => $scoring->id,
                        'locker_id' => $scoring->locker_id,
                        'last_check' => (new DateTime('now')),
                    ];
                } else {
                    $db->query("DO RELEASE_LOCK('{$scoring->locker_id}')");
                }
			} else {
				sleep(static::SLEEP_SECONDS);
			}
		}
    }
    
    public function handleNewScoring($scoring)
    {
        // Для обработки получаем последний скоринг скористы по заявке, так как возможны дубликаты
        $another_lock = $this->scorings->checkSingleScoring_mt($this->scorings::TYPE_SCORISTA, $this->scorings::STATUS_NEW, 
                                                                $scoring->order_id, $scoring->id, 'NEW_SCORISTA');
        if ($another_lock) {
            return false;
        }

        $scoringType = $this->scorings->get_type((int)$scoring->type);
        $scoring_result = $this->{$scoringType->name}->run_scoring($scoring->id);

        // Останавливаем дубликаты скористы по этой заявке
        $this->scorings->stopOrderScoringsByType($scoring->order_id, ['string_result' => 'Дубликат'], Scorings::TYPE_SCORISTA, (int)$scoring->id);
        
        $query = $this->db->placehold("UPDATE s_scorings SET `status` = ? WHERE id = ?", $this->scorings::STATUS_IMPORT, (int)$scoring->id);
        $this->db->query($query);

        return true;
    }
}

set_time_limit(0);
$cron = new ScoristaNewCron_mt();
$cron->run();
