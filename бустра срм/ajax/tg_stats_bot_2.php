<?php

date_default_timezone_set('Europe/Moscow');

header('Content-type: application/json; charset=UTF-8');
header('Cache-Control: must-revalidate');
header('Pragma: no-cache');
header('Expires: -1');

define('ROOT', dirname(__DIR__));

session_start();
chdir('..');

require 'api/Simpla.php';
require_once dirname(__DIR__) . '/api/YaMetric.php';

$simpla = new Simpla();

class TgStatsBot extends Simpla
{
    const TOKEN = 'tZvT2jlqWrWF8mtzAbiFBY4kRR5QLS';

    private $response = [];

    public function __construct()
    {
        parent::__construct();
        if ($this->can_access_api()) {
            $this->handle_api_method();
        } else {
            $this->response['error'] = 'Wrong token.';
        }

        echo json_encode($this->response);
    }

    private function can_access_api()
    {
        $token = $this->request->get('token');
        return !empty($token) && $token == self::TOKEN;
    }

    private function handle_api_method()
    {
        $method = $this->request->get('method');
        if (empty($method)) {
            $this->response['error'] = '"method" param is empty.';
            return;
        }

        switch ($method) {
            case 'nk_statistic':
                $this->response = $this->nk_statistic();
                break;

            case 'pk_statistic':
                $this->response = $this->pk_statistic();
                break;

            case 'nk_ya_metric':
                $this->response = $this->nk_ya_metric();
                break;

            case 'nk_dobegs':
                $this->response = $this->nk_dobegs();
                break;

            case 'auto_statistic':
                $this->response = $this->auto_statistic();
                break;

            case 'crossorder_statistic':
                $this->response = $this->crossorder_statistic();
                break;

            case 'pk_auto_statistic':
                $this->response = $this->pk_auto_statistic();
                break;

            case 'pk_crossorder_statistic':
                $this->response = $this->pk_crossorder_statistic();
                break;

            case 'nk_crossorder_statistic':
                $this->response = $this->nk_crossorder_statistic();
                break;

            default:
                $this->response['error'] = 'Wrong method.';
                break;
        }
    }

    /**
     * Возвращает текущую дату в формате Y-m-d.
     * Если сейчас полночь - возвращает прошедший день.
     */
    private function get_today()
    {
        $current_hour = date('H');
        if ($current_hour == '00') {
            return date('Y-m-d', strtotime('-1 days'));
        } else {
            return date('Y-m-d');
        }
    }

    /**
     * Проверяет, является ли заявка ping3 (order_from_partner).
     */
    private function is_ping3($order_id)
    {
        $sql = $this->db->placehold(
            "SELECT 1 FROM s_order_data WHERE order_id = ? AND `key` = 'order_from_partner' LIMIT 1",
            $order_id
        );
        $this->db->query($sql);
        return $this->db->result() !== null;
    }

    /**
     * Проверяет, является ли заявка авто-заявкой.
     */
    private function is_auto_application($user_id)
    {
        $sql = $this->db->placehold(
            "SELECT 1 FROM s_user_data WHERE user_id = ? AND `key` = 'active_autoconfirm_flow' LIMIT 1",
            $user_id
        );
        $this->db->query($sql);
        return $this->db->result() !== null;
    }

    /**
     * Проверяет, является ли заявка кросс-заявкой.
     */
    private function is_cross_application($order_id)
    {
        $sql = $this->db->placehold(
            "SELECT 1 FROM s_cross_orders WHERE parent_order_id = ? LIMIT 1",
            $order_id
        );
        $this->db->query($sql);
        return $this->db->result() !== null;
    }

    /**
     * НК статистика с расширенными метриками.
     */
    private function nk_statistic()
    {
        $statistic = [
            'total' => 0,
            'total_hour' => 0,
            'total_approved' => 0,
            'total_confirmed' => 0,
            'total_confirmed_ping3' => 0,
            'total_sum_day' => 0,
            'total_sum_confirmed_day' => 0,
            'total_count_confirmed_day' => 0,
            'total_rc' => 0,
            'total_auto' => 0,
            'total_cross' => 0,
            'approved_ping3' => 0,
            'approved_cpa' => 0
        ];
        $today = $this->get_today();
        $current_hour = date('H');
        $hour_start = $today . ' ' . $current_hour . ':00:00';
        $hour_end = $today . ' ' . $current_hour . ':59:59';

        $sql = $this->db->placehold(
            "SELECT o.id, o.user_id, o.confirm_date, o.approve_date, o.amount, o.date, o.status
                FROM s_orders o
                LEFT JOIN s_users AS u ON u.id = o.user_id
                WHERE o.date >= ? AND o.date <= ? 
                AND o.have_close_credits = 0
                AND u.additional_data_added = 1",
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $orders = $this->db->results() ?? [];

        $today_timestamp = strtotime($today . ' 23:59:59');

        foreach ($orders as $order) {
            $statistic['total']++;

            $order_date = strtotime($order->date);
            if ($order->date >= $hour_start && $order->date <= $hour_end) {
                $statistic['total_hour']++;
            }

            $is_ping3 = $this->is_ping3($order->id);
            $is_auto = $this->is_auto_application($order->user_id);
            $is_cross = $this->is_cross_application($order->id);

            if ($is_auto) {
                $statistic['total_auto']++;
            } elseif ($is_cross) {
                $statistic['total_cross']++;
            }

            if ($order->approve_date) {
                $statistic['total_approved']++;
                if ($is_ping3) {
                    $statistic['approved_ping3']++;
                } else {
                    $statistic['approved_cpa']++;
                }
            }

            if ($order->confirm_date && $order->status == 10) {
                $confirm_date = strtotime($order->confirm_date);
                if ($confirm_date <= $today_timestamp) {
                    $statistic['total_sum_day'] += $order->amount;
                    $statistic['total_confirmed']++;

                    if ($is_ping3) {
                        $statistic['total_confirmed_ping3']++;
                    }
                }
            }
        }

        $sql = $this->db->placehold(
            "SELECT SUM(amount) as orders_sum, COUNT(*) AS orders_count
            FROM s_orders 
            WHERE have_close_credits = 0
            AND confirm_date >= ? AND confirm_date <= ?  
            AND status = 10",
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $confirmed_orders = $this->db->result();

        if ($confirmed_orders) {
            $statistic['total_sum_confirmed_day'] = (float)($confirmed_orders->orders_sum ?? 0);
            $statistic['total_count_confirmed_day'] = (int)($confirmed_orders->orders_count ?? 0);
        }

        return $statistic;
    }

    /**
     * ПК статистика с расширенными метриками.
     */
    private function pk_statistic()
    {
        $statistic = [
            'total' => 0,
            'total_approved' => 0,
            'total_confirmed' => 0,
            'total_confirmed_ping3' => 0,
            'total_sum_day' => 0,
            'total_sum_confirmed_day' => 0,
            'total_count_confirmed_day' => 0,
            'total_auto' => 0,
            'total_cross' => 0
        ];
        $today = $this->get_today();

        $sql = $this->db->placehold(
            "SELECT o.id, o.user_id, o.confirm_date, o.approve_date, o.amount, o.date, o.status
                FROM s_orders o
                LEFT JOIN s_users AS u ON u.id = o.user_id
                WHERE o.date >= ? AND o.date <= ? 
                AND o.have_close_credits = 1
                AND u.additional_data_added = 1",
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $orders = $this->db->results() ?? [];

        $today_timestamp = strtotime($today . ' 23:59:59');

        foreach ($orders as $order) {
            $statistic['total']++;

            $is_ping3 = $this->is_ping3($order->id);
            $is_auto = $this->is_auto_application($order->user_id);
            $is_cross = $this->is_cross_application($order->id);

            if ($is_auto) {
                $statistic['total_auto']++;
            } elseif ($is_cross) {
                $statistic['total_cross']++;
            }

            if ($order->approve_date) {
                $statistic['total_approved']++;
            }

            if ($order->confirm_date && $order->status == 10) {
                $confirm_date = strtotime($order->confirm_date);
                if ($confirm_date <= $today_timestamp) {
                    $statistic['total_sum_day'] += $order->amount;
                    $statistic['total_confirmed']++;

                    if ($is_ping3) {
                        $statistic['total_confirmed_ping3']++;
                    }
                }
            }
        }

        $sql = $this->db->placehold(
            "SELECT SUM(amount) as orders_sum, COUNT(*) AS orders_count
            FROM s_orders 
            WHERE have_close_credits = 1
            AND confirm_date >= ? AND confirm_date <= ?  
            AND status = 10",
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $confirmed_orders = $this->db->result();

        if ($confirmed_orders) {
            $statistic['total_sum_confirmed_day'] = (float)($confirmed_orders->orders_sum ?? 0);
            $statistic['total_count_confirmed_day'] = (int)($confirmed_orders->orders_count ?? 0);
        }

        return $statistic;
    }

    /**
     * Яндекс Метрика - переходы.
     */
    private function nk_ya_metric()
    {
        $today = $this->get_today();
        $current_hour = date('H');

        $filter_data = [
            'filter_date_start' => new \DateTime($today),
            'filter_date_end' => new \DateTime($today),
            'isNewUser' => true,
            'filter_group_by' => 'day',
            'filter_client_type' => $this->orders::ORDER_BY_NEW_CLIENT,
            'filter_no_validate_postback' => $this->orders::UTM_SOURCES_NO_VALIDATE_POSTBACK
        ];

        $yandex_metric = new api\YaMetric\YaMetric();
        $metric_response = $yandex_metric->getStatistic(
            ['view' => 'ym:s:{{type_visit}}'],
            $filter_data
        );

        $result = [
            'visits' => 0,
            'visits_hour' => 0
        ];

        foreach ($metric_response['data'] as $metric_data) {
            $result['visits'] = (int)$metric_data['metrics'][0];
        }

        $filter_data_hour = [
            'filter_date_start' => new \DateTime($today . ' ' . $current_hour . ':00:00'),
            'filter_date_end' => new \DateTime($today . ' ' . $current_hour . ':59:59'),
            'isNewUser' => true,
            'filter_group_by' => 'hour',
            'filter_client_type' => $this->orders::ORDER_BY_NEW_CLIENT,
            'filter_no_validate_postback' => $this->orders::UTM_SOURCES_NO_VALIDATE_POSTBACK
        ];

        $metric_response_hour = $yandex_metric->getStatistic(
            ['view' => 'ym:s:{{type_visit}}'],
            $filter_data_hour
        );

        foreach ($metric_response_hour['data'] as $metric_data) {
            $result['visits_hour'] = (int)$metric_data['metrics'][0];
        }

        return $result;
    }

    /**
     * Получение добегов.
     */
    private function nk_dobegs()
    {
        $today = $this->get_today();
        $dobegs = [];

        $sql = $this->db->placehold(
            "SELECT o.id, o.user_id, o.date, o.confirm_date, o.approve_date,
                     o.amount, o.status, o.have_close_credits
            FROM s_orders o
            LEFT JOIN s_users AS u ON u.id = o.user_id
            WHERE o.date < ?
            AND o.confirm_date >= ? AND o.confirm_date <= ?
            AND o.have_close_credits = 0
            AND o.status = 10
            AND u.additional_data_added = 1",
            $today . ' 00:00:00',
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $orders = $this->db->results() ?? [];

        foreach ($orders as $order) {
            $is_ping3 = $this->is_ping3($order->id);
            $is_auto = $this->is_auto_application($order->user_id);
            $is_cross = $this->is_cross_application($order->id);

            $dobegs[] = [
                'id' => (int)$order->id,
                'user_id' => (int)$order->user_id,
                'date' => $order->date,
                'confirm_date' => $order->confirm_date,
                'approve_date' => $order->approve_date,
                'amount' => (float)$order->amount,
                'approved' => !empty($order->approve_date),
                'confirmed' => true,
                'is_ping3' => $is_ping3,
                'is_auto' => $is_auto,
                'is_cross' => $is_cross,
                'is_rc' => false
            ];
        }

        return $dobegs;
    }

    /**
     * Статистика по автозаявкам (все — и НК, и ПК).
     * Автозаявка = запись в s_orders_auto_approve по order_id.
     */
    private function auto_statistic()
    {
        $today = $this->get_today();

        $statistic = [
            'total' => 0,
            'total_approved' => 0,
            'total_confirmed' => 0,
            'total_sum_confirmed_day' => 0,
            'total_count_confirmed_day' => 0,
        ];

        $sql = $this->db->placehold(
            "SELECT o.id, o.user_id, o.approve_date, o.confirm_date, o.amount, o.status
            FROM s_orders o
            INNER JOIN s_orders_auto_approve aa ON aa.order_id = o.id
            LEFT JOIN s_users AS u ON u.id = o.user_id
            WHERE o.date >= ? AND o.date <= ?
            AND u.additional_data_added = 1",
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $orders = $this->db->results() ?? [];

        $today_timestamp = strtotime($today . ' 23:59:59');

        foreach ($orders as $order) {
            $statistic['total']++;

            if ($order->approve_date) {
                $statistic['total_approved']++;
            }

            if ($order->confirm_date && $order->status == 10) {
                $confirm_date = strtotime($order->confirm_date);
                if ($confirm_date <= $today_timestamp) {
                    $statistic['total_confirmed']++;
                    $statistic['total_sum_confirmed_day'] += (float)$order->amount;
                    $statistic['total_count_confirmed_day']++;
                }
            }
        }

        return $statistic;
    }

    /**
     * Статистика по кросс-ордерам (все — и НК, и ПК).
     * Кросс-ордер = utm_source = 'cross_order'.
     */
    private function crossorder_statistic()
    {
        $today = $this->get_today();

        $statistic = [
            'total' => 0,
            'total_approved' => 0,
            'total_confirmed' => 0,
            'total_sum_confirmed_day' => 0,
            'total_count_confirmed_day' => 0,
        ];

        $sql = $this->db->placehold(
            "SELECT o.id, o.approve_date, o.confirm_date, o.amount, o.status
            FROM s_orders o
            LEFT JOIN s_users AS u ON u.id = o.user_id
            WHERE o.date >= ? AND o.date <= ?
            AND o.utm_source = 'cross_order'
            AND u.additional_data_added = 1",
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $orders = $this->db->results() ?? [];

        $today_timestamp = strtotime($today . ' 23:59:59');

        foreach ($orders as $order) {
            $statistic['total']++;

            if ($order->approve_date) {
                $statistic['total_approved']++;
            }

            if ($order->confirm_date && $order->status == 10) {
                $confirm_date = strtotime($order->confirm_date);
                if ($confirm_date <= $today_timestamp) {
                    $statistic['total_confirmed']++;
                    $statistic['total_sum_confirmed_day'] += (float)$order->amount;
                    $statistic['total_count_confirmed_day']++;
                }
            }
        }

        return $statistic;
    }

    /**
     * TR автозаявок в разрезе ПК (have_close_credits = 1).
     * Для отображения "TR автозаявки" в разделе ПК ежечасного отчёта.
     */
    private function pk_auto_statistic()
    {
        $today = $this->get_today();

        $statistic = [
            'total' => 0,
            'total_approved' => 0,
            'total_confirmed' => 0,
            'total_sum_confirmed_day' => 0,
            'total_count_confirmed_day' => 0,
        ];

        $sql = $this->db->placehold(
            "SELECT o.id, o.approve_date, o.confirm_date, o.amount, o.status
            FROM s_orders o
            INNER JOIN s_orders_auto_approve aa ON aa.order_id = o.id
            LEFT JOIN s_users AS u ON u.id = o.user_id
            WHERE o.date >= ? AND o.date <= ?
            AND o.have_close_credits = 1
            AND u.additional_data_added = 1",
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $orders = $this->db->results() ?? [];

        $today_timestamp = strtotime($today . ' 23:59:59');

        foreach ($orders as $order) {
            $statistic['total']++;

            if ($order->approve_date) {
                $statistic['total_approved']++;
            }

            if ($order->confirm_date && $order->status == 10) {
                $confirm_date = strtotime($order->confirm_date);
                if ($confirm_date <= $today_timestamp) {
                    $statistic['total_confirmed']++;
                    $statistic['total_sum_confirmed_day'] += (float)$order->amount;
                    $statistic['total_count_confirmed_day']++;
                }
            }
        }

        return $statistic;
    }

    /**
     * TR кросс-ордеров в разрезе ПК (have_close_credits = 1).
     * Для отображения "TR кросс-ордер" в разделе ПК ежечасного отчёта.
     */
    private function pk_crossorder_statistic()
    {
        $today = $this->get_today();

        $statistic = [
            'total' => 0,
            'total_approved' => 0,
            'total_confirmed' => 0,
            'total_sum_confirmed_day' => 0,
            'total_count_confirmed_day' => 0,
        ];

        $sql = $this->db->placehold(
            "SELECT o.id, o.approve_date, o.confirm_date, o.amount, o.status
            FROM s_orders o
            LEFT JOIN s_users AS u ON u.id = o.user_id
            WHERE o.date >= ? AND o.date <= ?
            AND o.utm_source = 'cross_order'
            AND o.have_close_credits = 1
            AND u.additional_data_added = 1",
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $orders = $this->db->results() ?? [];

        $today_timestamp = strtotime($today . ' 23:59:59');

        foreach ($orders as $order) {
            $statistic['total']++;

            if ($order->approve_date) {
                $statistic['total_approved']++;
            }

            if ($order->confirm_date && $order->status == 10) {
                $confirm_date = strtotime($order->confirm_date);
                if ($confirm_date <= $today_timestamp) {
                    $statistic['total_confirmed']++;
                    $statistic['total_sum_confirmed_day'] += (float)$order->amount;
                    $statistic['total_count_confirmed_day']++;
                }
            }
        }

        return $statistic;
    }

    /**
     * TR кросс-ордеров в разрезе НК (have_close_credits = 0).
     * Для отображения "TR кросс-ордер" в разделе НК ежечасного отчёта.
     */
    private function nk_crossorder_statistic()
    {
        $today = $this->get_today();

        $statistic = [
            'total' => 0,
            'total_approved' => 0,
            'total_confirmed' => 0,
            'total_sum_confirmed_day' => 0,
            'total_count_confirmed_day' => 0,
        ];

        $sql = $this->db->placehold(
            "SELECT o.id, o.approve_date, o.confirm_date, o.amount, o.status
            FROM s_orders o
            LEFT JOIN s_users AS u ON u.id = o.user_id
            WHERE o.date >= ? AND o.date <= ?
            AND o.utm_source = 'cross_order'
            AND o.have_close_credits = 0
            AND u.additional_data_added = 1",
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $orders = $this->db->results() ?? [];

        $today_timestamp = strtotime($today . ' 23:59:59');

        foreach ($orders as $order) {
            $statistic['total']++;

            if ($order->approve_date) {
                $statistic['total_approved']++;
            }

            if ($order->confirm_date && $order->status == 10) {
                $confirm_date = strtotime($order->confirm_date);
                if ($confirm_date <= $today_timestamp) {
                    $statistic['total_confirmed']++;
                    $statistic['total_sum_confirmed_day'] += (float)$order->amount;
                    $statistic['total_count_confirmed_day']++;
                }
            }
        }

        return $statistic;
    }
}

(new TgStatsBot());
