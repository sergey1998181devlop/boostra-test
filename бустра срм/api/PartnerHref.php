<?php

/**
 * Class PartnerHref
 * Класс для работы с партнерскими ссылками
 */

require_once 'Simpla.php';

class PartnerHref extends Simpla
{
    /**
     * Получает элемент
     * @param int $id
     * @return false|int
     */
    public function getItem(int $id)
    {
        $this->db->query("SELECT * FROM s_partner_href WHERE id = ?", $id);
        return $this->db->result();
    }

    /**
     * Добавляет новую запись
     * @param array $data
     * @return mixed
     */
    public function addItem(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_partner_href SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Удаляет запись
     * @param int $id
     * @return mixed
     */
    public function deleteItem(int $id)
    {
        $query = $this->db->placehold("DELETE FROM s_partner_href WHERE id = ?", $id);
        return $this->db->query($query);
    }

    /**
     * Обновляет запись
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateItem(int $id, array $data = [])
    {
        $query = $this->db->placehold("UPDATE s_partner_href SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Возвращает все записи
     * @return array|false
     */
    public function getAll()
    {
        $this->db->query("SELECT * FROM s_partner_href");
        return $this->db->results();
    }

    /**
     * Возвращает кол-во данных для отчёта по ссылкам отказников
     * @param array $filter_data
     * @return int
     */
    public function getReportTotals(array $filter_data = [])
    {
        $where = [];
        $select = ['COUNT(*) as total'];

        $query = "SELECT 
                    -- {{select}}
                FROM s_partner_href_statistics
                WHERE (1=1)
                -- {{where}}
                ";

        if (!empty($filter_data['date'])) {
            if ($filter_data['filter_group_by'] === 'month') {
                $where[] = " DATE_FORMAT(date_added, '%Y-%m') = '" . $filter_data['date'] . "'";
            } else {
                $where[] = " DATE(date_added) = '" . $filter_data['date'] . "'";
            }
        }

        if (!empty($filter_data['type_action'])) {
            $where[] = " type_action = '" . $filter_data['type_action'] . "'";
        }

        if (!empty($filter_data['href_id'])) {
            $where[] = " href_id = " . (int)$filter_data['href_id'];
        }

        if (!empty($filter_data['filter_unique'])) {
            $select = ['COUNT(DISTINCT user_id) as total'];
        }

        $query = strtr($query, [
            '-- {{select}}' => !empty($select) ? implode("\n", $select) : '',
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $sql = $this->db->placehold($query);

        $this->db->query($sql);
        return (int)$this->db->result('total');
    }
}
