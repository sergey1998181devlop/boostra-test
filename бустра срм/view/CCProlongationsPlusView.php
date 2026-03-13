<?php

require_once 'View.php';

class CCProlongationsPlusView extends View
{
    public function fetch()
    {
        $filter = array();
        if ($this->request->method('post'))
        {

                $filter['from']  = date('Y-m-d', time() - 86400*2);

                $filter['to'] = date('Y-m-d', time() - 86400);

            switch ($this->request->post('action', 'string')):

                case 'status':

                    $task_id = $this->request->post('task_id', 'integer');
                    $status = $this->request->post('status', 'integer');

                    $this->tasks->update_pr_task($task_id, array('status'=>$status));

                    if($status == 4){

                        $taskData =  $this->users->get_users_ccprolongations(['task_id'=>$task_id,'date' => date("Y-m-d")]);

                        $company = $this->managers->getCompany((int)$taskData[0]->manager_id);
                        $voximplant = new Voximplant();
                        $voximplant->sendDnc($company,["'".$taskData[0]->phone."'"]);
                    }

                break;

                case 'add_perspective':

                    $task_id = $this->request->post('task_id', 'integer');
                    $perspective_date = date('Y-m-d H:i:s', strtotime($this->request->post('perspective_date')));
                    $status = 3;
                    $zaimNumber = $this->tasks->get_pr_task($task_id)->number;
                    $vox_call = false;
                    $get_asp = $this->users->getZaimListAsp($zaimNumber);
                    if (empty($get_asp)) {
                        $vox_call = true;
                    }
                    $this->tasks->update_pr_task($task_id, array(
                        'status' => $status,
                        'perspective_date' => $perspective_date,
                        'vox_call' => $vox_call
                    ));

                    $taskData =  $this->users->get_users_ccprolongations(['task_id'=>$task_id,'date' => date("Y-m-d")]);

                    $company = $this->managers->getCompany((int)$taskData[0]->manager_id);
                    $voximplant = new Voximplant();
                    $voximplant->sendDnc($company,["'".$taskData[0]->phone."'"]);


                    if ($text = $this->request->post('text'))
                    {
                        if ($task = $this->tasks->get_pr_task($task_id))
                        {
                            $balance = $this->users->get_user_balance($task->user_id);
                            if (!empty($balance->zayavka) && ($order_id = $this->orders->get_order_1cid($balance->zayavka)))
                            {
                                $order_id_1c = $balance->zayavka;
                            }
                            elseif ($order = $this->orders->get_user_last_order($user_id))
                            {
                                $order = (array)$order;

                                $order_id_1c = $order['1c_id'];
                                $order_id = $order['id'];
                            }

                            $comment = array(
                                'manager_id' => $this->manager->id,
                                'user_id' => $task->user_id,
                                'order_id' => $order_id,
                                'block' => 'cc_prolongation',
                                'text' => $text,
                                'created' => date('Y-m-d H:i:s'),
                            );

                            if ($comment_id = $this->comments->add_comment($comment))
                            {
                                $this->soap->send_comment(array(
                                    'manager' => $this->manager->name_1c,
                                    'text' => $text,
                                    'created' => date('Y-m-d H:i:s'),
                                    'number' => $order_id_1c
                                ));
                            }
                        }
                    }
                break;

                case 'add_recall':

                    $task_id = $this->request->post('task_id', 'integer');
                    $recall_date = null;
                    if ($this->request->post('recall_date') !="dont-call") {

                        $recall_date = date('Y-m-d H:i:s', strtotime(' +' . $this->request->post('recall_date') . ' hours'));
                    }
                    $status = 1;
                    $zaimNumber = $this->tasks->get_pr_task($task_id)->number;
                    $vox_call = false;
                    $get_asp = $this->users->getZaimListAsp($zaimNumber);
                    if (empty($get_asp)) {
                        $vox_call = true;
                    }
                    $this->tasks->update_pr_task($task_id, array(
                        'status' => $status,
                        'recall_date' => $recall_date,
                        'vox_call' => $vox_call
                    ));
                    $taskData =  $this->users->get_users_ccprolongations(['task_id'=>$task_id,'date' => date("Y-m-d")]);
                    $voximplant = new Voximplant();
                    $voximplant->deleteFromDnc($taskData[0]->manager_id,$taskData[0]->phone);
                    $dnc = $voximplant->getDncNumbers('ongoing', 'checkRecall',$taskData[0]->manager_id);

                    if (in_array($taskData[0]->phone, $dnc)){
                        header('Content-type:application/json');
                        echo json_encode(array('exists' => true));
                        exit;
                    }


                    $this->tasks->update_pr_task($task_id, array(
                        'status' => $status,
                        'recall_date' => $recall_date,
                    ));

                break;

                case 'distribute':
                    $managers = $this->request->post('managers');
                    $tasks = $this->users->get_cctasks($filter);

                    $tasks = $this->format_tasks($tasks);
                    $voximplant = new Voximplant();

                    if ($this->request->post('deleted')){
                        $manager = new Managers();
                        $deleted = $this->request->post('deleted');

                        forEach($deleted as $key => $del){
                            $manager->update_manager_data($del,['vox_deleted'=>true]);
                            $voximplant = new Voximplant();
                            $company = $this->managers->getCompany((int)$del);
                            $phones = $voximplant->getDncNumbers('ongoing','deleteManager',$del);

                            $chunks = array_chunk($phones, 50);

                            foreach ($chunks as $chunk) {
                                $voximplant->sendDnc($company,$chunk);
                            }

                            foreach ($phones as $phone){
                                if ($this->request->post('added') && $this->request->post('boolean')){
                                    $this->tasks->update_vox_pr_task($phone,$this->request->post('added')[$key]);
                                }else{
                                    $this->tasks->delete_vox_pr_task($phone);
                                }
                            }

                        }
                    } elseif ($this->request->post('newData')) {

                        foreach ($this->request->post('diffAddedMan') as $manager_id) {
                            file_put_contents('voximplant/voximplant.txt', "manager : $manager_id \n", FILE_APPEND);
                            $numbers = $voximplant->getDncNumbers('ongoing', 'getOngoing', $manager_id);
                            $this->tasks->deleteTasks($numbers);
                            $company = $this->managers->getCompany((int)$manager_id);
                            $chunks = array_chunk($numbers, 50);
                            foreach ($chunks as $chunk) {
                                $voximplant->sendDnc($company, $chunk);
                            }
                        }
                    }


                    $i = 0;
                    $max_i = count($managers);
                    $day = date('d');
                    if ($day % 2 == 0) {
                        usort($managers, function($a, $b) {
                            return strcmp($a->name, $b->name);
                        });
                    }else{
                        usort($managers, function($a, $b) {
                            return strcmp($b->name,$a->name);
                        });
                    }
                    foreach ($tasks as $t) {
                        $user = $this->users->get_user((int)$t->user_id);
                        $timezone = $this->users->get_timezone($user->Faktregion);

                        $existingTask = $this->tasks->existingTask($t->zaim_number);

                        if (empty($existingTask)) {
                            $vox_call = false;
                            $get_asp = $this->users->getZaimListAsp($t->zaim_number);
                            if (empty($get_asp)) {
                                $vox_call = $this->tasks->getVoxCall($t->zaim_number);
                            }

                            $this->tasks->add_pr_task(array(
                                'number' => $t->zaim_number,
                                'user_id' => $t->user_id,
                                'task_date' => date('Y-m-d'),
                                'user_balance_id' => $t->id,
                                'manager_id' => $managers[$i],
                                'close' => 0,
                                'prolongation' => 0,
                                'created' => date('Y-m-d H:i:s'),
                                'od_start' => $t->loan_type == 'IL' ? $t->overdue_debt_od_IL+ $t->next_payment_od : $t->ostatok_od,
                                'percents_start' => $t->loan_type == 'IL' ? $t->overdue_debt_percent_IL+ $t->next_payment_percent : $t->ostatok_percents,
                                'period' => 'period_one_two',
                                'status' => 0,
                                'timezone' => $timezone,
                                'vox_call' => $vox_call
                            ));

                            $i++;
                            if ($i == $max_i)
                                $i = 0;
                        }
                    }
                    if (empty($this->request->post('diffAddedMan'))){
                        foreach ($managers as $manager)
                        {
                            $this->voximplant->sendCcprolongations($manager->id,true, $manager->role);

                        }
                    }

                    header('Content-type:application/json');
                    echo json_encode(array('success' => 1));
                    exit;

                    break;


            endswitch;
        }

        $items_per_page = 100;

        if (!($sort = $this->request->get('sort')))
            $sort = 'timezone_desc';
        $this->design->assign('sort', $sort);


        $period = ['period_one_two'];


        $task_date_from = date('Y-m-d');
        $task_date_to = date('Y-m-d');

        $filter_date_range = $this->request->get('date_range') ?? '';

        if (!empty($filter_date_range)) {
            $filter_date_array = array_map('trim', explode('-', $filter_date_range));
            $task_date_from = str_replace('.', '-', $filter_date_array[0]);
            $task_date_to = str_replace('.', '-', $filter_date_array[1]);
        }

        $this->design->assign('request_date_from', $task_date_from);
        $this->design->assign('request_date_to', $task_date_to);
        if ($task_date_from == $task_date_to && $task_date_to == date("Y-m-d") )
            $this->design->assign('today_date', true);

        $filter = array(
            'period' => $period,
            'task_date_from' => $task_date_from,
            'task_date_to' => $task_date_to,
            'sort' => $sort,
            'close' => 0,
            'prolongation' => 0,
        );

        if ($this->manager->role == "contact_center_new"){
            $filter['contact_center_new']  = $this->manager->id;
        }

        $this->design->assign('filter_period', 'period_one_two');

        if ($filter_manager = $this->request->get('manager_id'))
        {
            $filter['manager_id'] = $filter_manager;
            $this->design->assign('filter_manager', $filter_manager);
        }

        if ($search = $this->request->get('search'))
        {
            $filter['search'] = array_filter($search, function($var){
                return !is_null($var) && $var != '';
            });
            $this->design->assign('search', $filter['search']);
        }

        if ($this->manager->role == 'contact_center_new')
        {
            $filter['manager_id'] = $this->manager->id;
        }
        /*данные для пагинации*/
        $current_page = $this->request->get('page', 'integer');
        $current_page = max(1, $current_page);
        $filter['page'] = $current_page;
        $filter['limit'] = $items_per_page;
        $this->design->assign('current_page_num', $current_page);

//        $filter['status'] = array(0, 1);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($filter);echo '</pre><hr />';

        $query_res = $this->tasks->get_pr_tasks($filter,'plus');
        $tasks = $query_res["data"];
       
        $pages_num = ceil($query_res["total_count"] / $items_per_page);
        $this->design->assign('total_pages_num', $pages_num);

        $ub_ids = array();
        $user_ids = array();
        foreach ($tasks as $task)
        {
            $ub_ids[] = $task->user_balance_id;
            $user_ids[] = $task->user_id;
        }
        $users = array();
        if (!empty($user_ids))
            foreach ($this->users->get_users(array('id'=>$user_ids)) as $u)
                $users[$u->id] = $u;

        $balances = array();
        if (!empty($ub_ids))
            foreach ($this->users->get_user_balances(array('id'=>$ub_ids)) as $ub)
                $balances[$ub->id] = $ub;

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($balances);echo '</pre><hr />';
        foreach ($tasks as $task)
        {
            if (isset($users[$task->user_id]))
                $task->user = $users[$task->user_id];
            if (isset($balances[$task->user_balance_id]))
                $task->balance = $balances[$task->user_balance_id];

            if (in_array('looker_link', $this->manager->permissions))
            {
                $task->looker_link = $this->users->get_looker_link($task->user_id);
            }
            if (isset($task->balance)){
                $payment_error = $this->orders->getPaymentError($task->balance->zaim_number);
                if ($payment_error && $task->user->last_mark != date('Y-m-d')){
                    $this->users->update_mark_day($task->user_id);
                    $this->tasks->update_pr_task($task->id, ['marked' => true]);
                   $task->marked = true;
                }
            }
        }
        $tv_medical_tariffs = $this->tv_medical->getAllTariffs();
        $tv_medical_price = $tv_medical_tariffs[0]->price;
        $this->design->assign('tv_medical_price', $tv_medical_price);

        $this->design->assign('tasks', $tasks);

        $statistic = new StdClass();

        $statistic->total = 0;
        $statistic->inwork = 0;
        $statistic->closed = 0;
        $statistic->total_amount = 0;
        $statistic->total_paid = 0;
        $statistic->prolongation = 0;
        $statistic->perezvon = 0;
        $statistic->nedozvon = 0;
        $statistic->perspective = 0;
        $statistic->alreadyPaid = 0;
        $statistic->receivedInformation = 0;
        $statistic->identityConfirmed = 0;
        $statistic->diedPrisonBankrupt = 0;
        $statistic->stopListNumber = 0;
        $statistic->negative = 0;
        $statistic->thirdPerson = 0;
        $statistic->refinancing = 0;
        $statistic->decline = 0;
//        $statistic->perezvonPaid= 0;
//        $statistic->nedozvonPaid= 0;
//        $statistic->perspectivePaid= 0;
//        $statistic->declinePaid= 0;
//        $statistic->totalPaid = 0;
//        $statistic->alreadyPaidSum = 0;
//        $statistic->receivedInformationpaid = 0;
//        $statistic->identityConfirmedPaid = 0;
//        $statistic->diedPrisonBankruptPaid = 0;
//        $statistic->negativePaid = 0;
//        $statistic->thirdPersonPaid = 0;
//        $statistic->refinancingPaid = 0;

        $statistic_filter = array(
            'task_date_from' => $task_date_from,
            'task_date_to' => $task_date_to,
            'period' => $period,
        );
        if ($this->manager->role == 'contact_center_new')
        {
            $statistic_filter['manager_id'] = $this->manager->id;
        }
        elseif (!empty($filter_manager))
        {
            $statistic_filter['manager_id'] = $filter_manager;
        }

        $dashboard_tasks = $this->tasks->get_pr_tasks($statistic_filter);
        foreach ($dashboard_tasks["data"] as $item)
        {
            if ($this->manager->role == 'contact_center_new' || $this->manager->role == 'contact_center_new_robo' || $this->manager->role == 'robot_vox') {
                if ($item->manager_id == $this->manager->id) {
                    $statistic->total++;
                    $statistic->totalPaid += $item->paid;

                    switch ($item->status) {
                    case 1:
                        $statistic->perezvon++;
//                            $statistic->perezvonPaid += $item->paid;
                        break;
                        case 2:
                            $statistic->nedozvon++;
//                            $statistic->nedozvonPaid += $item->paid;
                            break;
                    case 3:
                        $statistic->perspective++;
//                            $statistic->perspectivePaid += $item->paid;
                        break;
                        case 4:
                            $statistic->decline++;
//                            $statistic->declinePaid += $item->paid;
                            break;
                        case 5:
                            $statistic->alreadyPaid++;
                            break;
                        case 6:
                            $statistic->receivedInformation++;
                            break;
                        case 7:
                            $statistic->identityConfirmed++;
                            break;
                        case 8:
                            $statistic->diedPrisonBankrupt++;
                            break;
                    case 9:
                        $statistic->stopListNumber++;
                        break;
                    case 10:
                        $statistic->negative++;
                        break;
                    case 11:
                        $statistic->thirdPerson++;
                        break;
                    case 12:
                        $statistic->refinancing++;
                        break;
                    }

                    if ($item->status > 0)
                        $statistic->inwork++;
                    if ($item->close)
                        $statistic->closed++;
                    if ($item->prolongation)
                        $statistic->prolongation++;

                    $statistic->total_amount += $item->od_start + $item->percents_start;
                    $statistic->total_paid += $item->paid;
                }
            }
            else{
                $statistic->total++;
                $statistic->totalPaid += $item->paid;

                switch ($item->status) {
                    case 1:
                        $statistic->perezvon++;
//                            $statistic->perezvonPaid += $item->paid;
                        break;
                    case 2:
                        $statistic->nedozvon++;
//                            $statistic->nedozvonPaid += $item->paid;
                        break;
                    case 3:
                        $statistic->perspective++;
//                            $statistic->perspectivePaid += $item->paid;
                        break;
                    case 4:
                        $statistic->decline++;
//                            $statistic->declinePaid += $item->paid;
                        break;
                    case 5:
                        $statistic->alreadyPaid++;
                        break;
                    case 6:
                        $statistic->receivedInformation++;
                        break;
                    case 7:
                        $statistic->identityConfirmed++;
                        break;
                    case 8:
                        $statistic->diedPrisonBankrupt++;
                        break;
                    case 9:
                        $statistic->stopListNumber++;
                        break;
                    case 10:
                        $statistic->negative++;
                        break;
                    case 11:
                        $statistic->thirdPerson++;
                        break;
                    case 12:
                        $statistic->refinancing++;
                        break;
                }

                if ($item->status > 0)
                    $statistic->inwork++;
                if ($item->close)
                    $statistic->closed++;
                if ($item->prolongation)
                    $statistic->prolongation++;

                $statistic->total_amount += $item->od_start + $item->percents_start;
                $statistic->total_paid += $item->paid;
            }
        }

        $this->design->assign('statistic', $statistic);

        $sms_templates = $this->sms->get_templates(array('type' => 'prolongation'));
        $this->design->assign('sms_templates', $sms_templates);

        $pr_statuses = $this->tasks->get_pr_statuses();
        $this->design->assign('pr_statuses', $pr_statuses);

        return $this->design->fetch('cc_prolongations_plus.tpl');
    }

    public  function format_tasks($tasks)
    {
        $numbers = [];
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($tasks);echo '</pre><hr />';

        foreach ($tasks as $key => $task)
        {
            if (!isset($numbers[$task->zaim_number]))
                $numbers[$task->zaim_number] = [];

            $numbers[$task->zaim_number][$key] = $task;
        }
        $numbers = array_filter($numbers, function($var){
            return count($var) - 1;
        });

        foreach ($numbers as $number => $number_tasks)
        {
            $user_id = NULL;
            $k = NULL;
            foreach ($number_tasks as $number_task_key => $number_task)
            {
                if (empty($user_id))
                {
                    $user_id = $number_task->user_id;
                    $k = $number_task_key;
                }
                elseif ($number_task->user_id > $user_id)
                {
                    $user_id = $number_task->user_id;
                    unset($tasks[$k]);
                    $k = $number_task_key;
                }
                else
                {
                    unset($tasks[$number_task_key]);
                }
            }
        }

        return $tasks;
    }
}
