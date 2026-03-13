<?php

namespace App\Repositories;

use App\Core\Database\SimplaDatabase;

class TicketCompanyRepository
{
    private $db;

    public function __construct()
    {
        $this->db = SimplaDatabase::getInstance()->db();
    }

    public function getAll()
    {
        $query = "SELECT * FROM s_organizations";
        $this->db->query($query);
        return $this->db->results();
    }

    public function getById($id)
    {
        $query = "SELECT * FROM s_organizations WHERE id = ?";
        $this->db->query($query, $id);
        return $this->db->result();
    }

    public function create($name, $is_active = 1)
    {
        $query = "INSERT INTO s_organizations (name, is_active) VALUES (?, ?)";
        $this->db->query($query, $name, $is_active);
        return $this->db->insert_id();
    }

    public function update($id, $name, $is_active)
    {
        $query = "UPDATE s_organizations SET name = ?, is_active = ? WHERE id = ?";
        return $this->db->query($query, $name, $is_active, $id);
    }

    public function delete($id)
    {
        $query = "DELETE FROM s_organizations WHERE id = ?";
        return $this->db->query($query, $id);
    }

    public function isUsedInTickets($companyId)
    {
        $query = "SELECT COUNT(*) as count FROM s_mytickets WHERE company_id = ?";
        $this->db->query($query, $companyId);
        $result = $this->db->result();
        return $result && $result->count > 0;
    }

    public function setUseInTickets(int $id, bool $isActive): bool
    {
        $query = "UPDATE s_organizations SET use_in_tickets = ? WHERE id = ?";
        return (bool)$this->db->query($query, (int)$isActive, $id);
    }
}