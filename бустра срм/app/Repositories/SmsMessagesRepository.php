<?php

namespace App\Repositories;

use App\Models\SmsMessages;

class SmsMessagesRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    public function get(int $currentPageNum = 1, int $itemsPerPage = 20): array
    {
        $offset = $itemsPerPage * ($currentPageNum - 1);
        $params = [];

        $params[] = $itemsPerPage;
        $params[] = $offset;

        $this->db->query(
            "SELECT s_sms_messages.*, 
                u.id as user_id,
                u.firstname,
                u.lastname,
                u.patronymic FROM s_sms_messages
            LEFT JOIN s_users u ON u.id = s_sms_messages.user_id
            WHERE s_sms_messages.type in ('".SmsMessages::TYPE_TICKET_IN_WORK."','".SmsMessages::TYPE_TICKET_CREATED."')
            ORDER BY s_sms_messages.created DESC
            LIMIT ? OFFSET ?",
            ...$params
        );
        return $this->db->results();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        $this->db->query(
            "SELECT COUNT(*) as count FROM s_sms_messages
             WHERE s_sms_messages.type in ('".SmsMessages::TYPE_TICKET_IN_WORK."','".SmsMessages::TYPE_TICKET_CREATED."')",
        );

        $result = $this->db->result();

        return $result->count ?? 0;
    }

    /**
     * @param array $data
     * @return int
     */
    public function log(array $data): int
    {
        $query = $this->db->placehold("
            INSERT INTO s_sms_messages SET ?%
        ", [
            'phone' => $data['phone'],
            'message' => $data['message'],
            'created' => date('Y-m-d H:i:s'),
            'send_status' => $data['send_status'] ?? '',
            'delivery_status' => $data['delivery_status'] ?? '',
            'send_id' => $data['send_id'] ?? '',
            'type' => 'from_tech',
            'user_id' => $data['user_id'] ?? 0,
            'order_id' => $data['order_id'] ?? 0,
            'validated' => 1,
            'is_last_sms' => null,
            'code' => null,
        ]);

        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * @param int $userId
     * @param string $type
     * @param string $timeFrom
     * @param string $timeTo
     * @return object|null
     */
    public function findByUserTypeAndTime(
        int $userId,
        string $type,
        string $timeFrom,
        string $timeTo
    ): ?object {
        if ($userId === 0) {
            return null;
        }

        $query = $this->db->placehold("
            SELECT sm.id, sm.message, sm.created, sm.send_status
            FROM s_sms_messages sm
            WHERE sm.user_id = ?
              AND sm.type = ?
              AND sm.created BETWEEN ? AND ?
              AND sm.send_status = 'success'
            ORDER BY sm.created DESC
            LIMIT 1
        ", $userId, $type, $timeFrom, $timeTo);

        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * @param int $userId
     * @param string $type
     * @param string $status
     * @param string $timeFrom
     * @param string $timeTo
     * @return int
     */
    public function countByUserTypeStatusAndTime(
        int $userId,
        string $type,
        string $status,
        string $timeFrom,
        string $timeTo
    ): int {
        if ($userId === 0) {
            return 0;
        }

        $query = $this->db->placehold("
            SELECT COUNT(*) as count
            FROM s_sms_messages sm
            WHERE sm.user_id = ?
              AND sm.type = ?
              AND sm.send_status = ?
              AND sm.created BETWEEN ? AND ?
        ", $userId, $type, $status, $timeFrom, $timeTo);

        $this->db->query($query);
        $result = $this->db->result();
        return $result ? (int)$result->count : 0;
    }

    /**
     * Выборка SMS по списку пользователей и диапазону времени.
     *
     * @param array $userIds
     * @param string $type
     * @param string $timeFrom
     * @param string $timeTo
     * @return array
     */
    public function findByUsersTypeAndTime(
        array $userIds,
        string $type,
        string $timeFrom,
        string $timeTo
    ): array {
        if (empty($userIds)) {
            return [];
        }

        $query = $this->db->placehold("
            SELECT sm.user_id, sm.message, sm.created, sm.send_status
            FROM s_sms_messages sm
            WHERE sm.user_id IN (?@)
              AND sm.type = ?
              AND sm.created BETWEEN ? AND ?
        ", $userIds, $type, $timeFrom, $timeTo);

        $this->db->query($query);
        $results = $this->db->results();
        return $results ?: [];
    }
}