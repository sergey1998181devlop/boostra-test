<?php

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once 'View.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

ini_set('max_execution_time', 600);

/**
 * Class CDTvMedicalView
 */
class CDTvMedicalView extends View
{

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
        return $this->design->fetch('cd_tv_medical_view.tpl');
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
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');

        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        $filename = 'files/reports/download_cd_tv_medical.xls';

        $active_sheet->setTitle(date('Y_m_d_H_i_s'));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $cell = 'A';

        $active_sheet->setCellValue($cell++ .'1', 'id заявки');
        $active_sheet->setCellValue($cell++ .'1', 'id пользователя');
        $active_sheet->setCellValue($cell++ .'1', 'ФИО');
        $active_sheet->setCellValue($cell++ .'1', 'Дата рождения');
        $active_sheet->setCellValue($cell++ .'1', 'Сумма');
        $active_sheet->setCellValue($cell++ .'1', 'Дата оплаты');
        $active_sheet->setCellValue($cell++ . '1', 'Поступление');
        $active_sheet->setCellValue($cell++ . '1', 'Возврат');
        $active_sheet->setCellValue($cell++ . '1', 'Тип');

        $row = 2;

        $set_row = function ($array) use (& $active_sheet, & $row) {
            $cell = 'A';
            foreach ($array as $item) {
                $active_sheet->setCellValue($cell++ . $row, $item);
            }
            $row++;
        };

        $filter_limit = [
//            'limit' => 10000,
            'offset' => 0,
        ];

        $filter_date = Helpers::getDataRange($this);

        $results = $this->tv_medical->getPayReport(
            [
                'status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS,
                'filter_limit' => $filter_limit,
                'filter_date_added' => $filter_date,
            ]
        );

        $rowNumber = 2;

        foreach ($results as $item) {
            $type = 'Частичная оплата';

            if ($item->prolongation) {
                $type = 'Пролонгация';
            } elseif ($item->is_full) {
                $type = 'Полная оплата';
            }

            $active_sheet->setCellValue('A' . $rowNumber, $item->order_id);
            $active_sheet->setCellValue('B' . $rowNumber, $item->user_id);
            $active_sheet->setCellValue('C' . $rowNumber, $item->fio);
            $active_sheet->setCellValue('D' . $rowNumber, $item->birth);
            $active_sheet->setCellValue('E' . $rowNumber, $item->amount);
            $active_sheet->setCellValue('F' . $rowNumber, $item->date_added);
            $active_sheet->setCellValue('G' . $rowNumber, $item->amount);
            $active_sheet->setCellValue('H' . $rowNumber, $item->rp_amount);
            $active_sheet->setCellValue('I' . $rowNumber, $type);

            $rowNumber++;
        }

        for ($i = 'A'; $i <= 'L'; $i++) {
            $active_sheet->getColumnDimension($i)->setAutoSize(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename . '?v=' . time());
        exit;
    }

}
