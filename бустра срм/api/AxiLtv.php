<?php

require_once 'Simpla.php';

/**
 * Для формирования отчетов LTV необходимо хранить в бд CRM поля - решения от аксиоматики
 * s_axi_ltv
 */
class AxiLtv extends Simpla
{
    /**
     * Получение конкретной записи по id заявки
     * @param int $order_id
     * @return false|ArrayObject
     */
    public function get($order_id)
    {
        $query = $this->db->placehold('SELECT * FROM __axi_ltv WHERE order_id = ?', $order_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Добавление новой записи
     * @param array $row
     * @return int
     */
    public function add($row)
    {
        $query = $this->db->placehold('INSERT INTO __axi_ltv SET ?%', (array)$row);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновление записи
     * @param int $order_id
     * @param array $update
     * @return mixed
     */
    public function update($order_id, $update)
    {
        $update['updated'] =  date('Y-m-d H:i:s');;
        $query = $this->db->placehold("UPDATE __axi_ltv SET ?% WHERE order_id = ?", $update, $order_id);
        return $this->db->query($query);
    }
}