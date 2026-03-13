<?php

namespace App\Modules\TicketAssignment\Services;

use App\Modules\TicketAssignment\Repositories\TicketAssignmentStatsRepository;

/**
 * Сервис для получения статистики автоназначения тикетов
 */
class TicketAssignmentStatsService
{
    /** @var TicketAssignmentStatsRepository */
    private $statsRepository;

    public function __construct()
    {
        $this->statsRepository = new TicketAssignmentStatsRepository();
    }

    /**
     * Получить статистику по распределению тикетов по сегментам просрочки
     */
    public function getDistributionStats($dateFrom = null, $dateTo = null): array
    {
        $dateFrom = $dateFrom ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?: date('Y-m-d');

        $results = $this->statsRepository->getDistributionStats($dateFrom, $dateTo);
        return $this->formatDistributionStats($results);
    }

    /**
     * Получить статистику по менеджерам
     */
    public function getManagerStats($dateFrom = null, $dateTo = null): array
    {
        $dateFrom = $dateFrom ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?: date('Y-m-d');

        $results = $this->statsRepository->getManagerStats($dateFrom, $dateTo);
        return $this->formatManagerStats($results);
    }

    /**
     * Получить статистику эффективности по сегментам
     */
    public function getEfficiencyStats($dateFrom = null, $dateTo = null): array
    {
        $dateFrom = $dateFrom ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?: date('Y-m-d');

        $results = $this->statsRepository->getEfficiencyStats($dateFrom, $dateTo);
        return $this->formatEfficiencyStats($results);
    }

    /**
     * Форматирование статистики распределения с группировкой по месяцам
     */
    private function formatDistributionStats($results): array
    {
        $stats = [
            'additional_services' => [
                'soft' => ['count' => 0, 'avg_coefficient' => 0, 'total_load' => 0, 'monthly' => []],
                'middle' => ['count' => 0, 'avg_coefficient' => 0, 'total_load' => 0, 'monthly' => []],
                'hard' => ['count' => 0, 'avg_coefficient' => 0, 'total_load' => 0, 'monthly' => []]
            ],
            'collection' => [
                'soft' => ['count' => 0, 'avg_coefficient' => 0, 'total_load' => 0, 'monthly' => []],
                'middle' => ['count' => 0, 'avg_coefficient' => 0, 'total_load' => 0, 'monthly' => []],
                'hard' => ['count' => 0, 'avg_coefficient' => 0, 'total_load' => 0, 'monthly' => []]
            ]
        ];

        foreach ($results as $row) {
            $type = $row->type;
            $level = $row->complexity_level;
            $month = $row->month;
            
            if (isset($stats[$type][$level])) {
                // Общие данные
                $stats[$type][$level]['count'] += (int)$row->ticket_count;
                $stats[$type][$level]['total_load'] += (float)$row->total_load;
                
                // Данные по месяцам
                $stats[$type][$level]['monthly'][$month] = [
                    'count' => (int)$row->ticket_count,
                    'avg_coefficient' => round((float)$row->avg_coefficient, 2),
                    'total_load' => round((float)$row->total_load, 2)
                ];
            }
        }

        // Пересчитываем средние коэффициенты
        foreach ($stats as $type => $levels) {
            foreach ($levels as $level => $data) {
                if ($data['count'] > 0) {
                    $stats[$type][$level]['avg_coefficient'] = round($data['total_load'] / $data['count'], 2);
                }
            }
        }

        return $stats;
    }

    /**
     * Форматирование статистики менеджеров
     */
    private function formatManagerStats($results): array
    {
        $managers = [];
        
        foreach ($results as $row) {
            $managerId = $row->manager_id;
            if (!isset($managers[$managerId])) {
                $managers[$managerId] = [
                    'id' => $managerId,
                    'name' => $row->manager_name,
                    'additional_services' => ['soft' => 0, 'middle' => 0, 'hard' => 0, 'total_load' => 0, 'avg_coefficient' => 0],
                    'collection' => ['soft' => 0, 'middle' => 0, 'hard' => 0, 'total_load' => 0, 'avg_coefficient' => 0]
                ];
            }

            $type = $row->type;
            $level = $row->complexity_level;
            
            $managers[$managerId][$type][$level] = (int)$row->ticket_count;
            $managers[$managerId][$type]['total_load'] += (float)$row->total_load;
        }

        // Рассчитываем средние коэффициенты для каждого менеджера
        foreach ($managers as &$manager) {
            foreach (['additional_services', 'collection'] as $type) {
                $totalCount = $manager[$type]['soft'] + $manager[$type]['middle'] + $manager[$type]['hard'];
                if ($totalCount > 0) {
                    $manager[$type]['avg_coefficient'] = round($manager[$type]['total_load'] / $totalCount, 2);
                }
            }
        }

        return array_values($managers);
    }

    /**
     * Форматирование статистики эффективности
     */
    private function formatEfficiencyStats($results): array
    {
        $stats = [
            'additional_services' => [
                'soft' => ['total' => 0, 'resolved' => 0, 'active' => 0, 'resolution_time' => 0],
                'middle' => ['total' => 0, 'resolved' => 0, 'active' => 0, 'resolution_time' => 0],
                'hard' => ['total' => 0, 'resolved' => 0, 'active' => 0, 'resolution_time' => 0]
            ],
            'collection' => [
                'soft' => ['total' => 0, 'resolved' => 0, 'active' => 0, 'resolution_time' => 0],
                'middle' => ['total' => 0, 'resolved' => 0, 'active' => 0, 'resolution_time' => 0],
                'hard' => ['total' => 0, 'resolved' => 0, 'active' => 0, 'resolution_time' => 0]
            ]
        ];

        foreach ($results as $row) {
            $type = $row->type;
            $level = $row->complexity_level;
            
            if (isset($stats[$type][$level])) {
                $stats[$type][$level] = [
                    'total' => (int)$row->total_assigned,
                    'resolved' => (int)$row->resolved,
                    'active' => (int)$row->active,
                    'resolution_time' => round((float)$row->avg_resolution_time, 1)
                ];
            }
        }

        return $stats;
    }
}
