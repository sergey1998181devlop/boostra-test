<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir(dirname(__FILE__).'/../');
require_once 'api/Simpla.php';

/**
 * ExchangeDop1cCron
 * 
 * Скрипт отправляет в доповую 1с доп.услуги, возвраты доп услуг

Типы:
Мультиполис	multipolis
Кредитный доктор	credit_doctor
Кредитный рейтинг	credit_rating
Штрафной кредитный доктор	shtraf_credit_doctor
Страховка	insurance
Телемедицина	telemedicine


 */
class ExchangeDop1cCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    private function run()
    {    
        $this->send('send_multipolis');
        $this->send('send_return_multipolis');
        $this->send('send_tv_medical');
        $this->send('send_return_tv_medical');
        $this->send('send_credit_doctor');
        $this->send('send_return_credit_doctor');
        $this->send('send_penalty_credit_doctor');
        $this->send('send_credit_rating');
    }
    
    private function send($methodname)
    {
        $i = 1;
        do {
            $run_result = $this->$methodname();
            $i--;
        } while ($i > 0 && !empty($run_result));
    }
    
    private function send_multipolis()
    {
        if ($units = $this->get_units_for_send_multipolis()) {
            foreach ($units as $unit) {

                $payment = $this->best2pay->get_payment($unit->payment_id);
                
                $item = new StdClass();
                $item->user = $this->users->get_user($unit->user_id);
                $item->data = new StdClass();

                $item->data->operation_date = $payment->operation_date;
                $item->data->contract_number = $payment->contract_number;
                $item->data->amount = $unit->amount;
                $item->data->type = 'multipolis';
                $item->data->complect = '';
                $item->data->service_number = $unit->number;
                $item->data->number = 'MP-'.$unit->id;
                $item->data->b2p_order = $payment->register_id;
                $item->data->b2p_operation = $payment->operation_id;
                $item->data->b2p_sector = $payment->sector;
                $item->data->card_pan = $payment->card_pan;            
                // TODO: сделать организации нормально
                if ($unit->organization_id == 1) {
                    $item->data->provider_inn = '2902083979';
                    $item->data->agent = 1;
                } else {
                    $organization = $this->organizations->get_organization($unit->organization_id);
                    $item->data->provider_inn = $organization->inn;
                    $item->data->agent = $organization->agent;
                }
                
                $res = $this->dop1c->send_service($item);
                if ($res->return == 'OK') {
                    $this->multipolis->update_multipolis($unit->id, ['dop1c_sent' => 2]);
                } else {
                    $this->multipolis->update_multipolis($unit->id, ['dop1c_sent' => 3]);                   
                }
            }
        }
        
        return $units;
    }

    private function send_return_multipolis()
    {
        if ($units = $this->get_units_for_send_return_multipolis()) {
            foreach ($units as $unit) {
                $payment = $this->best2pay->get_payment($unit->payment_id);
                $return_transaction = $this->best2pay->get_transaction($unit->return_transaction_id);
                
                $item = new StdClass();
                $item->user = $this->users->get_user($unit->user_id);
                $item->data = new StdClass();
                
                $item->data->operation_date = $return_transaction->operation_date;
                $item->data->contract_number = $payment->contract_number;
                $item->data->amount = $return_transaction->amount / 100;
                $item->data->type = 'multipolis';
                $item->data->complect = ''; 
                $item->data->b2p_order = $payment->register_id;
                $item->data->b2p_operation = $payment->operation_id;
                $item->data->b2p_sector = $return_transaction->sector;
                $item->data->return_b2p_order = $return_transaction->register_id;
                $item->data->return_b2p_operation = $return_transaction->operation;
                $item->data->service_number = $unit->number;
                $item->data->number = 'RMP-'.$return_transaction->id;
                $item->data->sale_number = 'MP-'.$unit->id;
                $item->data->card_pan = $return_transaction->card_pan;
                // TODO: сделать организации нормально
                if ($unit->organization_id == 1) {
                    $item->data->provider_inn = '2902083979';
                    $item->data->agent = 1;
                } else {
                    $organization = $this->organizations->get_organization($unit->organization_id);
                    $item->data->provider_inn = $organization->inn;
                    $item->data->agent = $organization->agent;
                }
                
                $res = $this->dop1c->send_return_service($item);

                if ($res->return == 'OK') {
                    $this->multipolis->update_multipolis($unit->id, ['dop1c_sent_return' => 2]);
                } else {
                    $this->multipolis->update_multipolis($unit->id, ['dop1c_sent_return' => 3]);                   
                }
            }
            
        }
    }
    
    private function send_tv_medical()
    {
        if ($units = $this->get_units_for_send_tv_medical()) {
            $tariffs = [];
            foreach ($this->tv_medical->getAllTariffs() as $t) {
                $tariffs[$t->id] = $t;
            }
            $organizations = [];
            foreach ($this->organizations->getList() as $o) {
                $organizations[$o->id] = $o;
            }
            
            foreach ($units as $unit) {

                $payment = $this->best2pay->get_payment($unit->payment_id);
                
                $item = new StdClass();
                $item->user = $this->users->get_user($payment->user_id);
                $item->data = new StdClass();

                $item->data->operation_date = $payment->operation_date;
                $item->data->contract_number = $payment->contract_number;
                $item->data->amount = $unit->amount;
                $item->data->type = 'telemedicine';
                $item->data->complect = $tariffs[$unit->tv_medical_id]->name; 
                $item->data->service_number = '';
                $item->data->number = 'TM-'.$unit->id;
                $item->data->b2p_order = $payment->register_id;
                $item->data->b2p_operation = $payment->operation_id;
                $item->data->b2p_sector = $payment->sector;
                $item->data->card_pan = $payment->card_pan;            
                // TODO: сделать организации нормально
                if ($unit->organization_id == 1) {
                    $item->data->provider_inn = '9717034533';
                    $item->data->agent = 1;
                } else {
                    $item->data->provider_inn = $organizations[$unit->organization_id]->inn;
                    $item->data->agent = $organizations[$unit->organization_id]->agent;
                }
                
                $res = $this->dop1c->send_service($item);                

                if ($res->return == 'OK') {
                    $this->tv_medical->updatePayment((int)$unit->id, ['dop1c_sent' => 2]);
                } else {
                    $this->tv_medical->updatePayment((int)$unit->id, ['dop1c_sent' => 3]);                   
                }
            }
        }
        
        return $units;
    }

    private function send_return_tv_medical()
    {
        if ($units = $this->get_units_for_send_return_tv_medical()) {
            $tariffs = [];
            foreach ($this->tv_medical->getAllTariffs() as $t) {
                $tariffs[$t->id] = $t;
            }
            $organizations = [];
            foreach ($this->organizations->getList() as $o) {
                $organizations[$o->id] = $o;
            }
            
            foreach ($units as $unit) {
                $payment = $this->best2pay->get_payment($unit->payment_id);
                $return_transaction = $this->best2pay->get_transaction($unit->return_transaction_id);
                
                $item = new StdClass();
                $item->user = $this->users->get_user($payment->user_id);
                $item->data = new StdClass();
                
                $item->data->operation_date = $return_transaction->operation_date;
                $item->data->contract_number = $payment->contract_number;
                $item->data->amount = $return_transaction->amount / 100;
                $item->data->type = 'telemedicine';
                $item->data->complect = $tariffs[$unit->tv_medical_id]->name; 
                $item->data->b2p_order = $payment->register_id;
                $item->data->b2p_operation = $payment->operation_id;
                $item->data->b2p_sector = $return_transaction->sector;
                $item->data->return_b2p_order = $return_transaction->register_id;
                $item->data->return_b2p_operation = $return_transaction->operation;
                $item->data->service_number = '';
                $item->data->number = 'RTM-'.$return_transaction->id;
                $item->data->sale_number = 'TM-'.$unit->id;
                $item->data->card_pan = $return_transaction->card_pan;
                // TODO: сделать организации нормально
                if ($unit->organization_id == 1) {
                    $item->data->provider_inn = '9717034533';
                    $item->data->agent = 1;
                } else {
                    $item->data->provider_inn = $organizations[$unit->organization_id]->inn;
                    $item->data->agent = $organizations[$unit->organization_id]->agent;
                }
                
                $res = $this->dop1c->send_return_service($item);

                if ($res->return == 'OK') {
                    $this->tv_medical->updatePayment((int)$unit->id, ['dop1c_sent_return' => 2]);
                } else {
                    $this->tv_medical->updatePayment((int)$unit->id, ['dop1c_sent_return' => 3]);                   
                }
            }
            
        }
    }
    
    private function send_credit_doctor()
    {
        if ($units = $this->get_units_for_send_credit_doctor()) {
            foreach ($units as $unit) {

                $transaction = $this->best2pay->get_transaction($unit->transaction_id);
                
                $item = new StdClass();
                $item->user = $this->users->get_user($transaction->user_id);
                $item->data = new StdClass();

                $item->data->operation_date = $transaction->operation_date;
                $item->data->contract_number = $transaction->contract_number;
                $item->data->amount = $unit->amount;
                $item->data->type = 'credit_doctor';
                $item->data->complect = 'Комплект '.$unit->credit_doctor_condition_id; 
                $item->data->service_number = '';
                $item->data->number = 'CD-'.$unit->id;
                $item->data->b2p_order = $transaction->register_id;
                $item->data->b2p_operation = $transaction->operation;
                $item->data->b2p_sector = $transaction->sector;
                $item->data->card_pan = $transaction->card_pan;            
                // TODO: сделать организации нормально
                if ($unit->organization_id == 1) {
                    $item->data->provider_inn = '2902090888';
                    $item->data->agent = 1;
                } else {
                    $organization = $this->organizations->get_organization($unit->organization_id);
                    $item->data->provider_inn = $organization->inn;
                    $item->data->agent = $organization->agent;
                }
                
                $res = $this->dop1c->send_service($item);                

                if ($res->return == 'OK') {
                    $this->credit_doctor->updateUserCreditDoctorData((int)$unit->id, ['dop1c_sent' => 2]);
                } else {
                    $this->credit_doctor->updateUserCreditDoctorData((int)$unit->id, ['dop1c_sent' => 3]);                   
                }
            }
        }
        
        return $units;
    }

    private function send_return_credit_doctor()
    {
        if ($units = $this->get_units_for_send_return_credit_doctor()) {
            $tariffs = [];
            foreach ($this->tv_medical->getAllTariffs() as $t) {
                $tariffs[$t->id] = $t;
            }
            $organizations = [];
            foreach ($this->organizations->getList() as $o) {
                $organizations[$o->id] = $o;
            }
            
            foreach ($units as $unit) {
                $transaction = $this->best2pay->get_transaction($unit->transaction_id);
                $return_transaction = $this->best2pay->get_transaction($unit->return_transaction_id);
                
                $item = new StdClass();
                $item->user = $this->users->get_user($transaction->user_id);
                $item->data = new StdClass();
                
                $item->data->operation_date = $return_transaction->operation_date;
                $item->data->contract_number = $transaction->contract_number;
                $item->data->amount = $return_transaction->amount / 100;
                $item->data->type = 'credit_doctor';
                $item->data->complect = 'Комплект '.$unit->credit_doctor_condition_id; 
                $item->data->b2p_order = $transaction->register_id;
                $item->data->b2p_operation = $transaction->operation;
                $item->data->b2p_sector = $return_transaction->sector;
                $item->data->return_b2p_order = $return_transaction->register_id;
                $item->data->return_b2p_operation = $return_transaction->operation;
                $item->data->service_number = '';
                $item->data->number = 'RCD-'.$return_transaction->id;
                $item->data->sale_number = 'CD-'.$unit->id;
                $item->data->card_pan = $return_transaction->card_pan;
                // TODO: сделать организации нормально
                if ($unit->organization_id == 1) {
                    $item->data->provider_inn = '2902090888';
                    $item->data->agent = 1;
                } else {
                    $item->data->provider_inn = $organizations[$unit->organization_id]->inn;
                    $item->data->agent = $organizations[$unit->organization_id]->agent;
                }
                
                $res = $this->dop1c->send_return_service($item);

                if ($res->return == 'OK') {
                    $this->credit_doctor->updateUserCreditDoctorData((int)$unit->id, ['dop1c_sent_return' => 2]);
                } else {
                    $this->credit_doctor->updateUserCreditDoctorData((int)$unit->id, ['dop1c_sent_return' => 3]);                   
                }
            }
            
        }
    }
    
    private function send_penalty_credit_doctor()
    {
        if ($units = $this->get_units_for_send_penalty_credit_doctor()) {
            foreach ($units as $unit) {

                $item = new StdClass();
                $item->user = $this->users->get_user($unit->user_id);
                $item->data = new StdClass();

                $item->data->operation_date = $unit->operation_date;
                $item->data->contract_number = $unit->contract_number;
                $item->data->amount = $unit->insure;
                $item->data->type = 'shtraf_credit_doctor';
                $item->data->complect = 'Комплект 99'; 
                $item->data->service_number = '';
                $item->data->number = 'PCD-'.$unit->id;
                $item->data->b2p_order = $unit->register_id;
                $item->data->b2p_operation = $unit->operation_id;
                $item->data->b2p_sector = $unit->sector;
                $item->data->card_pan = $unit->card_pan;            
                $item->data->provider_inn = '2902090888';
                $item->data->agent = 1;
                
                $res = $this->dop1c->send_service($item);                

                if ($res->return == 'OK') {
                    $this->best2pay->update_payment((int)$unit->id, ['dop1c_sent' => 2]);
                } else {
                    $this->best2pay->update_payment((int)$unit->id, ['dop1c_sent' => 3]);                   
                }
            }
        }
        
        return $units;
    }

    private function send_credit_rating()
    {
        if ($units = $this->get_units_for_send_credit_rating()) {
            foreach ($units as $unit) {

                $item = new StdClass();
                $item->user = $this->users->get_user($unit->user_id);
                $item->data = new StdClass();

                $item->data->operation_date = $unit->operation_date;
                $item->data->contract_number = $unit->contract_number;
                $item->data->amount = $unit->amount;
                $item->data->type = 'credit_rating';
                $item->data->complect = ''; 
                $item->data->service_number = '';
                $item->data->number = 'CR-'.$unit->id;
                $item->data->b2p_order = $unit->register_id;
                $item->data->b2p_operation = $unit->operation_id;
                $item->data->b2p_sector = $unit->sector;
                $item->data->card_pan = $unit->card_pan;            
                $item->data->provider_inn = '6317102210';
                $item->data->agent = 0;
                
                $res = $this->dop1c->send_service($item);                

                if ($res->return == 'OK') {
                    $this->best2pay->update_payment((int)$unit->id, ['dop1c_sent' => 2]);
                } else {
                    $this->best2pay->update_payment((int)$unit->id, ['dop1c_sent' => 3]);                   
                }
            }
        }
        
        return $units;
    }
    
    
    private function get_units_for_send_multipolis()
    {
        $this->db->query("
            SELECT * 
            FROM s_multipolis
            WHERE status = 'SUCCESS'
            AND dop1c_sent = 0
AND id = 193017
            ORDER BY id DESC
            LIMIT 10
        ");
        return $this->db->results();
    }
    
    private function get_units_for_send_return_multipolis()
    {
        $this->db->query("
            SELECT * 
            FROM s_multipolis
            WHERE status = 'SUCCESS'
            AND dop1c_sent_return = 0
AND id = 193017
            ORDER BY id DESC
            LIMIT 10
        ");
        return $this->db->results();
    }

    private function get_units_for_send_tv_medical()
    {
        $this->db->query("
            SELECT * 
            FROM s_tv_medical_payments
            WHERE status = 'SUCCESS'
            AND dop1c_sent = 0
AND id = 170969
            ORDER BY id DESC
            LIMIT 10
        ");
        return $this->db->results();
    }
    
    private function get_units_for_send_return_tv_medical()
    {
        $this->db->query("
            SELECT * 
            FROM s_tv_medical_payments
            WHERE status = 'SUCCESS'
            AND dop1c_sent_return = 0
AND id = 170969
            ORDER BY id DESC
            LIMIT 10
        ");
        return $this->db->results();
    }
    
    private function get_units_for_send_credit_doctor()
    {
        $this->db->query("
            SELECT * 
            FROM s_credit_doctor_to_user
            WHERE status = 'SUCCESS'
            AND dop1c_sent = 0
AND id = 170969
            ORDER BY id DESC
            LIMIT 10
        ");
        return $this->db->results();
    }
    
    private function get_units_for_send_return_credit_doctor()
    {
        $this->db->query("
            SELECT * 
            FROM s_credit_doctor_to_user
            WHERE status = 'SUCCESS'
            AND dop1c_sent_return = 0
AND id = 225680
            ORDER BY id DESC
            LIMIT 10
        ");
        return $this->db->results();
    }
    
    private function get_units_for_send_penalty_credit_doctor()
    {
        $this->db->query("
            SELECT * 
            FROM b2p_payments
            WHERE reason_code = 1
            AND payment_type = 'debt'
            AND insure > 0
            AND dop1c_sent = 0
AND id = 1551995
            ORDER BY id DESC
            LIMIT 10
        ");
        return $this->db->results();
    }
    
    private function get_units_for_send_credit_rating()
    {
        $this->db->query("
            SELECT * 
            FROM b2p_payments
            WHERE reason_code = 1
            AND payment_type IN (?@)
            AND dop1c_sent = 0
AND id = 1547932
            ORDER BY id DESC
            LIMIT 10
        ", $this->best2pay::PAYMENT_TYPE_CREDIT_RATING_MAPPING_ALL);
        return $this->db->results();
    }
    
}

$cron = new ExchangeDop1cCron();