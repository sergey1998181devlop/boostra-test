<?php

namespace App\Core\Models\Traits;

trait ModelQuery
{

    public function select()
    {
        $this->data = $this->db->select($this->table, ...func_get_args());
        return $this;
    }

    public function insert()
    {
        $this->data = $this->db->insert($this->table, ...func_get_args());
        return $this;
    }

    public function id()
    {
        $this->data = $this->db->insert($this->table, ...func_get_args());
        return $this;
    }

    public function update()
    {
        $this->data = $this->db->update($this->table, ...func_get_args());
        return $this;
    }

    public function delete()
    {
        $this->data = $this->db->delete($this->table, ...func_get_args());
        return $this;
    }

    public function replace()
    {
        $this->data = $this->db->replace($this->table, ...func_get_args());
        return $this;
    }

    public function get()
    {
        $this->data = $this->db->get($this->table, ...func_get_args());
        return $this;
    }

    public function has()
    {
        $this->data = $this->db->has($this->table, ...func_get_args());
        return $this;
    }

    public function rand()
    {
        $this->data = $this->db->rand($this->table, ...func_get_args());
        return $this;
    }

    public function count()
    {
        $this->data = $this->db->count($this->table, ...func_get_args());
        return $this;
    }

    public function max()
    {
        $this->data = $this->db->max($this->table, ...func_get_args());
        return $this;
    }

    public function min()
    {
        $this->data = $this->db->min($this->table, ...func_get_args());
        return $this;
    }

    public function avg()
    {
        $this->data = $this->db->avg($this->table, ...func_get_args());
        return $this;
    }

    public function sum()
    {
        $this->data = $this->db->sum($this->table, ...func_get_args());
        return $this;
    }
}
