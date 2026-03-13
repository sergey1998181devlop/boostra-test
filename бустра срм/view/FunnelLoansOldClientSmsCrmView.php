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
class FunnelLoansOldClientSmsCrmView extends View
{
    use api\traits\FunnelLoansReportTrait;

//
//    private $yandex_metric;
//
//    private $totals;

//    /**
//     * Фильтр меток и веб мастеров для ПК пользователей
//     * определяем переходы ПК на сайт
//     */
//    public const UTM_SOURCES_FOR_METRIC = [
//        [
//            'utm_source' => 'sms',
//            'webmaster_id' => [
//                '0111',
//                '0112',
//                '0113',
//                '0114',
//                '0115',
//                '0116',
//                '0117',
//                '0118',
//                '0169',
//                '0179',
//            ]
//        ],
//        [
//            'utm_source' => 'beeline',
//            'webmaster_id' => [
//                90,
//                91,
//                92,
//                93,
//                94,
//                95,
//                96,
//                97,
//                98,
//                99,
//            ]
//        ],
//    ];

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
        'Дата',
        'Хвост смс',
        'Переход на сайт',
        'Вход в ЛК',
        'Подал заявку',
        'Одобрено',
        'Получил заём',
    ];


    public function fetch()
    {
        $filter_data = [
            'filter_group_by' => $this->request->get('filter_group_by') ?: 'day',
            'filter_webmaster_id' => $this->request->get('filter_webmaster_id') ?: [],
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

        if ($this->request->get('ajax') == 1 && !empty($this->request->get('filter_webmaster_id'))) {
            $fields_name = self::FIELDS_NAME;
            $results = $this->users->getFunnelOldUsersStatisticSms($filter_data);
            $totals = $this->generateTotals($results);

            $this->design->assign('results', $results);
            $this->design->assign('totals', $totals);
            $this->design->assign('fields_name', $fields_name);
            $client = 'pk';
            if ($this->request->get('download', 'boolean')) {
                $this->download(compact('results', 'totals', 'filter_data', 'fields_name', 'client'));
            }
        }
        return $this->design->fetch('funnel_loans_old_client_sms_crm.tpl');
    }


    /**
     * Генерируем итого
     * @param array $array
     */
    public function generateTotals(array $array): array
    {
        $sum = array(
            'total_links' => 0,
            'total_logins' => 0,
            'total_orders' => 0,
            'total_approved' => 0,
            'total_issued' => 0
        );
        foreach ($array as $values) {
            foreach ($values as $value) {
                $sum['total_links'] += $value->total_links;
                $sum['total_logins'] += $value->total_logins;
                $sum['total_orders'] += $value->total_orders;
                $sum['total_approved'] += $value->total_approved;
                $sum['total_issued'] += $value->total_issued;
            }
        }
        return $sum;
    }
}