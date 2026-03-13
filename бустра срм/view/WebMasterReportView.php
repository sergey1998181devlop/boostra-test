<?php

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once 'View.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

ini_set('max_execution_time', 180);

/**
 * Class WebMasterReportView
 * Класс для работы с отчётом - Анализ веб-мастеров
 */
class WebMasterReportView extends View
{
    /**
     * Список ключей массива
     */
    public const DEFAULT_ARRAY_KEYS = [
        'orders_nk',
        'orders_nk_with_sale_postback',
        'cv_sale_postback',
        'total_pays',
        'pay_avg_nk',
        'orders_pk',
        'orders_pk_confirmed',
        'cv_sale_pk_confirmed',
        'pay_avg',
        'nk_to_pk',
        'nk_amount',
        'totals_insurers',
        'total_amount_insurers',
        'insurer_client',
        'insurer_involvement',
        'paid_percents',
        'delay_in_payments',
        'profit',
        'personal_profit',
        'nk_profit',
        'score_ball',
    ];

    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');

        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {

        if ($this->request->get('ajax')) {
            $results = $this->getResults();
            $this->design->assign('results', $results);
        } else {
            $sources = $this->orders->getUtmSources();
            $this->design->assign('sources', $sources);
        }

        return $this->design->fetch('web_master_report_view.tpl');
    }

    /**
     * Получает список webmaster_id по метке
     */
    private function getWebmasterIds()
    {
        $filter_utm_source = $this->request->get('utm_source', 'string');
        $webmaster_ids = $this->orders->getWebmasterIds($filter_utm_source);

        $this->design->assign('webmaster_ids', $webmaster_ids);
        $response =  $this->design->fetch('html_blocks/filter_webmaster_ids.tpl');

        $this->response->html_output($response);
    }

    /**
     * Выбор фильтров
     * @return array
     */
    private function getFilterData(): array
    {
        return [
            'filter_date_order' => Helpers::getDataRange($this),
            'filter_utm_source' => $this->request->get('filter_utm_source') ?: [],
            'filter_webmaster_id' => $this->request->get('filter_webmaster_id') ?: [],
            'filter_no_validate_postback' => $this->orders::UTM_SOURCES_NO_VALIDATE_POSTBACK, // на время исключим заявки с utm метками для которых постбеки не учитываем
        ];
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
     * Генерация данных
     * @return array
     * @throws Exception
     */
    private function getResults(): array
    {
        $fields_list = "o.id, 
        o.user_id,
        o.scorista_ball, 
        o.webmaster_id, 
        o.utm_source, 
        o.confirm_date,
        u.loan_history,
        IF(o.have_close_credits = 0, (SELECT COUNT(*) FROM s_orders o3 WHERE o3.user_id = o.user_id AND o3.confirm_date IS NOT NULL) = 2, 0) as hav_2_confirmed_orders,
        (SELECT SUM(up1.percent_amount) FROM s_user_payments_1c up1 WHERE up1.uid = u.UID AND up1.payment_date <= NOW()) as paid_percents";

        $results = [
            'totals' => self::getDefaultArray(),
            'items' => [],
            'first_column_name' => '',
        ];

        $generateData = function ($filter) use ($fields_list) {
            $filter_data = $filter_data_nk = $filter;

            $result_array = self::getDefaultArray();

            // заявки НК
            $filter_data_nk['filter_client'] = 'NK';
            $filter_data_nk['filter_stage_completed'] = true; // добавим проверку на факт того что человек закончил регистрацию
            $filter_data_nk['filter_postback_type'] = $this->post_back::TYPE_HOLD;
            $result_array['orders_nk'] = $this->orders->getTotalOrders($filter_data_nk);

            // заявки с постбеком о выдаче
            $filter_data_nk['filter_is_confirmed'] = true; // добавим проверку на факт выдачи
            $filter_data_nk['filter_postback_type'] = $this->post_back::TYPE_SALE;
            $orders_nk_with_sale_postback_array = $this->orders->getTotalOrders($filter_data_nk, false, $fields_list);
            $result_array['orders_nk_with_sale_postback'] = count($orders_nk_with_sale_postback_array);

            // CV на выдачу
            if (!empty($result_array['orders_nk'])) {
                $result_array['cv_sale_postback'] = round( $result_array['orders_nk_with_sale_postback'] / $result_array['orders_nk'],2);
            }

            if (!empty($orders_nk_with_sale_postback_array)) {

                // выплаты
                foreach ($orders_nk_with_sale_postback_array as $order) {
                    $result_array['total_pays'] += $this->orders->getPayoutGrade($order);
                }

                // Цена выдачи НК
                $result_array['pay_avg_nk'] = round($result_array['total_pays']  / $result_array['orders_nk_with_sale_postback'], 2);

                // Заявки ПК
                $filter_data['filter_client'] = 'PK';
                $filter_data['filter_user_ids'] = array_column($orders_nk_with_sale_postback_array, 'user_id');

                $filter_data['filter_date_order']['filter_date_end'] = date('Y-m-d H:i:s');

                $result_array['orders_pk'] = $this->orders->getTotalOrders($filter_data);

                // заявки ПК с выдачами
                $filter_data['filter_is_confirmed'] = true;
                $result_array['orders_pk_confirmed'] = $this->orders->getTotalOrders($filter_data);

                // CV в выдачу ПК
                if (!empty($result_array['orders_pk'])) {
                    $result_array['cv_sale_pk_confirmed'] = round( $result_array['orders_pk_confirmed']  / $result_array['orders_pk'],2);
                }

                // Средняя цена выдачи
                if (!empty($result_array['orders_nk_with_sale_postback']) || !empty($result_array['orders_pk_confirmed'])) {
                    $result_array['pay_avg'] = round($result_array['total_pays'] / ($result_array['orders_nk_with_sale_postback'] + $result_array['orders_pk_confirmed']), 2);
                }

                // НК стал ПК
                $order_ids = array_column($orders_nk_with_sale_postback_array, 'id');
                $filter_data_nk_to_pk = [
                    'filter_date_order' => $filter_data['filter_date_order'],
                ];
                $result_array['nk_to_pk'] = $this->orders->hasNkToPkByOrderIds($order_ids, $filter_data_nk_to_pk);

                // Выдано, рублей
                $filter_data_nk_amount = [
                    'filter_date_order' => $filter_data['filter_date_order'],
                    'filter_user_ids' => $filter_data['filter_user_ids'],
                    'filter_is_confirmed' => true,
                    'filter_postback_type' => $this->post_back::TYPE_SALE,
                ];

                $nk_amount_array = $this->orders->getTotalOrders($filter_data_nk_amount, false, "SUM(o.amount) as amount");
                $nk_amount = array_shift($nk_amount_array);
                $result_array['nk_amount'] = $nk_amount->amount;

                // оплаченных страховок
                $filter_data_insurer = [
                    //'filter_has_pay' => true,
                    'user_id' => array_unique(array_column($orders_nk_with_sale_postback_array, 'user_id')),
                ];
                $insurers = $this->insurances->get_insurances($filter_data_insurer);
                $result_array['totals_insurers'] = count($insurers);

                // сумма страховок
                $result_array['total_amount_insurers'] = array_sum(array_column($insurers, 'amount'));

                // страховка с 1 клиента
                $result_array['insurer_client'] = round(
                    $result_array['total_amount_insurers'] / $result_array['orders_nk_with_sale_postback'],
                    2
                );

                // %% сумма уплаченных процентов по всем договорам займа клиентов
                $result_array['paid_percents'] += array_sum(array_column($orders_nk_with_sale_postback_array, 'paid_percents'));
            }

            // Страховка-Цена привлечения
            $result_array['insurer_involvement'] = $result_array['insurer_client'] - $result_array['pay_avg'];

            // ОД в просрочке
            $filter_delay_in_payments = $filter_data;

            $filter_delay_in_payments['filter_active_order'] = true;
            $filter_delay_in_payments['filter_payment_date_end'] =  date('Y-m-d');
            $filter_delay_in_payments['filter_prolongation_count_max'] = 1;
            $filter_delay_in_payments['filter_order_not_1c_statuses'] = [$this->orders::ORDER_1C_STATUS_CLOSED];
            $filter_delay_in_payments['filter_order_not_statuses'] = [3];

            $delay_in_payments = $this->users->getUserBalances($filter_delay_in_payments);

            if (!empty($delay_in_payments)) {
                $result_array['delay_in_payments'] = array_sum(array_column($delay_in_payments, 'ostatok_od'));
            }

            // выручка
            $result_array['profit'] = $result_array['total_amount_insurers'] + $result_array['paid_percents'];

            if (!empty($result_array['orders_nk_with_sale_postback'])) {
                // выручка с 1 клиента
                $result_array['personal_profit'] = round($result_array['profit'] / $result_array['orders_nk_with_sale_postback'], 2);

                // Доход с 1 НК с учётом просрочки и Цены привлечения
                $result_array['nk_profit'] = $result_array['personal_profit'] - $result_array['pay_avg_nk'] - $result_array['delay_in_payments'];
            }

            // скориста балл scorista_ball
            $score_ball_array = array_filter($orders_nk_with_sale_postback_array, fn($item) => !empty($item->scorista_ball));
            if (!empty($score_ball_array)) {
                $result_array['score_ball'] = round(array_sum(array_column($score_ball_array, 'scorista_ball')) / count($score_ball_array));
            }

            return $result_array;
        };

        $filter_data = $filter_for_generate_data = $this->getFilterData();
        $is_all = in_array('all', $filter_data['filter_utm_source']) || empty($filter_data['filter_utm_source']);
        if ($is_all || count($filter_data['filter_utm_source']) > 1) {
            // если выбраны все источники разбивка по дням
            if ($is_all) {
                unset($filter_for_generate_data['filter_utm_source']);
                $this->getDataWithDate($results, $filter_for_generate_data, $generateData);
            } else {
                // иначе разбивка по источникам
                $results['first_column_name'] = 'Источник';
                foreach ($filter_data['filter_utm_source'] as $utm_source) {
                    $filter_for_generate_data['filter_utm_source'] = [$utm_source];
                    $results['items'][$utm_source] = $generateData($filter_for_generate_data);
                }
            }
        } else {
            // если выбран 1 источник
            $is_all_webmaster_id = in_array('all', $filter_data['filter_webmaster_id']) || empty($filter_data['filter_webmaster_id']);

            // если выбраны все webmaster_id
            if ($is_all_webmaster_id) {
                $this->getDataWithDate($results, $filter_for_generate_data, $generateData);
            } else {
                $results['first_column_name'] = 'Webmaster_id';
                foreach ($filter_data['filter_webmaster_id'] as $webmaster_id) {
                    $filter_for_generate_data['filter_webmaster_id'] = [$webmaster_id];
                    $results['items'][$webmaster_id] = $generateData($filter_for_generate_data);
                }
            }
        }

        // сгенерируем итоговые значения
        foreach ($results['items'] as $result_row) {
            foreach ($result_row as $key => $item) {
                $results['totals'][$key] += $item;
            }
        }

        if (!empty($results['totals']['orders_pk'])) {
            $results['totals']['cv_sale_pk_confirmed'] = round( $results['totals']['orders_pk_confirmed'] / $results['totals']['orders_pk'],2);
        }

        if (!empty($results['totals']['orders_nk_with_sale_postback'])) {
            $results['totals']['pay_avg_nk'] = round( $results['totals']['total_pays'] / $results['totals']['orders_nk_with_sale_postback'],2);
            $results['totals']['insurer_client'] = round($results['totals']['total_amount_insurers'] / $results['totals']['orders_nk_with_sale_postback'], 2);
        }

        if (!empty($results['totals']['orders_nk'])) {
            $results['totals']['cv_sale_postback'] = round( $results['totals']['orders_nk_with_sale_postback'] / $results['totals']['orders_nk'],2);
        }

        if (!empty($results['totals']['orders_nk_with_sale_postback']) || !empty($results['totals']['orders_pk_confirmed'])) {
            $results['totals']['pay_avg'] = round( $results['totals']['total_pays'] / ($results['totals']['orders_nk_with_sale_postback'] + $results['totals']['orders_pk_confirmed']),2);
        }

        if (!empty($results['totals']['orders_nk_with_sale_postback'])) {
            $results['totals']['personal_profit'] = round($results['totals']['profit'] / $results['totals']['orders_nk_with_sale_postback'], 2);
        }

        $results['totals']['insurer_involvement'] = $results['totals']['insurer_client'] - $results['totals']['pay_avg'];

        if (!empty($results['items'])) {
            $results['totals']['score_ball'] = round($results['totals']['score_ball'] / count($results['items']));
        }

        return $results;
    }

    /**
     * Выгрузка данных в Excel
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function download()
    {
        require_once $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';
        error_reporting(0);
        ini_set('display_errors', 'Off');

        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();

        $filter_data = $this->getFilterData();

        $is_all = in_array('all', $filter_data['filter_utm_source']) || empty($filter_data['filter_utm_source']);

        $name_source = ($is_all ? 'Все' : implode(
                '|',
                $filter_data['filter_utm_source']
            )) . "_" . $filter_data['filter_date_order']['filter_date_start'] . "_" . $filter_data['filter_date_order']['filter_date_end'];

        $filename = 'files/reports/' . $name_source . '_webmaster_report.xls';

        $active_sheet->setTitle(
            " " . $filter_data['filter_date_order']['filter_date_start'] . "_" . $filter_data['filter_date_order']['filter_date_end']
        );

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $results = $this->getResults();

        $cell = 'A';

        $active_sheet->setCellValue($cell++ .'1', $results['first_column_name']);
        $active_sheet->setCellValue($cell++ .'1', 'Заявки НК');
        $active_sheet->setCellValue($cell++ .'1', 'Выдача НК');
        $active_sheet->setCellValue($cell++ .'1', 'CV в выдачу НК');
        $active_sheet->setCellValue($cell++ .'1', 'Выплаты');
        $active_sheet->setCellValue($cell++ .'1', 'Цена выдачи НК');
        $active_sheet->setCellValue($cell++ .'1', 'Заявки ПК');
        $active_sheet->setCellValue($cell++ .'1', 'Выдача ПК');
        $active_sheet->setCellValue($cell++ .'1', 'CV в выдачу ПК');
        $active_sheet->setCellValue($cell++ .'1', 'Средняя цена выдачи');
        $active_sheet->setCellValue($cell++ .'1', 'Стал ПК');
        $active_sheet->setCellValue($cell++ .'1', 'ПК1');
        $active_sheet->setCellValue($cell++ .'1', 'CV ПК1');
        $active_sheet->setCellValue($cell++ .'1', 'Страховка штук');
        $active_sheet->setCellValue($cell++ .'1', 'Страховка рублей');
        $active_sheet->setCellValue($cell++ .'1', 'Страховка с 1 клиента');
        $active_sheet->setCellValue($cell++ .'1', 'Страховка-Цена привлечения');
        $active_sheet->setCellValue($cell++ .'1', '%%');
        $active_sheet->setCellValue($cell++ .'1', 'ОД в просрочке');
        $active_sheet->setCellValue($cell++ .'1', 'Доход');
        $active_sheet->setCellValue($cell++ .'1', 'Доход с 1 клиента');
        $active_sheet->setCellValue($cell++ .'1', 'Скорбалл выданных');

        $row = 2;

        $set_row = function ($array, $key) use (& $active_sheet, & $row) {
            $cell = 'A';
            $active_sheet->setCellValue($cell++ . $row, $key);
            foreach ($array as $item) {
                $active_sheet->setCellValue($cell++ . $row, $item);
            }
            $row++;
        };

        $set_row_color = function ($color_bg, $color_text = 'EA0F0F') use (& $active_sheet, & $row) {
            $active_sheet->getStyle('A' . $row.':'. 'V' .$row)->applyFromArray(
                [
                    'fill' => [
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['rgb' => $color_bg],
                    ],
                    'font' => [
                        'color' => ['rgb' => $color_text],
                    ]
                ]
            );
        };

        foreach ($results['items'] as $key => $array) {
            $set_row($array, $key);
        }

        $set_row_color('FFDD00');
        $set_row($results['totals'], 'Итого');

        for ($i = 'A'; $i <= 'V'; $i++) {
            $active_sheet->getColumnDimension($i)->setAutoSize(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename);
        exit;
    }

    /**
     * @param array $results
     * @param array $filter_for_generate_data
     * @param Closure $generateData
     * @return void
     * @throws Exception
     */
    private function getDataWithDate(array & $results, array $filter_for_generate_data, Closure $generateData): void
    {
        $results['first_column_name'] = 'Дата';
        $date_start = new \DateTime($filter_for_generate_data['filter_date_order']['filter_date_start']);
        $date_end = new \DateTime($filter_for_generate_data['filter_date_order']['filter_date_end']);

        $interval = DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($date_start, $interval, $date_end->modify('+1 day'));
        foreach ($period as $dt) {
            $date_format = $dt->format('Y-m-d');
            $filter_for_generate_data['filter_date_order'] = [
                'filter_date_start' => $date_format,
                'filter_date_end' => $date_format,
            ];
            $results['items'][$date_format] = $generateData($filter_for_generate_data);
        }
    }
}
