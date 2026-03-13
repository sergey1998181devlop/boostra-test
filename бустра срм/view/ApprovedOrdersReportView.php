<?php

require_once 'View.php';

class ApprovedOrdersReportView extends View
{
    /**
     * Список ключей массива
     */
    public const DEFAULT_ARRAY_KEYS = [
        'approved_total',
        'confirm_total',
        'cv',
        'same_day',
        'cv1',
        'day1',
        'cv2',
        'day2',
        'cv3',
        'day3',
        'cv4',
        'day4',
        'cv5',
        'day5',
        'cv6',
        'day6',
        'cv7',
        'day7',
        'cv8',
    ];

    public const FIELDS_NAME = [
        'Дата',
        'Количество одобренных заявок',
        'Забрали займы по этим заявкам',
        'CV',
        'В день одобрения',
        'CV1',
        'Плюс 1',
        'CV2',
        'Плюс 2',
        'CV3',
        'Плюс 3',
        'CV4',
        'Плюс 4',
        'CV5',
        'Плюс 5',
        'CV6',
        'Плюс 6',
        'CV7',
        'Плюс 7',
        'CV8',
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
     * Get data for report
     *
     * @return array
     */
    public function getData(): array
    {
        // Define source (NK/PK/All) condition
        $condition = [
            'NK' => 'AND o.first_loan = 1',
            'PK' => 'AND o.first_loan = 0',
        ];
        $source = $this->request->get('client_type');
        $sourceCond = $source ? $condition[$source] : '';
        $filterDateRange = Helpers::getDataRange($this);
        $dateFrom = $filterDateRange['filter_date_start'];
        $dateTo = $filterDateRange['filter_date_end'] . ' 23:59:59';

        // Define date range condition
        $dateCond = $dateFrom === $dateTo
            ? "AND CAST(o.date as DATE) = ?"
            : "AND o.date BETWEEN ? AND ?";
        $bindParams = substr_count($dateCond, '?') === 2 ?
            [$dateFrom, $dateTo]
            : [$dateFrom];

        $queryAll = $this->db->placehold(
            "SELECT
                DATE_FORMAT(o.date, '%d-%m-%Y') approve_date_filter,
                SUM(IF(o.approve_date IS NOT NULL, 1, 0)) approved_total,
                SUM(IF(o.confirm_date IS NOT NULL AND o.status != 11, 1, 0)) confirm_total,
                SUM(IF(DATEDIFF(o.confirm_date, o.approve_date) = 0 AND o.status != 11, 1, 0)) same_day,
                SUM(IF(DATEDIFF(o.confirm_date, o.approve_date) = 1 AND o.status != 11, 1, 0)) day1,
                SUM(IF(DATEDIFF(o.confirm_date, o.approve_date) = 2 AND o.status != 11, 1, 0)) day2,
                SUM(IF(DATEDIFF(o.confirm_date, o.approve_date) = 3 AND o.status != 11, 1, 0)) day3,
                SUM(IF(DATEDIFF(o.confirm_date, o.approve_date) = 4 AND o.status != 11, 1, 0)) day4,
                SUM(IF(DATEDIFF(o.confirm_date, o.approve_date) = 5 AND o.status != 11, 1, 0)) day5,
                SUM(IF(DATEDIFF(o.confirm_date, o.approve_date) = 6 AND o.status != 11, 1, 0)) day6,
                SUM(IF(DATEDIFF(o.confirm_date, o.approve_date) > 6 AND o.status != 11, 1, 0)) day7
            FROM s_orders o
            WHERE 1
                $dateCond $sourceCond
            GROUP BY approve_date_filter
            ORDER BY STR_TO_DATE(approve_date_filter, '%d-%m-%Y')
        ",
            $bindParams[0],
            $bindParams[1] ?? ''
        );

        $this->db->query($queryAll);
        $report = $this->db->results();
        $clientTypes = [
            ['type' => 'NK', 'title' => 'НК'],
            ['type' => 'PK', 'title' => 'ПК'],
        ];

        return compact('report', 'clientTypes', 'source', 'dateFrom', 'dateTo');
    }

    public function fetch()
    {
        [$report, $clientTypes, $source, $dateFrom, $dateTo] = array_values($this->getData());

        if (!empty($report)) {
            $totals = $this->generateTotals($report);
            $this->design->assign('totals', $totals);
        }

        $this->design->assign('clientTypes', $clientTypes);
        $this->design->assign('filterSource', $source);
        $this->design->assign('report', $report);
        $this->design->assign('date_from', $dateFrom);
        $this->design->assign('date_to', $dateTo);
        $this->design->assign('report_uri', strtok($_SERVER['REQUEST_URI'], '?'));
        $this->design->assign('fields_name', self::FIELDS_NAME);

        return $this->design->fetch('approved_orders_report.tpl');
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
     * Получаем итоговые значения
     * @param array $results
     * @return array
     */
    private function generateTotals(array $results): array
    {
        $totals = self::getDefaultArray();
        $prevKey = self::DEFAULT_ARRAY_KEYS[0];

        foreach (self::DEFAULT_ARRAY_KEYS as $key) {
            $value = array_sum(array_column($results, $key));

            if (!is_numeric(strpos($key, 'cv'))) {
                $totals[$key] = $value;
                $prevKey = $key;
            } else {
                $totals[$key] = round($totals[$prevKey] * 100 / $totals['approved_total'], 2);
            }
        }

        return $totals;
    }

    /**
     * Выгрузка данных в Excel
     *
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function download(): void
    {
        require_once $this->config->rootDir . 'PHPExcel/Classes/PHPExcel.php';

        $filename = 'files/reports/approved_orders_report.xls';
        $excel    = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();

        [$report] = array_values($this->getData());

        $generateCV = function (& $oldItem) {
            $newItem = [];

            foreach ($oldItem as $key => $value) {
                $newItem[] = $value;
                if (!in_array($key, ['approve_date_filter', 'approved_total'])) {
                    $newItem[] = $oldItem->approved_total ? round(($value * 100 / $oldItem->approved_total), 2) : 0;
                }
            }

            $oldItem = $newItem;
        };

        array_walk($report, $generateCV);

        $activeSheet->setTitle('ApprovedOrdersReport');
        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $col = 'A';
        $row = 1;

        foreach (self::FIELDS_NAME as $field_name)
        {
            $activeSheet->setCellValue($col++ . $row, $field_name);
        }

        $activeSheet->fromArray($report, null, 'A2');

        $activeSheet->getColumnDimension()->setAutoSize(true);
        // перенос слов
        $rowCount = $activeSheet->getHighestRow();
        $activeSheet->getStyle("A1:C" . $rowCount)->getAlignment()->setWrapText(true);

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename);
        exit;
    }
}