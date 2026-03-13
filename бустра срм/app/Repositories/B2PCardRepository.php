<?php

namespace App\Repositories;

use Database;

class B2PCardRepository
{
    /** @var Database */
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает все карты пользователя
     *
     * @param int $userId
     * @return array
     */
    public function getAllByUserId(int $userId): array
    {
        $query = "SELECT id FROM b2p_cards WHERE user_id = ?";
        $this->db->query($query, $userId);
        return $this->db->results();
    }

}
