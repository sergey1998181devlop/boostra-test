<?php

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');
date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__).'/../api/Simpla.php';

class RestoreFinlabCron extends Simpla
{
    /** @var int Количество заявок для обработки за раз */
    const ORDERS_LIMIT = 10;

    private $offset;

    const SQL = <<<SQL
SELECT o.id, o.user_id, u.phone_mobile
FROM s_orders o
LEFT JOIN s_orders fnlb_o ON o.id = fnlb_o.utm_medium
JOIN s_users u ON o.user_id = u.id
WHERE
	o.`date` >= '2025-06-02'
	AND o.`date` <= '2025-06-04 14:00:00'
	AND o.have_close_credits = 0
	AND fnlb_o.id IS NULL
	AND o.`status` IN (2, 10)
LIMIT ? OFFSET ?
SQL;

    const SMS_TEXT = "Доступен второй займ без проверок! Зайдите в личный кабинет boostra.ru";

    public function __construct()
    {
        parent::__construct();

        $hour = (int)date('H');
        if ($hour < 9 || $hour >= 22) {
            var_dump("Выключен с 22 до 9");
            return;
        }

        $this->offset = $this->settings->restore_finlab_cron_offset2;
        if (empty($this->offset))
            $this->offset = 0;
        $this->offset = (int)$this->offset;

        $this->run();

        $this->settings->restore_finlab_cron_offset2 = $this->offset + self::ORDERS_LIMIT;
    }

    public function run()
    {
        $this->db->query(self::SQL, self::ORDERS_LIMIT, $this->offset);
        $orders = $this->db->results();
        if (empty($orders)) {
            var_dump("Заявки закончились");
            exit();
        }

        foreach ($orders as $order) {
            if (!$this->cross_orders->create($order->id))
                continue;

            $site_id = $this->users->get_site_id_by_user_id($order->user_id);
            $response = $this->smssender->send_sms(
                $order->phone_mobile,
                self::SMS_TEXT,
                $site_id,
                1
            );

            $this->sms->add_message([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'phone' => $order->phone_mobile,
                'message' => self::SMS_TEXT,
                'created' => date('Y-m-d H:i:s'),
                'send_status' => $response[1],
                'delivery_status' => '',
                'send_id' => $response[0],
                'type' => $this->smssender::TYPE_RESTORE_FINLAB,
                'code' => 0
            ]);

            sleep(10);
        }

        var_dump("Крон отработал");
    }
}

new RestoreFinlabCron();
