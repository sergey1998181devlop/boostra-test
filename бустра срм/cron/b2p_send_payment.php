<?php
error_reporting(-1);
ini_set('display_errors', 'On');


chdir(dirname(__FILE__).'/../');
require_once 'api/Simpla.php';

/**
 * B2pSendPaymentCron 
 * 
 * Скрипт отправляет в 1с оплаты
 * 
 * @author Ruslan Kopyl
 * @copyright 2021
 * @version $Id$
 * @access public
 */
class B2pSendPaymentCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $i = 0;
        while ($i < 5)
        {
            $this->send_payment();
            $i++;
        }
    }
    
    private function send_payment()
    {
        $params = array(
            'sent' => 0,
            'reason_code' => 1,
            'limit' => 5,
        );
        if ($payments = $this->best2pay->get_payments($params))
        {
            array_walk($payments, function ($payment) {
                // проверим был ли куплен мультиполис
                $filter_data_multipolis = [
                    'filter_payment_id' => (int)$payment->id,
                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                ];

                if ($multipolis = $this->multipolis->selectAll($filter_data_multipolis, false)) {
                    $payment->multipolis = $multipolis;
                }

                // проверим была ли куплена телемедицина
                $filter_data_tv_medical = [
                    'filter_payment_id' => (int)$payment->id,
                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                ];

                if ($tv_medical_payment = $this->tv_medical->selectPayments($filter_data_tv_medical, false)) {
                    $payment->tv_medical = $tv_medical_payment;
                }
            });

            $result = $this->soap->send_payments($payments);
        
            if (!empty($result->return) && $result->return == 'OK')
            {
                foreach ($payments as $p)
                {
                    $this->best2pay->update_payment($p->id, array(
                        'sent' => 1,
                        'send_date' => date('Y-m-d H:i:s'),
                    ));
                }
            }
        }
    }
    
    
}

$cron = new B2pSendPaymentCron();
