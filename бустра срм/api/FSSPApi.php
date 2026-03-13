<?php

require_once( __DIR__ . '/../api/Simpla.php');

/**
 * Класс для работы с ФССП ручного режима
 * Class FsspApi
 */
class FSSPApi extends Simpla
{
    /**
     * Получает все основания
     * @return array|false
     */
    public function getBasis()
    {
        $sql = "SELECT * FROM s_fssp_basis";
        $this->db->query($sql);
        return $this->db->results();
    }

    /**
     * Получает все причины
     * @return array|false
     */
    public function getReasons()
    {
        $sql = "SELECT * FROM s_fssp_reasons";
        $this->db->query($sql);
        return $this->db->results();
    }

    /**
     * Получает значения ФССП для заявки
     * @param int $order_id
     * @return array|false
     */
    public function getFsspByOrderId(int $order_id)
    {
        $sql = "SELECT * FROM s_fssp_to_orders WHERE order_id = ?";
        $this->db->query($this->db->placehold($sql, $order_id));
        return $this->db->results();
    }

    /**
     * Удаляет fssp
     * @param int $order_id
     * @return mixed
     */
    public function deleteFsspByOrderId(int $order_id)
    {
        return $this->db->query($this->db->placehold("DELETE FROM s_fssp_to_orders WHERE order_id = ?", $order_id));
    }

    /**
     * Добавляет запись ФССП к заказу
     * @param array $data
     * @return mixed
     */
    public function addFsspByOrder(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_fssp_to_orders SET ?%", $data);
        return $this->db->query($query);
    }
}
