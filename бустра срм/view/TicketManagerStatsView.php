<?php

use api\tickets\TicketManagerStats;

require_once 'View.php';

/**
 * Контроллер для страницы "Статистика менеджеров"
 */
class TicketManagerStatsView extends View
{
    private TicketManagerStats $ticketManagerStats;

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        parent::__construct();
        $this->ticketManagerStats = new TicketManagerStats($this->db, $this->tickets);
    }

    /**
     * Обработка запросов
     *
     * @return string Результат выполнения запроса
     */
    public function fetch(): ?string
    {
        $action = $this->request->method('post')
            ? $this->request->post('action', 'string')
            : $this->request->get('action', 'string');

        switch ($action) {
            case 'get_monthly_data':
                return $this->getManagerDailyStats();

            default:
                return $this->showStatistics();
        }
    }

    /**
     * Отображение страницы статистики
     *
     * @return string HTML-код страницы
     */
    private function showStatistics(): string
    {
        // Получаем параметры фильтрации
        //$filter = $this->getFilterParams();
        
        // Получаем статистику
        $statistics = $this->ticketManagerStats->getMonthlyManagerStats();
        $managers = $this->ticketManagerStats->getManagersList();

        // Передаем данные в шаблон
        $this->design->assign_array([
            'statistics' => $statistics,
            'managersWithTickets' => $managers,
            //'filter' => $filter,
            'statuses' => $this->tickets->getStatuses(),
        ]);

        return $this->design->fetch('contact_center/manager_statistics.tpl');
    }

    /**
     * Получение статистики менеджеров по дням для месяца
     * @return void
     */
    private function getManagerDailyStats(): void
    {
        $month = $this->request->get('month', 'string');

        try {
            $dailyStats = $this->ticketManagerStats->getDailyManagerStats($month);

            $this->json_output([
                'success' => true,
                'data' => $dailyStats
            ]);
        } catch (Exception $e) {
            $this->json_output([
                'success' => false,
                'message' => 'Ошибка при получении статистики: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Получение параметров фильтрации из запроса
     *
     * @return array Параметры фильтрации
     */
    private function getFilterParams(): array
    {
        $filter = [];

        // Диапазон дат
        $dateFrom = $this->request->get('date_from', 'string');
        $dateTo = $this->request->get('date_to', 'string');

        if (!empty($dateFrom)) {
            $filter['date_from'] = date('Y-m-d', strtotime($dateFrom));
        }

        if (!empty($dateTo)) {
            $filter['date_to'] = date('Y-m-d', strtotime($dateTo));
        }

        // Идентификаторы менеджеров
        $managerIds = $this->request->get('manager_ids', 'array');
        if (!empty($managerIds)) {
            $filter['manager_ids'] = $managerIds;
        }

        return $filter;
    }
}