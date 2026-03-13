<?php

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', '600');

require_once 'View.php';

/**
 * Воронка займы промокод
 * Class FunnelLoansPromoCodeView
 */
class FunnelLoansPromoCodeView extends View
{
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

    public function fetch()
    {
        $dateStart = date('Y-m-d');
        $dateEnd = date('Y-m-d');
        $dateRange = $this->request->get('date_range') ?? '';
        if (!empty($dateRange)) {
            $filter_date_array = array_map('trim', explode('-', $dateRange));
            $dateStart = str_replace('.', '-', $filter_date_array[0]);
            $dateEnd = str_replace('.', '-', $filter_date_array[1]);
        }

        $promocodes = $this->promocodes->getListWithOrdersUsersCount();
        $result = $this->filterPromocodesByDate($promocodes, $dateStart, $dateEnd);
        $this->design->assign('promocodes', $result);

        return $this->design->fetch('funnel_loans_promocode.tpl');
    }

    /**
     * Filter all received promocodes by date range.
     *
     * @param array $promocodes
     * @param string $dateStart
     * @param string $dateEnd
     * @return array
     */
    private function filterPromocodesByDate(array $promocodes, string $dateStart, string $dateEnd): array
    {
        $result = [];
        foreach ($promocodes as $promocode) {
            if (
                ($promocode->date_start <= $dateStart && $promocode->date_end >= $dateEnd)
                || ($promocode->date_end >= $dateStart && $promocode->date_end <= $dateEnd)
            ) {
                $result[] = $promocode;
            }
        }

        return $result;
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
        $active_sheet = $excel->getActiveSheet();
        $filename = 'files/reports/funnel_loans_promocodes_users_count_report.xls';
        $active_sheet->setTitle(date('Y_m_d_H_i_s'));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $cell = 'A';
        $active_sheet->setCellValue($cell++ .'1', 'Промокод');
        $active_sheet->setCellValue($cell++ .'1', 'Дана начала действия');
        $active_sheet->setCellValue($cell++ .'1', 'Дата конца действия');
        $active_sheet->setCellValue($cell++ .'1', 'Количество пользователей, активировавших промокод');
        $row = 2;
        $set_row = function ($array) use (& $active_sheet, & $row) {
            $cell = 'A';
            foreach ($array as $item) {
                $active_sheet->setCellValue($cell++ . $row, $item);
            }
            $row++;
        };

        $filter_date = Helpers::getDataRange($this);
        $promocodes = $this->promocodes->getListWithOrdersUsersCount();
        $result = $this->filterPromocodesByDate($promocodes, $filter_date['filter_date_start'], $filter_date['filter_date_end']);
        foreach ($result as $array) {
            $set_row(array_slice((array)$array, 0, 10));
        }

        for ($i = 'A'; $i <= 'J'; $i++) {
            $active_sheet->getColumnDimension($i)->setAutoSize(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename . '?v=' . time());
        exit;
    }
}