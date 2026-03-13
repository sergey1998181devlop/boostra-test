<?php

namespace App\Modules\TicketAssignment\Repositories;

use App\Modules\TicketAssignment\Dto\AssignmentDto;

class TicketAssignmentRepository
{
    /** @var \Simpla */
    private $db;

    public function __construct(\Simpla $db)
    {
        $this->db = $db;
    }

    /**
     * Сохранить информацию о назначении тикета
     */
    public function save(AssignmentDto $assignment): void
    {
        $data = $assignment->toArray();
        $data['assigned_at'] = date('Y-m-d H:i:s');

        $query = $this->db->placehold("
            INSERT INTO __ticket_assignments SET ?%
        ", $data);

        $this->db->query($query);
    }

    /**
     * Получить историю назначений тикета
     */
    public function getByTicket(int $ticketId): array
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __ticket_assignments
            WHERE ticket_id = ?
            ORDER BY assigned_at DESC
        ", $ticketId);

        $this->db->query($query);
        $results = $this->db->results();

        if (!$results) {
            return [];
        }

        $assignments = [];
        foreach ($results as $row) {
            $assignments[] = AssignmentDto::fromArray((array)$row);
        }
        return $assignments;
    }

    /**
     * Получить текущую нагрузку менеджера (сумма коэффициентов)
     */
    public function getManagerLoad(int $managerId, string $type): float
    {
        $query = $this->db->placehold("
            SELECT COALESCE(SUM(ta.coefficient), 0) as total_load
            FROM __ticket_assignments ta
            JOIN __mytickets t ON t.id = ta.ticket_id
            WHERE ta.manager_id = ?
                AND ta.type = ?
                AND t.status_id != 4
                AND t.closed_at IS NULL
        ", $managerId, $type);

        $this->db->query($query);
        $result = $this->db->result();

        return (float)($result->total_load ?? 0);
    }

    /**
     * Получить неназначенные тикеты
     * @return array
     */
    public function getUnassignedTickets(): array
    {
        $query = $this->db->placehold("
            SELECT t.*, s.parent_id as subject_parent_id,
                   o.id as order_id, o.user_id,
                   t.priority_id
            FROM __mytickets t
            LEFT JOIN __mytickets_subjects s ON s.id = t.subject_id
            LEFT JOIN __orders o ON o.id = t.order_id
            WHERE t.manager_id IS NULL
                AND (
                    t.subject_id IN (10, 9)
                    OR s.parent_id IN (10, 9)
                )
                AND t.status_id != 4
                AND t.closed_at IS NULL
                AND t.created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)
            ORDER BY 
                JSON_EXTRACT(t.data, '$.escalation_level') DESC,
                t.created_at ASC
        ");

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получить тикеты, нарушившие SLA
     * @return array
     */
    public function getSLAViolatedTickets(): array
    {
        $slaTimeouts = $this->getSLATimeouts();
        
        $query = $this->db->placehold("
            SELECT t.*, s.parent_id as subject_parent_id,
                   o.id as order_id, o.user_id,
                   t.priority_id
            FROM __mytickets t
            LEFT JOIN __mytickets_subjects s ON s.id = t.subject_id
            LEFT JOIN __orders o ON o.id = t.order_id
            WHERE t.manager_id IS NULL
                AND t.status_id = 1 -- Только новые тикеты
                AND (
                    t.subject_id IN (10, 9)
                    OR s.parent_id IN (10, 9)
                )
                AND t.created_at < DATE_SUB(NOW(), INTERVAL ? HOUR) -- Нарушили SLA
                AND (
                    JSON_EXTRACT(t.data, '$.escalation_level') IS NULL 
                    OR JSON_EXTRACT(t.data, '$.escalation_level') < 3
                )
            ORDER BY t.created_at ASC
        ", $slaTimeouts[1]); // Используем таймаут первого уровня

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получить настройки SLA таймаутов
     * @return array
     */
    public function getSLATimeouts(): array
    {
        $defaults = [1 => 4, 2 => 8];

        $this->db->query("SELECT value FROM s_settings WHERE name = ?", 'sla_settings');
        $result = $this->db->result();
        
        if (!$result) {
            return $defaults;
        }
        
        $settings = json_decode($result->value, true) ?: [];
        
        return [
            1 => (int)($settings['timeout_level_1'] ?? $defaults[1]),
            2 => (int)($settings['timeout_level_2'] ?? $defaults[2])
        ];
    }

    /**
     * Логировать изменение тикета
     *
     * @param int $ticketId
     * @param string $fieldName
     * @param string $oldValue
     * @param string $newValue
     * @param int $changedBy
     * @param string $comment
     * @return void
     */
    public function logTicketHistory(
        int $ticketId,
        string $fieldName,
        string $oldValue,
        string $newValue,
        int $changedBy,
        string $comment
    ): void {
        $this->db->query("
            INSERT INTO s_tickets_history 
            SET ticket_id = ?, field_name = ?, old_value = ?, new_value = ?, 
                changed_by = ?, changed_at = NOW(), comment = ?
        ", $ticketId, $fieldName, $oldValue, $newValue, $changedBy, $comment);
    }
}