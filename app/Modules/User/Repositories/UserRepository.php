<?php

namespace App\Modules\User\Repositories;

class UserRepository
{
    private \Database $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    public function getUID(int $userId): ?string
    {
        $query = $this->db->placehold("
            SELECT UID FROM s_users
            WHERE id = ?
        ", $userId);

        $this->db->query($query);

        $result = $this->db->result();
        if (is_object($result)) {
            return $result->UID ?? null;
        }

        return null;
    }
}