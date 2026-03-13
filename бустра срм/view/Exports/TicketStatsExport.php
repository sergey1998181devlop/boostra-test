<?php

class TicketStatsExport
{
    private const SHEET_NAME = 'Статистика обращений';

    private const BASE_STYLES = [
        'halign' => 'center',
        'border' => 'left,right,top,bottom',
        'border-style' => 'thin'
    ];

    private array $config = [
        'colors' => [
            'header' => '#E8E8E8',
            'percentage' => '#E8F5E9',
            'child_header' => '#FFEBEE'
        ]
    ];

    private array $data;
    private array $dimensions;
    private array $styles;
    private XLSXWriter $writer;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->dimensions = $this->calculateDimensions();
        $this->styles = $this->defineStyles();
        $this->writer = new XLSXWriter();
    }

    private function calculateDimensions(): array
    {
        $channelsCount = count($this->data['channels']);
        $baseColumns = 1 + $channelsCount + 1;

        $mainBlock = count($this->data['statuses'])
            * count($this->data['mainSubjects'])
            * ($channelsCount + 2);

        $childBlock = count($this->data['childSubjects']) * $channelsCount;

        return [
            'total' => $baseColumns + $mainBlock + $childBlock,
            'base' => $baseColumns,
            'main' => $mainBlock,
            'child' => $childBlock,
            'channels' => $channelsCount
        ];
    }

    private function defineStyles(): array
    {
        return [
            'header' => array_merge(self::BASE_STYLES, [
                'font-style' => 'bold',
                'fill' => $this->config['colors']['header'],
                'wrap_text' => true
            ]),
            'header_empty' => [
                'fill' => '#FFFFFF',
                'halign' => 'center',
                'border' => 'none',
                'border-style' => 'none'
            ],
            'header_child' => array_merge(self::BASE_STYLES, [
                'font-style' => 'bold',
                'fill' => $this->config['colors']['child_header']
            ]),
            'data' => self::BASE_STYLES,
            'bold' => array_merge(self::BASE_STYLES, [
                'font-style' => 'bold'
            ]),
            'percentage' => array_merge(self::BASE_STYLES, [
                'font-style' => 'bold',
                'fill' => $this->config['colors']['percentage']
            ])
        ];
    }

    private function writeHeaders(): void
    {
        $this->writer->writeSheetHeader(self::SHEET_NAME, ['dummy' => 'string'], ['suppress_row' => true]);

        $headerRows = $this->prepareHeaderRows();

        $this->writer->writeSheetRow(self::SHEET_NAME, $headerRows['row1'], $headerRows['row1Styles']);
        $this->writer->writeSheetRow(self::SHEET_NAME, $headerRows['row2'], $headerRows['row2Styles']);
        $this->writer->writeSheetRow(self::SHEET_NAME, $headerRows['row3'], $this->styles['header']);
    }

    private function fillFirstRow(array &$rows): void
    {
        // Пустые ячейки слева делаем белыми без границ
        for ($i = 0; $i < $this->dimensions['base']; $i++) {
            $rows['row1Styles'][$i] = $this->styles['header_empty'];
        }

        $colIndex = $this->dimensions['base'];

        // Добавляем статусы
        foreach ($this->data['statuses'] as $status) {
            $colspan = count($this->data['mainSubjects']) * ($this->dimensions['channels'] + 2);
            $rows['row1'][$colIndex] = $status->name;
            $this->writer->markMergedCell(self::SHEET_NAME, 0, $colIndex, 0, $colIndex + $colspan - 1);
            $colIndex += $colspan;
        }

        // Дочерние темы (пустая область)
        foreach ($this->data['childSubjects'] as $childSubject) {
            $colspan = $this->dimensions['channels'];
            $this->writer->markMergedCell(self::SHEET_NAME, 0, $colIndex, 0, $colIndex + $colspan - 1);

            // Делаем пустые ячейки белыми без границ
            for ($i = $colIndex; $i < $colIndex + $colspan; $i++) {
                $rows['row1Styles'][$i] = $this->styles['header_empty'];
            }
            $colIndex += $colspan;
        }
    }

    private function fillSecondRow(array &$rows): void
    {
        $colIndex = $this->dimensions['base'];

        // Основные темы под статусами
        foreach ($this->data['statuses'] as $status) {
            foreach ($this->data['mainSubjects'] as $subjectName) {
                $colspan = $this->dimensions['channels'] + 2;
                $rows['row2'][$colIndex] = $subjectName;
                $this->writer->markMergedCell(self::SHEET_NAME, 1, $colIndex, 1, $colIndex + $colspan - 1);
                $colIndex += $colspan;
            }
        }

        // Дочерние темы с другим фоном
        foreach ($this->data['childSubjects'] as $subjectName) {
            $colspan = $this->dimensions['channels'];
            $rows['row2'][$colIndex] = $subjectName;
            $this->writer->markMergedCell(self::SHEET_NAME, 1, $colIndex, 1, $colIndex + $colspan - 1);

            // Применяем стиль для дочерних тем
            for ($i = $colIndex; $i < $colIndex + $colspan; $i++) {
                $rows['row2Styles'][$i] = $this->styles['header_child'];
            }
            $colIndex += $colspan;
        }
    }

    private function fillThirdRow(array &$rows): void
    {
        // Первая колонка - Месяц
        $rows['row3'][0] = 'Месяц';

        // Каналы для общей статистики
        $col = 1;
        foreach ($this->data['channels'] as $channel) {
            $rows['row3'][$col++] = $channel->name;
        }
        $rows['row3'][$col] = 'Итого';

        $colIndex = $this->dimensions['base'];

        // Каналы для основных тем
        foreach ($this->data['statuses'] as $status) {
            foreach ($this->data['mainSubjects'] as $subjectName) {
                foreach ($this->data['channels'] as $channel) {
                    $rows['row3'][$colIndex++] = $channel->name;
                }
                $rows['row3'][$colIndex++] = 'Итого';
                $rows['row3'][$colIndex++] = '%';
            }
        }

        // Каналы для дочерних тем
        foreach ($this->data['childSubjects'] as $subjectName) {
            foreach ($this->data['channels'] as $channel) {
                $rows['row3'][$colIndex++] = $channel->name;
            }
        }
    }

    private function fillRowData(array &$rowData, array &$rowStyles, string $month, array $data): void
    {
        // Месяц
        $rowData[0] = $month;
        $rowStyles[0] = $this->styles['data'];

        // Общая статистика по каналам
        $col = 1;
        foreach ($this->data['channels'] as $channel) {
            $value = $data['total'][$channel->id] ?? 0;
            $rowData[$col] = $value;
            $rowStyles[$col] = ($value > 0) ? $this->styles['bold'] : $this->styles['data'];
            $col++;
        }

        // Общий итог
        $rowData[$col] = $data['total_tickets'] ?? 0;
        $rowStyles[$col] = $this->styles['bold'];
        $col++;

        // Данные по статусам и темам
        foreach ($this->data['statuses'] as $status) {
            foreach ($this->data['mainSubjects'] as $subjectId => $subjectName) {
                // Данные по каналам
                foreach ($this->data['channels'] as $channel) {
                    $value = $data[$status->id][$subjectId][$channel->id] ?? 0;
                    $rowData[$col] = $value;
                    $rowStyles[$col] = ($value > 0) ? $this->styles['bold'] : $this->styles['data'];
                    $col++;
                }

                // Итог и процент
                $total = $data[$status->id][$subjectId]['total'] ?? 0;
                $percentage = $data[$status->id][$subjectId]['percentage'] ?? 0;

                $rowData[$col] = $total;
                $rowStyles[$col] = $this->styles['bold'];
                $col++;

                $rowData[$col] = ($percentage > 0) ? number_format($percentage, 2) . '%' : '0%';
                $rowStyles[$col] = $this->styles['percentage'];
                $col++;
            }
        }

        // Данные по дочерним темам
        foreach ($this->data['childSubjects'] as $subjectId => $subjectName) {
            foreach ($this->data['channels'] as $channel) {
                $value = $this->data['childData'][$month][$subjectId][$channel->id] ?? 0;
                $rowData[$col] = $value;
                $rowStyles[$col] = ($value > 0) ? $this->styles['bold'] : $this->styles['data'];
                $col++;
            }
        }
    }

    private function prepareHeaderRows(): array
    {
        $rows = [
            'row1' => array_fill(0, $this->dimensions['total'], ''),
            'row1Styles' => array_fill(0, $this->dimensions['total'], $this->styles['header']),
            'row2' => array_fill(0, $this->dimensions['total'], ''),
            'row2Styles' => array_fill(0, $this->dimensions['total'], $this->styles['header']),
            'row3' => array_fill(0, $this->dimensions['total'], '')
        ];

        $this->fillFirstRow($rows);
        $this->fillSecondRow($rows);
        $this->fillThirdRow($rows);

        return $rows;
    }

    private function writeData(): void
    {
        foreach ($this->data['parentData'] as $month => $data) {
            $rowData = $this->prepareDataRow($month, $data);
            $this->writer->writeSheetRow(self::SHEET_NAME, $rowData['data'], $rowData['styles']);
        }
    }

    private function prepareDataRow(string $month, array $data): array
    {
        $rowData = array_fill(0, $this->dimensions['total'], '');
        $rowStyles = array_fill(0, $this->dimensions['total'], $this->styles['data']);

        $this->fillRowData($rowData, $rowStyles, $month, $data);

        return ['data' => $rowData, 'styles' => $rowStyles];
    }

    public function export(): XLSXWriter
    {
        $this->writeHeaders();
        $this->writeData();

        return $this->writer;
    }
}