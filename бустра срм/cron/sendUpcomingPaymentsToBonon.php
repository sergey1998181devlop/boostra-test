<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__FILE__).'/../api/Simpla.php';

class sendUpcomingPaymentsToBonon extends Simpla
{
    public function run()
    {
        $today = (new DateTime('now'))->format('Y-m-d');
        $this->db->query("SET @issue_end := '{$today}'");
        $this->db->query('SET @issue_start := @issue_end - INTERVAL 90 DAY + INTERVAL 2 DAY');
        $this->db->query('SET @target_start := @issue_end + INTERVAL 2 DAY');
        $this->db->query('SET @target_end := @target_start + INTERVAL 3 DAY');
        $this->db->query("SELECT o.id order_id
                            FROM s_orders o
                            WHERE
                                o.loan_type = 'PDL'
                                AND o.confirm_date >= @issue_start
                                AND o.confirm_date < @issue_end
                                AND o.`1c_status` <> '6.Закрыт'
                                AND o.confirm_date + INTERVAL o.period DAY >= @target_start
                                AND o.confirm_date + INTERVAL o.period DAY < @target_end");
        $order_ids = $this->db->results('order_id');

        foreach($order_ids as $order_id) {
            $response = $this->bonondo->sendUpcomingPayments($order_id);
        }
    }
}

set_time_limit(0);
$cron = new sendUpcomingPaymentsToBonon();
$cron->run();
