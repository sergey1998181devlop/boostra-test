<?php

require_once 'Simpla.php';

class B2pSbpIssuanceLog extends Simpla
{
    /**
     * @param array $where
     * @return stdClass|null|false
     */
    public function get(array $where)
    {
        $conditions = [];
        foreach ($where as $condition => $value) {
            $conditions[] = $this->db->placehold("`$condition` = ?", $value);
        }

        $conditions = implode(' AND ', $conditions);
        $this->db->query("SELECT * FROM b2p_sbp_issuance_log WHERE 1 AND $conditions ORDER BY id DESC");
        return $this->db->result();
    }

    /**
     * Добавление новой записи
     * @param array $row
     * @return int
     */
    public function add($row)
    {
        $query = $this->db->placehold('INSERT INTO b2p_sbp_issuance_log SET ?%', (array)$row);
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
        $query = $this->db->placehold("UPDATE b2p_sbp_issuance_log SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }
}