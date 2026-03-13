<?php

require_once 'Simpla.php';

class VoxUserDepartments extends Simpla
{
    public function getAll(): array
    {
        $this->db->query("SELECT * FROM __vox_user_departments ORDER BY name");
        return $this->db->results();
    }

    public function getById(int $id): ?object
    {
        $this->db->query("SELECT * FROM __vox_user_departments WHERE id = ? LIMIT 1", $id);
        $result = $this->db->result();
        return $result ?: null;
    }

    public function add(string $name): int
    {
        $this->db->query(
            "INSERT INTO __vox_user_departments (name) VALUES (?)",
            $name
        );
        return (int)$this->db->insert_id();
    }

    public function update(int $id, string $name): void
    {
        $this->db->query(
            "UPDATE __vox_user_departments SET name = ? WHERE id = ?",
            $name,
            $id
        );
    }

    public function delete(int $id): void
    {
        $this->db->query(
            "UPDATE __vox_users SET department_id = NULL WHERE department_id = ?",
            $id
        );
        $this->db->query(
            "DELETE FROM __vox_user_departments WHERE id = ?",
            $id
        );
    }
}
