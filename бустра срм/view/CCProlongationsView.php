<?php

require_once 'View.php';

use App\Service\OrganizationService;
use App\Service\CCTaskService;
use App\Service\VoximplantDncService;
use App\Service\VoximplantCampaignService;
use App\Service\VoximplantApiClient;
use App\Service\VoximplantLogger;

class CCProlongationsView extends View
{
    private ?CCTaskService $taskService = null;
    private ?VoximplantDncService $dncService = null;

    /**
     * Получить экземпляр CCTaskService
     */
    private function getTaskService(): CCTaskService
    {
        if ($this->taskService === null) {
            $organizationService = new OrganizationService();
            $logger = new VoximplantLogger();
            $apiClient = new VoximplantApiClient($organizationService, $logger);
            $dncService = new VoximplantDncService($apiClient, $logger, $organizationService);
            $campaignService = new VoximplantCampaignService($apiClient, $logger, $organizationService);
            $this->taskService = new CCTaskService($dncService, $campaignService, $logger);
            $this->dncService = $dncService;
        }
        return $this->taskService;
    }

    public function fetch()
    {
        $filter = array();

        $organizationService = new OrganizationService();
        $organizationSessionKey = 'ccprolongations_selected_organization';

        $selectedOrganizationId = $this->request->post('organization_id', 'integer');

        if (empty($selectedOrganizationId))
            $selectedOrganizationId = $this->request->get('organization_id', 'integer');

        if (empty($selectedOrganizationId) && !empty($_SESSION[$organizationSessionKey]))
            $selectedOrganizationId = (int)$_SESSION[$organizationSessionKey];

        $selectedOrganizationId = $organizationService->resolveOrganizationId(
            $selectedOrganizationId !== null ? (int)$selectedOrganizationId : null
        );

        $_SESSION[$organizationSessionKey] = $selectedOrganizationId;

        $filter['organization_id'] = $selectedOrganizationId;
        $this->design->assign('selected_organization_id', $selectedOrganizationId);
        $this->design->assign('available_organizations', $organizationService->getOptions());
        $this->design->assign('selected_organization_name', $organizationService->getLabel($selectedOrganizationId));

        if ($this->request->method('post'))
        {
            if (!($period = $this->request->post('period')))
                $period = 'all';

            if ($period == 'minus1')
                $filter['from'] = $filter['to'] = date('Y-m-d', time() + 86400*1);
            elseif ($period == 'minus2')
                $filter['from'] = $filter['to'] = date('Y-m-d', time() + 86400*2);
            elseif ($period == 'minus3')
                $filter['from'] = $filter['to'] = date('Y-m-d', time() + 86400*3);
            elseif ($period == 'zero') {
                $filter['from'] = date('Y-m-d 00:00:00');
                $filter['to'] = date('Y-m-d 23:59:59');
            }
            elseif ($period == 'plus1')
                $filter['from'] = $filter['to'] = date('Y-m-d', time() - 86400*1);
            elseif ($period == 'plus3')
                $filter['from'] = $filter['to'] = date('Y-m-d', time() - 86400*3);
            elseif ($period == 'all') {
                $filter['from'] = date('Y-m-d', time() + 86400*3);
                $filter['to'] = date('Y-m-d', time() - 86400*3);
            }

            switch ($this->request->post('action', 'string')):

                case 'status':

                    $task_id = $this->request->post('task_id', 'integer');
                    $status = $this->request->post('status', 'integer');

                    $this->getTaskService()->updateTaskStatus($task_id, $status);

                break;

                case 'add_perspective':

                    $task_id = $this->request->post('task_id', 'integer');
                    $perspective_date = date('Y-m-d H:i:s', strtotime($this->request->post('perspective_date')));
                    $text = $this->request->post('text');

                    $this->getTaskService()->addPerspective(
                        $task_id,
                        $perspective_date,
                        $text,
                        $this->manager->id
                    );

                break;

                case 'add_recall':

                    $task_id = $this->request->post('task_id', 'integer');
                    $recall_date = null;
                    if ($this->request->post('recall_date') != "dont-call") {
                        $recall_date = date('Y-m-d H:i:s', strtotime(' +' . $this->request->post('recall_date') . ' hours'));
                    }

                    $result = $this->getTaskService()->addRecall($task_id, $recall_date);

                    if (isset($result['exists']) && $result['exists']) {
                        header('Content-type:application/json');
                        echo json_encode(array('exists' => true));
                        exit;
                    }

                break;

                case 'distribute':
                    $managers = $this->request->post('managers');
                    $filter['sort'] = 'sum_of_columns';
                    
                    // Добавляем фильтр по организации если указан
                    if ($organization_id = $this->request->post('organization_id')) {
                        $filter['organization_id'] = intval($organization_id);
                    }
                    
                    // Получаем дату задачи из запроса, если не указана - используем сегодняшнюю
                    $task_date = $this->request->post('task_date') ?: date('Y-m-d');
                    
                    $tasks = $this->users->get_cctasks($filter);
                    $taskService = $this->getTaskService();

                    // Обработка удаленных менеджеров
                    if ($this->request->post('deleted')){
                        $manager = new Managers();
                        $deleted = $this->request->post('deleted');

                        foreach($deleted as $key => $del){
                            $manager->update_manager_data($del,['vox_deleted'=>true]);
                            $company = $this->managers->getCompany((int)$del);
                            $phones = $this->dncService->getDncNumbers('ongoing','deleteManager',$del);

                            $chunks = array_chunk($phones, 50);
                            foreach ($chunks as $chunk) {
                                $this->dncService->addToDnc($company, $chunk);
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
                            $numbers = $this->dncService->getDncNumbers('ongoing', 'getOngoing', $manager_id);
                            $this->tasks->deleteTasks($numbers);
                            $company = $this->managers->getCompany((int)$manager_id);
                            $chunks = array_chunk($numbers, 50);
                            foreach ($chunks as $chunk) {
                                $this->dncService->addToDnc($company, $chunk);
                            }
                        }
                    }

                    // Распределяем задачи
                    $taskService->distributeTasks($tasks, $managers, $task_date, $period);

                    // Отправляем в Vox только если дата сегодняшняя или прошедшая
                    // Для будущих дат отправка произойдет автоматически через крону
                    if (empty($this->request->post('diffAddedMan')) && $task_date <= date('Y-m-d')){
                        $organization_id = $this->request->post('organization_id') ? intval($this->request->post('organization_id')) : null;
                        $taskService->sendTasksToVoximplant($managers, $organization_id, $task_date);
                    }

                    header('Content-type:application/json');
                    echo json_encode(array('success' => 1));
                    exit;

                    break;

                case 'distribute_me':
                    // Распределение задач на текущего менеджера
                    $result = $this->getTaskService()->distributeTasksToMe(
                        $period,
                        $this->manager->id,
                        $selectedOrganizationId,
                        $filter
                    );

                    header('Content-type:application/json');
                    echo json_encode($result);
                    exit;
                break;

            endswitch;
        }

        $items_per_page = 100;

        if (!($sort = $this->request->get('sort')))
            $sort = 'timezone_desc';
        $this->design->assign('sort', $sort);

        if (!($period = $this->request->get('period')) || $period == 'all') {
            $period = ['zero', 'minus1', 'minus2', 'minus3', 'plus1', 'plus3'];
        }

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

        if ($this->manager->role == "contact_center" || $this->manager->role== "contact_center_robo"){
            $filter['contact_center']  = $this->manager->id;
        }

        $this->design->assign('filter_period', is_array($period) ? 'all' : $period);

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

        if ($this->manager->role == 'contact_center' || $this->manager->role == 'contact_center_robo')
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

        $query_res = $this->tasks->get_pr_tasks($filter);
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
            $callsBlacklist = false;
            if(!empty($this->tasks->getCallsBlacklistUsers($task->user_id))){
                $callsBlacklist = true;
            };
            $task->callsBlacklist = $callsBlacklist;
        }
        //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($tasks);echo '</pre><hr />';

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
        $statistic->perezvonPaid= 0;
        $statistic->nedozvonPaid= 0;
        $statistic->perspectivePaid= 0;
        $statistic->declinePaid= 0;
        $statistic->totalPaid = 0;

        $statistic_filter = array(
            'task_date_from' => $task_date_from,
            'task_date_to' => $task_date_to,
            'period' => $period,
            'organization_id' => $selectedOrganizationId,
        );
        if ($this->manager->role == 'contact_center' || $this->manager->role == 'contact_center_robo')
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
            if ($this->manager->role == 'contact_center' || $this->manager->role == 'contact_center_robo') {
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
            }
            else{
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

        return $this->design->fetch('cc_prolongations.tpl');
    }

    public function format_tasks($tasks)
    {
        return $this->getTaskService()->formatTasks($tasks);
    }
}
