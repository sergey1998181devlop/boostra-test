<?php

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '1200');

require_once dirname(__FILE__) . '/../api/Simpla.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';

class GenerateCDReport extends Simpla {
    public function run()
    {
        error_reporting(0);
        ini_set('display_errors', 'Off');

        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        $filename = 'files/reports/generate_cd_report.xls';

        $active_sheet->setTitle(date('Y_m_d_H_i_s'));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $cell = 'A';

        $active_sheet->setCellValue($cell++ .'1', 'id заявки');
        $active_sheet->setCellValue($cell++ .'1', 'id пользователя');
        $active_sheet->setCellValue($cell++ .'1', 'ФИО');
        $active_sheet->setCellValue($cell++ .'1', 'Дата рождения');
        $active_sheet->setCellValue($cell++ .'1', 'Ссылка на док');
        $active_sheet->setCellValue($cell++ .'1', 'Сумма');
        $active_sheet->setCellValue($cell++ .'1', 'Дата оплаты');

        $row = 2;

        $set_row = function ($array) use (& $active_sheet, & $row) {
            $cell = 'A';
            foreach ($array as $item) {
                $active_sheet->setCellValue($cell++ . $row, $item);
            }
            $row++;
        };

        $results = $this->credit_doctor->getPayReport(
            [
                'status' => $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS,
                'filter_date_added' => [
                    'filter_date_start' => (new DateTime())->modify('-1 days')->format('Y-m-d 00:00:00'),
                    'filter_date_end' => (new DateTime())->modify('-1 days')->format('Y-m-d 23:59:59'),
                ],
            ]
        );

        foreach ($results as $array) {
            $set_row($array);
        }

        for ($i = 'A'; $i <= 'G'; $i++) {
            $active_sheet->getColumnDimension($i)->setAutoSize(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);
    }
}

(new GenerateCDReport())->run();
exit('Ok...');
