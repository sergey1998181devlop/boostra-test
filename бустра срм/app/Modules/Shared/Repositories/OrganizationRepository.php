<?php

namespace App\Modules\Shared\Repositories;

class OrganizationRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    public function getInnById(int $organizationId): ?string
    {
        $this->db->query("SELECT inn FROM s_organizations WHERE id = ? LIMIT 1", $organizationId);
        $result = $this->db->result();
        
        return $result->inn ?? null;
    }
}

