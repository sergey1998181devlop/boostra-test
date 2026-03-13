<?php

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

session_start();
chdir('..');

require_once 'api/Simpla.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';

class GetSmsReport extends Simpla
{
    function __construct()
    {
        $this->run();
    }

    private function run()
    {
        $period = $this->request->get('period');
        $date_range = $this->request->get('date');
        $date_from = null;
        $date_to = null;
        if (empty($date_range)) {
            $this->response->json_output(['error' => 'Неправильная дата']);
            exit();
        }
        $date_range = array_map('trim', explode('-', $date_range));
        $date_from = str_replace('.', '-', $date_range[0]);
        $date_to = str_replace('.', '-', $date_range[1]);
        if ($date_from != $date_to) {
            $this->response->json_output(['error' => 'Выберите только один день']);
        }
        $periodQuery = [$period];
        if ($period == 'minus') {
            $periodQuery = [-1,-2,-3,-4,-5];
        }
        $response = $this->sms->get_pr_sms($periodQuery, $date_to);
        if (empty($response)) {
            $this->response->json_output(['error' => 'Отчет еще не создан']);
            exit();
        }
        $file = $this->createExcel($date_from, $date_to, $period,$periodQuery);
        $this->response->json_output(['success' => true, 'file' => $file]);
    }

    private function createExcel($date_from, $date_to, $period,$periodQuery)
    {
        $phpExcel = new PHPExcel();
        $data = $this->getData($date_from, $date_to, $periodQuery);
        $phpExcel->getProperties()
            ->setTitle('SMS Delivery Report')
            ->setSubject('SMS Delivery Report')
            ->setDescription('SMS Delivery Report');
        $phpExcel->setActiveSheetIndex(0);
        $sheet = $phpExcel->getActiveSheet();
        $sheet->setTitle('SMS Delivery Report');
        $sheet->setCellValue('A1', 'дата');
        $sheet->setCellValue('B1', 'телефон');
        $sheet->setCellValue('C1', 'доставлено');
        $sheet->setCellValue('D1', 'не доставлено');
        $row = 2;
        $deliveredCount = 0;
        $undeliveredCount = 0;
        $totalRows = count($data);
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->date);
            $sheet->setCellValue('B' . $row, $item->phone);
            $sheet->setCellValue('C' . $row, $item->sent == 1 ? 'Да' : 'Нет');
            $sheet->setCellValue('D' . $row, $item->sent == 0 ? 'Да' : 'Нет');
            $row++;
            if ($item->sent == 1) {
                $deliveredCount++;
            } else {
                $undeliveredCount++;
            }
        }
        $summaryRowHeader = $totalRows + 2;
        $sheet->setCellValue('A' . $summaryRowHeader, 'итог дата');
        $sheet->setCellValue('B' . $summaryRowHeader, 'номеров всего');
        $sheet->setCellValue('C' . $summaryRowHeader, 'доставлено всего');
        $sheet->setCellValue('D' . $summaryRowHeader, 'недоставлено всего');
        $summaryRow = $totalRows + 3;
        $sheet->setCellValue('A' . $summaryRow, $date_from);
        $sheet->setCellValue('B' . $summaryRow, $totalRows);
        $sheet->setCellValue('C' . $summaryRow, $deliveredCount);
        $sheet->setCellValue('D' . $summaryRow, $undeliveredCount);
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(30);
        $styleArray = [
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $sheet->getStyle('A1:D' . $summaryRow)->applyFromArray($styleArray);
        $writer = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
        $filename = 'sms_delivery_report_' . $period . '_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        if (!is_dir('files/sms_report')) {
            mkdir('files/sms_report', 0777, true);
        }
        $writer->save('files/sms_report/' . $filename);
        return 'files/sms_report/' . $filename;
    }

    private function getData($date_from, $date_to, $period)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __pr_tasks_sms_daily
            WHERE period in (?@) AND date BETWEEN ? and ?
        ", $period, $date_from, $date_to);
        $this->db->query($query);
        return $this->db->results();
    }
}


new GetSmsReport();