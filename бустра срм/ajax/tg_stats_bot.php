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

/*
 * Любые публичные функции этого класса можно вызвать при обращении к апи
 * Аргументы функций будут автоматически браться из get запроса
 * Аргументы могут иметь значение по-умолчанию
 * Задать ответ можно используя return или напрямую отредактировав массив $this->response
 * Ответ будет преобразован в JSON
 *
 * ПРИМЕР
 * public function test(a, b, c = 100)
 * {
 *    return a + b * c;
 * }
 * Теперь в нашем АПИ есть метод test, он ожидает параметры a, b и имеет необязательный параметр c
 * В ответ на обращение к апи мы получим результат a + b * c
 */

class TgStatsBot extends Simpla
{
    const TOKEN = 'tZvT2jlqWrWF8mtzAbiFBY4kRR5QLS';

    private $response = [];

    public function __construct()
    {
        parent::__construct();
        if ($this->can_access_api())
            $this->handle_api_method();
        else
            $this->response['error'] = 'Wrong token.';

        echo json_encode($this->response);
    }

    /**
     * true, если разрешён доступ к апи.
     * @return bool
     */
    private function can_access_api()
    {
        $token = $this->request->get('token');
        return !empty($token) && $token == self::TOKEN;
    }

    /**
     * Получает имя метода из GET параметра "method".
     * Если метод существует в классе и он public - вызывает его.
     */
    private function handle_api_method()
    {
        $method = $this->request->get('method');
        if (empty($method))
        {
            $this->response['error'] = '"method" param is empty.';
            return;
        }

        if (method_exists($this, $method))
        {
            $reflection = new ReflectionMethod($this, $method);
            if ($reflection->isPublic())
            {
                $required_params = $reflection->getParameters();
                $params = [];
                foreach ($required_params as $param)
                {
                    $param_name = $param->getName();
                    $param_value = $this->request->get($param_name);
                    if (!isset($param_value))
                    {
                        if ($param->isOptional())
                        {
                            $params[] = $param->getDefaultValue();
                            continue;
                        }
                        else
                        {
                            $this->response['error'] = 'Method "' . $param_name . '" argument is missing.';
                            return;
                        }
                    }
                    $params[] = $param_value;
                }
                $return = $this->$method(...$params);
                if (!empty($return))
                    $this->response = $return;
                return;
            }
        }
        $this->response['error'] = 'Wrong method.';
    }

    /**
     * Возвращает текущую дату в формате Y-m-d.
     * **Если сейчас полночь** - возвращает прошедший день.
     * @return string
     */
    private function get_today()
    {
        $current_hour = date('H');
        if ($current_hour == '00') // Если полночь - берём предыдущий день
            return date('Y-m-d', strtotime('-1 days'));
        else
            return date('Y-m-d');
    }

    /*
     * МЕТОДЫ API
     */

    public function nk_statistic()
    {
        $statistic = [
            'total' => 0,
            'total_approved' => 0,
            'total_confirmed' => 0,
            'total_sum_day' => 0,
            'total_sum_confirmed_day' => 0,
            'total_count_confirmed_day' => 0
        ];
        $today = $this->get_today();

        //  Статистика по заявкам
        $sql = $this->db->placehold(
            "SELECT s_orders.id, confirm_date, approve_date, amount, s_orders.utm_source, date, status
                FROM s_orders
                LEFT JOIN s_users AS u
                ON u.id = s_orders.user_id
                WHERE date >= ? AND date <= ? AND have_close_credits = 0
                AND additional_data_added = 1",
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $orders = $this->db->results() ?? [];

        $today_timestamp = strtotime($today . ' 23:59:59');
        foreach ($orders as $order)
        {
            $statistic['total']++;
            if ($order->approve_date)
                $statistic['total_approved']++;
            if ($order->confirm_date && $order->status == 10)
            {
                $confirm_date = strtotime($order->confirm_date);
                if ($confirm_date <= $today_timestamp)
                {
                    $statistic['total_sum_day'] += $order->amount;
                    $statistic['total_confirmed']++;
                }
            }
        }

        //  Сумма одобренных заявок
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
        $confirmed_orders = $this->db->result() ?? [];

        $statistic['total_sum_confirmed_day'] += $confirmed_orders->orders_sum;
        $statistic['total_count_confirmed_day'] += $confirmed_orders->orders_count;

        return $statistic;
    }

    public function pk_statistic()
    {
        $statistic = [
            'total' => 0,
            'total_approved' => 0,
            'total_confirmed' => 0,
            'total_sum_day' => 0,
            'total_sum_confirmed_day' => 0,
            'total_count_confirmed_day' => 0
        ];
        $today = $this->get_today();

        //  Статистика по заявкам
        $sql = $this->db->placehold(
            "SELECT s_orders.id, confirm_date, approve_date, amount, s_orders.utm_source, date, status
                FROM s_orders
                LEFT JOIN s_users AS u
                ON u.id = s_orders.user_id
                WHERE date >= ? AND date <= ? AND have_close_credits = 1
                AND additional_data_added = 1",
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        );
        $this->db->query($sql);
        $orders = $this->db->results() ?? [];

        $today_timestamp = strtotime($today . ' 23:59:59');
        foreach ($orders as $order)
        {
            $statistic['total']++;
            if ($order->approve_date)
                $statistic['total_approved']++;
            if ($order->confirm_date && $order->status == 10)
            {
                $confirm_date = strtotime($order->confirm_date);
                if ($confirm_date <= $today_timestamp)
                {
                    $statistic['total_sum_day'] += $order->amount;
                    $statistic['total_confirmed']++;
                }
            }
        }

        //  Сумма одобренных заявок
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
        $confirmed_orders = $this->db->result() ?? [];

        $statistic['total_sum_confirmed_day'] += $confirmed_orders->orders_sum;
        $statistic['total_count_confirmed_day'] += $confirmed_orders->orders_count;

        return $statistic;
    }

    public function nk_ya_metric()
    {
        $today = $this->get_today();
        $filter_data = [
            'filter_date_start' => new \DateTime($today),
            'filter_date_end' => new \DateTime($today),
            'isNewUser' => true,
            'filter_group_by' => 'day',
            'filter_client_type' => $this->orders::ORDER_BY_NEW_CLIENT,
            'filter_no_validate_postback' => $this->orders::UTM_SOURCES_NO_VALIDATE_POSTBACK
        ];

        $yandex_metric = new api\YaMetric\YaMetric();
        $metric_response =  $yandex_metric->getStatistic(
            [
                'view' => 'ym:s:{{type_visit}}'
            ], $filter_data);

        foreach ($metric_response['data'] as $metric_data) {
            $result = [
                'visits' => (int)$metric_data['metrics'][0]
            ];
        }

        return $result;
    }
}

(new TgStatsBot());
