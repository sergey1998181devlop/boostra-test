<?php

require_once 'Simpla.php';

class CreditHistory extends Simpla
{
    /**
     * @param array $where
     * @return stdClass|null|bool
     */
    public function getRow(array $where)
    {
        $conditions = [];
        foreach ($where as $condition => $value) {
            $conditions[] = $this->db->placehold("`$condition` = ?", $value);
        }

        $conditions = implode(' AND ', $conditions);
        $this->db->query("SELECT * FROM __credit_histories WHERE $conditions ORDER BY id DESC LIMIT 1");

        return $this->db->result();
    }

    /**
     * @param array $data
     * @return int
     */
    public function insertRow(array $data): int
    {
        $this->db->query("INSERT INTO __credit_histories SET ?%", $data);
        return $this->db->insert_id();
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateRow(int $id, array $data): bool
    {
        $query = $this->db->placehold("UPDATE __credit_histories SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }
}