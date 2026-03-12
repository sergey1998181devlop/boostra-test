<?php

require_once 'Simpla.php';

/**
 * Class LeadPriceLogs
 * s_lead_price_logs
 */
class LeadPriceLogs extends Simpla
{
    /**
     * Получение всех записей
     * @return array|false
     */
    public function getAll()
    {
        $this->db->query("SELECT * FROM __lead_price_logs");
        return $this->db->results();
    }


    /**
     * Получение конкретной записи по id заявки
     * @param int $orderId
     * @return false|ArrayObject
     */
    public function get(int $orderId)
    {
        $query = $this->db->placehold('SELECT * FROM __lead_price_logs WHERE `order_id` = ?', $orderId);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Добавление новой записи
     * @param array $row
     * @return int
     */
    public function add(array $row)
    {
        $query = $this->db->placehold('INSERT INTO __lead_price_logs SET ?%', (array)$row);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновление записи
     * @param int $orderId
     * @param array $data
     * @return mixed
     */
    public function update(int $orderId, array $data)
    {
        $query = $this->db->placehold("UPDATE __lead_price_logs SET ?% WHERE `order_id` = ?", $data, $orderId);
        return $this->db->query($query);
    }

    /**
     * Удаление записи
     * @param int $orderId
     * @return mixed
     */
    public function delete(int $orderId)
    {
        $query = $this->db->placehold("DELETE FROM __lead_price_logs WHERE `order_id` = ?", $orderId);
        return $this->db->query($query);
    }
}