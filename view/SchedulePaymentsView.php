<?php

require_once 'View.php';

class SchedulePaymentsView extends View
{
    public function fetch()
    {
        if (!$this->user->id) {
            return false;
        }
        $orders = $this->orders->get_orders(['user_id' => $this->user->id, 'loan_type' => $this->orders::LOAN_TYPE_IL, 'status_1c' => $this->orders::ORDER_1C_STATUS_CONFIRMED]);

        $resultOrders = [];

        foreach ($orders as $order) {
            if (!($contract = $this->contracts->get_contract_by_params(['order_id' => $order->id]))) {
                continue;
            }

            $schedule_payments = $this->soap->get_schedule_payments($contract->number);
            $schedule_payments = (array)end($schedule_payments);
            $schedule_payments['Платежи'] = array_map(static function ($var) {
                return (array)$var;
            }, $schedule_payments['Платежи']);

            $resultOrders[$contract->number]['contract'] = $contract;
            $resultOrders[$contract->number]['Платежи'] = $schedule_payments;
        }

        $this->design->assign('result_orders', $resultOrders);
        
        return $this->design->fetch('installment/schedule_payments.tpl');
    }
}
