<?php

require_once 'Simpla.php';

/**
 * Class JuicescoreCriteria
 * s_juicescore_criteria
 */
class JuicescoreCriteria extends Simpla
{
    /**
     * Получение всех записей
     * @return array|false
     */
    public function getAll()
    {
        $this->db->query("SELECT * FROM __juicescore_criteria");
        return $this->db->results();
    }

    /**
     * Получение конкретной записи по её Id
     * @param string $name
     * @return false|ArrayObject
     */
    public function get(string $name)
    {
        $query = $this->db->placehold('SELECT * FROM __juicescore_criteria WHERE `name` = ?', $name);
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
        $query = $this->db->placehold('INSERT INTO __juicescore_criteria SET ?%', (array)$row);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновление записи
     * @param string $name
     * @param array $data
     * @return mixed
     */
    public function update(string $name, array $data)
    {
        $query = $this->db->placehold("UPDATE __juicescore_criteria SET ?% WHERE `name` = ?", $data, $name);
        return $this->db->query($query);
    }

    /**
     * Удаление записи
     * @param string $name
     * @return mixed
     */
    public function delete(string $name)
    {
        $query = $this->db->placehold("DELETE FROM __juicescore_criteria WHERE `name` = ?", $name);
        return $this->db->query($query);
    }
}