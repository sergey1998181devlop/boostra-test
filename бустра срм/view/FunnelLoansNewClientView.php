<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', '600');

require_once 'View.php';
require_once dirname(__DIR__) . '/api/YaMetric.php';

/**
 * Воронка займы Новый клиент
 * Class FunnelLoansNewClientView
 */
class FunnelLoansNewClientView extends View
{
    public const DEFAULT_ARRAY_KEYS = [
        'visitors',
        'registration_click',
        'fio',
        'telephone',
        'passport',
        'address',
        'predreshenie',
        'reg_cards',
        'page_photo',
        'photo',
        'work',
        'orders_all',
        'orders_approved',
        'orders_issued'
    ];

    private $yandex_metric;

    public function __construct()
    {
        parent::__construct();
        $this->yandex_metric = new api\YaMetric\YaMetric();
    }

    public function fetch()
    {
        $data = [];

        $filter_data = [
            'filter_group_by' => $this->request->get('filter_group_by') ?: 'day',
            'filter_utm_source' => $this->request->get('filter_utm_source'),
        ];

        $filter_date_start = 'now';
        $filter_date_end = 'now';

        $filter_date_range = $this->request->get('date_range') ?? '';

        if (!empty($filter_date_range)) {
            $filter_date_array = array_map('trim', explode('-', $filter_date_range));
            $filter_date_start = str_replace('.', '-', $filter_date_array[0]);
            $filter_date_end = str_replace('.', '-', $filter_date_array[1]);
        }

        $filter_data['filter_date_start'] = new \DateTime($filter_date_start);
        $filter_data['filter_date_end'] = new \DateTime($filter_date_end);

        $utm_sources = $this->orders->getUtmSources();
        $filter_data['filter_user_registered'] = true;

        //$filter_level = ['has_orders' => 0];

        //$filter_data['is_main_page'] = true;

        // отсеим лишние метки
        if (!empty($filter_data['filter_utm_source']) && !in_array('all', $filter_data['filter_utm_source'])) {
            $utm_sources = array_intersect($utm_sources, $filter_data['filter_utm_source']);
        }

        // если выбрано ВСЕ, в источниках покажем только итоговую строчку
        if (!empty($filter_data['filter_utm_source']) && in_array('all', $filter_data['filter_utm_source'])) {
            $this->design->assign('view_only_total', true);
        }

        if($this->request->get('ajax', 'boolean')) {
            foreach ($utm_sources as $utm_source) {
                unset($filter_data['filter_status'], $filter_data['filter_status_issued']);

                $filter_level['utm_source'] = $utm_source;
                $filter_data['paramsLevel1'] = $this->yandex_metric::generateFilterLevel1($filter_level);
                $data['items'][$utm_source]['metric'] = $this->yandex_metric->getStatistic($this->yandex_metric::GOALS_ANALYTICS, $filter_data);

                // всего
                $filter_data['filter_utm_source'] = $utm_source;
                $data['items'][$utm_source]['orders']['orders_all'] = $this->orders->getTotalOrdersForAnalytic($filter_data);

                // одобрено
                $filter_data['filter_status'] = 2;
                $filter_data['filter_status_approved'] = true;
                $data['items'][$utm_source]['orders']['orders_approved'] = $this->orders->getTotalOrdersForAnalytic($filter_data);

                // одобрено и выдано
                $filter_data['filter_status_issued'] = true;
                $data['items'][$utm_source]['orders']['orders_issued'] = $this->orders->getTotalOrdersForAnalytic($filter_data);

                //найдем визиты главной страницы
                //$data['items'][$utm_source]['visitors'] = $this->users->getTotalVisitors($filter_data);
            }

            $metric = $this->mergeData($data, $filter_data['filter_group_by'] === 'month');
            $totals = $this->generateTotals($metric);
        }

        $this->design->assign('metric', $metric ?? null);
        $this->design->assign('sources', $utm_sources);
        $this->design->assign('totals', $totals ?? null);

        return $this->design->fetch('funnel_loans_new_client.tpl');
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
     * Формирует массив конверсий
     * @param $data
     * @return array
     */
    public static function generateCV($data): array
    {
        $cv = self::getDefaultArray();

        foreach ($data as $key => $item) {
            if ($key === 'visitors') {
                $cv[$key] = $item;
            } else {
                $cv[$key] = empty($prev_item) ? 0 : round($item/$prev_item, 2) ;
            }
            $prev_item = $item;
        }

        return $cv;
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

        foreach ($data['items'] as $utm_source => $item) {
            // переберем сначала метрику и приведем к виду Б.Д.
            foreach ($item['metric']['data'] as $metric_data) {
                $date = str_replace('-', '.', $metric_data['dimensions'][0]['name']);
                if ($is_month) {
                    $date = substr($date, 0, -3);
                }

                if (empty($metric[$date][$utm_source])) {
                    $metric[$date][$utm_source] = [
                        'items' => $default_array,
                        'cv' => $default_array,
                    ];
                }

                $metric[$date][$utm_source]['items']['registration_click'] = $metric_data['metrics'][0];
                $metric[$date][$utm_source]['items']['fio'] = $metric_data['metrics'][1];
                $metric[$date][$utm_source]['items']['telephone'] = $metric_data['metrics'][2];
                $metric[$date][$utm_source]['items']['passport'] = $metric_data['metrics'][3];
                $metric[$date][$utm_source]['items']['address'] = $metric_data['metrics'][4];
                $metric[$date][$utm_source]['items']['predreshenie'] = $metric_data['metrics'][5];
                $metric[$date][$utm_source]['items']['reg_cards'] = $metric_data['metrics'][6];
                $metric[$date][$utm_source]['items']['page_photo'] = $metric_data['metrics'][7];
                $metric[$date][$utm_source]['items']['photo'] = $metric_data['metrics'][8];
                $metric[$date][$utm_source]['items']['work'] = $metric_data['metrics'][9];
                $metric[$date][$utm_source]['items']['visitors'] = $metric_data['metrics'][10];


                $metric[$date][$utm_source]['items'] = array_map('intval',  $metric[$date][$utm_source]['items']);
            }

            foreach ($item['orders'] as $key_list => $orders_item) {
                foreach ($orders_item as $order_row) {
                    $date = $order_row->filter_date;
                    if (empty($metric[$date][$utm_source])) {
                        $metric[$date][$utm_source] = [
                            'items' => $default_array,
                            'cv' => $default_array,
                        ];
                    }
                    $metric[$date][$utm_source]['items'][$key_list] = (int)$order_row->total;
                }
            }

            /*foreach ($item['visitors'] as $visitor) {
                $date = $visitor->filter_date;
                if (empty($metric[$date][$utm_source])) {
                    $metric[$date][$utm_source] = [
                        'items' => $default_array,
                        'cv' => $default_array,
                    ];
                }
                $metric[$date][$utm_source]['items']['visitors'] = (int)$visitor->total;
            }*/
        }

        foreach ($metric as $date => $metric_row) {
            foreach ($metric_row as $utm_source => $type_row) {
                $metric[$date][$utm_source]['cv'] = self::generateCV($type_row['items']);
            }
        }

        return $metric;
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
            foreach ($metric_row as $type_row) {
                foreach ($type_row['items'] as $key => $item) {
                    if (empty($totals['items']['date'][$date])) {
                        $totals['items']['date'][$date] = $default_array;
                    }
                    $totals['items']['date'][$date][$key] += $item;
                }
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
}