<?php

namespace App\Modules\TicketAssignment\Repositories;

use App\Modules\TicketAssignment\Enums\TicketType;

/**
 * Репозиторий для работы с жалобами по ответственным лицам
 */
class ComplaintsByResponsibleRepository
{
    /** @var \Simpla */
    private $db;

    public function __construct(\Simpla $db)
    {
        $this->db = $db;
    }

    /**
     * Получить статистику жалоб по ответственным лицам
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
                rp.code as responsible_name,
                s.name as subject_name,
                COUNT(*) as complaint_count
            FROM s_mytickets t
            JOIN s_mytickets_subjects s ON t.subject_id = s.id
            JOIN s_responsible_persons rp ON rp.id = t.responsible_person_id
            WHERE s.parent_id = ?
                AND t.created_at BETWEEN ? AND ?
                AND t.responsible_person_id IS NOT NULL
            GROUP BY t.responsible_person_id, t.subject_id
            ORDER BY rp.code, s.name
        ", $parentId, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59');

        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Получить всех ответственных лиц
     *
     * @return array Массив ответственных лиц
     */
    public function getResponsiblePersons(): array
    {
        $query = $this->db->placehold("
            SELECT DISTINCT rp.id, rp.code as name
            FROM s_responsible_persons rp
            WHERE rp.is_sync_available = 1
            ORDER BY rp.code
        ");

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
