<?php

namespace App\Modules\OrderData\Infrastructure\Repository;

use Database;

/**
 * Class OrderDataRepository
 * Репозиторий для работы с дополнительными данными заказов (таблица s_order_data)
 */
class OrderDataRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Получает значение по ключу для конкретного заказа
     *
     * @param int $orderId ID заказа
     * @param string $key Ключ данных
     * @return string|null Значение или null если не найдено
     */
    public function getValueByKey(int $orderId, string $key): ?string
    {
        $query = $this->db->placehold(
            'SELECT value FROM __order_data WHERE order_id = ? AND `key` = ?',
            $orderId,
            $key
        );
        
        $this->db->query($query);
        $result = $this->db->result();
        
        return $result ? $result->value : null;
    }

    /**
     * Получает все данные для конкретного заказа
     *
     * @param int $orderId ID заказа
     * @return array Ассоциативный массив ключ => значение
     */
    public function getAllByOrderId(int $orderId): array
    {
        $query = $this->db->placehold(
            'SELECT `key`, value FROM __order_data WHERE order_id = ?',
            $orderId
        );
        
        $this->db->query($query);
        $results = $this->db->results();
        
        $data = [];
        foreach ($results as $result) {
            $data[$result->key] = $result->value;
        }
        
        return $data;
    }

    /**
     * Получает данные для нескольких заказов по ключу
     *
     * @param array $orderIds Массив ID заказов
     * @param string $key Ключ данных
     * @return array Ассоциативный массив order_id => значение
     */
    public function getValuesByOrderIds(array $orderIds, string $key): array
    {
        if (empty($orderIds)) {
            return [];
        }

        $query = $this->db->placehold(
            'SELECT order_id, value FROM __order_data WHERE order_id IN (?@) AND `key` = ?',
            $orderIds,
            $key
        );
        
        $this->db->query($query);
        $results = $this->db->results();
        
        $data = [];
        foreach ($results as $result) {
            $data[$result->order_id] = $result->value;
        }
        
        return $data;
    }

    /**
     * Устанавливает значение для заказа
     *
     * @param int $orderId ID заказа
     * @param string $key Ключ данных
     * @param string $value Значение
     * @return bool Результат операции
     */
    public function setValue(int $orderId, string $key, string $value): bool
    {
        $query = $this->db->placehold(
            'REPLACE INTO __order_data (order_id, `key`, value, updated) VALUES (?, ?, ?, NOW())',
            $orderId,
            $key,
            $value
        );
        
        return $this->db->query($query);
    }

    /**
     * Удаляет значение для заказа
     *
     * @param int $orderId ID заказа
     * @param string $key Ключ данных
     * @return bool Результат операции
     */
    public function deleteValue(int $orderId, string $key): bool
    {
        $query = $this->db->placehold(
            'DELETE FROM __order_data WHERE order_id = ? AND `key` = ?',
            $orderId,
            $key
        );
        
        return $this->db->query($query);
    }
}