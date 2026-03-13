<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', '600');

require_once 'View.php';
require_once dirname(__DIR__) . '/api/YaMetric.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Воронка займы Новый клиент данных из CRM
 * Class FunnelLoansNewClientCrmView
 */
class FunnelLoansOldClientCrmView extends View
{
    use api\traits\FunnelLoansReportTrait;

    private $yandex_metric;

    private $totals;

    /**
     * Фильтр меток и веб мастеров для ПК пользователей
     * определяем переходы ПК на сайт
     */
    public const UTM_SOURCES_FOR_METRIC = [
        [
            'utm_source' => 'sms',
            'webmaster_id' => [
                '0111',
                '0112',
                '0113',
                '0114',
                '0115',
                '0116',
                '0117',
                '0118',
                '0169',
                '0179',
            ]
        ],
        [
            'utm_source' => 'beeline',
            'webmaster_id' => [
                90,
                91,
                92,
                93,
                94,
                95,
                96,
                97,
                98,
                99,
            ]
        ],
    ];

    /**
     * Ключи массива по умолчанию
     */
    public const DEFAULT_ARRAY_KEYS = [
        'visits',
        'user_login',
        'orders_all',
        'orders_approved',
        'orders_issued',
    ];

    /**
     * Имена колонок
     */
    public const FIELDS_NAME = [
        'Переход на сайт',
        'Вход в ЛК',
        'Подал заявку',
        'Одобрено',
        'Получил заём',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->yandex_metric = new api\YaMetric\YaMetric();

        $this->totals = [
            'items' => self::getDefaultArray(),
            'cv' => self::getDefaultArray(),
        ];
    }

    public function fetch()
    {
        $filter_data = [
            'filter_group_by' => $this->request->get('filter_group_by') ?: 'day',
            'filter_utm_source' => $this->request->get('filter_utm_source') ?: [],
            'filter_webmaster_id' => $this->request->get('filter_webmaster_id') ?: [],
            'filter_client_type' => $this->orders::ORDER_BY_OLD_CLIENT,
        ];

        $filter_date_start = date('Y-m-d');
        $filter_date_end = date('Y-m-d');

        $filter_date_range = $this->request->get('date_range') ?? '';

        if (!empty($filter_date_range)) {
            $filter_date_array = array_map('trim', explode('-', $filter_date_range));
            $filter_date_start = str_replace('.', '-', $filter_date_array[0]);
            $filter_date_end = str_replace('.', '-', $filter_date_array[1]);
        }

        $filter_data['filter_date_start'] = $filter_date_start;
        $filter_data['filter_date_end'] = $filter_date_end;
        $filter_data['filter_no_validate_postback'] = $this->orders::UTM_SOURCES_NO_VALIDATE_POSTBACK;

        $utm_sources = $this->orders->getUtmSourcesByFilter(['filter_date_accept' => $filter_data]);
        $results = [];
        $fields_name = self::FIELDS_NAME;

        if ($this->request->get('ajax', 'boolean')) {

            if (!empty($filter_data['filter_webmaster_id'])) {
                array_unshift($fields_name , 'Id вебмастера');
            }

            if (!empty($filter_data['filter_utm_source']) && !in_array('all', $filter_data['filter_utm_source'])) {
                array_unshift($fields_name , 'Источник');
            }

            array_unshift($fields_name , 'Дата');

            if (in_array('all', $filter_data['filter_utm_source'])) {
                unset($filter_data['filter_utm_source']);
            }

            $getYaStatistic = function ($filter_data) {
                $result = [];

                $filter_data['filter_date_start'] = new \DateTime($filter_data['filter_date_start']);
                $filter_data['filter_date_end'] = new \DateTime($filter_data['filter_date_end']);
                $filter_data['filter_client'] = true;
                $metric_response = $this->yandex_metric->getStatistic(
                    [
                        'view' => 'ym:s:{{type_visit}}',
                    ],
                    $filter_data
                );

                // переберем сначала метрику и приведем к виду Б.Д.
                foreach ($metric_response['data'] as $metric_data) {
                    $date = str_replace('-', '.', $metric_data['dimensions'][0]['name']);
                    $result[$date] = [
                        'visits' => (int)$metric_data['metrics'][0],
                    ];
                }

                return $result;
            };

            // т.к. есть ограничения в метрики на кол-во фильтров раздробим на под-запросы
            $getYaStatisticByFilters = function ($filter_data) use ($getYaStatistic) {
                $results = [];

                // формируем массив для выборки ПК из ЯД метрики
                foreach (self::UTM_SOURCES_FOR_METRIC as $metric_filters) {
                    $filter_data['paramsLevel1'] = $this->yandex_metric::generateFilterLevel1($metric_filters);
                    $metric_data = $getYaStatistic($filter_data);

                    foreach ($metric_data as $date => $values) {
                        if (!isset($results[$date])) {
                            $results[$date] = $values;
                        } else {
                            $results[$date]['visits'] += $values['visits'];
                        }
                    }
                }

                return $results;
            };

            $metric_data = $getYaStatisticByFilters($filter_data);
            if (!empty($filter_data['filter_utm_source'])) {
                $filter_statistic = $filter_data;
                foreach ($filter_data['filter_utm_source'] as $filter_utm_source) {
                    $filter_statistic['utm_source'] = $filter_utm_source;

                    if (!empty($filter_data['filter_webmaster_id'])) {
                        $data_webmaster = [];
                        foreach ($filter_data['filter_webmaster_id'] as $webmaster_id) {
                            $filter_statistic['filter_webmaster_id'] = [$webmaster_id];
                            $data_webmaster[$webmaster_id] = $this->users->getFunnelOldUsersStatistic($filter_statistic);
                            foreach ($data_webmaster[$webmaster_id] as $date_action => $funnel_statistic) {
                                $results[$date_action][$filter_utm_source][$webmaster_id]['items'] = array_merge(self::getDefaultArray(), $metric_data[$date_action] ?? [], $funnel_statistic);
                                $results[$date_action][$filter_utm_source][$webmaster_id]['cv'] = self::generateCV($results[$date_action][$filter_utm_source][$webmaster_id]['items']);
                                $this->generateTotals($results[$date_action][$filter_utm_source][$webmaster_id]);
                            }
                        }
                    } else {
                        $data_webmaster = $this->users->getFunnelOldUsersStatistic($filter_statistic);
                        foreach ($data_webmaster as $date_action => $funnel_statistic) {
                            $results[$date_action][$filter_utm_source]['items'] = array_merge(self::getDefaultArray(), $metric_data[$date_action] ?? [], $funnel_statistic);
                            $results[$date_action][$filter_utm_source]['cv'] = self::generateCV($results[$date_action][$filter_utm_source]['items']);
                            $this->generateTotals($results[$date_action][$filter_utm_source]);
                        }
                    }
                }
            } else {
                $results_data = $this->users->getFunnelOldUsersStatistic($filter_data);
                $metric_data = $getYaStatisticByFilters($filter_data);
                if (!empty($results_data)) {
                    foreach ($results_data as $date_action => $result) {
                        $results[$date_action]['items'] = array_merge(self::getDefaultArray(), $metric_data[$date_action] ?? [], $result);
                        $results[$date_action]['cv'] = self::generateCV($results[$date_action]['items']);
                        $this->generateTotals($results[$date_action]);
                    }
                }
            }

            uksort($results, function($a, $b) {
                $time_1 = strtotime(str_replace('.', '-', $a));
                $time_2 = strtotime(str_replace('.', '-', $b));

                return $time_1 - $time_2;
            });

            if ($this->request->get('download', 'boolean')) {
                $this->download(compact('results', 'filter_data', 'fields_name'));
            }
        }

        $this->design->assign('utm_sources', $utm_sources);
        $this->design->assign('totals', $this->totals);
        $this->design->assign('results', $results);
        $this->design->assign('fields_name', $fields_name);
        $this->design->assign('filter_ajax_no_load_javascript', true);

        return $this->design->fetch('funnel_loans_old_client_crm.tpl');
    }

    /**
     * Генерируем итого
     * @param array $array
     */
    public function generateTotals(array $array): void
    {
        foreach ($array as $key_parent => $value) {
            if ($key_parent === 'cv') {
                continue;
            }
            foreach (self::DEFAULT_ARRAY_KEYS as $key) {
                $this->totals['items'][$key] += ($value['items'][$key] ?? $array['items'][$key] ?? 0);
            }
        }

        $this->totals['cv'] = self::generateCV($this->totals['items']);
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
            if ($key === self::DEFAULT_ARRAY_KEYS[0]) {
                $cv[$key] = '';
            } else {
                $cv[$key] = empty($prev_item) ? 0 : round($item/$prev_item, 2) ;
            }
            $prev_item = $item;
        }

        return $cv;
    }
}

