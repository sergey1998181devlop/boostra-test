<?php

namespace App\Repositories;

use Database;

class UserRepository
{
    /** @var Database */
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getById($id)
    {
        $query = "SELECT id, lastname, firstname, patronymic, phone_mobile, loan_history, blocked FROM s_users WHERE id = ?";
        $this->db->query($query, $id);
        return $this->db->result();
    }

    public function getByPhone($phone)
    {
        $query = "SELECT id, lastname, firstname, patronymic, phone_mobile, loan_history FROM s_users WHERE phone_mobile = ?";
        $this->db->query($query, $phone);
        return $this->db->result();
    }

    public function getByIp($ip)
    {
        $query = "SELECT id, lastname, firstname, patronymic, phone_mobile, loan_history 
              FROM s_users 
              WHERE reg_ip = ? OR last_ip = ?";
        $this->db->query($query, $ip, $ip);
        return $this->db->result();
    }

    public function updateBlocked(int $id, int $blocked): void
    {
        $query = "UPDATE s_users SET blocked = ? WHERE id = ?";
        $this->db->query($query, $blocked, $id);
    }

    public function resetPasswordIncorrectTotal(int $userId): void
    {
        $query = "UPDATE s_password SET incorrect_total = 0 WHERE user_id = ?";
        $this->db->query($query, $userId);
    }

    public function getByOrderId(int $orderId): ?object
    {
        $query = "SELECT u.* FROM s_users u 
                  JOIN s_orders o ON u.id = o.user_id 
                  WHERE o.id = ?";
        $this->db->query($query, $orderId);
        return $this->db->result();
    }

    /**
     * Получает UID пользователя по ID
     *
     * @param int $userId
     * @return string|null
     */
    public function getUidById(int $userId): ?string
    {
        $query = "SELECT UID as uid FROM s_users WHERE id = ? LIMIT 1";
        $this->db->query($query, $userId);
        $result = $this->db->result();
        return $result ? (string)($result->uid ?? '') : null;
    }

    /**
     * site_id пользователя
     *
     * @param int $userId
     * @return string|null
     */
    public function getSiteIdByUserId(int $userId): ?string
    {
        $query = "SELECT site_id FROM s_users WHERE id = ? LIMIT 1";
        $this->db->query($query, $userId);
        $result = $this->db->result();
        return $result && isset($result->site_id) && $result->site_id !== '' ? (string)$result->site_id : null;
    }
}