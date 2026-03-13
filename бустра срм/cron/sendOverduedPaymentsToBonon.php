<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__FILE__).'/../api/Simpla.php';

class sendOverduedPaymentsToBonon extends Simpla
{
    public function run()
    {
        $this->db->query("SET @today := CURRENT_DATE()");
        $this->db->query('SET @yesterday := @today - INTERVAL 1 DAY');
        $this->db->query("SELECT o.id order_id
                            FROM s_orders o
                            WHERE o.`1c_id` IN (SELECT ub.zayavka
                                                FROM s_user_balance ub
                                                WHERE
                                                    ub.zaim_number NOT IN ('Нет открытых договоров', 'Ошибка')
                                                    AND ub.zayavka > ''
                                                    AND ub.payment_date >= @yesterday
                                                    AND ub.payment_date < @today)
                            GROUP BY o.id");
        $order_ids = $this->db->results('order_id');

        foreach($order_ids as $order_id) {
            $response = $this->bonondo->sendOverduedPayments($order_id);
        }
    }
}

set_time_limit(0);
$cron = new sendOverduedPaymentsToBonon();
$cron->run();
