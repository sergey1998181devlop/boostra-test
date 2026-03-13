<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__).'/../api/Simpla.php';

class TehotkazCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    private function run()
    {
        $border_date = date('Y-m-d', time() - 86400 * 3);
        
    	$query = $this->db->placehold("
            SELECT 
                o.user_id,
                o.id, 
                o.1c_id AS id_1c,
                o.is_user_credit_doctor,
                o.status
            FROM __orders AS o
            WHERE status = 1
            AND DATE(o.date) <= ?
            AND DATE(o.date) >= ?
            LIMIT 500
        ", $border_date, '2021-03-13');
        $this->db->query($query);
        $results = $this->db->results();
        
        foreach ($results as $result)
        {
            $has_pay_credit_rating = $this->scorings->hasPayCreditRating((int)$result->user_id);
            $last_scorista_scoring = $this->scorings->get_last_scorista_for_user((int)$result->user_id, true);

            $user = $this->users->get_user((int)$result->user_id);

            if ((($has_pay_credit_rating && empty($last_scorista_scoring->scorista_id)) || empty($user->skip_credit_rating)) && !empty($user->accept_reject_orders)) {
                continue;
            }

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
                        if (!empty($result->is_user_credit_doctor))
                            $this->soap1c->send_credit_doctor($result->id_1c);
                        //$this->leadgid->reject_actions($result->id);
                        $this->virtualCard->forUser($result->user_id)->delete();
                    break;
                    
                    case 'Новая':
                    case '1.Рассматривается':
                        $this->orders->update_order($result->id, array('status' => 3, 'reason_id' => $this->reasons::REASON_END_TIME, '1c_status' => $response->Статус));
                        //$this->leadgid->reject_actions($result->id);
                        $resp = $this->soap->set_tehokaz($result->id_1c);
                        if (!empty($result->is_user_credit_doctor))
                            $this->soap1c->send_credit_doctor($result->id_1c);
                    break;
                    
                    case '6.Закрыт':
                    case '5.Выдан':
                    case '3.Одобрено':
                        $this->orders->update_order($result->id, array('status' => 2, '1c_status' => $response->Статус));                        
                    break;
                    
                endswitch;
            }
            
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';            
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($response);echo '</pre><hr />';            
            
//            
        }
        
    }
    
    
}
new TehotkazCron();