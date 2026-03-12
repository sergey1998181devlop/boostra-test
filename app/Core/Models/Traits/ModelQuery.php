<?php
namespace App\Core\Models\Traits;

use Database;

trait ModelQuery
{
    /** @var Database */
    protected $db;

    public function select(
        array  $conditions = [],
        ?array $joins      = null,
               $orderBy           = null,
        string $order      = 'desc',
               $offset            = null,
               $limit             = null,
        bool   $count      = false
    ) {
        $this->data = $this->db->select(
            $this->table,
            $conditions,
            $joins,
            $orderBy,
            $order,
            $offset,
            $limit,
            $count
        );
        return $this;
    }

    public function query(string $sql, ...$params)
    {
        $query = $this->db->placehold($sql, ...$params);
        $this->db->query($query);
        return $this;
    }

    public function result()
    {
        $this->data = $this->db->result();
        return $this;
    }

    public function results()
    {
        $this->data = $this->db->results();
        return $this;
    }

    public function get(array $conditions = [], ?array $joins = null)
    {
        $results = $this->db->select(
            $this->table,
            $conditions,
            $joins,
            null,
            'desc',
            null,
            1,
            false
        );

        $this->data = $results[0] ?? null;

        return $this;
    }

    public function insert(array $data)
    {
        $sql = $this->db->placehold("INSERT INTO __{$this->table} SET ?%", $data);
        $this->db->query($sql);
        $this->data = $this->db->insert_id();
        return $this;
    }

    public function replace(array $data)
    {
        $sql = $this->db->placehold("REPLACE INTO __{$this->table} SET ?%", $data);
        $this->db->query($sql);
        $this->data = $this->db->insert_id();
        return $this;
    }

    public function update(array $data, array $where)
    {
        $sql = $this->db->placehold("UPDATE __{$this->table} SET ?% WHERE ?%", $data, $where);
        $this->db->query($sql);
        $this->data = $this->db->affected_rows();
        return $this;
    }

    public function delete(array $where)
    {
        $sql = $this->db->placehold("DELETE FROM __{$this->table} WHERE ?%", $where);
        $this->db->query($sql);
        $this->data = $this->db->affected_rows();
        return $this;
    }

    public function has(array $where = [])
    {
        $count = $this->db->select($this->table, $where, null, null, 'desc', null, null, true);
        $this->data = (bool)$count;
        return $this;
    }

    public function count(array $where = [])
    {
        $this->data = (int)$this->db->select(
            $this->table, $where, null, null, 'desc', null, null, true
        );
        return $this;
    }

    public function rand(array $where = [], int $limit = 1)
    {
        $sql = $this->db->placehold(
            "SELECT * FROM __{$this->table} WHERE ?% ORDER BY RAND() LIMIT ?",
            $where,
            $limit
        );
        $this->db->query($sql);
        $this->data = $this->db->results();
        return $this;
    }

}
