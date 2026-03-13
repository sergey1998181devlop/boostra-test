<?php

namespace App\Modules\TicketAssignment\Repositories;

use App\Core\Database\SimplaDatabase;
use App\Modules\TicketAssignment\Enums\CompetencyLevel;

class ManagerCompetencyRepository
{
    /** @var \Simpla */
    private $db;

    public function __construct(\Simpla $db)
    {
        $this->db = $db;
    }

    /**
     * Получить компетенцию менеджера
     *
     * @param int $managerId
     * @param string $type
     * @return string|null
     */
    public function get(int $managerId, string $type): ?string
    {
        $query = $this->db->placehold("
            SELECT level
            FROM __manager_competencies
            WHERE manager_id = ?
            AND type = ?
        ", $managerId, $type);

        $this->db->query($query);
        $result = $this->db->result();

        return $result ? $result->level : null;
    }

    /**
     * Получить всех менеджеров с указанным уровнем компетенции
     *
     * @param string $type
     * @param string $level
     * @return array
     */
    public function getByLevel(string $type, string $level): array
    {
        $query = $this->db->placehold("
            SELECT manager_id
            FROM __manager_competencies
            WHERE type = ?
            AND level = ?
        ", $type, $level);

        $this->db->query($query);
        $results = $this->db->results();

        if (!$results) {
            return [];
        }

        return array_map(function($row) {
            return (int)$row->manager_id;
        }, $results);
    }

    /**
     * Установить компетенцию менеджера
     *
     * @param int $managerId
     * @param string $type
     * @param string $level
     * @return bool
     */
    public function set(int $managerId, string $type, string $level): bool
    {
        if (!CompetencyLevel::isValid($level)) {
            return false;
        }

        $query = $this->db->placehold("
            INSERT INTO __manager_competencies (manager_id, type, level, updated_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                level = VALUES(level),
                updated_at = NOW()
        ", $managerId, $type, $level);

        $this->db->query($query);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Удалить компетенцию менеджера
     *
     * @param int $managerId
     * @param string $type
     * @return bool
     */
    public function remove(int $managerId, string $type): bool
    {
        $query = $this->db->placehold("
            DELETE FROM __manager_competencies
            WHERE manager_id = ? AND type = ?
        ", $managerId, $type);

        $this->db->query($query);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Получить все компетенции менеджера
     *
     * @param int $managerId
     * @return array
     */
    public function getAllForManager(int $managerId): array
    {
        $query = $this->db->placehold("
            SELECT type, level
            FROM __manager_competencies
            WHERE manager_id = ?
        ", $managerId);

        $this->db->query($query);
        $results = $this->db->results();

        if (!$results) {
            return [];
        }

        $competencies = [];
        foreach ($results as $row) {
            $competencies[$row->type] = $row->level;
        }

        return $competencies;
    }

    /**
     * Получить менеджеров для SLA эскалации
     *
     * @param string $type Тип тикета ('collection' или 'additional_services')
     * @param int $level Уровень SLA (1 или 2)
     * @return array Массив ID менеджеров
     */
    public function getSLAEscalationManagers(string $type, int $level): array
    {
        $query = $this->db->placehold("
            SELECT manager_id
            FROM __manager_competencies
            WHERE type = ?
            AND sla_level = ?
        ", $type, $level);

        $this->db->query($query);
        $results = $this->db->results();

        if (!$results) {
            return [];
        }

        return array_map(function($row) {
            return (int)$row->manager_id;
        }, $results);
    }

    /**
     * Установить SLA уровень для менеджера
     *
     * @param int $managerId ID менеджера
     * @param string $type Тип тикета
     * @param int $level Уровень SLA (1 или 2)
     * @return bool
     */
    public function setSLAEscalationLevel(int $managerId, string $type, int $level): bool
    {
        if (!in_array($level, [1, 2])) {
            return false;
        }

        $query = $this->db->placehold("
            INSERT INTO __manager_competencies (manager_id, type, level, sla_level, updated_at)
            VALUES (?, ?, 'soft', ?, NOW())
            ON DUPLICATE KEY UPDATE
                sla_level = VALUES(sla_level),
                updated_at = NOW()
        ", $managerId, $type, $level);

        $this->db->query($query);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Удалить SLA уровень для менеджера
     *
     * @param int $managerId ID менеджера
     * @param string $type Тип тикета
     * @return bool
     */
    public function removeSLAEscalationLevel(int $managerId, string $type): bool
    {
        $query = $this->db->placehold("
            UPDATE __manager_competencies
            SET sla_level = NULL, updated_at = NOW()
            WHERE manager_id = ? AND type = ?
        ", $managerId, $type);

        $this->db->query($query);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Получить SLA уровни менеджера
     *
     * @param int $managerId ID менеджера
     * @return array Массив в формате ['тип' => 'уровень_sla']
     */
    public function getManagerSLAEscalationLevels(int $managerId): array
    {
        $query = $this->db->placehold("
            SELECT type, sla_level
            FROM __manager_competencies
            WHERE manager_id = ? AND sla_level IS NOT NULL
        ", $managerId);

        $this->db->query($query);
        $results = $this->db->results();

        if (!$results) {
            return [];
        }

        $slaLevels = [];
        foreach ($results as $row) {
            $slaLevels[$row->type] = (int)$row->sla_level;
        }

        return $slaLevels;
    }

    /**
     * Получить полную информацию о компетенциях менеджера (включая SLA)
     *
     * @param int $managerId ID менеджера
     * @return array Массив с полной информацией
     */
    public function getFullManagerCompetencies(int $managerId): array
    {
        $query = $this->db->placehold("
            SELECT type, level, sla_level
            FROM __manager_competencies
            WHERE manager_id = ?
        ", $managerId);

        $this->db->query($query);
        $results = $this->db->results();

        if (!$results) {
            return [];
        }

        $competencies = [];
        foreach ($results as $row) {
            $competencies[$row->type] = [
                'level' => $row->level,
                'sla_level' => $row->sla_level ? (int)$row->sla_level : null
            ];
        }

        return $competencies;
    }
}