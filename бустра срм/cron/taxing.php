<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', 12000);

require_once dirname(__FILE__).'/../api/Simpla.php';

/**
 * TaxingCron
 *
 * Скрипт производит начисление процентов, просрочек, пеней
 *
 */
class TaxingCron extends Simpla
{
    private $current_contract;
    private $current_date;
    private $percents_type_id;
    
    /**
     * TaxingCron::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->current_date = date('Y-m-d');
        
        $this->percents_type_id = $this->operations->get_operation_type_id('PERCENTS');

        $this->run();            

    }

    /**
     * TaxingCron::run()
     * 
     * @return void
     */
    private function run()
    {        
        while ($contracts = $this->get_contracts()) {
            foreach ($contracts as $contract) {
                
                $this->current_contract = $contract;
                                
                $this->check_penalty();

                $limit_percents = $this->get_limit_percents();
                $this->add_percents($limit_percents);
                
            }
        }
    }
    
    /**
     * TaxingCron::check_penalty()
     * Проверяет нужно ли начислять штраф
     * 
     * @return void
     */
    private function check_penalty()
    {
        $penalties = [
            9 => 2790,
            35 => 3790,
            80 => 4790,
        ];
        
        $current_datetime = new DateTime($this->current_date);
        $return_datetime = new DateTime($this->current_contract->return_date);
        $interval = $return_datetime->diff($current_datetime);
        $delay = $interval->format('%r%a');
        if (isset($penalties[$delay]))
            $this->add_penalty($penalties[$delay]);
            
    }
    
    /**
     * TaxingCron::add_penalty()
     * Начисляет штраф
     * 
     * @param mixed $penalty_amount
     * @return
     */
    private function add_penalty($penalty_amount)
    {
        $penalty_type_id = $this->operations->get_operation_type_id('PENALTY');
        
        $this->current_contract->loan_penalty_summ += $penalty_amount;
        
        $this->operations->add_operation([
            'user_id' => $this->current_contract->user_id,
            'order_id' => $this->current_contract->order_id,
            'contract_id' => $this->current_contract->id,
            'operation_type_id' => $penalty_type_id,
            'transaction_id' => 0,
            'operation_date' => $this->current_date,
            'create_date' => date('Y-m-d H:i:s'),
            'payment_date' => $this->current_contract->return_date,
            'amount' => $penalty_amount,
            'loan_body_summ' => $this->current_contract->loan_body_summ,
            'loan_percents_summ' => $this->current_contract->loan_percents_summ,
            'loan_penalty_summ' => $this->current_contract->loan_penalty_summ,
        ]);
    }
    
    /**
     * TaxingCron::add_percents()
     * начисляет проценты
     * 
     * @param int $limit_percents
     * @return void
     */
    private function add_percents($limit_percents)
    {
        if (empty($this->current_contract->grace_date))
        {
            $percents_summ = $this->get_standart_percents_summ();
        }
        else
        {
            $grace_datetime = new DateTime($this->current_contract->grace_date);
            $grace_datetime->add(DateInterval::createFromDateString('1 day'));
            $current_datetime = new DateTime($this->current_date);
            
            if ($current_datetime == $grace_datetime)
                $this->add_lost_percents();

            if ($current_datetime < $grace_datetime)
                $percents_summ = 0;
            else
                $percents_summ = $this->get_standart_percents_summ();
        }
        
        if ($limit_percents <= $percents_summ)
        {
            $percents_summ = min($limit_percents, $percents_summ);
            $this->contracts->update_contract($this->current_contract->id, ['stop_profit' => 1]);

        }
        
        $contract = $this->add_percent_operation($percents_summ);
    }
    
    /**
     * TaxingCron::add_lost_percents()
     * Начисляет проценты за прошлый период по просроченному льготному займу
     * 
     * @return void
     */
    private function add_lost_percents()
    {
        $percents_summ = $this->get_standart_percents_summ();
        $operation_date = new DateTime($this->current_contract->issuance_date);
        $current_datetime = new DateTime($this->current_date);
        $current_datetime->sub((DateInterval::createFromDateString('1 day')));
        
        do {
            $operation_date->add(DateInterval::createFromDateString('1 day'));
            
            $this->current_contract->loan_percents_summ += $percents_summ;
            
            $isset_operation = $this->operations->get_current_operation($this->current_contract->id, $operation_date->format('Y-m-d'), 'PERCENTS');
            if (!empty($isset_operation))
            {
                $this->operations->update_operation($isset_operation->id, [
                    'amount' => $percents_summ,
                    'loan_percents_summ' => $this->current_contract->loan_percents_summ,
                    'create_date' => date('Y-m-d H:i:s'),
                    'onec_sent' => 0,
                ]);
            }
            else
            {
                $this->operations->add_operation([
                    'user_id' => $this->current_contract->user_id,
                    'order_id' => $this->current_contract->order_id,
                    'contract_id' => $this->current_contract->id,
                    'operation_type_id' => $this->percents_type_id,
                    'transaction_id' => 0,
                    'operation_date' => $operation_date->format('Y-m-d'),
                    'create_date' => date('Y-m-d H:i:s'),
                    'payment_date' => $this->current_contract->return_date,
                    'amount' => $percents_summ,
                    'loan_body_summ' => $this->current_contract->loan_body_summ,
                    'loan_percents_summ' => $this->current_contract->loan_percents_summ,
                    'loan_penalty_summ' => $this->current_contract->loan_penalty_summ,
                ]);
                
            }

            $this->contracts->update_contract($this->current_contract->id, [
                'loan_percents_summ' => $this->current_contract->loan_percents_summ
            ]);
            
        } while ($operation_date < $current_datetime);
    }
    
    /**
     * TaxingCron::get_standart_percents_summ()
     * Возврашает сумму стандартных процентов за день
     * 
     * @return double
     */
    private function get_standart_percents_summ()
    {
        return round($this->current_contract->loan_body_summ / 100 * $this->current_contract->base_percent, 2);
    }
    
    /**
     * TaxingCron::add_percent_operation()
     * Создает операцию начисления процентов
     * 
     * @param double $percents_summ
     * @return void
     */
    private function add_percent_operation($percents_summ)
    {
        $this->current_contract->loan_percents_summ = $this->current_contract->loan_percents_summ + $percents_summ;
        
        $this->operations->add_operation([
            'user_id' => $this->current_contract->user_id,
            'order_id' => $this->current_contract->order_id,
            'contract_id' => $this->current_contract->id,
            'operation_type_id' => $this->percents_type_id,
            'transaction_id' => 0,
            'create_date' => date('Y-m-d H:i:s'),
            'operation_date' => date('Y-m-d', strtotime($this->current_date)),
            'payment_date' => $this->current_contract->return_date,
            'amount' => $percents_summ,
            'loan_body_summ' => $this->current_contract->loan_body_summ,
            'loan_percents_summ' => $this->current_contract->loan_percents_summ,
            'loan_penalty_summ' => $this->current_contract->loan_penalty_summ,
        ]);
        
        $this->contracts->update_contract($this->current_contract->id, [
            'loan_percents_summ' => $this->current_contract->loan_percents_summ
        ]);
    }
    
    /**
     * TaxingCron::get_limit_percents()
     * Проверяет достижение порога по займу
     * 
     * @return double
     */
    private function get_limit_percents()
    {
        $added_percents = $this->operations->get_added_percents($this->current_contract->id);
        $max_added_percents = $this->current_contract->amount * 1.5;

        return round($max_added_percents - $added_percents, 2);

    }
    
    /**
     * TaxingCron::get_contracts()
     * Выбирает из базы пакет договоров для начисления процентов
     * 
     * @return array
     */
    private function get_contracts()
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __contracts as c
            WHERE c.loan_body_summ > 0
            AND DATE(c.issuance_date) < ?
            AND c.stop_profit = 0
            AND c.status IN (2, 4)
            AND c.id NOT IN(
                SELECT contract_id 
                FROM __operations
                WHERE operation_date = ?
                AND operation_type_id = ?
            )
            LIMIT 100
        ", $this->current_date, $this->current_date, $this->percents_type_id);
        $this->db->query($query);

        $results = $this->db->results();
        return $results;
    }
    
}

new TaxingCron();