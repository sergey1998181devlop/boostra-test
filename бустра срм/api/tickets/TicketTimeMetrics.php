<?php

namespace api\tickets;

use DateTime;
use Tickets;

/**
 * Класс TicketTimeMetrics
 *
 * Сервисный класс для формирования аналитики по времени обработки тикетов,
 * включая время первоначальной реакции и общее время решения задачи
 * по различным категориям тикетов
 */
class TicketTimeMetrics
{
    private const STATUS_IN_PROGRESS = 2;
    private const STATUS_CLOSED = 4;

    // Минимальная дата для анализа тикетов
    private const MIN_TICKET_DATE = '2024-12-23';
    private const WORK_DAY_START = 7;
    private const WORK_DAY_END = 20;
    private object $db;
    private Tickets $tickets;

    /**
     * Конструктор класса
     *
     * @param object $db Объект доступа к базе данных
     * @param Tickets $tickets Объект для работы с тикетами
     */
    public function __construct(object $db, Tickets $tickets) {
        $this->db = $db;
        $this->tickets = $tickets;
    }

    /**
     * Получает полную статистику по времени обработки тикетов
     *
     * @param int|null $priorityId Фильтр по приоритету
     * @return array Комплексная статистика по времени обработки
     */
    public function getStatistics(?int $priorityId = null): array
    {
        return [
            'reaction_times' => $this->calculateMonthlyReactionTimes($priorityId),
            'processing_times' => $this->calculateTicketProcessingTimesByCategory($priorityId),
            'resolution_times' => $this->calculateMonthlyResolutionTimes($priorityId),
            'first_response_times' => $this->calculateMonthlyFirstResponseTimes($priorityId),
        ];
    }

    /**
     * Рассчитывает среднее время реакции на тикеты по месяцам
     *
     * @param int|null $priorityId Фильтр по приоритету
     * @return array Данные о времени реакции по месяцам
     */
    public function calculateMonthlyReactionTimes(?int $priorityId = null): array
    {
        $reactionTimeData = $this->initializeTimeDataStructures();
        $tickets = $this->fetchTicketsForReactionTime($priorityId);
        $this->aggregateTimeData($tickets, $reactionTimeData);
        $this->formatAverages($reactionTimeData);

        asort($reactionTimeData['types']);
        return $reactionTimeData;
    }

    /**
     * Формирует SQL-условие фильтрации по приоритету
     *
     * @param int|null $priorityId Фильтр по приоритету
     * @param string $alias Алиас таблицы тикетов в SQL
     * @return string SQL-условие или пустая строка
     */
    private function buildPriorityCondition(?int $priorityId, string $alias = 'ticket'): string
    {
        if ($priorityId === null) {
            return '';
        }
        return 'AND ' . $alias . '.priority_id = ' . (int)$priorityId;
    }

    /**
     * Инициализирует структуру данных для времени реакции
     *
     * @return array
     */
    private function initializeTimeDataStructures(): array
    {
        return [
            'monthly' => [],
            'daily' => [],
            'types' => []
        ];
    }

    /**
     * Получает данные о тикетах для расчета времени реакции
     *
     * @param int|null $priorityId Фильтр по приоритету
     * @return array
     */
    private function fetchTicketsForReactionTime(?int $priorityId = null): array
    {
        $priorityCondition = $this->buildPriorityCondition($priorityId);

        $query = $this->db->placehold("
            SELECT
                DATE_FORMAT(ticket.created_at, '%Y-%m-%d') as day,
                DATE_FORMAT(ticket.created_at, '%Y-%m') as month,
                COALESCE(parent_subject.id, 0) as parent_subject_id,
                COALESCE(parent_subject.name, 'Без категории') as parent_subject_name,
                ticket.created_at,
                ticket.accepted_at,
                COUNT(*) as count
            FROM __mytickets AS ticket
            LEFT JOIN __mytickets_subjects s ON ticket.subject_id = s.id
            LEFT JOIN __mytickets_subjects parent_subject
                ON (s.parent_id = parent_subject.id OR (s.parent_id = 0 AND s.id = parent_subject.id))
            WHERE
                ticket.accepted_at IS NOT NULL
                AND ticket.created_at >= ?
                {$priorityCondition}
            GROUP BY
                day,
                month,
                parent_subject_id,
                parent_subject_name,
                ticket.created_at,
                ticket.accepted_at
            ORDER BY
                day DESC,
                parent_subject_name ASC
    ", self::MIN_TICKET_DATE);

        $this->db->query($query);
        $results = $this->db->results();

        return $results ?: [];
    }

    /**
     * Агрегирует данные о времени между created_at и указанным полем конца
     *
     * @param array $tickets Данные тикетов из БД
     * @param array &$reactionTimeData Структура для агрегации данных
     * @param string $endDateField Поле объекта тикета с датой окончания
     */
    private function aggregateTimeData(array $tickets, array &$reactionTimeData, string $endDateField = 'accepted_at'): void
    {
        foreach ($tickets as $row) {
            $day = $row->day;
            $month = $row->month;
            $parentSubjectId = (int)$row->parent_subject_id;
            $parentSubjectName = $row->parent_subject_name;
            $count = (int)$row->count;

            $totalSeconds = $this->calculateBusinessHours($row->created_at, $row->$endDateField);

            // Заполняем справочник тем
            if (!isset($reactionTimeData['types'][$parentSubjectId])) {
                $reactionTimeData['types'][$parentSubjectId] = $parentSubjectName;
            }

            // Инициализируем структуры если нужно
            $this->ensureDataStructures($reactionTimeData, $day, $month, $parentSubjectId);

            // Агрегируем данные для дня
            $this->aggregateDailyData($reactionTimeData, $day, $parentSubjectId, $count, $totalSeconds);

            // Агрегируем данные для месяца
            $this->aggregateMonthlyData($reactionTimeData, $month, $parentSubjectId, $count, $totalSeconds);
        }
    }

    /**
     * Проверяет и инициализирует необходимые структуры данных
     */
    private function ensureDataStructures(array &$reactionTimeData, string $day, string $month, int $parentSubjectId): void
    {
        // Инициализируем данные по дню
        if (!isset($reactionTimeData['daily'][$day])) {
            $reactionTimeData['daily'][$day] = [
                'month' => $month,
                'total' => ['count' => 0, 'total_seconds' => 0, 'average' => '-'],
                'types' => []
            ];
        }

        // Инициализируем данные по месяцу
        if (!isset($reactionTimeData['monthly'][$month])) {
            $reactionTimeData['monthly'][$month] = [
                'total' => ['count' => 0, 'total_seconds' => 0, 'average' => '-'],
                'types' => []
            ];
        }

        // Инициализируем данные по типам
        if (!isset($reactionTimeData['daily'][$day]['types'][$parentSubjectId])) {
            $reactionTimeData['daily'][$day]['types'][$parentSubjectId] = [
                'count' => 0,
                'total_seconds' => 0,
                'average' => '-'
            ];
        }
        if (!isset($reactionTimeData['monthly'][$month]['types'][$parentSubjectId])) {
            $reactionTimeData['monthly'][$month]['types'][$parentSubjectId] = [
                'count' => 0,
                'total_seconds' => 0,
                'average' => '-'
            ];
        }
    }

    /**
     * Агрегирует данные для дневной статистики
     */
    private function aggregateDailyData(array &$reactionTimeData, string $day, int $parentSubjectId, int $count, int $totalSeconds): void
    {
        $reactionTimeData['daily'][$day]['types'][$parentSubjectId]['count'] += $count;
        $reactionTimeData['daily'][$day]['types'][$parentSubjectId]['total_seconds'] += $totalSeconds;
        $reactionTimeData['daily'][$day]['total']['count'] += $count;
        $reactionTimeData['daily'][$day]['total']['total_seconds'] += $totalSeconds;
    }

    /**
     * Агрегирует данные для месячной статистики
     */
    private function aggregateMonthlyData(array &$reactionTimeData, string $month, int $parentSubjectId, int $count, int $totalSeconds): void
    {
        $reactionTimeData['monthly'][$month]['types'][$parentSubjectId]['count'] += $count;
        $reactionTimeData['monthly'][$month]['types'][$parentSubjectId]['total_seconds'] += $totalSeconds;
        $reactionTimeData['monthly'][$month]['total']['count'] += $count;
        $reactionTimeData['monthly'][$month]['total']['total_seconds'] += $totalSeconds;
    }

    /**
     * Форматирует средние значения для всех периодов
     */
    private function formatAverages(array &$reactionTimeData): void
    {
        foreach ($reactionTimeData['daily'] as &$dayData) {
            $this->calculateAndFormatAverages($dayData);
        }
        foreach ($reactionTimeData['monthly'] as &$monthData) {
            $this->calculateAndFormatAverages($monthData);
        }
    }

    /**
     * Вычисляет рабочее время между двумя датами
     * Учитывает только время с 7:00 до 20:00 МСК
     *
     * @param string|DateTime $startDate Дата и время начала
     * @param string|DateTime $endDate Дата и время окончания
     * @return int Количество секунд рабочего времени
     */
    private function calculateBusinessHours($startDate, $endDate): int
    {
        $start = $startDate instanceof DateTime ? clone $startDate : new DateTime($startDate);
        $end = $endDate instanceof DateTime ? clone $endDate : new DateTime($endDate);

        if ($end <= $start) {
            return 0;
        }

        $totalSeconds = 0;
        $current = clone $start;

        while ($current < $end) {
            // Устанавливаем границы рабочего времени на текущий день
            $workStart = (clone $current)->setTime(self::WORK_DAY_START, 0, 0);
            $workEnd = (clone $current)->setTime(self::WORK_DAY_END, 0, 0);

            // Вычисляем границы в пределах текущего интервала
            $intervalStart = max($current, $workStart);
            $intervalEnd = min($end, $workEnd);

            if ($intervalStart < $intervalEnd) {
                $totalSeconds += $intervalEnd->getTimestamp() - $intervalStart->getTimestamp();
            }

            // Следующий день
            $current->modify('+1 day')->setTime(0, 0);
        }

        return $totalSeconds;
    }


    /**
     * Вычисляет и форматирует средние значения для общей статистики и по типам
     * Общее среднее вычисляется как среднее арифметическое средних значений по типам
     *
     * @param array &$data Массив данных для обработки
     */
    private function calculateAndFormatAverages(array &$data): void
    {
        // Сначала вычисляем средние значения для каждого типа
        foreach ($data['types'] as $typeId => &$typeData) {
            if ($typeData['count'] > 0) {
                $typeData['average'] = $typeData['total_seconds'] / $typeData['count'];
                $typeData['average'] = $this->formatTimeDetailed($typeData['average']);
            }
        }

        // Для общего среднего используем общее количество секунд и тикетов
        if ($data['total']['count'] > 0) {
            $data['total']['average'] = $this->formatTimeDetailed(
                $data['total']['total_seconds'] / $data['total']['count']
            );
        }
    }

    public function formatTimeDetailed(float $seconds): string
    {
        if ($seconds < 1 && $seconds > 0) {
            return round($seconds, 2) . 'с';
        }
        $seconds = round($seconds);

        $days = floor($seconds / 86400);
        $seconds %= 86400;

        $hours = floor($seconds / 3600);
        $seconds %= 3600;

        $minutes = floor($seconds / 60);
        $seconds %= 60;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . 'д';
        }

        if ($hours > 0) {
            $parts[] = $hours . 'ч';
        } elseif (count($parts) > 0 && $minutes > 0) {
            $parts[] = '0ч';
        }


        if ($minutes > 0) {
            $parts[] = $minutes . 'м';
        } elseif (count($parts) > 0 && $seconds > 0) {
            $parts[] = '0м';
        }

        if ($seconds > 0) {
            $parts[] = $seconds . 'с';
        } elseif (count($parts) > 0 && empty($parts[count($parts)-1] === $seconds . 'с')) {
            $parts[] = '0с';
        }


        if (empty($parts)) {
            return '0с';
        }

        return implode(' ', $parts);
    }

    /**
     * @param int|null $priorityId Фильтр по приоритету
     * @return array|array[]
     */
    public function calculateTicketProcessingTimesByCategory(?int $priorityId = null): array
    {
        $parentSubjects = $this->tickets->getParentSubjects();
        $result = $this->prepareTimeStatisticsStructure($parentSubjects);
        $tickets = $this->fetchTicketsForTimeAnalysis($priorityId);

        foreach ($tickets as $ticket) {
            if (!$this->isValidTicketForProcessing($ticket)) {
                continue;
            }
            $this->aggregateTicketProcessingTime($ticket, $result);
        }

        if (isset($result['daily']) && is_array($result['daily'])) {
            krsort($result['daily']);
        }

        if (isset($result['monthly']) && is_array($result['monthly'])) {
            krsort($result['monthly']);
        }


        $this->enhanceTimeFormattingInResults($result);

        return $result;
    }

    private function isValidTicketForProcessing(object $ticket): bool
    {
        if (!isset($ticket->day) || !is_string($ticket->day) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ticket->day)) {
            return false;
        }
        if (!isset($ticket->month) || !is_string($ticket->month) || !preg_match('/^\d{4}-\d{2}$/', $ticket->month)) {
            return false;
        }
        if (!isset($ticket->parent_id) || !is_numeric($ticket->parent_id)) {
            return false;
        }
        return true;
    }

    private function enhanceTimeFormattingInResults(array &$result): void
    {
        if (isset($result['monthly']) && is_array($result['monthly'])) {
            $this->formatTimeDataInCollection($result['monthly']);
        }

        if (isset($result['daily']) && is_array($result['daily'])) {
            $this->formatTimeDataInCollection($result['daily']);
        }
    }
    
    /**
     * Форматирует временные значения в указанной коллекции данных
     * 
     * @param array &$collection Коллекция данных для обработки
     */
    private function formatTimeDataInCollection(array &$collection): void
    {
        foreach ($collection as &$periodData) {
            if (!is_array($periodData)) continue;

            foreach ($periodData as $key => &$dataItem) {
                if (!is_array($dataItem) || $key === 'month' || $key === 'day') continue; // 'day' - потенциальное поле для daily

                if (isset($dataItem['count']) && $dataItem['count'] > 0 && isset($dataItem['average']) && is_numeric($dataItem['average'])) {
                    $secondsFromMinutes = $dataItem['average'] * 60;
                    $dataItem['average'] = $this->formatTimeDetailed($secondsFromMinutes);
                } elseif (isset($dataItem['average'])) { // Если count = 0 или average не число
                    $dataItem['average'] = '-';
                }
            }
        }
    }

    /**
     * Инициализирует структуру данных для статистики времени обработки
     *
     * @param array $parentSubjects Массив родительских тем
     * @return array Инициализированная структура данных
     */
    private function prepareTimeStatisticsStructure(array $parentSubjects): array
    {
        $result = [
            'types' => [],
            'monthly' => [],
            'daily' => []
        ];

        foreach ($parentSubjects as $subject) {
            $result['types'][$subject->id] = $subject->name;
        }
        asort($result['types']);

        return $result;
    }

    /**
     * Получает данные о тикетах для расчета времени обработки
     *
     * @param int|null $priorityId Фильтр по приоритету
     * @return array Массив данных о тикетах
     */
    private function fetchTicketsForTimeAnalysis(?int $priorityId = null): array
    {
        $priorityCondition = $this->buildPriorityCondition($priorityId, 't');

        $query = $this->db->placehold("
            SELECT
                t.id,
                DATE_FORMAT(t.created_at, '%Y-%m-%d') as day,
                DATE_FORMAT(t.created_at, '%Y-%m') as month,
                COALESCE(parent.id, 0) as parent_id,
                t.working_time,
                t.accepted_at,
                t.closed_at,
                t.status_id
            FROM __mytickets t
            LEFT JOIN __mytickets_subjects s ON t.subject_id = s.id
            LEFT JOIN __mytickets_subjects parent ON (s.parent_id = parent.id OR (s.parent_id = 0 AND s.id = parent.id))
            WHERE
                (t.working_time IS NOT NULL OR (t.accepted_at IS NOT NULL AND t.closed_at IS NOT NULL))
                AND t.status_id IN (?, ?)
                AND t.created_at >= ?
                {$priorityCondition}
            ORDER BY day DESC
        ", self::STATUS_IN_PROGRESS, self::STATUS_CLOSED, self::MIN_TICKET_DATE);

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Обрабатывает данные о тикете для расчета времени обработки
     *
     * @param object $ticket Данные тикета
     * @param array &$result Ссылка на массив результатов
     * @return void
     */
    private function aggregateTicketProcessingTime(object $ticket, array &$result): void
    {
        $day = $ticket->day;
        $month = $ticket->month;
        $parentId = (int)$ticket->parent_id;

        if (!isset($result['types'][$parentId])) {
            $result['types'][$parentId] = 'Тема ID: ' . $parentId;
            asort($result['types']);
        }


        $processingTimeMinutes = $this->calculateTicketProcessingTimeInMinutes($ticket);
        if ($processingTimeMinutes < 0) $processingTimeMinutes = 0;

        $this->ensureTimeStructureExists($result, $month, $day, $parentId);

        // Обновление статистики по месяцам
        $this->updateCategoryProcessingStats($result['monthly'][$month][$parentId], $processingTimeMinutes);
        $this->updateCategoryProcessingStats($result['monthly'][$month]['total'], $processingTimeMinutes);

        // Обновление статистики по дням
        $this->updateCategoryProcessingStats($result['daily'][$day][$parentId], $processingTimeMinutes);
        $this->updateCategoryProcessingStats($result['daily'][$day]['total'], $processingTimeMinutes);
        $result['daily'][$day]['month'] = $month;
    }

    /**
     * Рассчитывает время обработки заявки в минутах.
     *
     * Метод обрабатывает информацию о заявке и определяет общее время обработки.
     * Если рабочее время "working_time" указано, результат вычисляется исходя из этого значения.
     * Если "working_time" отсутствует, метод использует временные метки "accepted_at" и "closed_at".
     * В случае отсутствия необходимых данных возвращается 0.
     *
     * @param object $ticket Объект заявки, содержащий данные о времени обработки.
     *
     * @return float Время обработки заявки в минутах. Если данные о времени некорректные или отсутствуют, возвращает 0.
     */
    private function calculateTicketProcessingTimeInMinutes(object $ticket): float
    {
        if (!empty($ticket->working_time) && is_numeric($ticket->working_time)) { // working_time в секундах
            return (float)$ticket->working_time / 60;
        } elseif (!empty($ticket->accepted_at) && !empty($ticket->closed_at)) {
            try {
                $acceptedAt = new DateTime($ticket->accepted_at);
                $closedAt = new DateTime($ticket->closed_at);
                $diffSeconds = $closedAt->getTimestamp() - $acceptedAt->getTimestamp();
                return $diffSeconds > 0 ? $diffSeconds / 60 : 0;
            } catch (\Exception $e) {
                return 0;
            }
        }
        return 0;
    }

    /**
     * Инициализирует структуры данных для статистики времени, если они еще не созданы
     *
     * @param array &$result Ссылка на массив результатов
     * @param string $month Месяц в формате YYYY-MM
     * @param string $day День в формате YYYY-MM-DD
     * @param int $parentId
     * @return void
     */
    private function ensureTimeStructureExists(array &$result, string $month, string $day, int $parentId): void
    {
        // Для monthly
        if (!isset($result['monthly'][$month])) {
            $result['monthly'][$month]['total'] = $this->initializeEmptyCategoryTimeStats();
        }
        if (!isset($result['monthly'][$month][$parentId])) {
            $result['monthly'][$month][$parentId] = $this->initializeEmptyCategoryTimeStats();
        }

        // Для daily
        if (!isset($result['daily'][$day])) {
            $result['daily'][$day]['total'] = $this->initializeEmptyCategoryTimeStats();
        }
        if (!isset($result['daily'][$day][$parentId])) {
            $result['daily'][$day][$parentId] = $this->initializeEmptyCategoryTimeStats();
        }
    }

    /**
     * Создает пустую структуру данных для статистики времени
     *
     * @return array Пустая структура данных
     */
    private function initializeEmptyCategoryTimeStats(): array
    {
        return ['count' => 0, 'total_time' => 0, 'average' => 0];
    }

    private function updateCategoryProcessingStats(array &$data, float $timeInMinutes): void
    {
        $data['count']++;
        $data['total_time'] += $timeInMinutes;
        $data['average'] = $data['total_time'] / $data['count'];
    }

    /**
     * Рассчитывает среднее время решения (от создания до закрытия) по месяцам
     *
     * @param int|null $priorityId Фильтр по приоритету
     * @return array Данные о времени решения по месяцам
     */
    public function calculateMonthlyResolutionTimes(?int $priorityId = null): array
    {
        $data = $this->initializeTimeDataStructures();
        $tickets = $this->fetchTicketsForResolutionTime($priorityId);
        $this->aggregateTimeData($tickets, $data, 'closed_at');
        $this->formatAverages($data);
        asort($data['types']);

        return $data;
    }

    /**
     * Получает тикеты для расчёта времени решения (created_at → closed_at)
     *
     * @param int|null $priorityId Фильтр по приоритету
     * @return array
     */
    private function fetchTicketsForResolutionTime(?int $priorityId = null): array
    {
        $priorityCondition = $this->buildPriorityCondition($priorityId);

        $query = $this->db->placehold("
            SELECT
                DATE_FORMAT(ticket.created_at, '%Y-%m-%d') as day,
                DATE_FORMAT(ticket.created_at, '%Y-%m') as month,
                COALESCE(parent_subject.id, 0) as parent_subject_id,
                COALESCE(parent_subject.name, 'Без категории') as parent_subject_name,
                ticket.created_at,
                ticket.closed_at,
                COUNT(*) as count
            FROM __mytickets AS ticket
            LEFT JOIN __mytickets_subjects s ON ticket.subject_id = s.id
            LEFT JOIN __mytickets_subjects parent_subject
                ON (s.parent_id = parent_subject.id OR (s.parent_id = 0 AND s.id = parent_subject.id))
            WHERE
                ticket.closed_at IS NOT NULL
                AND ticket.status_id = ?
                AND ticket.created_at >= ?
                {$priorityCondition}
            GROUP BY
                day,
                month,
                parent_subject_id,
                parent_subject_name,
                ticket.created_at,
                ticket.closed_at
            ORDER BY
                day DESC,
                parent_subject_name ASC
        ", self::STATUS_CLOSED, self::MIN_TICKET_DATE);

        $this->db->query($query);
        $results = $this->db->results();

        return $results ?: [];
    }

    /**
     * Рассчитывает среднее время первого ответа менеджера клиенту по месяцам
     *
     * @param int|null $priorityId Фильтр по приоритету
     * @return array Данные о времени первого ответа по месяцам
     */
    public function calculateMonthlyFirstResponseTimes(?int $priorityId = null): array
    {
        $data = $this->initializeTimeDataStructures();
        $tickets = $this->fetchTicketsForFirstResponseTime($priorityId);
        $this->aggregateTimeData($tickets, $data, 'first_response_at');
        $this->formatAverages($data);
        asort($data['types']);

        return $data;
    }

    /**
     * Получает тикеты с временем первого ответа менеджера
     *
     * @param int|null $priorityId Фильтр по приоритету
     * @return array
     */
    private function fetchTicketsForFirstResponseTime(?int $priorityId = null): array
    {
        $priorityCondition = $this->buildPriorityCondition($priorityId);

        $query = $this->db->placehold("
            SELECT
                DATE_FORMAT(ticket.created_at, '%Y-%m-%d') as day,
                DATE_FORMAT(ticket.created_at, '%Y-%m') as month,
                COALESCE(parent_subject.id, 0) as parent_subject_id,
                COALESCE(parent_subject.name, 'Без категории') as parent_subject_name,
                ticket.created_at,
                first_msg.first_response_at,
                COUNT(*) as count
            FROM __mytickets AS ticket
            LEFT JOIN __mytickets_subjects s ON ticket.subject_id = s.id
            LEFT JOIN __mytickets_subjects parent_subject
                ON (s.parent_id = parent_subject.id OR (s.parent_id = 0 AND s.id = parent_subject.id))
            INNER JOIN (
                SELECT ticket_id, MIN(created_at) as first_response_at
                FROM __mytickets_comments
                WHERE manager_id IS NOT NULL
                GROUP BY ticket_id
            ) first_msg ON first_msg.ticket_id = ticket.id
            WHERE
                ticket.created_at >= ?
                {$priorityCondition}
            GROUP BY
                day,
                month,
                parent_subject_id,
                parent_subject_name,
                ticket.created_at,
                first_msg.first_response_at
            ORDER BY
                day DESC,
                parent_subject_name ASC
        ", self::MIN_TICKET_DATE);

        $this->db->query($query);
        $results = $this->db->results();

        return $results ?: [];
    }
}