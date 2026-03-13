<?php
error_reporting(-1);
ini_set('display_errors', 'On');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../../api/Simpla.php';
require_once dirname(__FILE__) . '/../../api/Scorings.php';

/**
 * Выполнение медленных скорингов
 */
class ScoringsCron2mt extends Simpla
{
    private const LOG_FILE = 'audit2mt.txt';

	public const LOCKER_NAME = self::class;
	public const WORKERS_COUNT = 5;
	public const SLEEP_SECONDS = 5;
	public const SUSPEND_SECONDS = 5;
	
	private $worker_id  = '';
	private $is_reverse = false;
	private $suspended  = [];

    public function run()
    {
        $sc_types = [
            $this->scorings::TYPE_AXILINK => 'SCORING_AXILINK',
            $this->scorings::TYPE_AXILINK_2 => 'SCORING_AXILINK_2',
            $this->scorings::TYPE_PDN => 'SCORING_PDN',
            //$this->scorings::TYPE_FINKARTA => 'SCORING_FINKARTA',
            //$this->scorings::TYPE_JUICESCORE => 'SCORING_JUICESCORE',
        ];
        $dontAddScoristaAxi = [
            $this->scorings::TYPE_AXILINK,
            $this->scorings::TYPE_BLACKLIST,
        ];
		$db = $this->db;
		$const = 'constant';
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
                $scoringType = $this->scorings->get_type((int)$scoring->type);
                $classname   = $scoring->type == $this->scorings::TYPE_AXILINK_2 ? 'dbrainAxi' : $scoringType->name;

                if (empty($scoringType->active)) {
                    $this->scorings->update_scoring($scoring->id, array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'Скоринг отключен',
                        'start_date' => date('Y-m-d H:i:s'),
                        'end_date' => date('Y-m-d H:i:s'),
                    ));
                } else {
                    $_class = get_class($this->{$classname});
                    $instance = new $_class;
                    $instance->run_scoring($scoring->id);
                    if (!in_array((int)$scoring->type, $dontAddScoristaAxi) && !empty($scoring->order_id)) {
                        $this->scorings->tryAddScoristaAndAxi_mt($scoring->order_id);
                    }
                }
                $db->query("DO RELEASE_LOCK('{$scoring->locker_id}')");
                continue;
				//on fail
				$this->suspended[] = [
					'id' => $scoring->id,
					'locker_id' => $scoring->locker_id,
					'last_check' => (new DateTime('now')),
				];
			} else {
				sleep(static::SLEEP_SECONDS);
			}
		}
    }
}

set_time_limit(0);
$cron = new ScoringsCron2mt();
$cron->run();
