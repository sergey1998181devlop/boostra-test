<?php

namespace App\Repositories;

use App\Dto\UserBalanceDto;

class UserBalanceRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    public function getByUserId(int $userId): ?UserBalanceDto
    {
        $query = "SELECT * FROM s_user_balance WHERE user_id = ?";
        $this->db->query($query, $userId);
        $row = $this->db->result();
        return $row ? UserBalanceDto::fromDbRow($row) : null;
    }
}
