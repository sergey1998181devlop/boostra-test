<?php
require_once 'Simpla.php';

/**
 * Class DiscountInsure
 * Класс для работы со скидками по страховкам
 */
class DiscountInsure extends Simpla
{
    /**
     * Список всех скидок
     * @return array|false
     */
    public function getDiscounts()
    {
        $query = $this->db->placehold("SELECT * FROM __discount_insure");
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Добавляет скидку
     * @param array $data
     * @return mixed
     */
    public function addDiscount(array $data)
    {
        $query = $this->db->placehold("INSERT INTO __discount_insure SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновляет скидку
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateDiscount(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE __discount_insure SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Удаляет скидку
     * @param int $id
     * @return mixed
     */
    public function deleteDiscount(int $id)
    {
        $query = $this->db->placehold("DELETE FROM __discount_insure WHERE id = ?", $id);
        $this->db->query($query);

        $query = $this->db->placehold("DELETE FROM __discount_insure_phones WHERE discount_insurer_id = ?", $id);
        return $this->db->query($query);
    }

    /**
     * Получает список телефонов акции
     * @param int $id
     * @param array $filter_data
     * @return array|false
     */
    public function getPhonesByDiscountId(int $id, array $filter_data = [])
    {
        $sql = "SELECT * FROM __discount_insure_phones WHERE discount_insurer_id = ?";

        if (isset($filter_data['page']) && isset($filter_data['limit'])) {
            $sql .= " LIMIT " . (($filter_data['page'] - 1) * $filter_data['limit']) . ", " . $filter_data['limit'];
        }

        $query = $this->db->placehold($sql, $id);

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Кол-во телефонов, для пагинации
     * @param int $id
     * @return int
     */
    public function getTotalPhonesByDiscountId(int $id): int
    {
        $query = $this->db->placehold("SELECT COUNT(*) as total FROM __discount_insure_phones WHERE discount_insurer_id = ?", $id);
        $this->db->query($query);
        return (int)$this->db->result('total');
    }

    /**
     * Удаляет телефон из акции
     * @param int $id
     * @return mixed
     */
    public function deletePhone(int $id)
    {
        $query = $this->db->placehold("DELETE FROM __discount_insure_phones WHERE id = ?", $id);
        return $this->db->query($query);
    }

    /**
     * Добавляет телефон для скидки
     * @param array $data
     * @return mixed
     */
    public function addDiscountPhone(array $data)
    {
        $query = $this->db->placehold("REPLACE INTO __discount_insure_phones SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Удаляет все телефоны для скидки
     * @param int $discount_insure_id
     * @return mixed
     */
    public function deletePhones(int $discount_insure_id)
    {
        $query = $this->db->placehold("DELETE FROM __discount_insure_phones WHERE discount_insure_id = ?", $discount_insure_id);
        return $this->db->query($query);
    }
}
