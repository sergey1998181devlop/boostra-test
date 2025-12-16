<?php

use App\Repositories\ReturnLogRepository;

error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__DIR__) . '/api/Simpla.php';
require_once dirname(__DIR__) . '/app/Repositories/ReturnLogRepository.php';

class AutoconfirmCron extends Simpla
{
    public function __construct()
    {
        parent::__construct();
    	$this->run();
    }
    
    private function run()
    {
        $returnLogRepo = new ReturnLogRepository('__credit_doctor_to_user', 'success');

        $filter = [
            'status' => $this->orders::ORDER_STATUS_CRM_AUTOCONFIRM,
            'limit' => 10,
        ];
        if ($orders = $this->orders->get_orders($filter)) {
            foreach ($orders as $order) {
                if ($autoconfirm_asp = $this->order_data->read($order->id, $this->order_data::AUTOCONFIRM_ASP)) {
                    $this->orders->update_order($order->id, [
                        'accept_sms' => $autoconfirm_asp,
                        'confirm_date' => date('Y-m-d H:i:s'),
                        'utm_medium' => 'autoconfirm',
                    ]);
                    $this->contracts->accept_credit($order, [
                        'is_user_credit_doctor' => $returnLogRepo->countByUser($order->user_id, 30) > 0 ? 0 : 1,
                        'is_star_oracle' => 1,
                    ]);
                    
                } else {
                    $this->orders->update_order($order->id, [
                        'status' => $this->orders::STATUS_NOT_ISSUED,
                        'reject_date' => date('Y-m-d H:i:s'),
                        'pay_result' => 'Ошибка: Код АСП',
                    ]);
                    
                }
            }
            
        }
    }
    
}
new AutoconfirmCron();
