<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', '600');

require_once 'View.php';
require_once dirname(__DIR__) . '/api/YaMetric.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

/**
 * Отчёт Кредитный рейтинг Почему отказ
 * Class OrderRatingRejectReportView
 */
class OrderRatingRejectReportView extends View
{
    /**
     * Список метрик для отчёта
     */
    public const GOALS = [
        'view' => 280631620, // показ ссылки "Почему отказ"
        'click_view_link' => 280693542, // клик по ссылке
        'click_get_rating_link' => 280695557, // клик "Получить рейтинг"
        'click_get_sms_code' => 280698004, // клик "получить код смс"
        'register_sms_code' => 280699067, // регистрация кода смс, когда правильно ввел отправленный смс код
        'send_to_pay' => 280699412, // отправка на оплату после ввода смс
    ];

    public const DEFAULT_ARRAY_KEYS = [
        'view',
        'click_view_link',
        'click_get_rating_link',
        'click_get_sms_code',
        'register_sms_code',
        'send_to_pay',
        'total_pays_count',
        'total_pays_money',
    ];

    private $yandex_metric;

    public function __construct()
    {
        parent::__construct();
        $this->yandex_metric = new api\YaMetric\YaMetric();
    }

    public function fetch()
    {
        if (!empty($this->request->get('ajax', 'integer'))) {
            $data = [];

            $filter_date = Helpers::getDataRange($this);

            $filter_data = [
                'filter_date_start' => new DateTime($filter_date['filter_date_start']),
                'filter_date_end' => new DateTime($filter_date['filter_date_end']),
            ];

            $filter_data['filter_group_by'] = $this->request->get('filter_group_by') ?: 'day';
            $filter_data['referer'] = $this->config->front_url.'/user/credit_rating';
            $filter_data['is_completed'] = true;
            $filter_data['isNewUser'] = true;

            $data['metric'] = $this->yandex_metric->getStatistic(self::GOALS, $filter_data);

            $filter_data_tinkoff = [
                'filter_date_created' => $filter_date,
                'filter_payment_type' => $this->transactions::PAYMENT_TYPE_CREDIT_RATING_AFTER_REJECTION,
                'filter_statuses' => $this->transactions::STATUSES_SUCCESS,
                'filter_group_by' => $filter_data['filter_group_by'],
            ];
            $total_pays_count_tinkoff = $this->transactions->count_transactions($filter_data_tinkoff);
            $total_pays_amount_tinkoff = $this->transactions->getTotalAmount($filter_data_tinkoff);

            $filter_data_b2p = [
                'filter_date_created' => $filter_date,
                'filter_payment_type' => $this->best2pay::PAYMENT_TYPE_CREDIT_RATING_AFTER_REJECTION,
                'reason_code' => 1,
                'filter_group_by' => $filter_data['filter_group_by'],
            ];
            $total_pays_count_b2p = $this->best2pay->count_payments($filter_data_b2p);
            $total_pays_amount_b2p = $this->best2pay->getTotalAmount($filter_data_b2p);

            $data['pays'] = compact(
                'total_pays_count_tinkoff',
                'total_pays_amount_tinkoff',
                'total_pays_count_b2p',
                'total_pays_amount_b2p'
            );

            // генерируем финальный массив
            $items = $this->mergeData($data, $filter_data['filter_group_by'] === 'month');
            $totals = $this->generateTotals($items);

            // записываем данные для шаблона
            $this->design->assign('items', $items);
            $this->design->assign('totals', $totals);
        }

        return $this->design->fetch('order_rating_reject_report.tpl');
    }

    /**
     * Формирует дефолтный массив
     * @return array|false
     */
    public static function getDefaultArray()
    {
       return array_combine(self::DEFAULT_ARRAY_KEYS, array_fill(0, count(self::DEFAULT_ARRAY_KEYS), 0));
    }


    /**
     * Делает гибридный массив из данных метрики и БД
     * @param $data
     * @param bool $is_month
     * @return array
     */
    private function mergeData($data, bool $is_month = false): array
    {
        $metric = [];
        $default_array = self::getDefaultArray();

        // переберем сначала метрику и приведем к виду Б.Д.
        foreach ($data['metric']['data'] as $metric_data) {
            $date = str_replace('-', '.', $metric_data['dimensions'][0]['name']);
            if ($is_month) {
                $date = substr($date, 0, -3);
            }

            if (!isset($metric[$date])) {
                $metric[$date] = $default_array;
            }

            $metric[$date]['view'] += $metric_data['metrics'][0];
            $metric[$date]['click_view_link'] += $metric_data['metrics'][1];
            $metric[$date]['click_get_rating_link'] += $metric_data['metrics'][2];
            $metric[$date]['click_get_sms_code'] += $metric_data['metrics'][3];
            $metric[$date]['register_sms_code'] += $metric_data['metrics'][4];
            $metric[$date]['send_to_pay'] += $metric_data['metrics'][5];

            $metric[$date] = array_map('intval', $metric[$date]);
        }

        // переберём платежи
        foreach ($data['pays'] as $key_array => $array) {
            foreach ($array as $row) {
                $date = $row->filter_date;

                if (!isset($metric[$date])) {
                    $metric[$date] = $default_array;
                }

                if (in_array($key_array, ['total_pays_count_tinkoff', 'total_pays_count_b2p'])) {
                    $key_result = 'total_pays_count';
                    $key_value = 'count';
                } else {
                    $key_result = 'total_pays_money';
                    $key_value = 'total_amount';
                }

                $metric[$date][$key_result] += (int)$row->{$key_value};
            }
        }

        return $metric;
    }

    /**
     * Генерируем итоговую строку
     * @param array $items
     * @return array|array[]
     */
    private function generateTotals(array $items): array
    {
        $totals = self::getDefaultArray();

        foreach (array_keys($totals) as $key) {
            $totals[$key] = array_sum(array_column($items, $key));
        }

        return $totals;
    }
}