<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__FILE__).'/../api/Simpla.php';

class SendOldAksiTo1cCron extends Simpla
{
    private const LOG_FILE = 'send_old_aksi_to_1c.php';
    /** @var int Если крон выполняется дольше - перестаёт обрабатывать заявки и завершается */
    private const MAX_CRON_DURATION = 100;
    /** @var int Короткая пауза после SLEEP_AFTER заявок */
    private const SLEEP_AFTER = 100;
    private const SLEEP_TIME = 2;
    private const ORDERS_LIMIT = 600;
    private const SETTINGS_KEY = 'send_old_aksi_to_1c-max_order_id';
    private $max_order_id;
    private $start_time;

    private const SQL = <<<SQL
        SELECT
            sal.order_id,
            so.1c_id AS 'id_1c',
            sal.final_limit,
            sal.sc_new01,
            sal.sc_new02,
            sal.sc_new03,
            sal.sc_rpt01,
            sal.sc_rpt02,
            sal.sc_rpt03
        FROM s_axi_ltv sal
        JOIN s_orders so ON so.id = sal.order_id
        WHERE sal.created <= '2025-06-18 15:30:00' AND sal.order_id < ?
        ORDER BY sal.order_id DESC LIMIT ?
    SQL;

    function __construct()
    {
        parent::__construct();

        $this->start_time = microtime(true);

        $this->max_order_id = $this->settings->{self::SETTINGS_KEY};
        if (empty($this->max_order_id)) {
            $this->max_order_id = 999_999_999;
        }
        else {
            $this->max_order_id = (int)$this->max_order_id;
        }

        $this->run();

        $this->settings->{self::SETTINGS_KEY} = $this->max_order_id;

        $duration = microtime(true) - $this->start_time;
        var_dump("Время выполнения: " . $duration);

        $this->logging(__METHOD__, '', '', 'Время выполнения: ' . $duration . ' секунд', self::LOG_FILE);
    }

    function run()
    {
        $this->db->query(self::SQL, $this->max_order_id, self::ORDERS_LIMIT);
        $rows = $this->db->results();
        if (empty($rows)) {
            var_dump("Крон закончил работу, можно его удалить");
            exit();
        }

        $counter = 0;
        foreach ($rows as $row) {
            $counter += 1;

            $scoring = $this->scorings->getLastScoring([
                'type' => $this->scorings::TYPE_AXILINK_2,
                'status' => $this->scorings::STATUS_COMPLETED,
                'order_id' => $row->order_id
            ]);
            if (empty($scoring)) {
                continue;
            }
            $body = $this->scorings->get_body_by_type($scoring);

            $this->soap->send_aksi([
                'ball' => $scoring->scorista_ball,
                'result' => $scoring->scorista_status,
                'limit' => $body->sum ?? 0,
                'order_id' => $row->id_1c,
                'version' => $body->strategy_version,
                'final_limit' => $row->final_limit ?? 0,
                'sc_new01' => $row->sc_new01 ?? 0,
                'sc_new02' => $row->sc_new02 ?? 0,
                'sc_new03' => $row->sc_new03 ?? 0,
                'sc_rpt01' => $row->sc_rpt01 ?? 0,
                'sc_rpt02' => $row->sc_rpt02 ?? 0,
                'sc_rpt03' => $row->sc_rpt03 ?? 0
            ]);

            if ($row->order_id < $this->max_order_id) {
                $this->max_order_id = $row->order_id;
            }

            $duration = microtime(true) - $this->start_time;
            if ($duration >= self::MAX_CRON_DURATION) {
                break;
            }

            if ($counter >= self::SLEEP_AFTER) {
                $counter = 0;
                sleep(self::SLEEP_TIME);
            }
        }

        var_dump("Крон отработал, id старшей заявки: ");
        var_dump($this->max_order_id);
    }
}
new SendOldAksiTo1cCron();