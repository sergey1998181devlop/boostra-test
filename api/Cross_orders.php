<?php

require_once 'Simpla.php';

class Cross_orders extends Simpla
{
    private $system_manager;
    private $reject_reason;
    
    private $open_next_order_statuses;
    private $reject_statuses;
    private $display_cross_statuses;

    private const LOG_FILE = 'cross_orders.txt';
    
    public function __construct()
    {
        parent::__construct();
        
        // статусы при которых открываются кросс-заявки
        $this->open_next_order_statuses = [
            $this->orders::STATUS_SIGNED,
            $this->orders::STATUS_PROCESS,
            $this->orders::STATUS_CONFIRMED,
        ];

        // статусы основной заявки при которых отменяем кросс-заявки
        $this->reject_statuses = [
            $this->orders::ORDER_1C_STATUS_REJECTED,
            $this->orders::ORDER_1C_STATUS_CLOSED,
            $this->orders::ORDER_1C_STATUS_REJECTED_TECH,
            $this->orders::ORDER_1C_STATUS_UNDEFINED,
        ];

        // статусы при которых отображаем в лк кросс-заявки
        $this->display_cross_statuses = [
            $this->orders::STATUS_APPROVED,
            $this->orders::STATUS_SIGNED,
            $this->orders::STATUS_PROCESS,
            $this->orders::STATUS_CONFIRMED,
            $this->orders::STATUS_NOT_ISSUED,
            $this->orders::STATUS_WAIT,
            $this->orders::STATUS_WAIT_CARD,
        ];
    }
    
    public function isAutoAccept($isSafetyFlow, $last_order)
    {
        return $last_order['have_close_credits'] 
            && !$isSafetyFlow 
            && (!in_array($last_order['status'], $this->open_next_order_statuses));
    }
    
    public function update_cross_orders($cross_orders, $last_order)
    {
        if (empty($cross_orders)) {
            return $cross_orders;
        }

        usort($cross_orders, function($a, $b){
            return $a['organization_id'] - $b['organization_id'];
        });
        
        if (in_array($last_order['1c_status'], $this->reject_statuses)) {
            foreach ($cross_orders as $key => $crossOrder) {
                if ((int)$crossOrder['status'] === $this->orders::STATUS_APPROVED) {
                    $system_manager = $this->get_system_manager();
                    $reject_reason = $this->get_reject_reason();
                    
                    $this->orders->update_order($crossOrder['id'], [
                        'status' => $this->orders::STATUS_REJECTED,
                        'reject_date' => date('Y-m-d H:i:s'),
                        'reason_id' => $reject_reason->id,
                        'manager_id' => $system_manager->id,
                    ]);

                    $this->logging(__METHOD__, '', 'Отказ кросс-ордера', ['crossOrder' => $crossOrder], self::LOG_FILE);

                    $this->soap->update_status_1c(
                        $crossOrder['1c_id'],
                        $this->orders::ORDER_1C_STATUS_REJECTED_FOR_SEND,
                        $system_manager->name_1c,
                        0,
                        $crossOrder['percent'],
                        $reject_reason->admin_name
                    );
                    
                    unset($cross_orders[$key]);
                }
            }
        }
        
        foreach ($cross_orders as $key => $crossOrder) {
            if (!in_array($crossOrder['status'], $this->display_cross_statuses)){
                unset($cross_orders[$key]);
            }
        }
        
        $prev_order = $last_order;
        foreach ($cross_orders as $key => $crossOrder) {
            if (!in_array($prev_order['status'], $this->open_next_order_statuses)) {
                $cross_orders[$key]['noactive'] = 1;
            }
            $prev_order = $crossOrder;
        }
        
        return $cross_orders;
    }
    
    private function get_system_manager()
    {
        if (empty($this->system_manager)) {
            $this->system_manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);
        }
        return $this->system_manager;
    }
    
    private function get_reject_reason()
    {
        if (empty($this->reject_reason)) {
            $this->reject_reason = $this->reasons->get_reason($this->reasons::REASON_EXPIRED_AUTO_APPROVE);
        }
        return $this->reject_reason;
        
    }
}