<?php

chdir('..');

require 'api/Simpla.php';

class DownloadPushSmsCount extends Simpla
{

    public function run()
    {
        $dataRange = $this->request->get('dataRange');
        $plus = $this->request->get('plus');
        $type = 'ccprolongation_zero';
        $dataRange = array_map('trim', explode('-', $dataRange));
        $date_from = str_replace('.', '-', $dataRange[0]);
        $date_to = str_replace('.', '-', $dataRange[1]);
        if (!empty($plus)) {
            $type = 'ccprolongation';
        }
        $push = $this->sms->get_pushes_sms([
            'date_from' => $date_from,
            'date_to' => $date_to,
            'type' => $type,
            'table' => 's_pushes'
        ]);
        $sms = $this->sms->get_pushes_sms([
            'date_from' => $date_from,
            'date_to' => $date_to,
            'type' => $type,
            'status' => 'ACCEPTED',
            'table' => 's_sms_messages'
        ]);

        $data = ['push' => $push,'sms' => $sms];
        $data = $this->formatData($data);
        $url = $this->downloadFile($data);
        echo json_encode([
            'success' => true,
            'message' => $url
        ]);

    }
    private function formatData($data) {
        $formattedArray = [];

        foreach ($data as $key => $entries) {
            foreach ($entries as $entry) {
                $date = $entry->created_date;
                if (!isset($formattedArray[$date])) {
                    $formattedArray[$date] = ['date' => $date,'push' => 0, 'sms' => 0];
                }
                $formattedArray[$date][$key] = $entry->count;
            }
        }
        return  array_values($formattedArray);

    }
    /**
     * @param $tasks
     * @return string
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function downloadFile($data): string
    {
        require_once $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';
        error_reporting(0);
        ini_set('display_errors', 'Off');

        $filename = 'files/reports/sms-push-count.xls';
        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();

        $active_sheet->setTitle('Call List');

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $active_sheet->getColumnDimension('A')->setWidth(20);
        $active_sheet->getColumnDimension('B')->setWidth(20);
        $active_sheet->getColumnDimension('B')->setWidth(25);

        $active_sheet->setCellValue('A1', 'Дата');
        $active_sheet->setCellValue('B1', 'Количество пушей');
        $active_sheet->setCellValue('C1', 'Количество смс');
        $active_sheet->fromArray(array_map(fn($item) => (array)$item, $data), null, 'A2');

        $active_sheet->getColumnDimension()->setAutoSize(true);
        $row_count = $active_sheet->getHighestRow();
        $active_sheet->getStyle("A1:C" . $row_count)->getAlignment()->setWrapText(true);

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);
        return $this->config->root_url . '/' . $filename;
    }

}

(new DownloadPushSmsCount())->run();

