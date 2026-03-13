<?php

require_once 'Simpla.php';

/**
 * Класс для работы с таблицей s_hyper_c, которая содержим результаты скоринга hyper-c
 *
 * success = null - скоринг не завершен
 * success = 0  - ошибка
 * success = 1 AND decision = 'Decline' - отказ
 * success = 1 AND decision = 'Approve' - одобрено
 */
class HyperC extends Simpla
{
    public const APPROVED_DECISION = 'Approve';
    public const REJECTED_DECISION = 'Decline';

    /**
     * @param array $where
     * @return array|null|false
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
        $this->db->query("SELECT * FROM __hyper_c WHERE 1 AND $conditions ORDER BY id DESC");
        return $this->db->results();
    }

    /**
     * @param array $where
     * @return stdClass|null|false
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
        $this->db->query("SELECT * FROM __hyper_c WHERE 1 AND $conditions ORDER BY id DESC");
        return $this->db->result();
    }

    /**
     * Добавление новой записи
     * @param array $row
     * @return int
     */
    public function add($row)
    {
        $query = $this->db->placehold('INSERT INTO __hyper_c SET ?%', (array)$row);
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
        $query = $this->db->placehold("UPDATE __hyper_c SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }
}