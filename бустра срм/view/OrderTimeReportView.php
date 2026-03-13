<?php

use api\DbModels\OrderTimeReportDbModel;

require_once 'View.php';

class OrderTimeReportView extends View
{
    public const ARRAY_TIME_PERCENTS = [
        '1-3' => 0,
        '4-5' => 0,
        '6-7' => 0,
        '8-9' => 0,
        '10+' => 0,
    ];

    /**
     * Статусы, которые будут в разбивке в колонке "Количество заявок в фильтре"
     */
    public const STATUS_ORDER_FOR_REPORT = [
        Orders::ORDER_STATUS_CRM_NEW => 'В работе',
        Orders::ORDER_STATUS_CRM_CORRECTION => 'На исправлении',
        Orders::ORDER_STATUS_CRM_CORRECTED => 'Исправлена',
        Orders::ORDER_STATUS_CRM_WAITING => 'В ожидании',
    ];

    public function fetch()
    {
        $data_filters = [
            'filter_manager_query' => '',
            'filter_client_query' => '',
            'filter_source_query' => '',
            'date_from' => '2021-01-01',
            'date_to' => date('Y-m-d'),
        ];

        $manager_data = [];

        if ($date_range = $this->request->get('daterange'))
        {
            list($from, $to) = explode('-', $date_range);

            $data_filters['date_from'] = date('Y-m-d', strtotime($from));
            $data_filters['date_to'] = date('Y-m-d', strtotime($to));
            
            $this->design->assign('date_from', $data_filters['date_from']);
            $this->design->assign('date_to', $data_filters['date_to']);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);
            $this->design->assign('statuses', self::STATUS_ORDER_FOR_REPORT);
            
            if ($filter_manager = $this->request->get('filter_manager'))
            {
                $data_filters['filter_manager_query'] = $this->db->placehold("AND manager_id = ?", $filter_manager);
                $this->design->assign('filter_manager', $data_filters['filter_manager_query']);
            }
            
            if ($filter_source = $this->request->get('filter_source'))
            {
                $data_filters['filter_source_query'] = $this->db->placehold("AND utm_source = ?", $filter_source);
                $this->design->assign('filter_source', $data_filters['filter_source_query']);
            }
            
            if ($filter_client = $this->request->get('filter_client'))
            {
                $data_filters['filter_client_query'] = $this->db->placehold("AND have_close_credits = ?", (int)($filter_client == 'pk'));
                $this->design->assign('filter_client', $data_filters['filter_client_query']);
            }

            $OrderTimeReportDbModel = new OrderTimeReportDbModel();
            $orders = $OrderTimeReportDbModel->getOrdersTime($data_filters);

            $total_count_orders = count($orders);
            $orders_array = [0, 0];
            $waiting_statuses_default_array = array_combine(array_keys(self::STATUS_ORDER_FOR_REPORT), array_fill(0, count(self::STATUS_ORDER_FOR_REPORT), 0));

            foreach ($orders as $order)
            {
                if (!isset($manager_data[$order->manager_id])) {
                    $manager_data[$order->manager_id] = [
                        'manager' => $this->managers->get_manager($order->manager_id),
                        'total_new_client_work_time' => $order->have_close_credits ? 0 : $order->time_seconds_diff,
                        'total_repeat_client_work_time' => $order->have_close_credits ? $order->time_seconds_diff : 0,
                        'avg_work_time' => 0,
                        'all_work_time' => 0,
                        'total_finished_orders' => $orders_array,
                        'total_in_processed_orders' => $orders_array,
                        'orders_for_percent_new_client' => self::ARRAY_TIME_PERCENTS,
                        'orders_for_percent_repeat_client' => self::ARRAY_TIME_PERCENTS,
                        'percent_new_client' => self::ARRAY_TIME_PERCENTS,
                        'percent_repeat_client' => self::ARRAY_TIME_PERCENTS,
                        'total_waiting_orders' => $waiting_statuses_default_array,
                        'waiting_orders' => $waiting_statuses_default_array,
                    ];
                } else {
                    $manager_data[$order->manager_id]['all_work_time'] += $order->time_seconds_diff;
                    $index_array_percent = $this->getArrayPercentsIndex($order->time_seconds_diff);

                    if (!empty($order->have_close_credits)) {
                        $index_orders_for_percent_array = 'orders_for_percent_repeat_client';
                        $index_client_work_time = 'total_repeat_client_work_time';
                    } else {
                        $index_orders_for_percent_array = 'orders_for_percent_new_client';
                        $index_client_work_time = 'total_new_client_work_time';
                    }

                    if ($order->finished) {
                        $manager_data[$order->manager_id][$index_orders_for_percent_array][$index_array_percent]++;
                        $manager_data[$order->manager_id][$index_client_work_time] += $order->time_seconds_diff;
                    } elseif(key_exists($order->status, self::STATUS_ORDER_FOR_REPORT)) {
                        $manager_data[$order->manager_id]['waiting_orders'][$order->status]++;
                    }
                }

                $this->incrementFinishedOrProcessed($manager_data[$order->manager_id], $order);
            }

            array_walk($manager_data, function (&$manager) {
                $all_time_seconds = $manager['total_new_client_work_time'] + $manager['total_repeat_client_work_time'];

                $total_finished_new_client_orders = $manager['total_finished_orders'][0];
                $total_finished_repeat_client_orders = $manager['total_finished_orders'][1];
                $total_finished_orders = $total_finished_new_client_orders + $total_finished_repeat_client_orders;

                $manager['all_work_time'] = $this->secondsToTime($all_time_seconds);
                $manager['avg_work_time'] = $this->get_avg_time($all_time_seconds, $total_finished_orders);

                $manager['percent_new_client'] = $this->generatePercents($manager['orders_for_percent_new_client'], $total_finished_new_client_orders);
                $manager['percent_repeat_client'] = $this->generatePercents($manager['orders_for_percent_repeat_client'], $total_finished_repeat_client_orders);

                $manager = (object)$manager;
            });

            $this->design->assign('total_count_orders', $total_count_orders);
            $this->design->assign('manager_data', $manager_data);

            $totals = $this->generateTotals($manager_data);
            $this->design->assign('totals', $totals);
        }

        $sources = $this->getUtmSources($data_filters);
        $this->design->assign('sources', $sources);
        
        return $this->design->fetch('order_time_report.tpl');
    }

    /**
     * @param array $manager_data
     * @param object $order
     * @return void
     */
    private function incrementFinishedOrProcessed(array &$manager_data, object $order)
    {
        if ($order->finished) {
            $manager_data['total_finished_orders'][$order->have_close_credits]++;
        } else {
            $manager_data['total_in_processed_orders'][$order->have_close_credits]++;
        }
    }

    /**
     * @param int $total_seconds
     * @param int $total_orders
     * @return false|string
     */
    private function get_avg_time(int $total_seconds, int $total_orders)
    {
        if ($total_orders > 0) {
            $avg_time = intval($total_seconds / $total_orders);
        } else {
            $avg_time = 0;
        }
        return $this->secondsToTime($avg_time);
    }

    /**
     * @param array $data_filters
     * @return array|false
     */
    private function getUtmSources(array $data_filters)
    {
        $query = $this->db->placehold("
            SELECT DISTINCT o.utm_source
            FROM s_orders AS o
            WHERE DATE(o.accept_date) >= ?
            AND DATE(o.accept_date) <= ?
            AND (o.confirm_date IS NOT NULL OR o.reject_date IS NOT NULL)
            " . $data_filters['filter_client_query'] . "
            " . $data_filters['filter_manager_query'] . "
        ", $data_filters['date_from'], $data_filters['date_to']);
        $this->db->query($query);

        $sources = $this->db->results('utm_source');
        sort($sources);

        return $sources;
    }

    /**
     * @param array $orders_for_percent
     * @param int $total_orders
     * @return int[]
     */
    private function generatePercents(array $orders_for_percent, int $total_orders): array
    {
        $percents_array = self::ARRAY_TIME_PERCENTS;
        foreach ($orders_for_percent as $key => $order_for_percent) {
            $percents_array[$key] = $total_orders ? round(($order_for_percent * 100 / $total_orders), 2) : 0;
        }
        return $percents_array;
    }

    /**
     * @param int $seconds
     * @return string
     */
    private function getArrayPercentsIndex(int $seconds): string
    {
        $minutes = round($seconds / 60);

        if ($minutes >= 10) {
            return '10+';
        } elseif ($minutes >= 8) {
            return '8-9';
        } elseif ($minutes >= 6) {
            return '6-7';
        } elseif ($minutes >= 4) {
            return '4-5';
        } else {
            return '1-3';
        }
    }

    /**
     * @param array $managers
     * @return object
     */
    private function generateTotals(array $managers): object
    {
        $total_new_client_work_time = array_sum(array_column($managers, 'total_new_client_work_time'));
        $total_repeat_client_work_time = array_sum(array_column($managers, 'total_repeat_client_work_time'));
        $total_seconds = $total_new_client_work_time + $total_repeat_client_work_time;

        $total_finished_orders = array_sum(
            array_map(fn($value) => array_sum($value), array_column($managers, 'total_finished_orders'))
        );
        $total_in_processed_orders = array_sum(
            array_map(fn($value) => array_sum($value), array_column($managers, 'total_in_processed_orders'))
        );

        $total_percent_new_client = self::ARRAY_TIME_PERCENTS;
        $total_percent_repeat_client = self::ARRAY_TIME_PERCENTS;
        $total_waiting_orders = array_combine(array_keys(self::STATUS_ORDER_FOR_REPORT), array_fill(0, count(self::STATUS_ORDER_FOR_REPORT), 0));

        foreach (array_column($managers, 'orders_for_percent_new_client') as $values_array_percent) {
            foreach ($values_array_percent as $key => $value) {
                $total_percent_new_client[$key] += $value;
            }
        }

        foreach (array_column($managers, 'orders_for_percent_repeat_client') as $values_array_percent) {
            foreach ($values_array_percent as $key => $value) {
                $total_percent_repeat_client[$key] += $value;
            }
        }

        foreach (array_column($managers, 'waiting_orders') as $waiting_orders) {
            foreach ($waiting_orders as $key => $value) {
                $total_waiting_orders[$key] += $value;
            }
        }

        return (object)[
            'total_waiting_orders' => $total_waiting_orders,
            'total_all_time' => $this->secondsToTime($total_seconds),
            'total_avg_work_time' => $this->get_avg_time($total_seconds, $total_finished_orders),
            'total_finished_orders' => $total_finished_orders,
            'total_in_processed_orders' => $total_in_processed_orders,
            'percent_new_client' => $this->generatePercents($total_percent_new_client, $total_finished_orders),
            'percent_repeat_client' => $this->generatePercents($total_percent_repeat_client, $total_finished_orders),
        ];
    }

    /**
     * @param $seconds
     * @return string
     */
    private function secondsToTime($seconds): string
    {
        $remains_hours = floor($seconds / (60 * 60));
        $remains_minutes = floor($seconds / 60) - $remains_hours * 60;
        $remains_seconds = $seconds - ($remains_minutes * 60) - ($remains_hours * 3600);

        return sprintf("%s ч. %s мин. %s сек.", $remains_hours, $remains_minutes, $remains_seconds);
    }
}