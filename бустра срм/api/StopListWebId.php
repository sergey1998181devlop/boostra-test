<?php

require_once 'Simpla.php';

/**
 * Class StopListWebId
 * Класс для работы со стоп листами трафика
 */
class StopListWebId extends Simpla
{
    /**
     * Добавляет запись в БД
     * @param array $data
     * @return mixed
     */
    public function addItem(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_stop_list_web_id SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Удаляет запись из БД
     * @param int $id
     * @return void
     */
    public function deleteItem(int $id)
    {
        $query = $this->db->placehold("DELETE FROM s_stop_list_web_id WHERE id = ?", $id);
        $this->db->query($query);
    }

    /**
     * Получает список записей
     * @return array|false
     */
    public function getItems()
    {
        $query = $this->db->placehold("SELECT * FROM s_stop_list_web_id");
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Поиск записей
     * @param array $filter_data
     * @return array|false
     */
    public function findItems(array $filter_data = [])
    {
        $where = [];
        $query = "SELECT * FROM s_stop_list_web_id WHERE 1=1
                  -- {{where}}";

        if (!empty($filter_data['utm_source'])) {
            $where[] = $this->db->placehold("utm_source = ?", trim($filter_data['utm_source']));
        }

        if (!empty($filter_data['web_master_id'])) {
            $where[] = $this->db->placehold("web_master_id = ?", trim($filter_data['web_master_id']));
        }

        $query = strtr($query, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);
        return $this->db->results();
    }
}
