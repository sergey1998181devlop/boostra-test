<?php

require_once 'View.php';
require_once dirname(__DIR__) . '/api/Helpers.php';
require_once dirname(__DIR__) . '/api/interfaces/ReportInterface.php';
require_once dirname(__DIR__) . '/api/YaMetric.php';

ini_set('max_execution_time', 180);
ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Отчёт Кредитный рейтинг с отказной заявкой в ЛК
 * Class CRLKRejectOrderReportView
 */
class CRLKRejectOrderReportView extends View implements ReportInterface
{
    /**
     * Список метрик для отчёта
     */
    public const GOALS = [
        'link_viewed' => 280631620, // Показ ссылки "почему отказано" в ЛК
        'link_click' => 280693542, // Клик по ссылке "почему отказано" в ЛК
        'get_rating' => 280695557, // НК Получить рейтинг
        'get_code' => 280698004, // НК Получить код рейтинга
        'reg_code' => 280699067, // НК после регистрации смс кода
        'get_pay' => 280699412, // НК Отправить/оплатить рейтинг
    ];

    public const DEFAULT_ARRAY_KEYS = [
        'link_viewed',
        'link_click',
        'get_rating',
        'get_code',
        'reg_code',
        'get_pay',
        'total_transactions',
        'total_fact_pay'
    ];

    private $yandex_metric;

    public function __construct()
    {
        parent::__construct();
        $this->yandex_metric = new api\YaMetric\YaMetric();
    }

    public function fetch()
    {
        if ($this->request->get('ajax')) {
            $data = $this->getResults();

            // записываем данные для шаблона
            $this->design->assign('metric', $data['metric']);
            $this->design->assign('totals', $data['totals']);
        }

        return $this->design->fetch('cr_lk_reject_order_report_view.tpl');
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

            if (!isset($metric[$date]['items'])) {
                $metric[$date]['items'] = $default_array;
            }

            $metric[$date]['items']['link_viewed'] += $metric_data['metrics'][0];
            $metric[$date]['items']['link_click'] += $metric_data['metrics'][1];
            $metric[$date]['items']['get_rating'] += $metric_data['metrics'][2];
            $metric[$date]['items']['get_code'] += $metric_data['metrics'][3];
            $metric[$date]['items']['reg_code'] += $metric_data['metrics'][4];
            $metric[$date]['items']['get_pay'] += $metric_data['metrics'][5];

            $metric[$date]['items'] = array_map('intval', $metric[$date]['items']);
        }

        // переберём заявки
        foreach ($data['orders'] as $order_row) {
            $date = $order_row->date;

            if (!isset($metric[$date]['items'])) {
                $metric[$date]['items'] = $default_array;
            }

            $metric[$date]['items']['total_transactions'] = (int)$order_row->total_transactions;
            $metric[$date]['items']['total_fact_pay'] = (int)$order_row->total_fact_pay;
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
        $result = [];
        $prev_key = false;

        foreach ($metric as $key => $metric_data) {
            if ($key === 'total_fact_pay') {
                continue;
            }

            if ($prev_key) {
                $result[$key] = $metric_data > 0 && !empty($metric[$prev_key]) ? round($metric_data * 100 / $metric[$prev_key], 2) : 0;
            }

            $prev_key = $key;
        }

        return $result;
    }

    /**
     * Генератор итоговых массивов
     * @param array $metric
     * @return array|array[]
     */
    private function generateTotals(array $metric): array
    {
        $default_array = self::getDefaultArray();
        $totals = [
            'items' => [
                'date' => [],
                'total' => $default_array,
            ],
            'cv' => [
                'date' => [],
                'total' => $default_array,
            ],
        ];

        foreach ($metric as $date => $metric_row) {
            foreach ($metric_row['items'] as $key => $item) {
                if (empty($totals['items']['date'][$date])) {
                    $totals['items']['date'][$date] = $default_array;
                }
                $totals['items']['date'][$date][$key] += $item;
            }
        }

        foreach ($totals['items']['date'] as $date => $total_item) {
            $totals['cv']['date'][$date] = self::generateCV($total_item);
            foreach ($total_item as $key => $total) {
                $totals['items']['total'][$key] += $total;
            }
        }

        $totals['cv']['total'] = self::generateCV($totals['items']['total']);
        return $totals;
    }

    /**
     * Генерируем данные
     * @return array
     */
    public function getResults(): array
    {
        $data = [];
        $filter_data = $this->getFilterData();

        $data['metric'] = $this->yandex_metric->getStatistic(self::GOALS, $filter_data);
        $data['orders'] = $this->yandex_metric->getOffersFromDB($filter_data);

        // генерируем финальный массив
        $metric = $this->mergeData($data, $filter_data['filter_group_by'] === 'month');
        $totals = $this->generateTotals($metric);

        return compact('metric', 'totals');
    }

    /**
     * Выбор фильтров
     * @return array
     * @throws Exception
     */
    public function getFilterData(): array
    {
        $filter_date_added = Helpers::getDataRange($this);
        return [
            'filter_date_start' => new \DateTime($filter_date_added['filter_date_start']),
            'filter_date_end' => new \DateTime($filter_date_added['filter_date_end']),
            'is_completed' => true,
            'isNewUser' => false,
            'filter_group_by' => $this->request->get('filter_group_by') ?: 'day',
        ];
    }
}