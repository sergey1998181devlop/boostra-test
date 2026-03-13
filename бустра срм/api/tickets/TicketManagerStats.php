<?php

namespace api\tickets;

use api\traits\TicketStatsHelper;
use Carbon\Carbon;
use Tickets;

/**
 * Класс TicketManagerStats
 *
 * Сервисный класс для формирования статистики тикетов по менеджерам
 */
class TicketManagerStats
{
    use TicketStatsHelper;

    private $db;
    private Tickets $tickets;

    /**
     * Статусы тикетов
     */
    const STATUS_UNRESOLVED = 2;
    const STATUS_RESOLVED = 4;

    public function __construct($db, $tickets) {
        $this->db = $db;
        $this->tickets = $tickets;
    }

    /**
     * Получает статистику тикетов по менеджерам за месяц
     *
     * @return array Массив с данными статистики
     */
    public function getMonthlyManagerStats(): array
    {
        $this->db->query("
            SELECT 
                DATE_FORMAT(t.created_at, '%Y-%m') AS month,
                m.id AS manager_id,
                m.name AS manager_name,
                t.status_id,
                COUNT(*) AS count
            FROM __mytickets AS t
            LEFT JOIN __managers AS m ON m.id = t.manager_id
            WHERE t.manager_id IS NOT NULL AND t.status_id IN (2, 4)
            GROUP BY 
                DATE_FORMAT(t.created_at, '%Y-%m'),
                t.manager_id,
                t.status_id
            ORDER BY 
                DATE_FORMAT(t.created_at, '%Y-%m') DESC,
                m.name ASC
        ");
        $results = $this->db->results();

        return $this->processMonthlyManagerStats($results);
    }

    /**
     * Обрабатывает результаты запроса и формирует структуру данных по месяцам и менеджерам
     *
     * @param array $results Результаты запроса
     * @return array Структурированные данные
     */
    private function processMonthlyManagerStats(array $results): array
    {
        $data = [];

        foreach ($results as $row) {
            $month = $row->month;
            $managerId = $row->manager_id;
            $managerName = $row->manager_name;
            $statusId = (int)$row->status_id;
            $count = (int)$row->count;

            // Инициализация структуры для месяца
            if (!isset($data[$month])) {
                $data[$month] = [
                    'managers' => [],
                    'totals' => [
                        'resolved' => 0,
                        'unresolved' => 0,
                        'total' => 0
                    ]
                ];
            }

            // Инициализация структуры для менеджера
            if (!isset($data[$month]['managers'][$managerId])) {
                $data[$month]['managers'][$managerId] = [
                    'name' => $managerName,
                    'resolved' => 0,
                    'unresolved' => 0,
                    'total' => 0
                ];
            }

            // Распределение по статусам
            switch ($statusId) {
                case self::STATUS_RESOLVED:
                    $data[$month]['managers'][$managerId]['resolved'] += $count;
                    $data[$month]['totals']['resolved'] += $count;
                    break;

                case self::STATUS_UNRESOLVED:
                    $data[$month]['managers'][$managerId]['unresolved'] += $count;
                    $data[$month]['totals']['unresolved'] += $count;
                    break;
            }

            // Суммарное количество тикетов
            $data[$month]['managers'][$managerId]['total'] += $count;
            $data[$month]['totals']['total'] += $count;
        }

        return $data;
    }

    /**
     * Получает детальную статистику по дням для указанного месяца и менеджеров
     *
     * @param string $month Месяц в формате 'YYYY-MM'
     * @return array Данные статистики по дням
     * @throws \Exception
     */
    public function getDailyManagerStats(string $month): array
    {
        // Проверка корректности формата месяца
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            throw new \Exception('Неверный формат месяца. Ожидается формат YYYY-MM');
        }

        $query = "
            SELECT 
                DATE(t.created_at) AS day,
                m.id AS manager_id,
                m.name AS manager_name,
                t.status_id,
                COUNT(*) AS count
            FROM __mytickets AS t
            JOIN __managers AS m ON m.id = t.manager_id
            WHERE DATE_FORMAT(t.created_at, '%Y-%m') = ?
                AND t.manager_id IS NOT NULL
            GROUP BY 
                DATE(t.created_at),
                t.manager_id,
                t.status_id
            ORDER BY 
                DATE(t.created_at) ASC,
                m.name ASC
        ";

        $this->db->query($query, $month);
        $results = $this->db->results();

        return $this->processDailyManagerStats($results, $month);
    }

    /**
     * Обрабатывает результаты запроса и формирует структуру данных по дням и менеджерам
     *
     * @param array $results Результаты запроса
     * @param string $month Месяц в формате 'YYYY-MM'
     * @return array Структурированные данные
     */
    private function processDailyManagerStats(array $results, string $month): array
    {
        $managers = [];
        $data = [];

        // Получаем список менеджеров, работавших с тикетами
        foreach ($results as $row) {
            $managers[$row->manager_id] = [
                'id' => $row->manager_id,
                'name' => $row->manager_name
            ];
        }

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        // Инициализация структуры данных для всех дней месяца
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $startOfMonth->copy()->day($day)->toDateString();

            $data[$date] = [
                'managers' => array_map(fn($m) => [
                    'resolved' => 0,
                    'unresolved' => 0,
                    'total' => 0
                ], $managers),
                'totals' => [
                    'resolved' => 0,
                    'unresolved' => 0,
                    'total' => 0
                ]
            ];
        }

        // Заполняем данными из результатов запроса
        foreach ($results as $row) {
            $day = $row->day;
            $managerId = $row->manager_id;
            $count = (int) $row->count;
            $statusId = (int) $row->status_id;

            if (!isset($data[$day]['managers'][$managerId])) {
                continue;
            }

            // Распределение тикетов по статусам
            if ($statusId === self::STATUS_RESOLVED) {
                $data[$day]['managers'][$managerId]['resolved'] += $count;
                $data[$day]['totals']['resolved'] += $count;
            } elseif ($statusId === self::STATUS_UNRESOLVED) {
                $data[$day]['managers'][$managerId]['unresolved'] += $count;
                $data[$day]['totals']['unresolved'] += $count;
            }

            // Общее количество тикетов
            $data[$day]['managers'][$managerId]['total'] += $count;
            $data[$day]['totals']['total'] += $count;
        }
        
        return $data;
    }
    
    /**
     * Получение списка менеджеров, работавших с тикетами
     *
     * @return array|bool Список менеджеров
     */
    public function getManagersList()
    {
        $this->db->query("
            SELECT DISTINCT 
                m.id,
                m.name
            FROM 
                __managers m
            JOIN 
                __mytickets tick ON tick.manager_id = m.id
            WHERE 
                tick.manager_id IS NOT NULL
            ORDER BY 
                m.name ASC
        ");
        
        return $this->db->results();
    }
}