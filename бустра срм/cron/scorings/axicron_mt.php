<?php

error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../../api/Simpla.php';

class AxiCron_mt extends Simpla
{
    private const LOG_FILE = 'AxiCron_mt.txt';

	public const LOCKER_NAME = self::class;
	public const WORKERS_COUNT = 10;
	public const SLEEP_SECONDS = 10;
	public const SUSPEND_SECONDS = 10;
	
	private $worker_id  = '';
	private $is_reverse = false;
	private $suspended  = [];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Обрабатываем АксиЛИНК и АксиНБКИ скоринги
     * @return void
     */
    public function run()
    {
		$db = $this->db;
		$const = 'constant';
        $sc_type = [
            'id' => $this->scorings::TYPE_AXILINK,
            'lock_name' => 'WAIT_AXILINK',
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
            $scoring = $this->scorings->getWaitScoring_mt($sc_type, $suspended, $this->is_reverse);
			
			if($scoring) {
				//run service
                $this->handleAxiLink($scoring);
                $_scoring = $this->scorings->get_scoring($scoring->id);
                if ($_scoring->status == $this->scorings::STATUS_WAIT) {
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

    /**
     * Обработка АксиЛИНК скоринга
     * @param $scoring
     * @return void
     */
    public function handleAxiLink($scoring)
    {
        try {
            $this->axilink->getInfo($scoring);
            $this->order_org_switch->trySwitchOrganization((int)$scoring->order_id);
        } catch (Exception $e) {
            $this->logError(__METHOD__, $e, $scoring);
        }
    }

    /**
     * @param string $method
     * @param Exception $error
     * @param object|null $scoring
     * @return void
     */
    private function logError($method, $error, $scoring = null)
    {
        $error_lines = [
            'Ошибка: ' . $error->getMessage(),
            'Файл: ' . $error->getFile(),
            'Строка: ' . $error->getLine(),
            'Подробности: ' . $error->getTraceAsString()
        ];

        $type = 'Пустой тип в $scoring';
        if (!empty($scoring->type))
            $type = $scoring->type;

        $this->logging($method, $type, $scoring, $error_lines, 'axi_cron.txt');
    }
}

set_time_limit(0);
$cron = new AxiCron_mt;
$cron->run();