<?php

require_once('Simpla.php');

class Acado extends Simpla
{
    private $acado_organization;
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->init_organization();
    }
    
    
    public function create_order($user_id, $balance)
    {
        if ($balance['ИНН'] != $this->acado_organization->inn) {
            return false;
        }
        
        if ($this->contracts->get_contract_by_params(['number' => $balance['НомерЗайма']])) {
            return false;
        }
        
        if ($order_id = $this->create_new_order($balance, $user_id)) {
            $this->create_new_contract($balance, $user_id, $order_id);
        }
        
    }
    
    private function create_new_order($balance, $user_id)
    {
        $order_date = date('Y-m-d H:i:s', strtotime($balance['ДатаЗайма']));

        $new_order = [
            'user_id' => $user_id,
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'confirm_date' => $order_date,
            'approve_date' => $order_date,
            'closed' => 0,
            'date' => $order_date,
            'email' => '',
            'comment' => '',
            'status' => $this->orders::STATUS_CONFIRMED,
            'payment_details' => '',
            'ip' => '',
            'note' => '',
            'coupon_code' => '',
            'amount' => $balance['СуммаЗайма'],
            'period' => $balance['Срок'],
            'percent' => $balance['ПроцентнаяСтавка'],
            'sent_1c' => 9,
            'sms' => '',
            '1c_id' => $balance['Заявка'],
            'utm_source' => 'acado',
            'utm_medium' => '',
            'utm_campaign' => '',
            'utm_content' => '',
            'utm_term' => '',
            'click_hash' => '',
            'call_variants' => '',
            'credit_getted' => 1,
            'b2p' => 1,
            'order_uid' => $balance['УИД_Займ'],
            'complete' => 1,
            'organization_id' => $this->organizations::ACADO_ID,
        ];
        
        $order_id = $this->orders->add_order($new_order);
        
        return $order_id;
    }
    
    private function create_new_contract($balance, $user_id, $order_id)
    {
        $order_date = date('Y-m-d H:i:s', strtotime($balance['ДатаЗайма']));
        $payment_date = date('Y-m-d', strtotime($balance['ПланДата']));
        
        $user_uid = $this->users->get_user_uid($user_id);
         
        $new_contract = [
            'user_id' => $user_id,
            'user_uid' => $user_uid->uid,
            'order_id' => $order_id,
            'number' => $balance['НомерЗайма'],
            'amount' => $balance['СуммаЗайма'],
            'period' => $balance['Срок'],
            'payment_method' => 'IMPORT',
            'status' => 2,
            'base_percent' => $balance['ПроцентнаяСтавка'],
            'uid' => $balance['УИД_Займ'],
            'loan_body_summ' => $balance['ОстатокОД'],
            'loan_percents_summ' => $balance['ОстатокПроцентов'],
            'create_date' => $order_date,
            'confirm_date' => $order_date,
            'issuance_date' => $order_date,
            'return_date' => $payment_date,
            'organization_id' => $this->organizations->get_base_organization_id(['user_id' => $user_id]),
        ];
        
        if ($contract_id = $this->contracts->add_contract($new_contract)) {
            $this->orders->update_order($order_id, ['contract_id' => $contract_id]);
        }
        
        return $contract_id;
    }
    
    private function init_organization()
    {
        if (empty($this->acado_organization)) {
            $this->acado_organization = $this->organizations->get_organization($this->organizations::ACADO_ID);
        }
    }    
}