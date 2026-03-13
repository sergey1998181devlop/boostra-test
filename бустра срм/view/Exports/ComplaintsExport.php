<?php

class ComplaintsExport
{
    private const BASE_STYLES = [
        'halign' => 'center',
        'border' => 'left,right,top,bottom',
        'border-style' => 'thin'
    ];

    private array $data;
    private array $styles;
    private XLSXWriter $writer;
    private string $sheetName;
    private string $firstColumnTitle;
    private string $filenamePrefix;

    public function __construct(
        array $data,
        string $sheetName,
        string $firstColumnTitle,
        string $filenamePrefix
    ) {
        $this->data = $data;
        $this->sheetName = $sheetName;
        $this->firstColumnTitle = $firstColumnTitle;
        $this->filenamePrefix = $filenamePrefix;
        $this->styles = $this->defineStyles();
        $this->writer = new XLSXWriter();
    }

    private function defineStyles(): array
    {
        return [
            'header' => array_merge(self::BASE_STYLES, [
                'font-style' => 'bold',
                'fill' => '#E8E8E8',
                'wrap_text' => true
            ]),
            'data' => self::BASE_STYLES,
            'total' => array_merge(self::BASE_STYLES, [
                'font-style' => 'bold',
                'fill' => '#F0F0F0'
            ])
        ];
    }

    /**
     * Экспорт данных в Excel
     *
     * @return XLSXWriter
     */
    public function export(): XLSXWriter
    {
        $this->writeHeaders();
        $this->writeData();
        $this->writeTotals();

        return $this->writer;
    }

    /**
     * Полный экспорт с отправкой файла
     */
    public function download(): void
    {
        $writer = $this->export();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->filenamePrefix . '_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    /**
     * Запись заголовков таблицы
     */
    private function writeHeaders(): void
    {
        $this->writer->writeSheetHeader($this->sheetName, ['dummy' => 'string'], ['suppress_row' => true]);

        $headers = [$this->firstColumnTitle];
        foreach ($this->data['subjects'] as $subject) {
            $headers[] = $subject;
        }
        $headers[] = 'Всего';

        $this->writer->writeSheetRow($this->sheetName, $headers, $this->styles['header']);
    }

    /**
     * Запись данных
     */
    private function writeData(): void
    {
        foreach ($this->data['data'] as $responsiblePerson => $personData) {
            $row = [$responsiblePerson];

            foreach ($this->data['subjects'] as $subject) {
                $row[] = $personData[$subject] ?? 0;
            }

            $row[] = $personData['total'] ?? 0;

            $this->writer->writeSheetRow($this->sheetName, $row, $this->styles['data']);
        }
    }

    /**
     * Запись итоговых строк
     */
    private function writeTotals(): void
    {
        if (empty($this->data['data'])) {
            return;
        }

        $totals = ['ИТОГО'];
        $subjectTotals = [];

        foreach ($this->data['subjects'] as $subject) {
            $subjectTotal = 0;
            foreach ($this->data['data'] as $personData) {
                $subjectTotal += $personData[$subject] ?? 0;
            }
            $subjectTotals[$subject] = $subjectTotal;
            $totals[] = $subjectTotal;
        }

        $grandTotal = array_sum($subjectTotals);
        $totals[] = $grandTotal;

        $this->writer->writeSheetRow($this->sheetName, $totals, $this->styles['total']);
    }
}
