<?php

namespace App\Repositories;

class IncomingCallBlacklistRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $phone
     * @return object|null
     */
    public function findByPhone(string $phone): ?object
    {
        $this->db->query("
            SELECT id, phone_number, reason, is_active
            FROM s_incoming_calls_blacklist 
            WHERE phone_number = ?",
            $phone
        );

        return $this->db->result();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function updateLastCallDate(int $id): bool
    {
        $this->db->query("
            UPDATE s_incoming_calls_blacklist 
            SET last_call_date = NOW() 
            WHERE id = ?",
            $id
        );

        return $this->db->affected_rows() > 0;
    }

    /**
     * @param string|null $search
     * @param int $currentPageNum
     * @param int $itemsPerPage
     * @return array
     */
    public function getBlacklist(?string $search = null, int $currentPageNum = 1, int $itemsPerPage = 20): array
    {
        $offset = $itemsPerPage * ($currentPageNum - 1);

        $whereClause = '';
        $params = [];

        if ($search !== null) {
            $search = $this->validatePhoneSearch($search);
            
            if ($search !== null) {
                $whereClause = 'WHERE b.phone_number LIKE ?';
                $params[] = '%' . $search . '%';
            }
        }

        $params[] = $itemsPerPage;
        $params[] = $offset;

        $this->db->query("
            SELECT 
                b.id,
                b.phone_number,
                b.reason,
                b.created_at,
                b.last_call_date,
                b.created_by,
                b.is_active,
                u.id as user_id,
                u.firstname,
                u.lastname,
                u.patronymic
            FROM s_incoming_calls_blacklist b
            LEFT JOIN s_users u ON u.phone_mobile = b.phone_number
            {$whereClause}
            ORDER BY b.created_at DESC
            LIMIT ? OFFSET ?",
            ...$params
        );

        return $this->db->results();
    }

    /**
     * @param string|null $search
     * @return int
     */
    public function count(?string $search = null): int
    {
        $whereClause = '';
        $params = [];

        if ($search !== null) {
            $search = $this->validatePhoneSearch($search);
            
            if ($search !== null) {
                $whereClause = 'WHERE phone_number LIKE ?';
                $params[] = '%' . $search . '%';
            }
        }

        $this->db->query("
            SELECT COUNT(*) as count
            FROM s_incoming_calls_blacklist
            {$whereClause}",
            ...$params
        );

        $result = $this->db->result();
        return $result->count ?? 0;
    }

    /**
     * @param string $phone
     * @param string $reason
     * @param int $createdBy
     * @return int|null
     */
    public function create(string $phone, string $reason, int $createdBy): ?int
    {
        $this->db->query("
            INSERT INTO s_incoming_calls_blacklist 
            (phone_number, reason, created_by, created_at, is_active, expires_at) 
            VALUES (?, ?, ?, NOW(), 1, DATE_ADD(NOW(), INTERVAL 24 HOUR))",
            $phone, $reason, $createdBy
        );

        return $this->db->insert_id();
    }

    /**
     * @param int $id
     * @param bool $isActive
     * @return bool
     */
    public function updateStatus(int $id, bool $isActive): bool
    {
        if ($isActive) {
            $this->db->query("
                UPDATE s_incoming_calls_blacklist 
                SET is_active = 1,
                    expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
                WHERE id = ?",
                $id
            );
        } else {
            $this->db->query("
                UPDATE s_incoming_calls_blacklist 
                SET is_active = 0,
                    expires_at = NULL
                WHERE id = ?",
                $id
            );
        }

        return $this->db->affected_rows() > 0;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->db->query("
            DELETE FROM s_incoming_calls_blacklist 
            WHERE id = ?",
            $id
        );

        return $this->db->affected_rows() > 0;
    }

    public function findById(int $id): ?object
    {
        $this->db->query("
            SELECT id, phone_number, is_active
            FROM s_incoming_calls_blacklist
            WHERE id = ?
            LIMIT 1
        ", $id);

        return $this->db->result() ?: null;
    }

    /**
     * @return array
     */
    public function getExpiredRecords(): array
    {
        $this->db->query("
            SELECT id, phone_number, created_at, expires_at
            FROM s_incoming_calls_blacklist 
            WHERE is_active = 1 
            AND expires_at IS NOT NULL
            AND expires_at <= NOW()
        ");

        return $this->db->results();
    }

    private function validatePhoneSearch(string $search): ?string
    {
        $search = trim($search);

        if (empty($search)) {
            return null;
        }

        $formatted = formatPhoneNumber($search);

        if ($formatted === false) {
            throw new \InvalidArgumentException('Некорректный формат номера телефона');
        }

        return $formatted;
    }
}