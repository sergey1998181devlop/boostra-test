<?php

namespace App\Modules\TicketAssignment\Repositories;

use App\Core\Database\SimplaDatabase;
use App\Modules\TicketAssignment\Contracts\SettingsInterface;

class ManagerRepository
{
    /** @var \Simpla */
    private $db;

    /** @var SettingsInterface */
    private $settings;

    /** @var int Таймаут для определения онлайн-статуса менеджера (в минутах) */
    private const ONLINE_TIMEOUT_MINUTES = 10;

    /** @var int Количество дней для поиска активных тикетов */
    private const TICKET_SEARCH_DAYS = 2;

    public function __construct(SettingsInterface $settings)
    {
        $this->db = SimplaDatabase::getInstance()->db();
        $this->settings = $settings;
    }

    /**
     * Найти менеджера с минимальной нагрузкой из списка доступных
     *
     * @param array $managerIds Список ID доступных менеджеров
     * @return object|null Данные менеджера или null
     */
    public function findLeastLoadedManager(array $managerIds): ?object
    {
        if (empty($managerIds)) {
            return null;
        }

        $query = $this->db->placehold("
            SELECT m.id, 
                   COALESCE(total_load, 0) as total_load, 
                   MAX(mv.last_visit) as last_visit
            FROM __managers m
            LEFT JOIN __manager_visits mv ON mv.manager_id = m.id
            LEFT JOIN (
                SELECT ta.manager_id, SUM(ta.coefficient) as total_load
                FROM __ticket_assignments ta
                JOIN __mytickets t ON t.id = ta.ticket_id
                WHERE t.status_id != 4 
                    AND t.closed_at IS NULL
                    AND t.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY ta.manager_id
            ) load_calc ON load_calc.manager_id = m.id
            WHERE m.id IN (?@)
            GROUP BY m.id, total_load
            HAVING MAX(mv.last_visit) >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ORDER BY total_load ASC, last_visit ASC
            LIMIT 1
        ", self::TICKET_SEARCH_DAYS, $managerIds, self::ONLINE_TIMEOUT_MINUTES);

        $this->db->query($query);
        $result = $this->db->result();
        return $result && is_object($result) ? $result : null;
    }


    /**
     * Назначить тикет на менеджера
     *
     * @param int $ticketId
     * @param int $managerId
     * @return bool
     */
    public function assignTicket(int $ticketId, int $managerId): bool
    {
        $this->db->query(
            "UPDATE __mytickets SET manager_id = ? WHERE id = ?",
            $managerId,
            $ticketId
        );

        return $this->db->affected_rows() > 0;
    }

    /**
     * Получить список менеджеров для автоназначения
     *
     * @return array
     */
    public function getAutoAssignManagers(): array
    {
        $autoAssignIds = $this->settings->get('auto_assign_ticket_managers', []);
        return is_array($autoAssignIds) ? $autoAssignIds : [];
    }

    /**
     * Получить список менеджеров с доступом к определенному типу тикетов
     *
     * @param string $type Тип тикета ('additional_services' или 'collection')
     * @return array
     */
    public function getAuthorizedManagers(string $type): array
    {
        $settingKey = $type === 'additional_services' 
            ? 'authorized_dopy_managers' 
            : 'authorized_collection_managers';

        $authorizedIds = $this->settings->get($settingKey, []);
        return is_array($authorizedIds) ? $authorizedIds : [];
    }

    /**
     * Найти менеджера с минимальной нагрузкой для эскалации
     *
     * @param array $managerIds Список ID доступных менеджеров
     * @return int|null ID менеджера или null
     */
    public function findLeastLoadedEscalationManager(array $managerIds): ?int
    {
        if (empty($managerIds)) {
            return null;
        }

        $query = $this->db->placehold("
            SELECT m.id, 
                   COALESCE(total_load, 0) as total_load
            FROM s_managers m
            LEFT JOIN (
                SELECT ta.manager_id, SUM(ta.coefficient) as total_load
                FROM __ticket_assignments ta
                JOIN __mytickets t ON t.id = ta.ticket_id
                WHERE t.status_id NOT IN (4, 2) 
                    AND t.closed_at IS NULL
                    AND t.created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)
                GROUP BY ta.manager_id
            ) load_calc ON load_calc.manager_id = m.id
            WHERE m.id IN (?@)
            ORDER BY total_load ASC
            LIMIT 1
        ", $managerIds);

        $this->db->query($query);
        $result = $this->db->result();

        return $result ? (int)$result->id : null;
    }
}
