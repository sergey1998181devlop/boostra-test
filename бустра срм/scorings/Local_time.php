<?php

class Local_time extends Simpla
{
    private $user_id;
    private $order_id;
    private $audit_id;
    private $type;
    
    
    public function run_scoring($scoring_id)
    {
        $update = array();
        
    	$scoring_type = $this->scorings->get_type($this->scorings::TYPE_LOCAL_TIME);
        
        if ($scoring = $this->scorings->get_scoring($scoring_id))
        {
            if ($order = $this->orders->get_order((int)$scoring->order_id))
            {
                if (empty($order->local_time))
                {
                    $update = [
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'в заявке не указано локальное время'
                    ];
                }
                else
                {
                    $diff = abs(strtotime($order->date) - $order->local_time);
                
                    $score = $diff <= $scoring_type->params['max_diff'];
                
                    $update = [
                        'status' => $this->scorings::STATUS_COMPLETED,
                        'body' => serialize(['diff' => $diff]),
                        'success' => $score
                    ];
                    if ($score)
                        $update['string_result'] = 'Локальное время < '.$scoring_type->params['max_diff'].' c';
                    else
                        $update['string_result'] = 'Локальное время > '.$scoring_type->params['max_diff'].' c';

                }
                
            }
            else
            {
                $update = array(
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'не найдена заявка'
                );
            }
            
            if (!empty($update))
            {
                $update['end_date'] = date('Y-m-d H:i:s');
                $this->scorings->update_scoring($scoring_id, $update);
            }
            return $update;
        }
    }
    
    
    
    public function run($audit_id, $user_id, $order_id)
    {
        $this->user_id = $user_id;
        $this->audit_id = $audit_id;
        $this->order_id = $order_id;
//$this->orders->update_order(102465, array('local_time'=>time()));        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($audit_id, $user_id, $order_id);echo '</pre><hr />';
        $this->type = $this->scorings->get_type($this->scorings::TYPE_LOCAL_TIME);

        $order = $this->orders->get_order((int)$order_id);
        if (!empty($order->local_time))
            return $this->scoring($order->local_time, strtotime($order->date));
        
        return false;
    }

    private function scoring($local_time, $order_time)
    {
        $diff = abs($order_time - $local_time);
        
        $score = $diff <= $this->type->params['max_diff'];
        
        $add_scoring = array(
            'user_id' => $this->user_id,
            'audit_id' => $this->audit_id,
            'type' => $this->scorings::TYPE_LOCAL_TIME,
            'body' => 'local_time: '.$local_time.'; diff: '.$diff,
            'success' => (int)$score,
        );        
        
        if ($score)
        {
            $add_scoring['string_result'] = 'Локальное время < '.$this->type->params['max_diff'].' c';
        }
        else
        {
            $add_scoring['string_result'] = 'Локальное время > '.$this->type->params['max_diff'].' c';
        }

        $this->scorings->add_scoring($add_scoring);

        return $score;        
    }

}