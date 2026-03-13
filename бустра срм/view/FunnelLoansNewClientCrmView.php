<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', '600');

require_once 'View.php';
require_once dirname(__DIR__) . '/api/YaMetric.php';
require_once dirname(__DIR__) . '/api/Helpers.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Воронка займы Новый клиент данных из CRM
 * Class FunnelLoansNewClientCrmView
 */
class FunnelLoansNewClientCrmView extends View
{
    use api\traits\FunnelLoansReportTrait;

    private $yandex_metric;

    private $totals;

    public const DEFAULT_ARRAY_KEYS = [
        'visits',
        'init_user_1',
        'init_user_2',
        'init_user_3',
        'contact_step_1',
        'account_created_data',
        'personal_data',
        'address_data',
        'accept_data',
        'credit_card_data',
        'photo_data',
        'work_data',
        'orders_all',
        'orders_approved',
        'orders_issued',
    ];

    /**
     * Имена колонок
     */
    public const FIELDS_NAME = [
        'Переход на сайт',
        'Зашел на шаг регистрации”(вводит номер телефона)',
        'Подтвердил номер телефона',
        'Ввел смс и прошел дальше',
        'Вход на 1 шаг анкеты (ФИО)',
        'Прошёл ФИО (Поставил Согласие + Клик Далее)',
        'Ввод паспортных данных (Корректно ввёл+клик Далее)',
        'Ввод адреса (Корректно+клик Получить решение)',
        'Прошёл страницу с предварительным решением (клик Получить деньги)',
        'Успешно привязал карту',
        'Успешно прикрепил фото (2 фото + клик Далее)',
        'Успешно ввёл данные о работе',
        'Заявки',
        'Одобрено',
        'Выдано',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->yandex_metric = new api\YaMetric\YaMetric();
        $this->totals = [];
    }

    public function fetch()
    {
        $utm_sources = $this->orders->getUtmSources();

        $filter_data = array_merge([
            'filter_group_by' => $this->request->get('filter_group_by') ?: 'day',
            'filter_utm_source' => $this->request->get('filter_utm_source') ?: $utm_sources,
            'filter_user_registered' => $this->request->get('filter_user_registered', 'boolean'),
            'filter_webmaster_id' => $this->request->get('filter_webmaster_id') ?: [],
            'filter_client_type' => $this->orders::ORDER_BY_NEW_CLIENT,
        ], Helpers::getDataRange($this));

        $filter_data['filter_no_validate_postback'] = $this->orders::UTM_SOURCES_NO_VALIDATE_POSTBACK;
        $results = [];

        $fields_name = self::FIELDS_NAME;

        if($this->request->get('ajax', 'boolean')) {
            if (!empty($filter_data['filter_webmaster_id'])) {
                array_unshift($fields_name , 'Id вебмастера');
            }

            if (!empty($filter_data['filter_utm_source']) && !in_array('all', $filter_data['filter_utm_source'])) {
                array_unshift($fields_name , 'Источник');
            }

            if (in_array('all', $filter_data['filter_utm_source'])) {
                unset($filter_data['filter_utm_source']);
            }

            array_unshift($fields_name , 'Дата');

            $this->totals = [
                'items' => self::getDefaultArray(),
                'cv' => self::getDefaultArray(),
            ];

            $getYaStatistic = function ($filter_data) {
                $result = [];

                $filter_data['filter_date_start'] = new \DateTime($filter_data['filter_date_start']);
                $filter_data['filter_date_end'] = new \DateTime($filter_data['filter_date_end']);
                $filter_data['isNewUser'] = true;

                $filter_level = [];

                if (!empty($filter_data['filter_utm_source'])) {
                    $filter_level['utm_source'] = $filter_data['filter_utm_source'];
                }

                if (!empty($filter_data['filter_webmaster_id'])) {
                    $filter_level['webmaster_id'] = $filter_data['filter_webmaster_id'];
                }

                if (!empty($filter_level)) {
                    $filter_data['paramsLevel1'] = $this->yandex_metric::generateFilterLevel1($filter_level);
                }

                $metric_response =  $this->yandex_metric->getStatistic(
                    [
                        'view' => 'ym:s:{{type_visit}}',
                        'init_user_1' => 346868632,
                        'init_user_2' => 346868720,
                        'init_user_3' => 346868810,
                        'contact_step_1' => 258855672,
                    ], $filter_data);

                // переберем сначала метрику и приведем к виду Б.Д.
                foreach ($metric_response['data'] as $metric_data) {
                    $date = str_replace('-', '.', $metric_data['dimensions'][0]['name']);
                    $result[$date] = [
                        'visits' => (int)$metric_data['metrics'][0],
                        'init_user_1' => (int)$metric_data['metrics'][1],
                        'init_user_2' => (int)$metric_data['metrics'][2],
                        'init_user_3' => (int)$metric_data['metrics'][3],
                        'contact_step_1' => (int)$metric_data['metrics'][4],
                    ];
                }

                return $result;
            };

            $metric_data = $funnel_statistics = [];

            // получим данные с метрики
            if (!empty($filter_data['filter_utm_source'])) {
                $metric_filter = $funnel_filter = $filter_data;
                foreach ($filter_data['filter_utm_source'] as $filter_utm_source) {
                    $metric_filter['filter_utm_source'] = $filter_utm_source;
                    $funnel_filter['filter_utm_source'] = $filter_utm_source;
                    if (!empty($filter_data['filter_webmaster_id'])) {
                        foreach ($filter_data['filter_webmaster_id'] as $webmaster_id)
                        {
                            $metric_filter['filter_webmaster_id'] = $funnel_filter['filter_webmaster_id'] = [$webmaster_id];
                            $metric_data[$filter_utm_source][$webmaster_id] = $getYaStatistic($metric_filter);
                            $funnel_statistics[$filter_utm_source][$webmaster_id] = $this->users->getFunnelStatistic($funnel_filter);
                        }
                    } else {
                        $metric_data[$filter_utm_source] = $getYaStatistic($metric_filter);
                        sleep(1);
                        $funnel_statistics[$filter_utm_source] = $this->users->getFunnelStatistic($funnel_filter);
                    }
                }
            } else {
                $metric_data = $getYaStatistic($filter_data);
                $funnel_statistics = $this->users->getFunnelStatistic($filter_data);
            }

            // собираем массивы воедино
            $MergeArray = function (array $metric_data, array $funnel_statistics) use ($filter_data): array {
                $arKeys = array_keys(array_merge($metric_data, $funnel_statistics));
                sort($arKeys);

                $results = array_combine($arKeys, array_fill(0, count($arKeys), [
                    'items' => self::getDefaultArray(),
                    'cv' => self::getDefaultArray(),
                ]));

                foreach ($funnel_statistics as $date_action => $funnel_statistic_array) {
                    foreach ($funnel_statistic_array as $key => $value) {
                        $results[$date_action]['items'][$key] = $value;
                    }
                }

                foreach ($metric_data as $date_action => $metric_data_array) {
                    foreach ($metric_data_array as $key => $value) {
                        $results[$date_action]['items'][$key] = $value;
                    }
                }

                array_walk($results, function (&$array) {
                    $array['cv'] = self::generateCV($array['items']);
                });

                return $results;
            };

            if (!empty($filter_data['filter_utm_source'])) {
                foreach ($filter_data['filter_utm_source'] as $filter_utm_source) {
                    if (!empty($filter_data['filter_webmaster_id'])) {
                        foreach ($filter_data['filter_webmaster_id'] as $webmaster_id)
                        {
                            $result_merge = $MergeArray($metric_data[$filter_utm_source][$webmaster_id], $funnel_statistics[$filter_utm_source][$webmaster_id]);
                            foreach (array_keys($result_merge) as $date_key) {
                                $results[$date_key][$filter_utm_source][$webmaster_id] = $result_merge[$date_key];
                                $this->generateTotals($results[$date_key][$filter_utm_source][$webmaster_id]);
                            }
                        }
                    } else {
                        $result_merge = $MergeArray($metric_data[$filter_utm_source], $funnel_statistics[$filter_utm_source]);
                        foreach (array_keys($result_merge) as $date_key) {
                            $results[$date_key][$filter_utm_source] = $result_merge[$date_key];
                            $this->generateTotals($results[$date_key][$filter_utm_source]);
                        }
                    }
                }
            } else {
                $results = $MergeArray($metric_data, $funnel_statistics);
                $this->generateTotals($results);
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

        $this->design->assign('sources', $utm_sources);
        $this->design->assign('totals', $this->totals);
        $this->design->assign('results', $results);
        $this->design->assign('fields_name', $fields_name);

        return $this->design->fetch('funnel_loans_new_client_crm.tpl');
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
     * @return array
     */
    public static function getDefaultArray(): array
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