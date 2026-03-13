<?php

require_once 'View.php';

class CCTasksView extends View
{
    public function fetch()
    {
    	if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'status':
                    
                    $task_id = $this->request->post('task_id', 'integer');
                    $status = $this->request->post('status', 'integer');
                    
                    $this->users->update_user_balance($task_id, array('cc_status'=>$status));
                    
                break;
                
            endswitch;
        }
        
        $filter = array();
        
        
        if (!($sort = $this->request->get('sort')))
        {
            $sort = 'payment_date_asc';
        }
        $filter['sort'] = $sort;
        $this->design->assign('sort', $sort);
        
        $filter['from'] = date('Y-m-d 00:00:00');
        $filter['to'] = date('Y-m-d 23:59:59');
        
        $tasks = array();
        $order_1c_ids = array();
        foreach ($this->users->get_cctasks($filter) as $t)
        {
            $tasks[$t->user_id] = $t;
            $order_1c_ids[] = (string)$t->zayavka;
        }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($tasks);echo '</pre><hr />';
//exit;
        if ($task_users = $this->users->get_users(array('id'=>array_keys($tasks), 'limit' => 3000)))
            foreach ($task_users as $task_user)
                $tasks[$task_user->id]->user = $task_user;

        $order_1c_ids = array_filter($order_1c_ids);
        if (!empty($order_1c_ids))
        {
            $orders = array();
            foreach ($this->orders->get_orders(array('id_1c' => $order_1c_ids)) as $o)
                $orders[$o->id_1c] = $o;
        }
        
        foreach ($tasks as $task)
        {
            if (isset($orders[$task->zayavka]))
                $task->order = $orders[$task->zayavka];
        }

        $this->design->assign('tasks', $tasks);

        $sms_templates = $this->sms->get_templates(array('type' => 'missing'));
        $this->design->assign('sms_templates', $sms_templates);
                
        return $this->design->fetch('cctasks.tpl');
    }
    
}