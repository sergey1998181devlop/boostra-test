<?php

namespace api\tickets;

use api\traits\TicketStatsHelper;
use Tickets;

/**
 * Класс TicketDetailStats
 *
 * Сервисный класс, предназначенный для формирования детальной статистики о тикетах
 */
class TicketDetailStats
{
    use TicketStatsHelper;

    private $db;
    private Tickets $tickets;

    public function __construct($db, $tickets) {
        $this->db = $db;
        $this->tickets = $tickets;
    }

    /**
     * Основной метод для получения агрегированной статистики тикетов
     *
     * @param string|null $month Опционально: конкретный месяц для получения статистики
     * @param int|null $managerId Опционально: ID менеджера для фильтрации статистики
     * @return array Комбинированные данные статистики: родительские темы, дочерние темы
     */
    public function getStatistics(?string $month = null, ?int $managerId = null): array
    {
        $parentData = $this->getParentStatistics($month, $managerId);
        $this->calculatePercentages($parentData);

        $childData = $this->getChildStatistics($month, $managerId);

        return [
            'parentData' => $parentData,
            'childData' => $childData,
        ];
    }

    /**
     * Получает статистику по родительским темам тикетов, сгруппированную по месяцам, статусам и каналам.
     *
     * @param string|null $month Опционально: конкретный месяц для получения статистики
     * @param int|null $managerId Опционально: ID менеджера для фильтрации статистики
     * @return array Данные статистики, организованные по месяцам, статусам, темам и каналам
     */
    private function getParentStatistics(?string $month = null, ?int $managerId = null): array
    {
        $dateFormat = '%Y-%m';
        $groupByFields = ['month', 'ticket.status_id', 'COALESCE(parent.id, s.id)', 'ticket.chanel_id'];

        $extraCondition = '';
        if ($month !== null) {
            $extraCondition = "DATE_FORMAT(ticket.created_at, '%Y-%m') = '{$month}'";
        }
        if ($managerId !== null) {
            $extraCondition .= ($extraCondition ? ' AND ' : '') . "ticket.manager_id = {$managerId}";
        }

        $query = $this->buildStatisticsQuery($dateFormat, $groupByFields, $extraCondition);

        $this->db->query($query, $dateFormat);
        $results = $this->db->results();

        // Обработка результатов с использованием общего подхода
        return $this->processStatisticsResults(
            $results,
            'month',
            function(&$data, $row) {
                $month = $row->month;
                $status = $row->status_id;
                $parentId = $row->parent_id;
                $channel = $row->chanel_id;
                $count = $row->count;

                // Добавление данных в общую статистику по каналам
                $data[$month]['total'][$channel] = ($data[$month]['total'][$channel] ?? 0) + $count;

                // Обновление общего количества тикетов за месяц
                $data[$month]['total_tickets'] += $count;

                // Добавление данных по каналам для конкретного статуса и темы
                if (!isset($data[$month][$status][$parentId])) {
                    $data[$month][$status][$parentId] = [];
                }

                $data[$month][$status][$parentId][$channel] =
                    ($data[$month][$status][$parentId][$channel] ?? 0) + $count;

                // Обновление общего количества тикетов для темы и статуса
                $data[$month][$status][$parentId]['total'] =
                    ($data[$month][$status][$parentId]['total'] ?? 0) + $count;
            },
            // Начальная структура для каждого периода
            [
                'total' => [],
                'total_tickets' => 0
            ]
        );
    }

    /**
     * Получает статистику для дочерних тем тикетов, сгруппированную по месяцам и каналам.
     *
     * @param string|null $month Опционально: конкретный месяц для получения статистики
     * @param int|null $managerId Опционально: ID менеджера для фильтрации статистики
     * @return array Данные статистики для дочерних тем
     */
    private function getChildStatistics(?string $month = null, ?int $managerId = null): array
    {
        $dateFormat = '%Y-%m';
        $groupByFields = ['month', 'ticket.chanel_id', 'ticket.subject_id'];

        $extraCondition = "s.parent_id IS NOT NULL";
        if ($month !== null) {
            $extraCondition .= " AND DATE_FORMAT(ticket.created_at, '%Y-%m') = '{$month}'";
        }
        if ($managerId !== null) {
            $extraCondition .= " AND ticket.manager_id = {$managerId}";
        }

        $query = $this->buildStatisticsQuery(
            $dateFormat,
            $groupByFields,
            $extraCondition,
            'ticket.subject_id'
        );

        $this->db->query($query, $dateFormat);
        $results = $this->db->results();

        return $this->processStatisticsResults(
            $results,
            'month',
            function(&$data, $row) {
                $month = $row->month;
                $subject = $row->subject_id;
                $channel = $row->chanel_id;
                $count = $row->count;

                if (!isset($data[$month][$subject])) {
                    $data[$month][$subject] = [];
                }

                $data[$month][$subject][$channel] =
                    ($data[$month][$subject][$channel] ?? 0) + $count;
            }
        );
    }

    /**
     * Рассчитывает процентные соотношения для каждого статуса в родительских темах.
     *
     * @param array &$dataByMonth Ссылка на массив данных для расчета и обновления процентов
     * @return void
     */
    private function calculatePercentages(array &$dataByMonth): void
    {
        $this->calculatePercentagesGeneric($dataByMonth);
    }

    /**
     * Получает детальную статистику по дням для указанного месяца
     *
     * @param string $month Месяц в формате 'YYYY-MM'
     * @param int|null $managerId Опционально: ID менеджера для фильтрации
     * @return array Данные статистики по дням
     * @throws \Exception
     */
    public function getDailyStatsForMonth(string $month, ?int $managerId = null): array
    {
        // Проверка корректности формата месяца
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            throw new \Exception('Неверный формат месяца. Ожидается формат YYYY-MM');
        }

        // Получение данных из БД
        $results = $this->getDailyStatsFromDB($month, $managerId);

        // Получение справочных данных
        $channels = $this->tickets->getChannels();
        $statuses = $this->tickets->getStatuses();
        ['main' => $mainSubjects, 'child' => $childSubjects] = $this->tickets->getMainAndChildSubjects();

        // Базовая обработка результатов запроса
        $dailyData = $this->processDailyStatsResults($results);

        // Заполнение недостающих структур данных
        $this->fillMissingDataStructures($dailyData, $channels, $statuses, $mainSubjects, $childSubjects);

        // Расчет процентных долей
        $this->calculateDailyPercentages($dailyData, $statuses);

        return $dailyData;
    }

    /**
     * Получает данные статистики по дням из базы данных
     *
     * @param string $month Месяц в формате 'YYYY-MM'
     * @param int|null $managerId ID менеджера для фильтрации
     * @return array Результаты запроса
     */
    private function getDailyStatsFromDB(string $month, ?int $managerId = null): array
    {
        $query = "
            SELECT 
                DATE(t.created_at) as date,
                t.status_id,
                s.parent_id,
                t.subject_id,
                t.chanel_id,
                COUNT(*) as count
            FROM __mytickets AS t
            JOIN __mytickets_subjects AS s ON s.id = t.subject_id AND s.is_active = TRUE
            WHERE DATE_FORMAT(t.created_at, '%Y-%m') = ?
        ";

        if ($managerId !== null) {
            $query .= " AND t.manager_id = " . (int)$managerId;
        }

        $query .= " GROUP BY date, t.status_id, s.parent_id, t.subject_id, t.chanel_id ORDER BY date ASC";

        $this->db->query($query, $month);
        return $this->db->results();
    }

    /**
     * Обрабатывает результаты запроса и формирует базовую структуру данных
     *
     * @param array $results Результаты запроса
     * @return array Структурированные данные
     */
    private function processDailyStatsResults(array $results): array
    {
        $dailyData = [];

        foreach ($results as $row) {
            $date = $row->date;
            $statusId = $row->status_id;
            $parentId = $row->parent_id;
            $subjectId = $row->subject_id;
            $channelId = $row->chanel_id;
            $count = $row->count;

            // Инициализация структуры для дня, если не существует
            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [
                    'total' => 0,
                    'channels' => []
                ];
            }

            // Добавление данных для общей статистики
            $dailyData[$date]['channels'][$channelId] = ($dailyData[$date]['channels'][$channelId] ?? 0) + $count;
            $dailyData[$date]['total'] += $count;

            // Обработка дочерних тем
            if ($parentId) {
                $this->addStatsToChildSubject($dailyData[$date], $subjectId, $channelId, $count);
            }

            // Обработка статистики по статусам
            if ($parentId) {
                // Для родительской темы
                $this->addStatsToParentSubject($dailyData[$date], $statusId, $parentId, $channelId, $count);
            } else {
                // Если это родительская тема
                $this->addStatsToParentSubject($dailyData[$date], $statusId, $subjectId, $channelId, $count);
            }
        }

        return $dailyData;
    }

    /**
     * Добавляет статистику для родительской темы
     */
    private function addStatsToParentSubject(array &$dayData, int $statusId, int $subjectId, int $channelId, int $count): void
    {
        if (!isset($dayData[$statusId])) {
            $dayData[$statusId] = [];
        }

        if (!isset($dayData[$statusId][$subjectId])) {
            $dayData[$statusId][$subjectId] = ['channels' => [], 'total' => 0];
        }

        $dayData[$statusId][$subjectId]['channels'][$channelId] =
            ($dayData[$statusId][$subjectId]['channels'][$channelId] ?? 0) + $count;
        $dayData[$statusId][$subjectId]['total'] += $count;
    }

    /**
     * Добавляет статистику для дочерней темы
     */
    private function addStatsToChildSubject(array &$dayData, int $subjectId, int $channelId, int $count): void
    {
        if (!isset($dayData['childSubjects'])) {
            $dayData['childSubjects'] = [];
        }

        if (!isset($dayData['childSubjects'][$subjectId])) {
            $dayData['childSubjects'][$subjectId] = ['channels' => [], 'total' => 0];
        }

        $dayData['childSubjects'][$subjectId]['channels'][$channelId] =
            ($dayData['childSubjects'][$subjectId]['channels'][$channelId] ?? 0) + $count;
        $dayData['childSubjects'][$subjectId]['total'] += $count;
    }

    /**
     * Заполняет недостающие структуры данных
     */
    private function fillMissingDataStructures(array &$dailyData, array $channels, array $statuses, array $mainSubjects, array $childSubjects): void
    {
        foreach ($dailyData as $date => &$dayData) {
            // Добавление всех каналов в общую статистику
            foreach ($channels as $channel) {
                if (!isset($dayData['channels'][$channel->id])) {
                    $dayData['channels'][$channel->id] = 0;
                }
            }

            // Добавление всех статусов и тем
            foreach ($statuses as $status) {
                if (!isset($dayData[$status->id])) {
                    $dayData[$status->id] = [];
                }

                foreach ($mainSubjects as $subjectId => $subjectName) {
                    if (!isset($dayData[$status->id][$subjectId])) {
                        $dayData[$status->id][$subjectId] = ['channels' => [], 'total' => 0];
                    }

                    foreach ($channels as $channel) {
                        if (!isset($dayData[$status->id][$subjectId]['channels'][$channel->id])) {
                            $dayData[$status->id][$subjectId]['channels'][$channel->id] = 0;
                        }
                    }
                }
            }

            // Добавление всех дочерних тем
            if (!isset($dayData['childSubjects'])) {
                $dayData['childSubjects'] = [];
            }

            foreach ($childSubjects as $subjectId => $subjectName) {
                if (!isset($dayData['childSubjects'][$subjectId])) {
                    $dayData['childSubjects'][$subjectId] = ['channels' => [], 'total' => 0];
                }

                foreach ($channels as $channel) {
                    if (!isset($dayData['childSubjects'][$subjectId]['channels'][$channel->id])) {
                        $dayData['childSubjects'][$subjectId]['channels'][$channel->id] = 0;
                    }
                }
            }
        }
    }

    /**
     * Рассчитывает процентные доли для дневной статистики
     */
    private function calculateDailyPercentages(array &$dailyData, array $statuses): void
    {
        foreach ($dailyData as &$dayData) {
            if ($dayData['total'] > 0) {
                foreach ($statuses as $status) {
                    if (isset($dayData[$status->id])) {
                        foreach ($dayData[$status->id] as &$subjectData) {
                            $subjectData['percentage'] = round(($subjectData['total'] / $dayData['total']) * 100, 1);
                        }
                    }
                }
            }
        }
    }
}