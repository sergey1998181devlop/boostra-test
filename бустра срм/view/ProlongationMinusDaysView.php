<?php

require_once 'View.php';

/**
 * Кто всё это отрефакторит, тот молодец
 */
class ProlongationMinusDaysView extends View
{
    public function fetch()
    {
        $filter = [];

        if ($this->request->method('post')) {
            if (!($period = $this->request->post('period')))
                $period = 'all';

            if ($period == '-1')
                $filter['from'] = $filter['to'] = date('Y-m-d', time() + 86400 * 1);
            elseif ($period == '-2')
                $filter['from'] = $filter['to'] = date('Y-m-d', time() + 86400 * 2);
            elseif ($period == '-3')
                $filter['from'] = $filter['to'] = date('Y-m-d', time() + 86400 * 3);
            elseif ($period == 'zero')
                $filter['from'] = $filter['to'] = date('Y-m-d');
            elseif ($period == 'plus1')
                $filter['from'] = $filter['to'] = date('Y-m-d', time() - 86400 * 1);
            elseif ($period == 'plus3')
                $filter['from'] = $filter['to'] = date('Y-m-d', time() - 86400 * 3);
            elseif ($period == 'all') {
                $filter['from'] = date('Y-m-d', time() + 86400 * 3);
                $filter['to'] = date('Y-m-d', time() - 86400 * 3);
            }

            switch ($this->request->post('action', 'string')):

                case 'status':

                    $task_id = $this->request->post('task_id', 'integer');
                    $status = $this->request->post('status', 'integer');

                    $this->tasks->update_pr_task($task_id, array('status' => $status));

                    if ($status == 4) {

                        $taskData = $this->users->get_users_ccprolongations(['task_id' => $task_id, 'date' => date("Y-m-d")]);

                        $company = $this->managers->getCompany((int)$taskData[0]->manager_id);
                        $voximplant = new Voximplant();
                        $voximplant->sendDnc($company, ["'" . $taskData[0]->phone . "'"]);
                    }

                    break;

                case 'add_perspective':

                    $task_id = $this->request->post('task_id', 'integer');
                    $perspective_date = date('Y-m-d H:i:s', strtotime($this->request->post('perspective_date')));
                    $status = 3;

                    $this->tasks->update_pr_task($task_id, array(
                        'status' => $status,
                        'perspective_date' => $perspective_date
                    ));

                    $taskData = $this->users->get_users_ccprolongations(['task_id' => $task_id, 'date' => date("Y-m-d")]);

                    $company = $this->managers->getCompany((int)$taskData[0]->manager_id);
                    $voximplant = new Voximplant();
                    $voximplant->sendDnc($company, ["'" . $taskData[0]->phone . "'"]);

                case 'add_recall':

                    $task_id = $this->request->post('task_id', 'integer');
                    $recall_date = null;
                    if ($this->request->post('recall_date') != "dont-call") {

                        $recall_date = date('Y-m-d H:i:s', strtotime(' +' . $this->request->post('recall_date') . ' hours'));
                    }
                    $status = 1;

                    $this->tasks->update_pr_task($task_id, array(
                        'status' => $status,
                        'recall_date' => $recall_date,
                    ));
                    $taskData = $this->users->get_users_ccprolongations(['task_id' => $task_id, 'date' => date("Y-m-d")]);
                    $voximplant = new Voximplant();
                    $voximplant->deleteFromDnc($taskData[0]->manager_id, $taskData[0]->phone);
                    $dnc = $voximplant->getDncNumbers('ongoing', 'checkRecall', $taskData[0]->manager_id);

                    if (in_array($taskData[0]->phone, $dnc)) {
                        header('Content-type:application/json');
                        echo json_encode(array('exists' => true));
                        exit;
                    }


                    $this->tasks->update_pr_task($task_id, array(
                        'status' => $status,
                        'recall_date' => $recall_date,
                    ));

                    break;

//                case 'distribute':
//                    $managers = $this->request->post('managers');
//                    $filter['sort'] = 'sum_of_columns';
//                    $tasks = $this->users->get_cctasks($filter);
//
//                    $tasks = $this->format_tasks($tasks);
//                    $voximplant = new Voximplant();
//
//                    if ($this->request->post('deleted')) {
//                        $manager = new Managers();
//                        $deleted = $this->request->post('deleted');
//
//                        foreach ($deleted as $key => $del) {
//                            $manager->update_manager_data($del, ['vox_deleted' => true]);
//                            $voximplant = new Voximplant();
//                            $company = $this->managers->getCompany((int)$del);
//                            $phones = $voximplant->getDncNumbers('ongoing', 'deleteManager', $del);
//
//                            $chunks = array_chunk($phones, 50);
//
//                            foreach ($chunks as $chunk) {
//                                $voximplant->sendDnc($company, $chunk);
//                            }
//
//                            foreach ($phones as $phone) {
//                                if ($this->request->post('added') && $this->request->post('boolean')) {
//                                    $this->tasks->update_vox_pr_task($phone, $this->request->post('added')[$key]);
//                                } else {
//                                    $this->tasks->delete_vox_pr_task($phone);
//                                }
//                            }
//
//                        }
//                    } elseif ($this->request->post('newData')) {
//
//                        foreach ($this->request->post('diffAddedMan') as $manager_id) {
//                            file_put_contents('voximplant/voximplant.txt', "manager : $manager_id \n", FILE_APPEND);
//                            $numbers = $voximplant->getDncNumbers('ongoing', 'getOngoing', $manager_id);
//                            $this->tasks->deleteTasks($numbers);
//                            $company = $this->managers->getCompany((int)$manager_id);
//                            $chunks = array_chunk($numbers, 50);
//                            foreach ($chunks as $chunk) {
//                                $voximplant->sendDnc($company, $chunk);
//                            }
//                        }
//                    }
//
//                    $i = 0;
//                    $max_i = count($managers);
//
//                    $day = date('d');
//                    if ($day % 2 == 0) {
//                        usort($managers, function ($a, $b) {
//                            return strcmp($a->name, $b->name);
//                        });
//                    } else {
//                        usort($managers, function ($a, $b) {
//                            return strcmp($b->name, $a->name);
//                        });
//                    }
//                    foreach ($tasks as $t) {
//                        $user = $this->users->get_user((int)$t->user_id);
//
//                        if ($user->id == 170906)
//                            $timezone = 4;
//                        else
//                            $timezone = $this->users->get_timezone($user->Faktregion);
//
//                        $existingTask = $this->tasks->existingTask($t->zaim_number);
//                        if (empty($existingTask)) {
//
//                            $this->tasks->add_pr_task(array(
//                                'number' => $t->zaim_number,
//                                'user_id' => $t->user_id,
//                                'task_date' => date('Y-m-d'),
//                                'user_balance_id' => $t->id,
//                                'manager_id' => $managers[$i],
//                                'close' => 0,
//                                'prolongation' => 0,
//                                'created' => date('Y-m-d H:i:s'),
//                                'od_start' => $t->ostatok_od,
//                                'percents_start' => $t->ostatok_percents,
//                                'period' => $period,
//                                'status' => 0,
//                                'timezone' => $timezone,
//                            ));
//
//                            $i++;
//                            if ($i == $max_i)
//                                $i = 0;
//                        }
//                    }
//                    if (empty($this->request->post('diffAddedMan'))) {
//                        foreach ($managers as $manager) {
//                            $this->voximplant->sendCcprolongations($manager->id, false, $manager->role);
//
//                        }
//                    }
//
//                    header('Content-type:application/json');
//                    echo json_encode(array('success' => 1));
//                    exit;
            endswitch;
        }

        $items_per_page = 100;

        if (!($sort = $this->request->get('sort')))
            $sort = 'timezone_desc';
        $this->design->assign('sort', $sort);

        if (!($period = $this->request->get('period')) || $period == 'all') {
            $period = ['-1', '-2', '-3', '-4', '-5'];
        }

        $task_date_from = date('Y-m-d');
        $task_date_to = date('Y-m-d');
        $filter_date_range = $this->request->get('date_range') ?? '';
        if (!empty($filter_date_range)) {
            $filter_date_array = array_map('trim', explode('-', $filter_date_range));
            $date['from'] = str_replace('.', '-', $filter_date_array[0]);
            $date['to'] = str_replace('.', '-', $filter_date_array[1]);
        }

        $this->design->assign('request_date_from', date('Y-m-d', strtotime($date['from'])));
        $this->design->assign('request_date_to', date('Y-m-d', strtotime($date['to'])));
        $this->design->assign('request_date_from', $task_date_from);
        $this->design->assign('request_date_to', $task_date_to);
        if ($task_date_from == $task_date_to && $task_date_to == date("Y-m-d") )
            $this->design->assign('today_date', true);

        $filter = array(
            'sort' => $sort,
            'close' => 0,
            'prolongation' => 0,
            'period' => $period,
            'task_date_from' => $task_date_from,
            'task_date_to' => $task_date_to,
        );

        if ($this->manager->role == "robot_minus") {
            $filter['robot_minus'] = $this->manager->id;
        }

        $this->design->assign('filter_period', is_array($period) ? 'all' : $period);

        if ($filter_manager = $this->request->get('manager_id')) {
            $filter['manager_id'] = $filter_manager;
            $this->design->assign('filter_manager', $filter_manager);
        }
        if ($search = $this->request->get('search')) {
            $filter['search'] = array_filter($search, function ($var) {
                return !is_null($var) && $var != '';
            });
            $this->design->assign('search', $filter['search']);
        }

        if ($this->manager->role == 'robot_minus') {
            $filter['manager_id'] = $this->manager->id;
        }

        /*данные для пагинации*/
        $current_page = $this->request->get('page', 'integer');
        $current_page = max(1, $current_page);
        $filter['page'] = $current_page;
        $filter['limit'] = $items_per_page;
        $this->design->assign('current_page_num', $current_page);

        $query_res = $this->tasks->get_pr_tasks($filter);
        $tasks = $query_res["data"];

        $pages_num = ceil($query_res["total_count"] / $items_per_page);
        $this->design->assign('total_pages_num', $pages_num);
        $ub_ids = array();
        $user_ids = array();
        foreach ($tasks as $task) {
            $ub_ids[] = $task->user_balance_id;
            $user_ids[] = $task->user_id;
        }
        $users = array();
        if (!empty($user_ids))
            foreach ($this->users->get_users(array('id' => $user_ids)) as $u)
                $users[$u->id] = $u;

        $balances = array();
        if (!empty($ub_ids))
            foreach ($this->users->get_user_balances(array('id' => $ub_ids)) as $ub)
                $balances[$ub->id] = $ub;

        foreach ($tasks as $task) {
            if (isset($users[$task->user_id]))
                $task->user = $users[$task->user_id];
            if (isset($balances[$task->user_balance_id]))
                $task->balance = $balances[$task->user_balance_id];

            if (in_array('looker_link', $this->manager->permissions)) {
                $task->looker_link = $this->users->get_looker_link($task->user_id);
            }
            $callsBlacklist = false;
            if(!empty($this->tasks->getCallsBlacklistUsers($task->user_id))){
                $callsBlacklist = true;
            };
            $task->callsBlacklist = $callsBlacklist;
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
        $statistic->decline = 0;
        $statistic->perezvonPaid = 0;
        $statistic->nedozvonPaid = 0;
        $statistic->perspectivePaid = 0;
        $statistic->declinePaid = 0;
        $statistic->totalPaid = 0;
        $statistic->clients_amount = 0;

        $statistic_filter = array(
            'period' => $period,
            'task_date_from' => $task_date_from,
            'task_date_to' => $task_date_to,
        );

        if ($this->manager->role == 'robot_minus') {
            $statistic_filter['manager_id'] = $this->manager->id;
        } elseif (!empty($filter_manager)) {
            $statistic_filter['manager_id'] = $filter_manager;
        }

        $dashboard_tasks = $this->tasks->get_pr_tasks($statistic_filter);
        $statistic->clients_amount = $dashboard_tasks['total_count'];
        foreach ($dashboard_tasks["data"] as $item) {
            if ($this->manager->role == 'robot_minus') {
                if ($item->manager_id == $this->manager->id) {
                    $statistic->total++;
                    $statistic->totalPaid += $item->paid;

                    switch ($item->status) {
                        case 1:
                            $statistic->perezvon++;
                            $statistic->perezvonPaid += $item->paid;
                            break;
                        case 2:
                            $statistic->nedozvon++;
                            $statistic->nedozvonPaid += $item->paid;
                            break;
                        case 3:
                            $statistic->perspective++;
                            $statistic->perspectivePaid += $item->paid;
                            break;
                        case 4:
                            $statistic->decline++;
                            $statistic->declinePaid += $item->paid;
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
            } else {
                $statistic->total++;
                $statistic->totalPaid += $item->paid;

                switch ($item->status) {
                    case 1:
                        $statistic->perezvon++;
                        $statistic->perezvonPaid += $item->paid;
                        break;
                    case 2:
                        $statistic->nedozvon++;
                        $statistic->nedozvonPaid += $item->paid;
                        break;
                    case 3:
                        $statistic->perspective++;
                        $statistic->perspectivePaid += $item->paid;
                        break;
                    case 4:
                        $statistic->decline++;
                        $statistic->declinePaid += $item->paid;
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

        return $this->design->fetch('prolongation_minus_days.tpl');
    }

    public function format_tasks($tasks)
    {
        $numbers = [];

        foreach ($tasks as $key => $task) {
            if (!isset($numbers[$task->zaim_number]))
                $numbers[$task->zaim_number] = [];

            $numbers[$task->zaim_number][$key] = $task;
        }
        $numbers = array_filter($numbers, function ($var) {
            return count($var) - 1;
        });

        foreach ($numbers as $number => $number_tasks) {
            $user_id = NULL;
            $k = NULL;
            foreach ($number_tasks as $number_task_key => $number_task) {
                if (empty($user_id)) {
                    $user_id = $number_task->user_id;
                    $k = $number_task_key;
                } elseif ($number_task->user_id > $user_id) {
                    $user_id = $number_task->user_id;
                    unset($tasks[$k]);
                    $k = $number_task_key;
                } else {
                    unset($tasks[$number_task_key]);
                }
            }
        }

        return $tasks;
    }
}
