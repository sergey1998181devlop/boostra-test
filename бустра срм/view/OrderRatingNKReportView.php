<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', '600');

require_once 'View.php';
require_once dirname(__DIR__) . '/api/CustomMetric.php';

/**
 * Отчёт Кредитный рейтинг Новый клиент
 * Class OrderRatingNKReportView
 */
class OrderRatingNKReportView extends View
{
    /**
     * Список метрик для отчёта
     */
    public const GOALS = [
        'view' => CustomMetric::GOAL_CR_NK_VISIT_PAGE,
        'get_rating' => CustomMetric::GOAL_CR_NK_CLICK_BTN,
        'get_code' => CustomMetric::GOAL_CR_NK_CLICK_SMS_CODE,
        'reg_code' => CustomMetric::GOAL_CR_NK_REGISTER_SMS_CODE,
        'get_pay' => CustomMetric::GOAL_CR_NK_OPEN_PAY_PAGE,
    ];

    /**
     * Дефолтные ключи массива
     */
    public const DEFAULT_ARRAY_KEYS = [
        'view',
        'get_rating',
        'get_code',
        'reg_code',
        'get_pay',
        'total_pays_count',
        'total_pays_money'
    ];

    public function fetch()
    {
        if (!empty($this->request->get('ajax', 'integer'))) {
            $data = [];

            $filter_date = Helpers::getDataRange($this);

            $filter_data = [
                'filter_date_added' => $filter_date,
            ];

            $filter_data['filter_group_by'] = $this->request->get('filter_group_by') ?: 'day';

            $data['items'] = [];

            foreach (self::GOALS as $goal_key => $goal) {
                $filter_data['filter_goal_id'] = $goal;
                $data['items'][$goal_key] = $this->custom_metric->getTotalsMetricActions($filter_data);
            }

            $filter_data_tinkoff = [
                'filter_date_created' => $filter_date,
                'filter_payment_type' => $this->transactions::PAYMENT_TYPE_CREDIT_RATING_FOR_NK,
                'filter_statuses' => $this->transactions::STATUSES_SUCCESS,
                'filter_group_by' => $filter_data['filter_group_by'],
            ];
            $total_pays_count_tinkoff = $this->transactions->count_transactions($filter_data_tinkoff);
            $total_pays_amount_tinkoff = $this->transactions->getTotalAmount($filter_data_tinkoff);

            $filter_data_b2p = [
                'filter_date_created' => $filter_date,
                'filter_payment_type' => $this->best2pay::PAYMENT_TYPE_CREDIT_RATING_FOR_NK,
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
            $items = $this->mergeData($data);
            $totals = $this->generateTotals($items);

            // записываем данные для шаблона
            $this->design->assign('items', $items);
            $this->design->assign('totals', $totals);
        }

        return $this->design->fetch('order_rating_nk_report.tpl');
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
     * @return array
     */
    private function mergeData($data): array
    {
        $metric = [];
        $default_array = self::getDefaultArray();

        foreach ($data['items'] as $array_key => $arrays) {
            foreach ($arrays as $array) {
                $date = $array->filter_date;

                if (!isset($metric[$date])) {
                    $metric[$date] = [
                        'items' => $default_array,
                        'cv' => $default_array,
                    ];
                }

                $metric[$date]['items'][$array_key] += (int)$array->total;
            }
        }

        // переберём платежи
        foreach ($data['pays'] as $key_array => $array) {
            foreach ($array as $row) {
                $date = $row->filter_date;

                if (!isset($metric[$date]['items'])) {
                    $metric[$date] = [
                        'items' => $default_array,
                        'cv' => $default_array,
                    ];
                }

                if (in_array($key_array, ['total_pays_count_tinkoff', 'total_pays_count_b2p'])) {
                    $key_result = 'total_pays_count';
                    $key_value = 'count';
                } else {
                    $key_result = 'total_pays_money';
                    $key_value = 'total_amount';
                }

                $metric[$date]['items'][$key_result] += (int)$row->{$key_value};
            }
        }

        // после сбора данных соберем отчёт по формулам
        foreach ($metric as $date => $metric_row) {
            $metric[$date]['cv'] = $this->generateCV($metric_row['items']);
        }

        return $metric;
    }

    /**
     * Генерирует конверсии
     * @param array $metric
     * @return array
     */
    private function generateCV(array $metric): array
    {
        $result = self::getDefaultArray();
        $prev_key = false;

        foreach ($metric as $key => $metric_data) {
            if ($key === 'get_rating') {
                $prev_key = $key;
            } elseif ($key === 'total_pays_money') {
                continue;
            }

            if ($prev_key) {
                $result[$key] = $metric_data > 0 && !empty($metric[$prev_key]) ? round($metric_data * 100 / $metric[$prev_key], 2) : 0;
            }
        }

        return $result;
    }

    /**
     * Генерируем итоговую строку
     * @param array $items
     * @return array|array[]
     */
    private function generateTotals(array $items): array
    {
        $totals = [
            'items' => self::getDefaultArray(),
        ];

        foreach (array_keys($totals['items']) as $key) {
            foreach ($items as $array) {
                $totals['items'][$key] += $array['items'][$key];
            }
        }

        $totals['cv'] = $this->generateCV($totals['items']);

        return $totals;
    }
}