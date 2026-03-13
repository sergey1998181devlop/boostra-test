<?php

namespace App\Modules\TicketAssignment\Repositories;

use App\Core\Database\SimplaDatabase;
use App\Modules\TicketAssignment\Enums\TicketType;

/**
 * Репозиторий для работы с жалобами по менеджерам
 */
class ComplaintsByManagerRepository
{
    /** @var \Simpla */
    private $db;

    public function __construct(\Simpla $db)
    {
        $this->db = $db;
    }

    /**
     * Получить статистику жалоб по менеджерам
     *
     * @param string $dateFrom Дата начала периода (Y-m-d)
     * @param string $dateTo Дата окончания периода (Y-m-d)
     * @param string $type Тип тикета (collection | additional_services)
     * @return array Статистика жалоб
     */
    public function getComplaintsStats(string $dateFrom, string $dateTo, string $type = 'collection'): array
    {
        $parentId = $type === TicketType::ADDITIONAL_SERVICES ? TicketType::ADDITIONAL_SERVICES_PARENT_ID : TicketType::COLLECTION_PARENT_ID;
        $query = $this->db->placehold("
            SELECT
                m.name as manager_name,
                s.name as subject_name,
                COUNT(*) as complaint_count
            FROM s_mytickets t
            JOIN s_mytickets_subjects s ON t.subject_id = s.id
            JOIN s_managers m ON m.id = t.manager_id
            JOIN s_manager_competencies mc ON mc.manager_id = m.id
            WHERE s.parent_id = ?
                AND mc.type = ?
                AND t.created_at BETWEEN ? AND ?
                AND t.manager_id IS NOT NULL
            GROUP BY t.manager_id, t.subject_id
            ORDER BY m.name, s.name
        ", $parentId, $type, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59');

        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Получить всех менеджеров с компетенцией по заданному типу тикетов
     *
     * @param string $type Тип тикета (collection | additional_services)
     * @return array Массив менеджеров
     */
    public function getManagers(string $type = 'collection'): array
    {
        $query = $this->db->placehold("
            SELECT DISTINCT m.id, m.name
            FROM s_manager_competencies mc
            JOIN s_managers m ON m.id = mc.manager_id
            WHERE mc.type = ?
            ORDER BY m.name
        ", $type);

        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Получить темы по parent_id (9 — взыскание, 10 — допы)
     *
     * @param int $parentId
     * @return array
     */
    public function getSubjectsByParent(int $parentId): array
    {
        $query = $this->db->placehold("
            SELECT id, name
            FROM __mytickets_subjects
            WHERE parent_id = ?
                AND is_active = TRUE
            ORDER BY name
        ", $parentId);

        $this->db->query($query);
        return $this->db->results() ?: [];
    }
}