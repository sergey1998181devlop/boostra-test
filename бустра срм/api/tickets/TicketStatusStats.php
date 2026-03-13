<?php

namespace api\tickets;

use Tickets;

/**
 * Класс TicketSubjectStats
 *
 * Сервисный класс, предназначенный для формирования общей статистики по родительским тетам
 * */
class TicketStatusStats
{
    private $db;
    private Tickets $tickets;

    public function __construct($db, $tickets) {
        $this->db = $db;
        $this->tickets = $tickets;
    }

    /**
     * Получает детальную статистику тикетов с группировкой по темам и статусам.
     *
     * @return array Детальная статистика с разбивкой по темам и статусам
     */
    public function getStatistics(): array
    {
        $statuses = $this->tickets->getStatuses();
        $results = $this->getDetailedTicketData();

        return $this->groupStatisticsByMonthAndDay($results, $statuses);
    }

    /**
     * Группирует статистику по месяцам и дням.
     *
     * @param array $results Результаты запроса
     * @param array $statuses Массив статусов тикетов
     * @return array Статистика, сгруппированная по месяцам и дням
     */
    private function groupStatisticsByMonthAndDay(array $results, array $statuses): array
    {
        $groupedStatistics = [];

        foreach ($results as $row) {
            $month = $row->month ?? 'Без даты';
            $day = $row->day ?? 'Без даты';

            // Инициализируем месяц
            if (!isset($groupedStatistics[$month])) {
                $groupedStatistics[$month] = [
                    'total' => $this->initializeDetailedStatisticsStructure($statuses)['total'],
                    'subjects' => [],
                    'repeats' => [
                        'total' => 0,
                        'by_subject' => []
                    ],
                    'days' => []
                ];
            }

            // Инициализируем день внутри месяца
            if (!isset($groupedStatistics[$month]['days'][$day])) {
                $groupedStatistics[$month]['days'][$day] = $this->initializeDetailedStatisticsStructure($statuses);
            }

            // Обрабатываем строку как для дня
            $this->processDetailedStatisticsResults([$row], $groupedStatistics[$month]['days'][$day], $statuses);

            // Обрабатываем строку как для месяца
            $this->processDetailedStatisticsResults([$row], $groupedStatistics[$month], $statuses);
        }

        return $groupedStatistics;
    }


    /**
     * Получает данные о тикетах для детальной статистики.
     *
     * @return array Массив данных о тикетах
     */
    private function getDetailedTicketData(): array
    {
        $query = $this->db->placehold("
            SELECT 
                DATE_FORMAT(t.created_at, '%Y-%m') AS month,
                DATE_FORMAT(t.created_at, '%Y-%m-%d') AS day,
                COUNT(t.id) AS total,
                SUM(CASE WHEN t.is_repeat = 1 THEN 1 ELSE 0 END) as repeat_count,
                COALESCE(parent.id, s.id) as subject_id,
                COALESCE(parent.name, s.name) as subject_name,
                t.status_id,
                st.name as status_name,
                st.color as status_color,
                SUM(CASE WHEN t.feedback_received = 1 THEN 1 ELSE 0 END) as feedback_received_count,
                SUM(CASE WHEN t.feedback_received = 0 THEN 1 ELSE 0 END) as feedback_not_received_count
            FROM __mytickets t
            LEFT JOIN __mytickets_subjects s ON t.subject_id = s.id AND s.is_active = TRUE
            LEFT JOIN __mytickets_subjects parent ON s.parent_id = parent.id
            LEFT JOIN __mytickets_statuses st ON t.status_id = st.id
            WHERE COALESCE(parent.parent_id, s.parent_id) = 0
            GROUP BY month, day, COALESCE(parent.id, s.id), t.status_id
            ORDER BY month DESC, day DESC, subject_id, t.status_id
        ");

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Инициализирует структуру данных для детальной статистики.
     *
     * @param array $statuses Массив статусов тикетов
     * @return array Инициализированная структура данных
     */
    private function initializeDetailedStatisticsStructure(array $statuses): array
    {
        $statistics = [
            'total' => [
                'count' => 0,
                'by_status' => [],
                'feedback_received' => 0,
                'feedback_not_received' => 0
            ],
            'subjects' => [],
            'repeats' => [
                'total' => 0,
                'by_subject' => []
            ]
        ];

        foreach ($statuses as $status) {
            $statistics['total']['by_status'][$status->id] = [
                'count' => 0,
                'repeat_count' => 0,
                'name' => $status->name,
                'color' => $status->color,
                'feedback_received' => 0,
                'feedback_not_received' => 0
            ];
        }

        return $statistics;
    }

    /**
     * Обрабатывает результаты запроса для детальной статистики.
     *
     * @param array $results Результаты запроса
     * @param array &$statistics Ссылка на массив статистики
     * @param array $statuses Массив статусов тикетов
     * @return void
     */
    private function processDetailedStatisticsResults(array $results, array &$statistics, array $statuses): void
    {
        foreach ($results as $row) {
            if (!isset($row->status_id) || !isset($statistics['total']['by_status'][$row->status_id])) {
                continue;
            }

            $statistics['total']['count'] += $row->total;
            $statistics['total']['feedback_received'] += $row->feedback_received_count;
            $statistics['total']['feedback_not_received'] += $row->feedback_not_received_count;

            $statistics['total']['by_status'][$row->status_id]['count'] += $row->total;
            $statistics['total']['by_status'][$row->status_id]['repeat_count'] += $row->repeat_count;
            $statistics['total']['by_status'][$row->status_id]['feedback_received'] += $row->feedback_received_count;
            $statistics['total']['by_status'][$row->status_id]['feedback_not_received'] += $row->feedback_not_received_count;

            if (!isset($statistics['subjects'][$row->subject_id])) {
                $statistics['subjects'][$row->subject_id] = $this->initializeSubjectStatistics($row, $statuses);
            }

            $this->updateSubjectStatistics($statistics['subjects'][$row->subject_id], $row, $statuses);

            $statistics['repeats']['total'] += $row->repeat_count;

            if ($row->repeat_count > 0) {
                $this->updateRepeatStatistics($statistics['repeats'], $row);
            }
        }
    }

    /**
     * Инициализирует структуру статистики для темы.
     *
     * @param object $row Данные строки результата
     * @param array $statuses Массив статусов тикетов
     * @return array Инициализированная структура данных для темы
     */
    private function initializeSubjectStatistics(object $row, array $statuses): array
    {
        $subjectStats = [
            'name' => $row->subject_name,
            'total' => 0,
            'repeat_count' => 0,
            'feedback_received' => 0,
            'feedback_not_received' => 0,
            'by_status' => []
        ];

        foreach ($statuses as $status) {
            $subjectStats['by_status'][$status->id] = [
                'count' => 0,
                'repeat_count' => 0,
                'name' => $status->name,
                'color' => $status->color,
                'feedback_received' => 0,
                'feedback_not_received' => 0
            ];
        }

        return $subjectStats;
    }

    /**
     * Обновляет статистику для темы.
     *
     * @param array &$subjectStats Ссылка на статистику темы
     * @param object $row Данные строки результата
     * @param array $statuses Массив статусов тикетов для проверки
     * @return void
     */
    private function updateSubjectStatistics(array &$subjectStats, object $row, array $statuses): void
    {
        if (!isset($row->status_id) || !isset($subjectStats['by_status'][$row->status_id])) {
            return;
        }

        $subjectStats['total'] += $row->total;
        $subjectStats['repeat_count'] += $row->repeat_count;
        $subjectStats['feedback_received'] += $row->feedback_received_count;
        $subjectStats['feedback_not_received'] += $row->feedback_not_received_count;

        $subjectStats['by_status'][$row->status_id]['count'] += $row->total;
        $subjectStats['by_status'][$row->status_id]['repeat_count'] += $row->repeat_count;
        $subjectStats['by_status'][$row->status_id]['feedback_received'] += $row->feedback_received_count;
        $subjectStats['by_status'][$row->status_id]['feedback_not_received'] += $row->feedback_not_received_count;
    }

    /**
     * Обновляет статистику по повторным обращениям.
     *
     * @param array &$repeatStats Ссылка на статистику повторных обращений
     * @param object $row Данные строки результата
     * @return void
     */
    private function updateRepeatStatistics(array &$repeatStats, object $row): void
    {
        if (!isset($row->subject_id) || !isset($row->subject_name)) {
            return;
        }

        if (!isset($repeatStats['by_subject'][$row->subject_id])) {
            $repeatStats['by_subject'][$row->subject_id] = [
                'name' => $row->subject_name,
                'count' => 0
            ];
        }
        $repeatStats['by_subject'][$row->subject_id]['count'] += $row->repeat_count;
    }
}