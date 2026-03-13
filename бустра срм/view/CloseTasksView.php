<?php

require_once 'View.php';

class CloseTasksView extends View
{
    public function fetch()
    {
        $items_per_page = 20;
        
        $offer_types = array(
            'percents' => array('0.0', '0.1', '0.2', '0.3', '0.4', '0.5', '0.6', '0.7', '0.8', '0.9'),
            'amount' => array(1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000),
            'insure' => array(0, 5, 10, 15, 20, 25, 30),
        );
        
        $this->design->assign('offer_types', $offer_types);
        
    	if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'manager':
                    
                    $task_id = $this->request->post('task_id', 'integer');
                    $manager_id = $this->request->post('manager_id', 'integer');
                    
                    $this->tasks->update_close_task($task_id, array('manager_id'=>$manager_id));
                    
                break;
                
                case 'status':
                    
                    $task_id = $this->request->post('task_id', 'integer');
                    $status = $this->request->post('status', 'integer');
                    
                    $this->tasks->update_close_task($task_id, array('status'=>$status));
                    
                break;
                
                case 'add_perspective':
                    
                    $task_id = $this->request->post('task_id', 'integer');
                    $perspective_date = date('Y-m-d H:i:s', strtotime($this->request->post('perspective_date')));
                    $status = 3;
                    
                    $this->tasks->update_close_task($task_id, array(
                        'status' => $status, 
                        'perspective_date' => $perspective_date
                    ));
                    
                    if ($text = $this->request->post('text'))
                    {
                        if ($task = $this->tasks->get_close_task($task_id))
                        {
                            $comment = array(
                                'manager_id' => $this->manager->id,
                                'user_id' => $task->user_id,
                                'order_id' => $task->order_id,
                                'block' => 'close_task',
                                'text' => $text,
                                'created' => date('Y-m-d H:i:s'),
                            );
                            $comment_id = $this->comments->add_comment($comment);

                            if ($order = $this->orders->get_order($task->order_id))
                            {
                                $this->soap->send_comment(array(
                                    'manager' => $this->manager->name_1c,
                                    'text' => $text,
                                    'created' => date('Y-m-d H:i:s'),
                                    'number' => $order->id_1c
                                ));
                                
                            }
                        }
                    }
                break;
                
                case 'add_recall':
                    
                    $task_id = $this->request->post('task_id', 'integer');
                    $recall_date = date('Y-m-d H:i:s', strtotime($this->request->post('recall_date')));
                    $status = 1;                    
                    
                    $this->tasks->update_close_task($task_id, array(
                        'status' => $status,
                        'recall_date' => $recall_date,
                    ));
                    
                break;
                
                case 'add_offer':
                    
                    $task_id = $this->request->post('task_id', 'integer');
                    $end_date = $this->request->post('end_date');
                    $type = $this->request->post('type');
                    $value = $this->request->post('value');
                    
                    if ($task = $this->tasks->get_close_task($task_id))
                    {
                        $offer = array(
                            'close_task_id' => $task_id,
                            'user_id' => $task->user_id,
                            'manager_id' => $this->manager->id,
                            'created' => date('Y-m-d H:i:s'),
                            'type' => $type,
                            'value' => $value,                            
                            'end_date' => date('Y-m-d H:i:s', strtotime($end_date)),
                            'used' => 0,
                        );
                        
                        if ($offer_id = $this->users->add_offer($offer))
                        {
                            $this->json_output(array('success'=>'1', 'offer'=>$offer));
                        }
                        else
                        {
                            $this->json_output(array('error'=>'не удалось создать оффер', 'offer'=>$offer));
                        }
                    }
                    else
                    {
                        $this->json_output(array('error'=>'не найдена задача', 'task_id'=>$task_id));
                    }
                    
                break;
                
            endswitch;
        }
        

        $filter = array();
        
        
        if (!($sort = $this->request->get('sort')))
            $sort = 'timezone_desc';
        $this->design->assign('sort', $sort);        
        $filter['sort'] = $sort;
        
        if (!($filter_type = $this->request->get('type')))
            $filter_type = 'nk';
        
        if ($filter_type == 'nk')
            $filter['pk'] = 0;
        if ($filter_type == 'pk')
            $filter['pk'] = 1;
        
        $this->design->assign('filter_type', $filter_type);
        
        if ($search = $this->request->get('search'))
        {
            $filter['search'] = array_filter($search, function($var){
                return !is_null($var) && $var != '';
            });
            $this->design->assign('search', $filter['search']);
        }
        
		$current_page = $this->request->get('page', 'integer');
		$current_page = max(1, $current_page);
		$this->design->assign('current_page_num', $current_page);

		$task_count = $this->tasks->count_close_tasks($filter);
		
		$pages_num = ceil($task_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $task_count);

		$filter['page'] = $current_page;
		$filter['limit'] = $items_per_page;
        
        
        
        $user_ids = array();
        $tasks = array();
        foreach ($this->tasks->get_close_tasks($filter) as $task)
        {
            $user_ids[] = $task->user_id;
            $tasks[$task->id] = $task;
        }
        
        $users = array();
        if (!empty($user_ids))
        {
            foreach ($this->users->get_users(array('id'=>$user_ids)) as $u)
                $users[$u->id] = $u;
        }
        
        foreach ($tasks as $t)
        {
            if (isset($users[$t->user_id]))
                $t->user = $users[$t->user_id];
        }
        
        foreach ($this->users->get_offers(array('task_id'=>array_keys($tasks))) as $off)
        {
            $tasks[$off->close_task_id]->offer = $off;
        }
        
        $this->design->assign('close_tasks', $tasks);

        $sms_templates = $this->sms->get_templates(array('type' => 'missing'));
        $this->design->assign('sms_templates', $sms_templates);
                
        return $this->design->fetch('close_tasks.tpl');
    }
    
}