<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once __DIR__.'/../api/Simpla.php';

class FsspCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
    }
    
    public function run()
    {
    	$scoring_type = $this->scorings->get_type($this->scorings::TYPE_FSSP);

        $query = $this->db->placehold("
            UPDATE __scorings
            SET status = ?
            WHERE status = ?
            AND type = ?
        ", $this->scorings::STATUS_IMPORT, $this->scorings::STATUS_WAIT, $this->scorings::TYPE_FSSP);
        $this->db->query($query);
        
        // получаем результаты по созданным заданиям
        $i = 30;
        while ($i > 0)
        {
            if ($scoring = $this->scorings->get_import_scoring($this->scorings::TYPE_FSSP))
            {
                $result = $this->fssp->check_task($scoring->scorista_id);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($scoring->scorista_id, $result);echo '</pre><hr />';                
                
                if (isset($result->response->status) && !in_array($result->response->status, array(1, 2)))
                {
                    if ($resp = $this->fssp->get_task($scoring->scorista_id))
                    {
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($resp);echo '</pre><hr />';            
                        $debt = 0;
                        $pattern = '~([0-9.]*)\sруб~';
                        if (!empty($resp->response->result[0]->result))
                        {
                            foreach ($resp->response->result[0]->result as $item)
                            {
                                preg_match_all($pattern, $item->subject, $founds);
                                foreach ($founds[1] as $f)
                                    $debt += $f;
                            }
                        }
                        
                        $score = $debt < $scoring_type->params['amount'];
                        
                        $update = array(
                            'status' => $this->scorings::STATUS_COMPLETED,
                            'body' => serialize($resp),
                            'success' => $score,
                            'string_result' => 'Найденная сумма долга: '.$debt.' руб',
                            'end_date' => date('Y-m-d H:i:s'),
                        );

/*
                                        if (!empty($result->ip_end))
                                        {
                                            $ip_end = array_map('trim', explode(',', $result->ip_end));
                                            if (in_array(46, $ip_end))
                                                $scoring->found_46 = 1;
                                            if (in_array(47, $ip_end))
                                                $scoring->found_47 = 1;
                                        }
*/

                    }
                    else
                    {
                        $update = array(
                            'status' => $this->scorings::STATUS_ERROR,
                            'body' => serialize($error),
                            'string_result' => 'Не  удалось получить результат '.$scoring->scorista_id,
                            'end_date' => date('Y-m-d H:i:s'),
                        );
                        
                    }
                    
                    if (!empty($update))
                        $this->scorings->update_scoring($scoring->id, $update);
                }
                elseif (empty($result->response->status))
                {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'body' => serialize($result),
                        'string_result' => 'Не  удалось получить результат '.$scoring->scorista_id,
                        'end_date' => date('Y-m-d H:i:s'),
                    );
                    $this->scorings->update_scoring($scoring->id, $update);
                }
                else
                {
                    $this->scorings->update_scoring($scoring->id, array(
                        'status' => $this->scorings::STATUS_WAIT
                    ));
                }                
            }                  

            $i--;
        }
        
                
        // новые скоринги
        $i = 10;
        $scoring = 1;
        while ($i > 0 && !empty($scoring))
        {
            $update = array();
            
            if ($scoring = $this->scorings->get_new_scoring([$this->scorings::TYPE_FSSP]))
            {
                $this->scorings->update_scoring($scoring->id, array(
                    'status' => $this->scorings::STATUS_PROCESS,
                    'start_date' => date('Y-m-d H:i:s')
                ));
                
                if ($order = $this->orders->get_order((int)$scoring->order_id))
                {
                    if (empty($order->lastname) || empty($order->firstname) || empty($order->patronymic) || empty($order->Regregion) || empty($order->birth))
                    {
                        $update = array(
                            'status' => $this->scorings::STATUS_ERROR,
                            'string_result' => 'в заявке не достаточно данных для проведения скоринга'
                        );
                    }
                    else
                    {
    
                        $data = array(
                            'region' => $this->fssp->get_code($order->Regregion),
                            'lastname' => $order->lastname,
                            'firstname' => $order->firstname,
                            'secondname' => $order->patronymic,
                            'birthdate' => $order->birth,
                        );
    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($data);echo '</pre><hr />';                    
                        $task = $this->fssp->create_task($data);
    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($task);echo '</pre><hr />';
                        if (!empty($task->response->task))
                        {
                            
                            $update = array(
                                'status' => $this->scorings::STATUS_IMPORT,
                                'scorista_id' => $task->response->task,
                            );
                        }
                        else
                        {
                            $update = array(
                                'status' => $this->scorings::STATUS_ERROR,
                                'body' => serialize($task),
                                'string_result' => 'Не удалось создать запрос: '.$task->exception,
                            );
                        }
    
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
                    $this->scorings->update_scoring($scoring->id, $update);
                
    
            }

            $i--;
            sleep(1);
        }
    }
    
    
}

$cron = new FsspCron();
$cron->run();
