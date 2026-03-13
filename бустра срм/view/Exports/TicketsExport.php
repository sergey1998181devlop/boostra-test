<?php

require_once __DIR__ . '/../../PHPExcel/Classes/PHPExcel.php';
require_once __DIR__ . '/../View.php';

class TicketsExport extends View
{
    private $tickets;
    private $filter;

    public function __construct($tickets, array $filter)
    {
        parent::__construct();
        $this->tickets = $tickets;
        $this->filter = $filter;
    }

    /**
     * Экспортирует тикеты в Excel и сохраняет файл.
     *
     * @return array ['success' => bool, 'filename' => string, 'path' => string]
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws PHPExcel_Reader_Exception
     */
    public function export(): array
    {
        $phpExcel = new PHPExcel();
        $sheet = $phpExcel->getActiveSheet();

        $headers = [
            'ID', 'Клиент', 'Канал', 'Дата', 'Тип обращения', 'Тема',
            'Статус проработки', 'Приоритет', 'Статус обращения',
            'Компания', 'Телефон', 'Регион', 'Дни просрочки',
            'Исполнитель', 'Ответственный по договору', 'Группа',
            'Описание', 'Результат отработки'
        ];

        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
        }

        $this->filter['limit'] = 10000;

        $tickets = $this->tickets->getAllTickets($this->filter);

        $row = 2;
        foreach ($tickets['data'] as $ticket) {
            $col = 0;
            $data = [
                (string)$ticket->id,
                (string)($ticket->client_name ?? ''),
                (string)($ticket->chanel_name ?? ''),
                (string)($ticket->created_at ?? ''),
                (string)($ticket->subject_parent_name ?? ''),
                (string)($ticket->ticket_subject ?? ''),
                (string)($ticket->status_name ?? ''),
                (string)($ticket->priority_name ?? ''),
                ($ticket->is_repeat ? 'Повторное' : 'Первичное'),
                (string)($ticket->company_name ?? ''),
                (string)($ticket->client_phone ?? ''),
                (string)($ticket->client_region ?? ''),
                (string)($ticket->data['overdue_days'] ?? ''),
                (string)($ticket->name_manager ?? ''),
                (string)($ticket->responsible_person_name ?? ''),
                (string)($ticket->responsible_group_name ?? ''),
                strip_tags($ticket->description ?? ''),
                strip_tags($ticket->last_comment ?? '')
            ];

            foreach ($data as $value) {
                $sheet->setCellValueExplicitByColumnAndRow(
                    $col++,
                    $row,
                    $value,
                    PHPExcel_Cell_DataType::TYPE_STRING
                );
            }

            $row++;
        }

        foreach (range(0, count($headers) - 1) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'name' => 'Arial'],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'E8E8E8']
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ]
        ]);

        $sheet->getStyle('A2:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray([
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
            'alignment' => ['vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true]
        ]);

        $exportDir = 'files/exports/';
        if (!file_exists($this->config->root_dir . $exportDir)) {
            mkdir($this->config->root_dir . $exportDir, 0777, true);
        }

        $filename = $exportDir . 'tickets_export_' . date('Y-m-d_H-i-s') . '.xls';
        $writer = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel5');
        $writer->save($this->config->root_dir . $filename);

        return [
            'success' => true,
            'filename' => basename($filename),
            'path' => $filename
        ];
    }

    /**
     * Отдаёт готовый файл пользователю (скачивание)
     *
     * @throws RuntimeException
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws PHPExcel_Reader_Exception
     */
    public function download(): void
    {
        $result = $this->export();

        if (!$result['success']) {
            throw new RuntimeException("Ошибка при экспорте файла");
        }

        $file = $this->config->root_dir . $result['path'];

        if (!file_exists($file)) {
            throw new RuntimeException("Файл не найден: {$file}");
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        if (headers_sent()) {
            throw new RuntimeException("Заголовки уже отправлены. Нельзя скачать файл.");
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));

        readfile($file);
        exit;
    }
}
