<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../../api/Simpla.php';

class ScoringsCronUprid extends Simpla
{
    private const LOG_FILE = 'scoring_uprid.log';

    public const LOCKER_NAME    = self::class;
    public const WORKERS_COUNT  = 5;
    public const SLEEP_SECONDS  = 5;
    public const SUSPEND_SECONDS = 60;

    private $worker_id  = '';
    private $suspended  = [];

    public function run()
    {
        $db = $this->db;
        $const = 'constant';
        $case_block = array_map(fn($idx) => "WHEN GET_LOCK('{$const('static::LOCKER_NAME')}_{$idx}', 0) THEN '{$idx}'",
            range(1, static::WORKERS_COUNT));

        while (true) {
            $db->query("SELECT IS_USED_LOCK('{$const('static::LOCKER_NAME')}_EXIT') stop_all_workers");
            if ($db->result('stop_all_workers')) {
                break;
            }

            // Освобождение заблокированных
            $this->suspended = array_filter($this->suspended, function ($item) use ($db) {
                if ($item['last_check']->diff(new DateTime)->s >= static::SUSPEND_SECONDS) {
                    $db->query("DO RELEASE_LOCK('{$item['locker_id']}')");
                    return false;
                }
                return true;
            });

            if (!$this->worker_id) {
                $query = $db->placehold('SELECT CASE ' . implode(' ', $case_block) . ' END locker');
                $db->query($query);
                $this->worker_id = $db->result('locker');
                if (!$this->worker_id) break;
                $this->logging(__METHOD__, '', '', "Worker $this->worker_id started", self::LOG_FILE);
            }

            // Получить подходящий скоринг
            $db->query("SELECT NOW() as now");
            $now = $db->result('now');

            $query = $db->placehold("
                SELECT * FROM s_scorings
                WHERE status = ?
                AND (next_run_at IS NULL OR next_run_at <= ?)
                LIMIT 1
            ", $this->scorings::STATUS_WAIT, $now);
            $db->query($query);
            $scoring = $db->result();

            if ($scoring) {
                $db->query("SELECT GET_LOCK('scoring_uprid_{$scoring->id}', 0) as lock_acquired");
                if (!$db->result('lock_acquired')) {
                    // заблокировано другим воркером
                    $this->suspended[] = [
                        'id' => $scoring->id,
                        'locker_id' => "scoring_uprid_{$scoring->id}",
                        'last_check' => new DateTime(),
                    ];
                    continue;
                }

                $this->logging(__METHOD__, '', '', "Обработка скоринга UPRID #{$scoring->id}", self::LOG_FILE);
                $this->uprid->run_scoring($scoring->id);
                $db->query("DO RELEASE_LOCK('scoring_uprid_{$scoring->id}')");
            } else {
                sleep(static::SLEEP_SECONDS);
            }
        }
    }
}

set_time_limit(0);
$cron = new ScoringsCronUprid();
$cron->run();
