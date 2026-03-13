<?php

require_once 'Simpla.php';

class VsevDebtTask extends Simpla
{
    public function get_task($id)
    {
        $query = $this->db->placehold("SELECT * FROM s_vsev_debt_tasks WHERE id = ?", $id);
        $this->db->query($query);
        return $this->db->result();
    }

    public function get_tasks($filter = [])
    {
        $limit = 100;
        $page = 1;
        $where = '1';
        $sort = 'id DESC';

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        if (isset($filter['status'])) {
            $where .= $this->db->placehold(' AND status = ?', $filter['status']);
        }

        if (isset($filter['processing_older_than'])) {
            $where .= $this->db->placehold(' AND updated_at < NOW() - INTERVAL 1 HOUR');
        }

        if (isset($filter['original_filename'])) {
            $where .= $this->db->placehold(' AND original_filename LIKE ?', '%' . $filter['original_filename'] . '%');
        }

        if (isset($filter['sort'])) {
            switch ($filter['sort']) {
                case 'date_asc':
                    $sort = 'created_at ASC';
                    break;
                case 'date_desc':
                    $sort = 'created_at DESC';
                    break;
            }
        }

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT *
            FROM s_vsev_debt_tasks
            WHERE $where
            ORDER BY $sort
            $sql_limit
        ");

        $this->db->query($query);
        return $this->db->results();
    }

    public function count_tasks($filter = [])
    {
        $where = '1';

        if (isset($filter['status'])) {
            $where .= $this->db->placehold(' AND status = ?', $filter['status']);
        }

        if (isset($filter['original_filename'])) {
            $where .= $this->db->placehold(' AND original_filename LIKE ?', '%' . $filter['original_filename'] . '%');
        }

        $query = $this->db->placehold("SELECT COUNT(id) as count FROM s_vsev_debt_tasks WHERE $where");
        $this->db->query($query);
        return $this->db->result('count');
    }

    public function add_task($task)
    {
        $query = $this->db->placehold("INSERT INTO s_vsev_debt_tasks SET ?%", $task);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    public function update_task($id, $task)
    {
        $query = $this->db->placehold("UPDATE s_vsev_debt_tasks SET ?% WHERE id = ?", $task, $id);
        $this->db->query($query);
        return $id;
    }
}