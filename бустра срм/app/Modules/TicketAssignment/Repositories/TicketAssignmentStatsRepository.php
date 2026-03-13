<?php

namespace App\Modules\TicketAssignment\Repositories;

use App\Core\Database\SimplaDatabase;

class TicketAssignmentStatsRepository
{
    /** @var \Simpla */
    private $db;

    public function __construct()
    {
        $this->db = SimplaDatabase::getInstance()->db();
    }

    /**
     * Получить статистику распределения по сегментам просрочки
     */
    public function getDistributionStats(string $dateFrom, string $dateTo): array
    {
        $query = $this->db->placehold("
            SELECT 
                ta.type,
                ta.complexity_level,
                ta.overdue_days,
                ta.coefficient,
                DATE_FORMAT(ta.assigned_at, '%Y-%m') as month,
                COUNT(*) as ticket_count,
                AVG(ta.coefficient) as avg_coefficient,
                SUM(ta.coefficient) as total_load
            FROM __ticket_assignments ta
            WHERE ta.assigned_at BETWEEN ? AND ?
            GROUP BY ta.type, ta.complexity_level, DATE_FORMAT(ta.assigned_at, '%Y-%m')
            ORDER BY ta.type, ta.complexity_level, month
        ", $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59');

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получить статистику по менеджерам
     */
    public function getManagerStats(string $dateFrom, string $dateTo): array
    {
        $query = $this->db->placehold("
            SELECT 
                ta.manager_id,
                m.name as manager_name,
                ta.type,
                ta.complexity_level,
                COUNT(*) as ticket_count,
                AVG(ta.coefficient) as avg_coefficient,
                SUM(ta.coefficient) as total_load,
                AVG(ta.overdue_days) as avg_overdue_days
            FROM __ticket_assignments ta
            LEFT JOIN __managers m ON m.id = ta.manager_id
            WHERE ta.assigned_at BETWEEN ? AND ?
            GROUP BY ta.manager_id, ta.type, ta.complexity_level
            ORDER BY ta.manager_id, ta.type, ta.complexity_level
        ", $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59');

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получить статистику эффективности по сегментам
     */
    public function getEfficiencyStats(string $dateFrom, string $dateTo): array
    {
        $query = $this->db->placehold("
            SELECT 
                ta.type,
                ta.complexity_level,
                COUNT(*) as total_assigned,
                COUNT(CASE WHEN t.status_id = 4 THEN 1 END) as resolved,
                COUNT(CASE WHEN t.status_id != 4 AND t.closed_at IS NULL THEN 1 END) as active,
                AVG(CASE WHEN t.status_id = 4 THEN TIMESTAMPDIFF(HOUR, ta.assigned_at, t.closed_at) END) as avg_resolution_time
            FROM __ticket_assignments ta
            LEFT JOIN __mytickets t ON t.id = ta.ticket_id
            WHERE ta.assigned_at BETWEEN ? AND ?
            GROUP BY ta.type, ta.complexity_level
            ORDER BY ta.type, ta.complexity_level
        ", $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59');

        $this->db->query($query);
        return $this->db->results();
    }
}
