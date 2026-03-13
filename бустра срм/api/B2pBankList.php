<?php

require_once 'Simpla.php';

class B2pBankList extends Simpla
{
    /**
     * @param array $where
     * @return array|false
     */
    public function get(array $where)
    {
        $conditions = [];
        foreach ($where as $condition => $value) {
            if (is_array($value)) {
                $conditions[] = $this->db->placehold("`$condition` IN(?@)", $value);
            } else {
                $conditions[] = $this->db->placehold("`$condition` = ?", $value);
            }
        }

        $conditions = implode(' AND ', $conditions);

        $query = "SELECT * FROM b2p_bank_list WHERE 1 AND $conditions ORDER BY id DESC";
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * @param array $where
     * @return array|false
     */
    public function getOne(array $where)
    {
        $conditions = [];
        foreach ($where as $condition => $value) {
            if (is_array($value)) {
                $conditions[] = $this->db->placehold("`$condition` IN(?@)", $value);
            } else {
                $conditions[] = $this->db->placehold("`$condition` = ?", $value);
            }
        }

        $conditions = implode(' AND ', $conditions);

        $query = "SELECT * FROM b2p_bank_list WHERE 1 AND $conditions LIMIT 1";
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
        $query = $this->db->placehold('INSERT INTO b2p_bank_list SET ?%', (array)$row);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновление записи
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data)
    {
        $query = $this->db->placehold("UPDATE b2p_bank_list SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }
}