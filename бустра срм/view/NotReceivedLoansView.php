<?php

declare(strict_types=1);

require_once 'View.php';

class NotReceivedLoansView extends View
{
    public const ITEMS_PER_PAGE = 20;

    /**
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->request->method('post')) {
            if ($this->request->post('action', 'string') == 'set_manager') {
                $this->setManagerAction();
            }
        }

        if ($this->request->method('get')) {
            if ($this->request->get('action', 'string') == 'download') {
                $this->download();
            }
        }
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {
        [$orders, $managers] = array_values($this->getData(true, true));
        [$allOrders] = array_values($this->getData());
        $ordersCount = count($allOrders);

        $this->design->assign('orders', $orders);
        $this->design->assign('total_pages_num', ceil($ordersCount / self::ITEMS_PER_PAGE));
        $this->design->assign('total_orders_count', $ordersCount);
        $this->design->assign('last_calls', $this->getLastCalls($orders));
        $this->design->assign('statistic', $this->getStatistic($allOrders, $ordersCount));
        $this->design->assign('managers', $managers);
        $this->design->assign('listingUri', strtok($_SERVER['REQUEST_URI'], '?'));

        return $this->design->fetch('not_received_loans.tpl');
    }

    /**
     * @param bool $withItemPerPage
     * @param bool $withLimit
     * @return array
     */
    private function getData(bool $withItemPerPage = false, bool $withLimit = false): array
    {
        $managers = [];
        foreach ($this->managers->get_managers() as $m) {
            $managers[$m->id] = $m;
        }

        $filter = [
            'approve' => true,
            'approve_period' => true,
            'auto_approve' => true,
            'user_timezone' => true,
        ];

        if ($withLimit === true) {
            $filter['limit'] = self::ITEMS_PER_PAGE;
        }

        if (!($sort = $this->request->get('sort', 'string'))) {
            $sort = 'approve_date_desc';
        }

        $filter['sort'] = $sort;
        $this->design->assign('sort', $sort);

        if ($search = $this->request->get('search')) {
            $filter['search'] = array_filter($search);
            $this->design->assign('search', array_filter($search));
        }

        $smsTemplates = $this->sms->get_templates(['type' => 'missing']);
        $this->design->assign('sms_templates', $smsTemplates);

        if ($withItemPerPage === true) {
            $currentPage = $this->request->get('page', 'integer');
            $currentPage = max(1, $currentPage);
            $this->design->assign('current_page_num', $currentPage);
            $filter['page'] = $currentPage;
        }

        $orders = $this->orders->get_orders($filter);
        foreach ($orders as $order) {
            $order->approve_period_date = date('Y-m-d H:i:s', strtotime('+7 day', strtotime($order->approve_date)));
        }

        return [$orders, $managers];
    }

    /**
     * Set not received loans manager
     *
     * @return void
     */
    public function setManagerAction(): void
    {
        if ($order_id = $this->request->post('order_id', 'integer')) {
            if ($order = $this->orders->get_order($order_id)) {
                if (empty($order->not_received_loan_manager_id)) {
                    $this->orders->update_order($order_id, [
                        'not_received_loan_manager_id' => $this->manager->id,
                        'not_received_loan_manager_update_date' => date('Y-m-d H:i:s')
                    ]);

                    $this->json_output(['success' => 1, 'manager_name' => $this->manager->name]);
                }
            } else {
                $this->json_output(['error' => 'UNDEFINED_USER']);
            }
        } else {
            $this->json_output(['error' => 'EMPTY_USER_ID']);
        }
    }

    /**
     * Get statistics for a dashboard
     *
     * @param array $orders
     * @param int $ordersCount
     * @return StdClass
     */
    private function getStatistic(array $orders, int $ordersCount): StdClass
    {
        $statistic = new StdClass();
        $statistic->totals = $ordersCount;
        $statistic->in_progress = 0;
        $statistic->conversion = 0;

        $filtersConfirmed = [
            'has_not_received_loan_manager' => true,
            'issued' => true
        ];
        $statistic->completed = $this->orders->count_orders($filtersConfirmed);
        foreach ($orders as $order) {
            if ($order->not_received_loan_manager_id > 0) {
                $statistic->in_progress++;
            }
        }

        if ($statistic->totals > 0 && $statistic->in_progress > 0) {
            $statistic->conversion = round($statistic->completed / $statistic->in_progress * 100);
        }

        return $statistic;
    }

    /**
     * @param array $orders
     * @return array
     * @throws Exception
     */
    private function getLastCalls(array $orders): array
    {
        $lastCalls = [];

        if (empty($orders)) {
            return $lastCalls;
        }

        $usersIds = [];
        foreach ($orders as $order) {
            $usersIds[] = $order->user_id;
        }

        $calls = $this->voxCalls->get_calls(['user_id' => $usersIds]);
        foreach ($orders as $order) {
            foreach ($calls as $call) {
                if ($order->user_id == $call->id) {
                    $order->dump = $call;
                    $order->dump->callDate = $call->created;
                }
            }
        }

        return Helpers::filterCalls($calls, $lastCalls);
    }

    /**
     * @return void
     * @throws Exception
     */
    private function download(): void
    {
        ini_set('memory_limit', '-1');

        $writer = new XLSXWriter();
        [$orders, $managers] = array_values($this->getData());
        $lastCalls = $this->getLastCalls($orders);
        $filename = 'files/reports/not_received_loans_report.xlsx';

        $header = [
            'Дата одобрения' => 'string',
            'Срок действия одобрения' => 'string',
            'Последний вход в ЛК' => 'string',
            'ФИО' => 'string',
            'Телефон' => 'string',
            'Время клиента' => 'string',
            'Автоодобрение' => 'string',
            'Ответственный' => 'string',
            'Время контакта' => 'string',
            'Статус звонка' => 'string',
            'Клиент получит займ' => 'string'
        ];

        $writer->writeSheetHeader($filename, $header);
        array_map(function ($order) use ($writer, $filename, $lastCalls, $managers) {
            $callStatus = Users::CALL_STATUS_MAP[$order->call_status] ?? '';
            $willClientReceiveLoan = Orders::WILL_CLIENT_RECEIVE_LOAN_MAP[$order->will_client_receive_loan] ?? '';
            $managerName = $managers[$order->not_received_loan_manager_id]->name ?? '';
            $lastCall = $lastCalls[$order->user_id]->created ?? '';
            $fio = "{$order->lastname} {$order->firstname} {$order->patronymic}";

            $row_data = [
                $order->approve_date,
                $order->approve_period_date,
                $order->last_lk_visit_time,
                $fio,
                $order->phone_mobile,
                $order->timezone,
                $order->auto_approve,
                $managerName,
                $lastCall,
                $callStatus,
                $willClientReceiveLoan
            ];

            $writer->writeSheetRow($filename, $row_data);
        }, $orders);

        $writer->writeToFile($this->config->root_dir . '/' . $filename);
        header('Location:' . $this->config->root_url . '/' . $filename);
        exit;
    }
}
