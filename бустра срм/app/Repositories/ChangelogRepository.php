<?php

namespace App\Repositories;

use Database;

class ChangelogRepository
{
    /** @var Database */
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function addLog(
        int $managerId,
        string $type,
        string $oldValues,
        string $newValues,
        ?int $orderId = null,
        ?int $userId = null,
        ?int $fileId = null
    ): void {
        $data = [
            'manager_id' => $managerId,
            'created'    => date('Y-m-d H:i:s'),
            'type'       => $type,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'order_id'   => $orderId,
            'user_id'    => $userId,
            'file_id'    => $fileId,
        ];
        $query = $this->db->placehold("INSERT INTO __changelogs SET ?%", $data);
        $this->db->query($query);
    }

    public function addIncomingBlacklistToggle(
        int $managerId,
        int $blacklistId,
        bool $oldStatus,
        bool $newStatus,
        ?string $phone = null,
        ?int $userId = null
    ): void {
        $type = 'incoming_blacklist.toggle';

        $oldValues = json_encode([
            'blacklist_id' => $blacklistId,
            'is_active' => (int)$oldStatus,
            'phone' => $phone,
        ], JSON_UNESCAPED_UNICODE);

        $newValues = json_encode([
            'blacklist_id' => $blacklistId,
            'is_active' => (int)$newStatus,
            'phone' => $phone,
        ], JSON_UNESCAPED_UNICODE);

        $this->addLog($managerId, $type, $oldValues, $newValues, null, $userId);
    }

    /**
     * @param int $userId
     * @param array $types
     * @param string $timeFrom
     * @param string $timeTo
     * @param int|null $managerId
     * @return array
     */
    public function findByUserTypeAndTime(
        int $userId,
        array $types,
        string $timeFrom,
        string $timeTo,
        ?int $managerId = null
    ): array {
        $whereParts = [
            $this->db->placehold("user_id = ?", $userId),
            $this->db->placehold("type IN (?@)", $types),
            $this->db->placehold("created BETWEEN ? AND ?", $timeFrom, $timeTo)
        ];

        if ($managerId !== null) {
            $whereParts[] = $this->db->placehold("manager_id = ?", $managerId);
        }

        $query = "
            SELECT id, type, old_values, new_values, created
            FROM s_changelogs
            WHERE " . implode(' AND ', $whereParts) . "
            ORDER BY created DESC
        ";

        $this->db->query($query);
        $results = $this->db->results();
        return $results ?: [];
    }

    /**
     * Выборка изменений по списку пользователей и типам.
     *
     * @param array $userIds
     * @param array $types
     * @param string $timeFrom
     * @param string $timeTo
     * @param int|null $managerId
     * @return array
     */
    public function findByUsersTypesAndTime(
        array $userIds,
        array $types,
        string $timeFrom,
        string $timeTo,
        ?int $managerId = null
    ): array {
        if (empty($userIds) || empty($types)) {
            return [];
        }

        $whereParts = [
            $this->db->placehold("user_id IN (?@)", $userIds),
            $this->db->placehold("type IN (?@)", $types),
            $this->db->placehold("created BETWEEN ? AND ?", $timeFrom, $timeTo)
        ];

        if ($managerId !== null) {
            $whereParts[] = $this->db->placehold("manager_id = ?", $managerId);
        }

        $query = "
            SELECT id, user_id, type, old_values, new_values, created
            FROM s_changelogs
            WHERE " . implode(' AND ', $whereParts) . "
            ORDER BY created DESC
        ";

        $this->db->query($query);
        $results = $this->db->results();
        return $results ?: [];
    }
}


