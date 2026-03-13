<?php

declare(strict_types=1);

require_once 'View.php';

/**
 * Класс для вывода отчёта по отвалам
 */
class DopConversionReportView extends View
{
    public const NEW_CONTRACTS_TYPE = 'new_contracts';
    public const NEW_CONTRACTS_TITLE = 'Новые договоры';
    public const PROLONGATIONS_TYPE = 'prolongations';
    public const PROLONGATIONS_TITLE = 'Пролонгация';
    public const CONVERSION_TYPES_MAPPING = [
        self::NEW_CONTRACTS_TYPE => self::NEW_CONTRACTS_TITLE,
        self::PROLONGATIONS_TYPE => self::PROLONGATIONS_TITLE
    ];

    private string $dateFrom;
    private string $dateTo;

    public function __construct()
    {
        $dateRange = Helpers::getDataRange($this);
        $this->dateFrom = $dateRange['filter_date_start'];
        $this->dateTo = $dateRange['filter_date_end'] . ' 23:59:59';

        parent::__construct();
        $action = $this->request->get('action');

        if ($action && method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        $result = [];
        $totals = new StdClass();
        $query = '';
        $filterType = $this->request->get('filter_type') ?? self::NEW_CONTRACTS_TYPE;
        if ($filterType === self::NEW_CONTRACTS_TYPE) {
            $sql = "
               SELECT (CASE WHEN o.have_close_credits = 0 THEN 'НК' ELSE 'ПК' END) as client_type, 
                      COUNT(o.id) as total_count, 
                      COUNT(i.id) as insurance_count, 
                      COUNT(scdtu.id) as credit_doctor_count, 
                      0 as multipolis_count, 
                      0 as telemedicina_count
                FROM s_orders o
                LEFT JOIN s_credit_doctor_to_user scdtu ON scdtu.order_id = o.id AND scdtu.status = ?
                LEFT JOIN s_insurances i ON i.order_id = o.id
                LEFT JOIN s_users u ON o.user_id = u.id
                WHERE o.date >= ? AND o.date <= ? AND o.confirm_date IS NOT NULL
                GROUP BY client_type 
            ";

            $query = $this->db->placehold(
                $sql,
                CDoctor::STATUS_SUCCESS,
                $this->dateFrom,
                $this->dateTo,
            );
        }

        if ($filterType === self::PROLONGATIONS_TYPE) {
            $sql = "
               SELECT 
                  (CASE WHEN have_close_credits = 0 THEN 'НК' ELSE 'ПК' END) as client_type, 
                  SUM(total_count) as total_count, 
                  SUM(insurance_count) as insurance_count, 
                  SUM(credit_doctor) as credit_doctor_count, 
                  SUM(multipolis_count) as multipolis_count, 
                  SUM(telemedicina_count) as telemedicina_count 
               FROM 
                  (
                    SELECT 
                      so.have_close_credits, 
                      COUNT(bpp.id) AS total_count, 
                      SUM(CASE WHEN bpp.insure > 0 THEN 1 ELSE 0 END) AS insurance_count, 
                      0 AS credit_doctor, 
                      0 AS multipolis_count,
                      0 AS telemedicina_count 
                    FROM 
                      b2p_payments bpp 
                      INNER JOIN s_users u ON bpp.user_id = u.id 
                        AND (u.loan_history ->> '$[*].number' LIKE CONCAT('%', bpp.contract_number, '%')) = 1
                      INNER JOIN s_orders so ON so.user_id = bpp.user_id 
                      AND so.confirm_date IS NOT NULL 
                    WHERE 
                      bpp.prolongation = 1 
                      AND bpp.reason_code = 1 
                      AND bpp.created >= ? 
                      AND bpp.created <= ? 
                    GROUP BY so.have_close_credits 
                    UNION ALL 
                    SELECT 
                      so.have_close_credits, 
                      COUNT(st.id) AS total_count, 
                      SUM(CASE WHEN st.insure_amount > 0 THEN 1 ELSE 0 END) AS insurance_count, 
                      0 AS credit_doctor, 
                      0 AS multipolis_count, 
                      0 AS telemedicina_count 
                    FROM 
                      s_transactions st 
                      INNER JOIN s_users u ON st.user_id = u.id 
                        AND (u.loan_history ->> '$[*].number' LIKE CONCAT('%', st.contract_number, '%')) = 1 
                      INNER JOIN s_orders so ON so.user_id = st.user_id 
                      AND so.confirm_date IS NOT NULL 
                    WHERE 
                      st.prolongation = 1 
                      AND st.created >= ? 
                      AND st.created <= ? 
                      AND st.status IN(?@)
                    GROUP BY 
                      so.have_close_credits
                    UNION ALL 
                    SELECT 
                      so.have_close_credits, 
                      0 AS total_count, 
                      0 AS insurance_count, 
                      0 AS credit_doctor, 
                      0 AS multipolis_count, 
                      COUNT(tvp.id) AS telemedicina_count 
                    FROM 
                      s_tv_medical_payments tvp
                      INNER JOIN s_orders so ON so.id = tvp.order_id 
                      AND so.confirm_date IS NOT NULL 
                    WHERE 
                      tvp.date_added >= ? 
                      AND tvp.date_added <= ? 
                      AND tvp.status = ?  
                    GROUP BY so.have_close_credits
                    UNION ALL 
                    SELECT 
                      so.have_close_credits, 
                      0 AS total_count, 
                      0 AS insurance_count, 
                      0 AS credit_doctor, 
                      COUNT(m.id) AS multipolis_count, 
                      0 AS telemedicina_count 
                    FROM 
                      s_multipolis m
                      INNER JOIN s_orders so ON so.id = m.order_id 
                      AND so.confirm_date IS NOT NULL 
                    WHERE 
                      m.date_added >= ? 
                      AND m.date_added <= ? 
                      AND m.status = ?  
                    GROUP BY so.have_close_credits
                  ) a 
                GROUP BY client_type
            ";

            $query = $this->db->placehold(
                $sql,
                $this->dateFrom,
                $this->dateTo,
                $this->dateFrom,
                $this->dateTo,
                array_map('strval', Transactions::STATUSES_SUCCESS),
                $this->dateFrom,
                $this->dateTo,
                $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS,
                $this->dateFrom,
                $this->dateTo,
                Multipolis::STATUS_SUCCESS,
            );
        }

        if (!$query) {
            return compact('result', 'totals', 'filterType');
        }

        $this->db->query($query);
        $result = $this->db->results() ?? [];

        if ($result) {
            $total = 0;
            $totalInsurances = 0;
            $totalCD = 0;
            $totalMultipolis = 0;
            $totalTelemedicina = 0;

            foreach ($result as $item) {
                $total += $item->total_count;
                $totalInsurances += $item->insurance_count;
                $totalCD += $item->credit_doctor_count;
                $totalMultipolis += $item->multipolis_count;
                $totalTelemedicina += $item->telemedicina_count;
            }

            $totals->total = $total;
            $totals->totalInsurances = $totalInsurances;
            $totals->totalCD = $totalCD;
            $totals->totalMultipolis = $totalMultipolis;
            $totals->totalTelemedicina = $totalTelemedicina;
        }

        return compact('result', 'totals', 'filterType');
    }

    public function fetch()
    {
        [$report, $totals, $filterType] = array_values($this->getData());

        $this->design->assign('dateStart', $this->dateFrom);
        $this->design->assign('dateEnd', $this->dateTo);
        $this->design->assign('clientTypes', self::CONVERSION_TYPES_MAPPING);
        $this->design->assign('filterType', $filterType);
        $this->design->assign('report', $report);
        $this->design->assign('totals', $totals);
        $this->design->assign('report_uri', strtok($_SERVER['REQUEST_URI'], '?'));

        return $this->design->fetch('dop_conversion_report.tpl');
    }

    /**
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws Exception
     */
    public function download()
    {
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');

        require_once $this->config->rootDir . 'PHPExcel/Classes/PHPExcel.php';
        $excel = new PHPExcel();
        [$report, $totals] = array_values($this->getData());

        $filename = 'files/reports/dop_conversion_report.xls';

        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();

        $activeSheet->setTitle('MissingsReport');
        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $activeSheet->getColumnDimension('A')->setWidth(20);
        $activeSheet->getColumnDimension('B')->setWidth(20);
        $activeSheet->getColumnDimension('C')->setWidth(20);
        $activeSheet->getColumnDimension('D')->setWidth(20);
        $activeSheet->getColumnDimension('E')->setWidth(20);
        $activeSheet->getColumnDimension('F')->setWidth(20);

        $activeSheet->getRowDimension("1")->setRowHeight(30);
        $activeSheet->setCellValue("A1", "");
        $activeSheet->setCellValue("B1", "Новые договоры");
        $activeSheet->setCellValue("C1", "Доп на выдачи Страховка, шт");
        $activeSheet->setCellValue("D1", "Доп на выдачи КД");
        $activeSheet->setCellValue("E1", "Доп на выдачи Телемедицина");
        $activeSheet->setCellValue("F1", "Доп на выдачи Мультиполис");

        $rowNumber = 2;
        foreach ($report as $row) {
            $activeSheet->setCellValue("A" . $rowNumber, $row->client_type);
            $activeSheet->setCellValue("B" . $rowNumber, $row->total_count);
            $activeSheet->setCellValue("C" . $rowNumber, $row->insurance_count);
            $activeSheet->setCellValue("D" . $rowNumber, $row->credit_doctor_count);
            $activeSheet->setCellValue("E" . $rowNumber, $row->telemedicina_count);
            $activeSheet->setCellValue("F" . $rowNumber, $row->multipolis_count);

            $rowNumber++;

            $activeSheet->setCellValue("A" . $rowNumber, 'Проникновение');
            $activeSheet->setCellValue("B" . $rowNumber, '');
            $activeSheet->setCellValue("C" . $rowNumber, number_format($row->insurance_count / $row->total_count, 2, '.', ''));
            $activeSheet->setCellValue("D" . $rowNumber, number_format($row->credit_doctor_count / $row->total_count, 2, '.', ''));
            $activeSheet->setCellValue("E" . $rowNumber, number_format($row->telemedicina_count / $row->total_count, 2, '.', ''));
            $activeSheet->setCellValue("F" . $rowNumber, number_format($row->multipolis_count / $row->total_count, 2, '.', ''));

            $rowNumber++;
        }

        $activeSheet->setCellValue("A" . $rowNumber, 'Итого');
        $activeSheet->setCellValue("B" . $rowNumber, $totals->totalOrders);
        $activeSheet->setCellValue("C" . $rowNumber, $totals->totalInsurances);
        $activeSheet->setCellValue("D" . $rowNumber, $totals->totalCD);
        $activeSheet->setCellValue("E" . $rowNumber, $totals->totalTelemedicina);
        $activeSheet->setCellValue("F" . $rowNumber, $totals->totalMultipolis);

        $activeSheet->getColumnDimension()->setAutoSize(true);
        $rowCount = $activeSheet->getHighestRow();
        $activeSheet->getStyle("A1:F" . $rowCount)->getAlignment()->setWrapText(true);

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename);
        exit;
    }
}