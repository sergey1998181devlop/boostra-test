<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__).'/../api/Simpla.php';

class UpdateOldOrdersCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    private function run()
    {
        $border_date = date('Y-m-d', time() - 86400 * 7);
        
    	$query = $this->db->placehold("
            SELECT
            id, 
            1c_id AS id_1c,
            is_user_credit_doctor,
            status
            FROM `s_orders`
            where status = 2 
            AND confirm_date IS NULL 
            AND 1c_status != '5.Выдан' 
            AND 1c_status != '6.Закрыт'  
            and DATE(date) <= ?
            LIMIT 300
        ", $border_date);
        $this->db->query($query);
        $results = $this->db->results();
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump(count($results));echo '</pre><hr />';  
        //exit;
        foreach ($results as $result)
        {
            $response = $this->soap->check_order_1c($result->id_1c);
            
            if (empty($response->Статус))
            {
                $this->orders->update_order($result->id, array('status' => 3, 'reason_id' => $this->reasons::REASON_END_TIME));
                //$this->leadgid->reject_actions($result->id);
            }
            else
            {
                switch ($response->Статус):
                    
                    case 'Не определено':
                    case '2.Отказано':
                    case '7.Технический отказ':
                        $this->orders->update_order($result->id, array('status' => 3, 'reason_id' => $this->reasons::REASON_END_TIME, '1c_status' => $response->Статус));
                        $this->virtualCard->forUser($result->user_id)->delete();
                    break;
                    
                    case 'Новая':
                    case '1.Рассматривается':
                        $this->orders->update_order($result->id, array('status' => 3, 'reason_id' => $this->reasons::REASON_END_TIME, '1c_status' => $response->Статус));
                        $resp = $this->soap->set_tehokaz($result->id_1c);
                        if ($result->is_user_credit_doctor)
                            $this->soap1c->send_credit_doctor($result->id_1c);
                    break;
                    
                endswitch;
            }
            
            //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';            
            echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($response);echo '</pre><hr />';   
        }
    }
}

new UpdateOldOrdersCron();