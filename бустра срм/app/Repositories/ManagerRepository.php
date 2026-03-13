<?php

namespace App\Repositories;

use Database;

class ManagerRepository
{
    /**
     * System менеджер
     */
    public const MANAGER_SYSTEM_ID = 50;

    /** @var Database */
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @param int $id
     * @return object|null
     */
    public function getById(int $id): ?object
    {
        $query = $this->db->placehold("
            SELECT id, name_1c
            FROM s_managers 
            WHERE id = ? 
            LIMIT 1
        ", $id);

        $this->db->query($query);
        return $this->db->result();
    }
}

