<?php

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once 'View.php';

/**
 * Class RejectReportView
 * Класс для работы с отчётом по отказам
 */
class RejectReportView extends View
{
    /**
     * Сегменты по причинам отказа
     */
    public const CATEGORY_REASONS = [
        [
            'name' => 'Автоматическая проверка до андеррайтинга',
            'items' => [28, 23, 14, 2, 22, 19, 21],
        ],
        [
            'name' => 'Отказ по информационным источникам',
            'items' => [5, 1, 24, 41, 44, 45, 46, 47, 48],
        ],
        [
            'name' => 'Отказ по проверке фото',
            'items' => [9, 7, 12, 49],
        ],
        [
            'name' => 'Отказ по анкетным данным о работе',
            'items' => [40, 26, 27],
        ],
        [
            'name' => 'Отказ по проблемной карте',
            'items' => [18, 53],
        ],
        [
            'name' => 'Иные причины',
            'items' => [10, 25, 30, 31, 32, 33, 34, 35, 36, 50, 51, 52, 54, 55, 56],
        ],
    ];

    /**
     * Значения фильтров для ПК
     */
    public const PK_FILTERS = [
        'PK_WITHOUT_AUTO_APPROVE',
        'PK_ONLY_AUTO_APPROVE',
        'PK_WITH_AUTO_APPROVE',
    ];

    /**
     * Результирующий массив
     * @var array
     */
    private array $results = [];

    private array $prev_sum = [];

    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');

        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    public function fetch()
    {
        $sources = $this->orders->getUtmSources(false);
        $this->design->assign('sources', $sources);
        $filter_group_by = $this->request->get('filter_group_by', 'boolean');

        if ($this->request->get('ajax')) {
            $total_orders = $this->getTotalOrders();
            $this->getResources($total_orders);
            $this->getOtherItems($total_orders);

            // получим общее кол-во подкатегорий для rowspan
            if (!$filter_group_by) {
                array_walk($this->results, function (&$array){
                    $rowspan = 1;
                    $rowspan += array_sum(array_map(fn($item) => count($item), array_column($array, 'items'))); // кол-во строк с данными
                    $rowspan += count($array); // + итого каждой подкатегории

                    $array['total_items'] = $rowspan;
                });
            }

            $this->design->assign('total_orders', $total_orders);
        }

        $this->design->assign('results', $this->results);
        $this->design->assign('filter_group_by', $filter_group_by);

        return $this->design->fetch('reject_report_view.tpl');
    }

    /**
     * Выбор фильтров
     * @return array
     */
    private function getFilterData(): array
    {
        $filter_data_range = $this->getDataRange();
        $filter_client = $this->request->get('filter_client') ?: 'NK';

        $filter_data = [
            'filter_date_order' => $filter_data_range,
            'filter_utm_source' => $this->request->get('filter_utm_source') ?: [],
            'filter_client' => in_array($filter_client, self::PK_FILTERS) ? 'PK' : $filter_client,
            'filter_group_by' => $this->request->get('filter_group_by', 'boolean'),
            'filter_stage_completed' => $this->request->get('filter_stage_completed', 'boolean'),
            'filter_user_registered' => !$this->request->get('filter_stage_completed', 'boolean'),
        ];

        if (in_array($filter_client, self::PK_FILTERS)) {
            list($pk_without_auto_approve, $pk_only_auto_approve) = self::PK_FILTERS;
            if ($pk_only_auto_approve === $filter_client) {
                $filter_data['filter_utm_source'] = [$this->orders::UTM_SOURCE_CRM_AUTO_APPROVE];
            } elseif ($pk_without_auto_approve === $filter_client) {
                $filter_data['filter_without_utm_source'] = [$this->orders::UTM_SOURCE_CRM_AUTO_APPROVE];
            }
        }

        return $filter_data;
    }

    /**
     * Получает кол-во заявок
     * @return array
     * @throws Exception
     */
    private function getTotalOrders(): array
    {
        $filter_data = $this->getFilterData();
        $results = [];

        $generateData = function ($filter_data) {
            $filter_data_not_validate = $filter_data_approve = $filter_data_confirm = $filter_data;

            // всего заявок за период
            $total_orders = $this->orders->getTotalOrders($filter_data);
            $filter_data['not_statuses'] = [3];

            // одобренных
            $filter_data_approve['filter_is_approved'] = true;
            if (!empty($filter_data['filter_stage_completed'])) {
                $filter_data_approve['filter_date_approve'] = $filter_data['filter_date_order'];
                unset($filter_data_approve['filter_date_order'], $filter_data_approve['filter_stage_completed']);
            }

            $total_approve_orders = $this->orders->getTotalOrders($filter_data_approve, true);

            // из них с изменением суммы
            $filter_data_approve['amount_change'] = true;
            $total_approve_orders_with_edit_amount = $this->orders->getTotalOrders($filter_data_approve);

            // выданных
            // если как в листинге, то используем дату выдачи
            if (!empty($filter_data['filter_stage_completed'])) {
                $filter_data_confirm['filter_date_confirm'] = $filter_data['filter_date_order'];
                unset($filter_data_confirm['filter_stage_completed'], $filter_data_confirm['filter_date_order']);
            }

            $total_confirm_orders = $this->orders->getTotalOrders($filter_data_confirm, true);

            // из них с изменением суммы
            $filter_data_confirm['amount_change'] = true;
            $total_confirm_orders_with_edit_amount = $this->orders->getTotalOrders($filter_data_confirm);

            // заявки не вошедшие в отказные, одобрено, выдано
            $filter_data_not_validate['not_approved_and_confirmed'] = true;
            $filter_data_not_validate['not_statuses'] = [3];
            $total_orders_not_validate = $this->orders->getTotalOrders($filter_data_not_validate);

            $total_orders_not_confirmed = count($total_approve_orders) - count($total_confirm_orders);

            return [
                'total_orders' => $total_orders,
                'total_approve_orders' => count($total_approve_orders),
                'total_confirm_orders' => count($total_confirm_orders),
                'total_approve_orders_with_edit_amount' =>  $total_approve_orders_with_edit_amount,
                'total_confirm_orders_with_edit_amount' => $total_confirm_orders_with_edit_amount,
                'total_orders_not_validate' => $total_orders_not_validate,
                'total_orders_not_confirmed' => $total_orders_not_confirmed,
            ];
        };

        if ($filter_data['filter_group_by']) {
            $results[] = $generateData($filter_data);
        } else {
            $date_start = new \DateTime($filter_data['filter_date_order']['filter_date_start']);
            $date_end = new \DateTime($filter_data['filter_date_order']['filter_date_end']);

            $interval = DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($date_start, $interval, $date_end->modify('+1 day'));
            foreach ($period as $dt) {
                $date_format = $dt->format('Y-m-d');
                $filter_data['filter_date_order'] = [
                    'filter_date_start' => $date_format,
                    'filter_date_end' => $date_format,
                ];
                $results[$date_format] = $generateData($filter_data);
            }
        }

        return $results;
    }

    /**
     * Получает данные из БД по отказам
     * @param array $total_orders
     * @return void
     */
    private function getResources(array $total_orders): void
    {
        $filter_data = $this->getFilterData();

        $generateData = function ($filter_data) use ($total_orders) {
            $results_db = $this->orders->getRejectedOrders($filter_data);

            // для удобства получим ключ общего массива
            $main_key = $filter_data['filter_group_by'] ? 0 : $filter_data['filter_date_order']['filter_date_start'];

            foreach ($results_db as $item) {
                // выполним сегментацию
                $result_search = array_filter(self::CATEGORY_REASONS, function ($row) use ($item) {
                    return in_array($item->reason_id, $row['items']);
                });

                if (!empty($result_search)) {
                    $array_key = array_key_first($result_search); // ключ сегмента

                    if (!isset($this->results[$main_key][$array_key])) {
                        $this->results[$main_key][$array_key] = [
                            'items' => [],
                            'category' => self::CATEGORY_REASONS[$array_key]['name'],
                            'total' => [
                                'count' => 0,
                                'cv' => 0,
                            ]
                        ];
                    }

                    // позиция элемента в массиве
                    $key_item = array_search($item->reason_id, self::CATEGORY_REASONS[$array_key]['items']);
                    $this->results[$main_key][$array_key]['items'][$key_item] = (array)$item;
                }
            }

            ksort($this->results[$main_key]);

            // посчитаем cv для каждого
            foreach ($this->results[$main_key] as $key_category => & $result) {

                foreach ($result['items'] as & $item) {
                    $item['cv'] = $item['total'] / $total_orders[$main_key]['total_orders'] * 100;
                }

                ksort($result['items']);

                $total_rejected_orders = array_sum(array_column($result['items'], 'total'));
                $this->results[$main_key][$key_category]['total']['count'] = $total_rejected_orders;
                $this->results[$main_key][$key_category]['total']['cv'] = 100 - ((($total_rejected_orders + $this->prev_sum[$main_key]) * 100) / $total_orders[$main_key]['total_orders']);

                $this->prev_sum[$main_key] += $total_rejected_orders;
            }
        };

        if ($filter_data['filter_group_by']) {
            $generateData($filter_data);
        } else {
            $date_start = new \DateTime($filter_data['filter_date_order']['filter_date_start']);
            $date_end = new \DateTime($filter_data['filter_date_order']['filter_date_end']);

            $interval = DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($date_start, $interval, $date_end->modify('+1 day'));
            foreach ($period as $dt) {
                $date_format = $dt->format('Y-m-d');
                $filter_data['filter_date_order']['filter_date_start'] = $filter_data['filter_date_order']['filter_date_end'] = $date_format;
                $generateData($filter_data);
            }
        }
    }

    /**
     * Получаем не стандартные поля
     * @param array $total_orders
     * @return void
     */
    private function getOtherItems(array $total_orders): void
    {
        $filter_data = $this->getFilterData();

        $generateData = function ($filter_data) use ($total_orders) {
            $main_key = $filter_data['filter_group_by'] ? 0 : $filter_data['filter_date_order']['filter_date_start'];

            // переименовываем последнюю категорию и добавляем костылем туда заявки без причины
            //*************//
            $filter_data_count_orders = [
                'filter_client' => strtolower($filter_data['filter_client']),
                'date_from' => $filter_data['filter_date_order']['filter_date_start'],
                'date_to' => $filter_data['filter_date_order']['filter_date_end'],
                'filter_stage_completed' => $this->request->get('filter_stage_completed', 'boolean'),
                'filter_without_utm_source' => $filter_data['filter_without_utm_source'],
                'filter_utm_source' => $filter_data['filter_utm_source'],
                'status' => 3,
                'search' => [
                    'reason' => 'is_null',
                ],
            ]; // Отказ без причины
            $total_orders_not_reason_and_null = $this->orders->count_orders($filter_data_count_orders);

            $last_key = array_key_last($this->results[$main_key]) ?: 0;
            $this->prev_sum[$main_key] -= $this->results[$main_key][$last_key]['total']['count'];
            $this->results[$main_key][$last_key]['items'][] = [
                'admin_name' => 'Причина неопределенна',
                'total' => $total_orders_not_reason_and_null,
                'cv' => $total_orders_not_reason_and_null / $total_orders[$main_key]['total_orders'] * 100,
            ];
            $this->results[$main_key][$last_key]['total']['count'] += $total_orders_not_reason_and_null;
            $this->results[$main_key][$last_key]['total']['cv'] = 100 - ((($this->results[$main_key][$last_key]['total']['count'] + $this->prev_sum[$main_key]) * 100) / $total_orders[$main_key]['total_orders']);
            $this->prev_sum[$main_key] += $this->results[$main_key][$last_key]['total']['count'];

            // Заявки, которые не вошли в отчёт по другим причинам
            if(!empty($total_orders[$main_key]['total_orders_not_validate'])) {
                $this->results[$main_key][] = [
                    'category' => 'Заявки в работе',
                    'items' => [
                        [
                            'admin_name' => 'На верификации',
                            'total' => $total_orders[$main_key]['total_orders_not_validate'],
                            'cv' => $total_orders[$main_key]['total_orders_not_validate'] / $total_orders[$main_key]['total_orders'] * 100,
                        ],
                    ],
                    'total' => [
                        'count' => $total_orders[$main_key]['total_orders_not_validate'],
                        'cv' => 100 - ((($total_orders[$main_key]['total_orders_not_validate'] + $this->prev_sum[$main_key]) * 100) / $total_orders[$main_key]['total_orders']),
                    ],
                ];

                $this->prev_sum[$main_key] += $total_orders[$main_key]['total_orders_not_validate'] ;
            }

            //*************//
            $this->results[$main_key][] = [
                'category' => 'Одобрено',
                'total' => [
                    'count' => $total_orders[$main_key]['total_approve_orders'],
                    'cv' => $total_orders[$main_key]['total_approve_orders'] / $total_orders[$main_key]['total_orders'] * 100,
                ],
            ];

            //*************//
            $this->results[$main_key][] = [
                'category' => 'Выдано',
                'items' => [
                    [
                        'admin_name' => 'Без изменения суммы',
                        'total' => $total_orders[$main_key]['total_confirm_orders'] - $total_orders[$main_key]['total_confirm_orders_with_edit_amount'],
                        'cv' => ($total_orders[$main_key]['total_confirm_orders'] - $total_orders[$main_key]['total_confirm_orders_with_edit_amount']) / $total_orders[$main_key]['total_orders'] * 100,
                    ],
                    [
                        'admin_name' => 'С изменением суммы',
                        'total' => $total_orders[$main_key]['total_confirm_orders_with_edit_amount'],
                        'cv' => $total_orders[$main_key]['total_confirm_orders_with_edit_amount'] / $total_orders[$main_key]['total_orders'] * 100,
                    ],
                ],
                'total' => [
                    'count' => $total_orders[$main_key]['total_confirm_orders'],
                    'cv' => $total_orders[$main_key]['total_confirm_orders'] / $total_orders[$main_key]['total_orders'] * 100,
                ],
            ];

            //*************//
            if (!empty($total_orders[$main_key]['total_orders_not_confirmed'])) {
                $this->results[$main_key][] = [
                    'category' => 'Не подписано',
                    'items' => [
                        [
                            'admin_name' => 'Ожидание подписания',
                            'total' => $total_orders[$main_key]['total_orders_not_confirmed'],
                            'cv' => $total_orders[$main_key]['total_orders_not_confirmed'] / $total_orders[$main_key]['total_orders'] * 100,
                        ],
                    ],
                    'total' => [
                        'count' => $total_orders[$main_key]['total_orders_not_confirmed'],
                        'cv' => $total_orders[$main_key]['total_orders_not_confirmed'] / $total_orders[$main_key]['total_orders'] * 100,
                    ],
                ];
            }
        };

        if ($filter_data['filter_group_by']) {
            $generateData($filter_data);
        } else {
            $date_start = new \DateTime($filter_data['filter_date_order']['filter_date_start']);
            $date_end = new \DateTime($filter_data['filter_date_order']['filter_date_end']);

            $interval = DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($date_start, $interval, $date_end->modify('+1 day'));
            foreach ($period as $dt) {
                $date_format = $dt->format('Y-m-d');
                $filter_data['filter_date_order']['filter_date_start'] = $filter_data['filter_date_order']['filter_date_end'] = $date_format;
                $generateData($filter_data);
            }
        }
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

        $filename = 'files/reports/download_rejected_orders.xls';

        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();

        $filter_data = $this->getFilterData();

        $active_sheet->setTitle(
            " " . $filter_data['filter_date_order']['filter_date_start'] . "_" . $filter_data['filter_date_order']['filter_date_end']
        );

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $active_sheet->getColumnDimension('A')->setWidth(15);
        $active_sheet->getColumnDimension('B')->setWidth(15);
        $active_sheet->getColumnDimension('C')->setWidth(15);
        $active_sheet->getColumnDimension('D')->setWidth(20);

        $filter_group_by = $this->request->get('filter_group_by', 'boolean');

        $cell = 'A';
        if (!$filter_group_by) {
            $active_sheet->setCellValue($cell++ .'1', 'Дата');
        }

        $active_sheet->setCellValue($cell++ .'1', 'Сегмент');
        $active_sheet->setCellValue($cell++ .'1', 'Причина');
        $active_sheet->setCellValue($cell++ .'1', 'Кол-во');
        $active_sheet->setCellValue($cell++ .'1', 'Конверсия');

        $row = 2;

        $set_row = function ($data) use (& $active_sheet, & $row, $filter_group_by) {
            $cell = 'A';
            if (!$filter_group_by) {
                $active_sheet->setCellValue($cell++ . $row, $data['date'] ?? '');
            }

            $active_sheet->setCellValue($cell++ . $row, $data['category'] ?? '');
            $active_sheet->setCellValue($cell++ . $row, $data['admin_name'] ?? '');
            $active_sheet->setCellValue($cell++ . $row, $data['total'] ?? '');
            $active_sheet->setCellValue($cell++ . $row, !empty($data['cv']) ? round($data['cv'], 2) : '');
            $row++;
        };

        $set_row_color = function ($color) use (& $active_sheet, & $row, $filter_group_by) {
            $cell_start = !$filter_group_by ? 'C' : 'B';
            $cell_end = !$filter_group_by ? 'E' : 'D';

            $active_sheet->getStyle($cell_start . $row.':'. $cell_end .$row)->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => $color)
                    )
                )
            );
        };

        $total_orders = $this->getTotalOrders();
        $this->getResources($total_orders);
        $this->getOtherItems($total_orders);

        $set_row_color('FFDD00');
        $set_row(
            [
                'category' => 'Поступило заявок всего',
                'total' => $total_orders['total_orders'],
            ]
        );

        foreach ($this->results as $key => $array) {
            $A_row_first = $row;

            foreach ($array as $result) {
                $first_row = $row;
                $is_first_row = true;

                foreach ($result['items'] as $item) {
                    $item['category'] = $is_first_row ? $result['category'] : '';

                    if (!$filter_group_by) {
                        $item['date'] = $is_first_row ? $key : '';
                    }

                    $set_row($item);
                    $is_first_row = false;
                }

                $set_row_color('FFDD00');

                $total_row = [
                    'category' => !empty($result['items']) ? '' : $result['category'],
                    'admin_name' => 'Итого',
                    'total' => $result['total']['count'],
                    'cv' => $result['total']['cv'],
                ];

                $set_row($total_row);

                if (!empty($result['items']) && !empty($result['total'])) {
                    $row_prev_2 = $row - 1;
                    $cell = !$filter_group_by ? 'B' : 'A';

                    $active_sheet->mergeCells($cell . $first_row . ':' . $cell . $row_prev_2);
                }

                $A_row_last = $row - 1;
            }

            if (!$filter_group_by && !empty($A_row_last)) {
                $active_sheet->mergeCells('A' . $A_row_first . ':' . 'A' . $A_row_last);
            }
        }

        for ($i = 'A'; $i < 'F'; $i++) {
            $active_sheet->getColumnDimension($i)->setAutoSize(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename);
        exit;
    }

    /**
     * Получение дат для фильтра
     * @return array
     */
    public function getDataRange(): array
    {
        $filter_data = [];

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

        return $filter_data;
    }
}
