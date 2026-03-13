<?php

declare(strict_types=1);

require_once 'View.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

/**
 * Class MultipolisStatisticsReportView
 */
class MultipolisStatisticsReportView extends View
{
    private const REPORT_FILENAME = 'files/reports/multipolis_statistics_report.xls';

    /**
     * Adds a possibility to call download method
     */
    public function __construct()
    {
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
        $filter_date_range = Helpers::getDataRange($this);
        $filters = [
            'filter_date_start' => $filter_date_range['filter_date_start'],
            'filter_date_end' => $filter_date_range['filter_date_end'] . ' 23:59:59',
            'filter_status' => Multipolis::STATUS_SUCCESS,
            'filter_group_by' => 'multipolis_id'
        ];

        $multipolisList = $this->multipolis->getAllWithUsersData(
            $filters,
            true,
            true,
            true
        );

        return compact('multipolisList');
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {
        $filter_date_range = Helpers::getDataRange($this);
        $filters = [
            'filter_date_start' => $filter_date_range['filter_date_start'],
            'filter_date_end' => $filter_date_range['filter_date_end'] . ' 23:59:59',
        ];

        $this->design->assign('dateStart', $filters['filter_date_start']);
        $this->design->assign('dateEnd', $filters['filter_date_end']);
        $this->design->assign('report_uri', strtok($_SERVER['REQUEST_URI'], '?'));

        return $this->design->fetch('multipolis_statistics_report.tpl');
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
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');

        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $filename = self::REPORT_FILENAME;
        $activeSheet->setTitle(date('Y_m_d_H_i_s'));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        [$multipolisList] = array_values($this->getData());

        $activeSheet->setCellValue('A1', 'id заявки');
        $activeSheet->setCellValue('B1', 'id пользователя');
        $activeSheet->setCellValue('C1', 'ФИО');
        $activeSheet->setCellValue('D1', 'Дата рождения');
        $activeSheet->setCellValue('E1', 'Ссылка на док');
        $activeSheet->setCellValue('F1', 'Сумма');
        $activeSheet->setCellValue('G1', 'Дата оплаты');
        $activeSheet->setCellValue('H1', 'id карты');
        $activeSheet->setCellValue('I1', 'метод оплаты');
        $activeSheet->setCellValue('J1', 'Pan card');
        $activeSheet->setCellValue('K1', 'Поступление');
        $activeSheet->setCellValue('L1', 'Возврат');

        $rowNumber = 2;
        foreach ($multipolisList as $item) {
            $activeSheet->setCellValue('A' . $rowNumber, $item->order_id);
            $activeSheet->setCellValue('B' . $rowNumber, $item->user_id);
            $activeSheet->setCellValue('C' . $rowNumber, $item->username);
            $activeSheet->setCellValue('D' . $rowNumber, $item->birth);
            $activeSheet->setCellValue('E' . $rowNumber, $item->doc_url);
            $activeSheet->setCellValue('F' . $rowNumber, $item->payment_sum);
            $activeSheet->setCellValue('G' . $rowNumber, $item->payment_date);
            $activeSheet->setCellValue('H' . $rowNumber, $item->card_id);
            $activeSheet->setCellValue('I' . $rowNumber, $item->payment_method);
            $activeSheet->setCellValue('J' . $rowNumber, $item->pan);
            $activeSheet->setCellValue('K' . $rowNumber, $item->amount);
            $activeSheet->setCellValue('L' . $rowNumber, $item->return_amount);
            $rowNumber++;
        }

        for ($i = 'A'; $i <= 'L'; $i++) {
            $activeSheet->getColumnDimension($i)->setAutoSize(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename . '?v=' . time());
        exit;
    }
}
