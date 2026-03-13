<?php

declare(strict_types=1);

require_once 'View.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

/**
 * Class CDReportView
 * Класс для отображения количества юзеров, купивших КД, с группировкой по уровню КД
 */
class CDUsersAmountReportView extends View
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

    /**
     * @throws Exception
     */
    public function fetch()
    {
        $total = 0;
        $totalSales = 0;
        $result = $this->credit_doctor->getUserCreditDoctorList();
        foreach ($result as $row) {
            $total += (int)$row->user_count;
            $totalSales += (int)$row->user_count * (int)$row->level_number;
        }

        $this->design->assign('result', $result);
        $this->design->assign('total', $total);
        $this->design->assign('totalSales', $totalSales);

        return $this->design->fetch('cdoctor_users_amount_report.tpl');
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
        $filename = 'files/reports/cd_users_amount_per_level_report.xls';
        $active_sheet->setTitle(date('Y_m_d_H_i_s'));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $cell = 'A';
        $active_sheet->setCellValue($cell++ . '1', 'Ступень обучения');
        $active_sheet->setCellValue($cell++ . '1', 'Количество пользователей, купивших КД этой ступени');
        $row = 2;
        $set_row = function ($array) use (&$active_sheet, &$row) {
            $cell = 'A';
            foreach ($array as $k => $item) {
                if ($k === 'level_number') {
                    continue;
                }

                $active_sheet->setCellValue($cell++ . $row, $item);
            }
            $row++;
        };

        $results = $this->credit_doctor->getUserCreditDoctorList();
        foreach ($results as $array) {
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
