<?php

error_reporting(-1);
ini_set('display_errors', 'On');

date_default_timezone_set('Europe/Moscow');
require_once dirname(__DIR__) . '/api/Simpla.php';

class RunB2pSbpPaymentsDevCron extends Simpla
{
    public function run()
    {
        $this->logging(__METHOD__, '', 'Начат крон', [], 'run_b2p_sbp_payments_dev2.txt');

        if (!$this->helpers->isDev()) {
            return;
        }

        $payments = $this->get_sbp_payments();

        $this->logging(__METHOD__, '', 'Получены платежи', ['payments' => $payments], 'run_b2p_sbp_payments_dev2.txt');

        if (empty($payments)) {
            return;
        }

        foreach ($payments as $payment) {
            $this->best2pay->runTestScenarioSbpPayment((int)$payment->sector, (string)$payment->register_id);
        }
    }

    private function get_sbp_payments()
    {
        $query = "
            SELECT *
            FROM b2p_payments
            WHERE operation_id IS NULL
                AND reason_code = 0
                AND is_sbp = 1
            ORDER BY id DESC
            LIMIT 50
        ";

        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }
}
$runB2pSbpPaymentsDevCron = new RunB2pSbpPaymentsDevCron();
$runB2pSbpPaymentsDevCron->run();