<?php

require_once 'View.php';

class OrdersView extends View
{
    /**
     * Ключи из s_order_data которые нужно подгрузить в заявку.
     */
    const REQUIRED_ORDER_DATA = [
        LeadgidScorista::ORDER_DATA_LEADGID_REJECT,
        OrderData::RCL_LOAN,
        OrderData::RCL_AMOUNT
    ];

    public function fetch()
    {
        if (!in_array('orders', $this->manager->permissions) &&
            $after_login = $this->managers->get_after_login_page($this->manager->role)) {
            header('Location: ' . $after_login);
            exit();
        }

        $pagespeed = ['start' => time()];
        
        $organizations = [];
        foreach ($this->organizations->getList() as $org) {
            $organizations[$org->id] = $org;
        }
        $this->design->assign('organizations', $organizations);

        $items_per_page = 20;
        $filter = [];
        $filter['dops'] = 0;

        if (!($sort = $this->request->get('sort', 'string'))) {
            $sort = 'date_desc';
        }

        $filter['sort'] = $sort;
        $this->design->assign('sort', $sort);

        if (in_array('credit_doctor_orders', $this->manager->permissions)) {
            $filter['credit_doctor'] = true;
        }

        if ($site_id = $this->request->get('site_id')) {
            $filter['site_id'] = $site_id;
            $this->design->assign('site_id', $site_id);
        }

        if ($organization_id = $this->request->get('organization_id', 'integer')) {
            $filter['organization_id'] = $organization_id;
            $this->design->assign('filter_organization_id', $organization_id);
        }

        if ($search = $this->request->get('search')) {
            $filter['search'] = array_filter($search);
            $this->design->assign('search', array_filter($search));

            foreach ($filter['search'] as &$item) {
                if ($item == 'lvtraff') {
                    $item = 'sms';
                }
                if (mb_strtolower($item, 'utf8') == 'Акция СД') {
                    $item = 'sms';
                }
            }
        }

        if ($status = $this->request->get('status')) {
            if ($status == 'notreceived') {
                $filter['notreceived'] = 1;
                $filter['date_from'] = date('Y-m-d', time() - 86400 * 25);
            } elseif ($status == 'notbusy') {
                $filter['notbusy'] = 1;
            } elseif ($status == 'inwork') {
                $filter['inwork'] = 1;
            } elseif ($status == 'issued') {
                $filter['issued'] = 1;
                $filter['credit_getted'] = 1;
            } elseif ($status == 'approve') {
                $filter['approve'] = 1;
            } elseif ($status == 'failed_to_issue') {
                $filter['failed_to_issue'] = 1;
            } elseif ($status == 'autoapprove') {
                $filter['autoapprove'] = 1;
            } else {
                $filter['status'] = $status;
            }
            $this->design->assign('filter_status', $status);
        }


        if ($utm = $this->request->get('utm')) {
            if ($utm == 'autoapprove') {
                $filter['autoapprove'] = 1;
            }
            $this->design->assign('filter_utm', $utm);
        }



        $filter['filter_client'] = $this->request->get('filter_client');
        $this->design->assign('filter_client', $filter['filter_client']);

        $filter['loan_type'] = $this->request->get('filter_loan_type', 'string');
        $this->design->assign('filter_loan_type', $filter['loan_type']);

        $pagespeed['point1'] = time();
        
        $current_page = $this->request->get('page', 'integer');
        $current_page = max(1, $current_page);
        $this->design->assign('current_page_num', $current_page);

//        $orders_count = $this->orders->count_orders($filter);
        $orders_count = 1000;

        $filter['stage_completed'] = 1;
        
        $pagespeed['point2'] = time();
        
        $pages_num = ceil($orders_count / $items_per_page);
        $this->design->assign('total_pages_num', $pages_num);
        $this->design->assign('total_orders_count', $orders_count);

        if (in_array($this->manager->role, ['verificator', 'edit_verificator'])) {
            $filter['autoretry'] = 0;
        }
        if (in_array($this->manager->role, ['verificator_minus'])) {
            $filter['organization_id'] = 6;
        }
        if ($this->request->get('only') == 'my') {
            $this->design->assign('my_orders', 1);
            $filter['manager_id'] = $this->manager->id;
        } elseif (in_array($this->manager->role, ['verificator', 'edit_verificator'])) {
            $filter['autoretry'] = 0;
            $filter['not_manager_id'] = 50;
            $filter['completed_scorings'] = 1;
        }
        if ($this->manager->role == 'chief_verificator') {
            $filter['not_status_1c'] = ['Не определено'];
        } elseif (!in_array('all_orders', $this->manager->permissions)) {
            $filter['not_status_1c'] = ['7.Технический отказ', 'Не определено'];
        }

        // Фильтр тестовых заявок для верификаторов
        $is_verificator_role = $this->managers->canVerify($this->manager->role);
        if ($is_verificator_role) {
            $show_test = isset($_COOKIE['show_test_orders']) ? (int)$_COOKIE['show_test_orders'] : 0;
            
            $this->design->assign('show_test_orders', $show_test);
            $this->design->assign('is_verificator_role', true);
            
            // По умолчанию скрываем тестовые заявки (когда show_test = 0)
            if (empty($show_test)) {
                $filter['hide_test_orders'] = 1;
            }
        }

        $filter['page'] = $current_page;
        $filter['limit'] = $items_per_page;
        $orders = [];

        if (!in_array('company_orders', $this->manager->permissions)) {
            $filter['not_company_orders'] = true;
        }
        
        $userIds = [];
        foreach ($this->orders->get_orders($filter) as $order) {
            $divide_order = $this->orders->getDivideOrderByOrderId((int)$order->order_id);
            $order->is_main_divide_order = (int)($divide_order->main_order_id ?? 0) === (int)$order->order_id;

            $order->scorings = [];
            $orders[$order->order_id] = $order;
            $userIds[] = $order->user_id;
        }
        
        $pagespeed['point3'] = time();

        if (!empty($orders)) {
            if ($orders_scorings = $this->scorings->get_scorings(['order_id' => array_keys($orders)])) {
                foreach ($orders_scorings as &$order_scoring) {
                    $orders[$order_scoring->order_id]->scorings[$order_scoring->type] = $order_scoring;
                }
            }

            $order_ids = array_keys($orders);
            $orders_data = $this->order_data->getMany($order_ids, self::REQUIRED_ORDER_DATA);
            foreach ($orders_data as $data) {
                $orders[$data->order_id]->order_data[$data->key] = $data->value;
            }
        }
        
        $pagespeed['point4'] = time();
        
        $CallsBlacklistUsers = [];
        foreach ($this->tasks->getCallsBlacklistUsers($userIds) as $cbu) {
            $CallsBlacklistUsers[$cbu->user_id] = $cbu;
        }
        
        foreach ($orders as $order) {
            if (isset($CallsBlacklistUsers[$order->user_id])) {
                $order->blockcalls = $CallsBlacklistUsers[$order->user_id];
            }

            if (!empty($order->approve_date)) {
                $processing_time = strtotime($order->approve_date) - strtotime($order->stage1_date ?: $order->date);

                $order->processing_time = $processing_time;
            }

            if (empty($order->scorings) || !count($order->scorings)) {
                $order->scorings_result = 'Не проводился';
            } else {
                $order->scorings_result = 'Пройден';
                foreach ($order->scorings as $scoring) {
                    if (!$scoring->success) {
                        $order->scorings_result = 'Не пройден: ' . $scoring->type;
                    }
                }
            }

            $order->credit_rating_view = isset($order->has_credit_rating) ? ($order->has_scoring ? '++' : '+') : '-';

            // проверка на возможность принять в работу
            $order->is_approve_order = 1;
        }
        
        $pagespeed['point5'] = time();

        $this->design->assign('orders', $orders);
        $scoring_types = $this->scorings->get_types();
        $this->design->assign('scoring_types', $scoring_types);

        // план верификаторов - отключил 02/08/25, так как существенно влияет на скорость загрузки листинга
        if (0 && in_array($this->manager->role, ['verificator', 'edit_verificator'])) {
            $daily_pk = $this->orders->count_orders([
                'manager_id' => $this->manager->id,
                'status' => 2,
                'date_from' => date('Y-m-d'),
                'date_to' => date('Y-m-d'),
                'close_credits' => 1
            ]);

            $daily_nk = $this->orders->count_orders([
                'manager_id' => $this->manager->id,
                'status' => 2,
                'date_from' => date('Y-m-d'),
                'date_to' => date('Y-m-d'),
                'close_credits' => 0
            ]);

            $this->design->assign('daily_nk', $daily_nk);
            $this->design->assign('daily_pk', $daily_pk);

            $month_pk = $this->orders->count_orders([
                'manager_id' => $this->manager->id,
                'date_from' => date('Y-m-01'),
                'date_to' => date('Y-m-d'),
                'status' => 2,
                'close_credits' => 1,
            ]);

            $month_nk = $this->orders->count_orders([
                'manager_id' => $this->manager->id,
                'date_from' => date('Y-m-01'),
                'date_to' => date('Y-m-d'),
                'status' => 2,
                'close_credits' => 0,
            ]);

            $this->design->assign('month_nk', $month_nk);
            $this->design->assign('month_pk', $month_pk);

        }

        if (in_array('chief_verificator', $this->manager->permissions)) {
            $sum = 0;
            $total = 0;
            $countRegularClients = 0;
            $summRegularClients = 0;
            $countNewClients = 0;
            $summNewClients = 0;
            $ordersCurentDay = $this->orders->getOrdersForCurrentDay($this->request->get('site_id'));

            $infoCurentDayPing3 = (object)[
                'totalSumm' => $sum,
                'totalOrders' => $total,
                'countNewClients' => $countNewClients,
                'summNewClients' => $summNewClients,
                'countRegularClients' => $countRegularClients,
                'summRegularClients' => $summRegularClients,
            ];

            $order_ids  = array_map(fn($order) => $order->order_id, $ordersCurentDay);
            $order_data = $this->order_data->getMany($order_ids, [$this->ping3_data::ORDER_FROM_PARTNER, $this->ping3_data::PING3_USER_STATUS]);
            $ping3_data = array_reduce($order_data, fn($carry, $data) => array_merge($carry, ["{$data->order_id}:{$data->key}" => $data->value]), []);

            foreach ($ordersCurentDay as $infoOrders) {
                $sum += $infoOrders->amount;
                $total++;
                if ($infoOrders->have_close_credits == 1 || $infoOrders->utm_source == $this->orders::UTM_SOURCE_CROSS_ORDER) {
                    $countRegularClients++;
                    $summRegularClients += $infoOrders->amount;
                } else {
                    $countNewClients++;
                    $summNewClients += $infoOrders->amount;
                }

                // Считаем пинг3
                // Т.к. мобильщики перезатирают наши метки, добавим дополнительную проверку
                $isPing3 = $infoOrders->utm_term === $this->ping3_data::UTM_TERM;
                if (!$isPing3) {
                    //$isPing3 = $this->order_data->read($infoOrders->order_id, $this->ping3_data::ORDER_FROM_PARTNER);
                    $isPing3 = $ping3_data[$infoOrders->order_id . ':' . $this->ping3_data::ORDER_FROM_PARTNER] ?? null;
                }

                if (!empty($isPing3)) {
                    $infoCurentDayPing3->totalSumm += $infoOrders->amount;
                    $infoCurentDayPing3->totalOrders++;

                    // Получим признак repeat или new
                    //$userPing3Status = $this->order_data->read($infoOrders->order_id, $this->ping3_data::PING3_USER_STATUS);
                    $userPing3Status = $ping3_data[$infoOrders->order_id . ':' . $this->ping3_data::PING3_USER_STATUS] ?? null;

                    if ($userPing3Status === $this->ping3_data::CHECK_USER_RESPONSE_REPEAT) {
                        $infoCurentDayPing3->countRegularClients++;
                        $infoCurentDayPing3->summRegularClients += $infoOrders->amount;
                    } else {
                        $infoCurentDayPing3->countNewClients++;
                        $infoCurentDayPing3->summNewClients += $infoOrders->amount;
                    }
                }
            }

            $infoCurentDay = (object)[
                'totalSumm' => $sum,
                'totalOrders' => $total,
                'countNewClients' => $countNewClients,
                'summNewClients' => $summNewClients,
                'countRegularClients' => $countRegularClients,
                'summRegularClients' => $summRegularClients,
            ];

            $this->design->assign('infoCurentDay', $infoCurentDay);

            // заявки ping3
            $this->design->assign('infoCurentDayPing3', $infoCurentDayPing3);
        }
        
        $pagespeed['point6'] = time();

        $reasons = [];
        foreach ($this->reasons->get_reasons() as $r) {
            $reasons[$r->id] = $r;
        }

        $this->design->assign('reasons', $reasons);

        if (!in_array($this->manager->role, ['verificator_minus'])) {
            $boostra_organizations_for_filter = $this->organizations->get_boostra_organizations_for_filter();
            $this->design->assign('boostra_organizations_for_filter', $boostra_organizations_for_filter);

            $soyaplace_organizations_for_filter = $this->organizations->get_soyaplace_organizations_for_filter();
            $this->design->assign('soyaplace_organizations_for_filter', $soyaplace_organizations_for_filter);
        }
        
        $pagespeed['end'] = time();

        if ($this->is_developer) {
            $this->design->assign('pagespeed', $pagespeed);
        }
        
        return $this->design->fetch('orders.tpl');
    }
}
