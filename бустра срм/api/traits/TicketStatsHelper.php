<?php

namespace api\traits;

trait TicketStatsHelper {
    /**
     * Общий метод для расчета процентов для данных статистики.
     * Используется как для ежемесячной, так и для ежедневной статистики.
     *
     * @param array &$data Ссылка на массив данных для расчета процентов
     * @return void
     */
    private function calculatePercentagesGeneric(array &$data): void
    {
        $statuses = $this->tickets->getStatuses();

        foreach ($data as $period => &$periodData) {
            foreach ($periodData as $status => &$subjects) {
                if ($status === 'total' || $status === 'total_tickets') {
                    continue;
                }

                foreach ($subjects as $parentId => &$subjectData) {
                    $totalForParent = $subjectData['total'] ?? 0;

                    $totalTicketsForParent = 0;
                    foreach ($statuses as $s) {
                        if (isset($periodData[$s->id][$parentId]['total'])) {
                            $totalTicketsForParent += $periodData[$s->id][$parentId]['total'];
                        }
                    }

                    // Расчет процента: (тикеты в этом статусе / общее количество тикетов для родителя) * 100
                    $subjectData['percentage'] = $totalTicketsForParent > 0
                        ? ($totalForParent / $totalTicketsForParent) * 100
                        : 0;
                }
            }
        }
    }

    /**
     * Формирует SQL-запрос для получения статистики тикетов.
     *
     * @param string $dateFormat Формат даты для группировки ('%Y-%m' или '%Y-%m-%d')
     * @param array $groupByFields Поля для группировки
     * @param string $extraCondition Дополнительное условие WHERE
     * @param string $subjectIdField
     * @return string SQL-запрос с плейсхолдерами
     */
    protected function buildStatisticsQuery(
        string $dateFormat,
        array $groupByFields,
        string $extraCondition = '',
        string $subjectIdField = ''
    ): string {
        $subjectIdSelect = !empty($subjectIdField)
            ? $subjectIdField
            : "COALESCE(parent.id, s.id) as parent_id";

        $query = "
            SELECT 
                DATE_FORMAT(ticket.created_at, '{$dateFormat}') as month,
                ticket.status_id,
                {$subjectIdSelect},
                ticket.chanel_id,
                COUNT(ticket.id) as count
            FROM __mytickets AS ticket
            LEFT JOIN __mytickets_subjects s ON s.id = ticket.subject_id AND s.is_active = TRUE
            LEFT JOIN __mytickets_subjects parent ON s.parent_id = parent.id AND parent.is_active = TRUE
            WHERE 1=1
            AND COALESCE(JSON_EXTRACT(ticket.data, '$.agreement_copy'), 0) = 0
        ";

        if (!empty($extraCondition)) {
            $query .= " AND {$extraCondition}";
        }

        $query .= " GROUP BY " . implode(', ', $groupByFields);

        return $query;
    }
    
    /**
     * Обрабатывает результаты статистики с общим подходом.
     * Этот метод извлекает общую логику из processParentStatisticsResults и processChildStatisticsResults.
     *
     * @param array $results Результаты запроса
     * @param string $periodField Название поля, содержащего период (месяц/день)
     * @param callable|null $processRowFunc Функция для обработки каждой строки результата
     * @param array $initialStructure Начальная структура данных для каждого периода
     * @return array Обработанные данные статистики
     */
    private function processStatisticsResults(
        array $results,
        string $periodField = 'month',
        callable $processRowFunc = null,
        array $initialStructure = []
    ): array {
        $processedData = [];

        foreach ($results as $row) {
            $period = $row->$periodField;

            // Инициализация структуры данных периода при необходимости
            if (!isset($processedData[$period])) {
                $processedData[$period] = $initialStructure;
            }

            // Обработка строки с предоставленной функцией
            if ($processRowFunc) {
                $processRowFunc($processedData, $row);
            }
        }

        return $processedData;
    }
}