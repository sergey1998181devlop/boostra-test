<?php

namespace api\tickets;

use api\traits\TicketStatsHelper;
use Tickets;

/**
 * Класс TicketSubjectStats
 *
 * Формирует статистику тикетов по типам (родительским темам) с разбивкой по месяцам
 * и дочерним темам обращений
 */
class TicketSubjectStats
{
    use TicketStatsHelper;

    private $db;
    private Tickets $tickets;

    public function __construct($db, $tickets)
    {
        $this->db = $db;
        $this->tickets = $tickets;
    }

    /**
     * Получает статистику по типам и темам обращений
     *
     * @param array $mainSubjects
     * @param array $childSubjects
     * @param int|null $managerId ID менеджера для фильтрации
     * @return array
     */
    public function getStatistics(array $mainSubjects, array $childSubjects, ?int $managerId = null): array
    {
        $rawData = $this->getRawStatisticsData($managerId);
        $processedData = $this->processRawData($rawData, $mainSubjects, $childSubjects);
        $this->calculatePercentages($processedData);

        krsort($processedData);

        return [
            'data' => $processedData
        ];
    }

    /**
     * Получает сырые данные статистики из БД
     *
     * @param int|null $managerId
     * @return array
     */
    private function getRawStatisticsData(?int $managerId = null): array
    {
        $query = "
            SELECT 
                DATE_FORMAT(t.created_at, '%Y-%m') AS month,
                CASE
                    WHEN s.parent_id IS NULL THEN s.id
                    ELSE s.parent_id
                END AS parent_subject_id,
                CASE
                    WHEN s.parent_id IS NULL THEN s.name
                    ELSE parent.name
                END AS parent_subject_name,
                t.subject_id AS child_subject_id,
                s.name AS child_subject_name,
                COUNT(*) AS count
            FROM __mytickets t
            LEFT JOIN __mytickets_subjects s ON s.id = t.subject_id
            LEFT JOIN __mytickets_subjects parent ON parent.id = s.parent_id
            WHERE 1=1
        ";

        if ($managerId !== null) {
            $query .= " AND t.manager_id = " . (int)$managerId;
        }

        $query .= "
            GROUP BY month, parent_subject_id, t.subject_id
            ORDER BY month DESC, parent_subject_id, t.subject_id
        ";

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Обрабатывает сырые данные и структурирует их, используя processStatisticsResults из трейта.
     *
     * @param array $rawData Массив объектов с результатами из БД
     * @param array $mainSubjects Ассоциативный массив родительских тем (id => name)
     * @param array $childSubjects Ассоциативный массив дочерних тем (id => name)
     * @return array
     */
    private function processRawData(array $rawData, array $mainSubjects, array $childSubjects): array
    {
        $processedData = [];

        foreach ($rawData as $row) {
            $month = $row->month;
            $parentId = (int)$row->parent_subject_id;
            $ticketSubjectId = (int)$row->child_subject_id;
            $parentName = $row->parent_subject_name;
            $count = (int)$row->count;

            if (!isset($processedData[$month][$parentId])) {
                $processedData[$month][$parentId] = [
                    'name' => $parentName,
                    'total' => 0,
                    'subjects' => [],
                ];

                // Добавляем родительскую тему в дочерние, чтобы учитывать тикеты с родительской темой
                $processedData[$month][$parentId]['subjects'][$parentId] = [
                    'count' => 0,
                    'percentage' => 0,
                ];

                // Инициализация всех известных дочерних тем нулями
                foreach ($childSubjects as $definedChildId => $definedChildName) {
                    if ($definedChildId === $parentId) {
                        continue; // Родительская тема уже добавлена
                    }

                    $processedData[$month][$parentId]['subjects'][$definedChildId] = [
                        'count' => 0,
                        'percentage' => 0,
                    ];
                }
            }

            // Увеличиваем общий total по типу
            $processedData[$month][$parentId]['total'] += $count;

            // Если subject_id совпадает с родителем - считаем тикеты в родительскую "дочернюю" тему
            if ($ticketSubjectId === $parentId && isset($processedData[$month][$parentId]['subjects'][$parentId])) {
                $processedData[$month][$parentId]['subjects'][$parentId]['count'] += $count;
            } elseif (isset($processedData[$month][$parentId]['subjects'][$ticketSubjectId])) {
                $processedData[$month][$parentId]['subjects'][$ticketSubjectId]['count'] += $count;
            }
        }

        foreach ($processedData as $month => &$monthData) {
            foreach ($mainSubjects as $mainSubjectId => $mainSubjectName) {
                if (!isset($monthData[$mainSubjectId])) {
                    $monthData[$mainSubjectId] = [
                        'name' => $mainSubjectName,
                        'total' => 0,
                        'subjects' => [],
                    ];

                    // Добавляем родительскую тему в дочерние
                    $monthData[$mainSubjectId]['subjects'][$mainSubjectId] = [
                        'count' => 0,
                        'percentage' => 0,
                    ];

                    foreach ($childSubjects as $definedChildId => $definedChildName) {
                        if ($definedChildId === $mainSubjectId) {
                            continue;
                        }
                        $monthData[$mainSubjectId]['subjects'][$definedChildId] = [
                            'count' => 0,
                            'percentage' => 0,
                        ];
                    }
                }
            }

            ksort($monthData);
        }

        foreach ($processedData as &$monthData) {
            foreach ($monthData as $parentId => $parentData) {
                if (empty($parentData['name'])) {
                    unset($monthData[$parentId]);
                }
            }
        }

        unset($monthData);

        return $processedData;
    }

    /**
     * Рассчитывает процентные доли
     *
     * @param array &$data
     * @return void
     */
    private function calculatePercentages(array &$data): void
    {
        foreach ($data as $month => &$monthData) {
            foreach ($monthData as $parentId => &$parentData) {
                $typeTotal = $parentData['total'];

                foreach ($parentData['subjects'] as $subjectId => &$subjectData) {
                    if ($typeTotal > 0) {
                        $subjectData['percentage'] = round(($subjectData['count'] / $typeTotal) * 100, 1);
                    } else {
                        $subjectData['percentage'] = 0;
                    }
                }
            }
        }
    }
}