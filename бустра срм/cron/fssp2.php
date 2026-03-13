<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once __DIR__.'/../api/Simpla.php';

class Fssp2Cron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
    }
    
    public function run()
    {
    	$scoring_type = $this->scorings->get_type('fssp2');
        
        $this->db->query("
            UPDATE __scorings
            SET status = 'import'
            WHERE status = 'wait'
            AND type ='fssp2'
        ");
        
        
        $params = array(
            'status' => 'import',
            'type' => 'fssp2'
        );
        if ($import_scorings = $this->scorings->get_scorings($params))
        {
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($import_scorings);echo '</pre><hr />';            
            $requests = array();
            foreach ($import_scorings as $is)
            {
                if (!isset($requests[$is->scorista_id]))
                    $requests[$is->scorista_id] = array();
                $requests[$is->scorista_id][] = $is;
            }
        
            if (!empty($requests))
            {
                foreach ($requests as $r => $request_scorings)
                {
                    list($session_id, $token) = explode(':', $r);
                    
                    $task = $this->fssp2->get_task($session_id, $token);
                    if ($task->status == 'OK')
                    {
                        foreach ($task->data as $item)
                        {
                            $update = array(
                                'status' => 'completed',
                                'end_date' => date('Y-m-d H:i:s'),
                                'body' => serialize($item),
                            );                        

                            if (empty($item->results))
                            {
                                $update['success'] = 1;
                                $update['string_result'] = 'Производства не найдены';
                            }
                            else
                            {
                                $debt_total = 0;
                                $found_4647 = 0;
                                foreach ($item->results as $result)
                                {
                                    if (!empty($result->has_article_46_or_47))
                                        $found_4647 = 1;
                                    $debt_total += $result->debt_total;
                                }
                                
                                $score = empty($found_4647) && $debt_total < $scoring_type->params['amount'];
                                $string_result = 'Найденная сумма долга: '.$debt_total.' руб';
                                if ($found_4647)
                                    $string_result .= ' Найдены 46-47';
                                    
                                $update = array(
                                    'success' => $score,
                                    'string_result' => $string_result,
                                );
                            
                            }
                            
                            $scoring_id = $item->requested_data->scoring_id;
                            $this->scorings->update_scoring($scoring_id, $update);
                            
                        }
                        
                    }
                    else
                    {
                        $update = array(
                            'status' => 'wait'
                        );
                        foreach ($request_scorings as $rs)
                            $this->scorings->update_scoring($rs->id, $update);
    
                        
                    }
                
                }
            }
        }
        
        
        // новые скоринги
        $params = array(
            'type' => 'fssp2', 
            'status' => 'new',
            'limit' => 20,
        );
        if ($scorings = $this->scorings->get_scorings($params))
        {
            $data = array();
            foreach ($scorings as $scoring)
            {
                $update = array();
                if ($order = $this->orders->get_order((int)$scoring->order_id))
                {
                    if (empty($order->lastname) || empty($order->firstname) || empty($order->patronymic) || empty($order->Regregion) || empty($order->birth))
                    {
                        $update = array(
                            'status' => 'error',
                            'string_result' => 'в заявке не достаточно данных для проведения скоринга',
                        );
                    }
                    else
                    {
                        $item = (object)array(
                            'last_name' => $order->lastname,
                            'first_name' => $order->firstname,
                            'patronymic' => $order->patronymic,
                            'birthdate' => $order->birth,
                            'scoring_id' => $scoring->id,
                        );
                        $data[] = $item;
                    }                        
                }
                else
                {
                    $update = array(
                        'status' => 'error',
                        'string_result' => 'не найдена заявка'
                    );
                    
                }
                
                if (!empty($update))
                    $this->scorings->update_scoring($scoring->id, $update);
                
            }
            
            if (!empty($data))
            {
                $response = $this->fssp2->create_task($data);
                if (!empty($response['status']) && $response['status'] == 'OK')
                {
                    foreach ($scorings as $sc)
                    {
                        $this->scorings->update_scoring($sc->id, array(
                            'start_date' => date('Y-m-d H:i:s'),
                            'status' => 'import',
                            'scorista_id' => $response['session']['session_id'].':'.$response['session']['token'],
                        ));
                    }
                }
                
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($data, $response);echo '</pre><hr />';
            }
        }





    }
    
    
}

$cron = new Fssp2Cron();
$cron->run();
