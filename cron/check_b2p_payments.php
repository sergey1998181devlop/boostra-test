<?php
error_reporting(-1);
ini_set('display_errors', 'On');

date_default_timezone_set('Europe/Moscow');
require_once dirname(__DIR__) . '/api/Simpla.php';

class CheckB2pPaymentsCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    private function run()
    {
        if ($payments = $this->get_payments())
        {
            foreach ($payments as $payment)
            {
                $this->check_payment($payment);
            }
        }
    }
    
    private function check_payment($payment)
    {
        $url = $this->config->front_url.'/best2pay_callback/payment?id='.$payment->register_id;
        $resp = file_get_contents($url);
        
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($payment);echo '</pre><hr />';
    
        usleep(500000);
    }
    
    private function get_payments()
    {
        $minus_time = date('Y-m-d H:i:s', time() - 3600);
        $this->db->query("
            SELECT * FROM b2p_payments
            WHERE (callback_response IS NULL OR callback_response = 'b:0;')
            AND register_id > 0
            AND created < ?
            ORDER BY id DESC
            LIMIT 50
        ", $minus_time);
        $results = $this->db->results();

        return $results;
    }
}
new CheckB2pPaymentsCron();