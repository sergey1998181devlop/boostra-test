<?php

namespace App\Repositories;

use Database;

class OrdersRepository
{
    /** @var Database */
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function hasActiveIssuedContracts(int $userId): bool
    {
        $sql = "SELECT COUNT(*) AS cnt FROM s_orders WHERE user_id = ? AND `1c_status` = '5.Выдан' LIMIT 1";
        $this->db->query($sql, $userId);
        $cnt = $this->db->result('cnt');
        return (int)$cnt > 0;
    }

    public function resetOrdersAcceptTry(int $userId): void
    {
        $sql = "UPDATE s_orders SET accept_try = 0 WHERE user_id = ?";
        $this->db->query($sql, $userId);
    }
    
    public function getOrderOneCStatusInfo(int $orderId): ?object
    {
        $sql = "SELECT `1c_id` AS id_1c, `1c_status` AS status_1c FROM s_orders WHERE id = ?";
        $this->db->query($sql, $orderId);
        return $this->db->result();
    }
    
    public function updateOrderStatus(int $orderId, array $data): void
    {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = ?";
            $values[] = $value;
        }
        
        $values[] = $orderId;
        
        $sql = "UPDATE s_orders SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($sql, ...$values);
    }
    
    public function getOrdersForSync(array $statuses, ?string $dateFrom = null): array
    {
        $statusPlaceholders = implode(',', array_fill(0, count($statuses), '?'));
        $params = $statuses;
        
        $dateFilter = '';
        if ($dateFrom) {
            $dateFilter = "AND date >= ?";
            $params[] = $dateFrom;
        }
        
        $sql = "
            SELECT id, user_id, `1c_id` as id_1c, `1c_status` as status_1c 
            FROM s_orders 
            WHERE `1c_status` IN ({$statusPlaceholders}) 
            AND `1c_id` IS NOT NULL 
            AND `1c_id` != ''
            {$dateFilter}
            ORDER BY id DESC
        ";
        
        $this->db->query($sql, ...$params);
        return $this->db->results();
    }

    /**
     * Получает user_id по order_id
     *
     * @param int $orderId
     * @return int|null
     */
    public function findByIdWithUserId(int $orderId): ?int
    {
        $sql = "SELECT user_id FROM s_orders WHERE id = ? LIMIT 1";
        $this->db->query($sql, $orderId);
        $result = $this->db->result();

        return $result ? (int)$result->user_id : null;
    }

    /**
     * Заявка по id.
     *
     * @param int $orderId
     * @return object|null id, user_id, organization_id
     */
    public function findByIdForDisableRobotCalls(int $orderId): ?object
    {
        $sql = "SELECT id, user_id, organization_id FROM s_orders WHERE id = ? LIMIT 1";
        $this->db->query($sql, $orderId);
        $row = $this->db->result();
        return $row ?: null;
    }
}


