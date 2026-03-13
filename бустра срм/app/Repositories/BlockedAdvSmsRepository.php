<?php

namespace App\Repositories;

class BlockedAdvSmsRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    public function blockAdvForUser(int $userId, ?string $phone = null): void
    {
        $this->db->query(
            "INSERT IGNORE INTO s_block_sms_adv (user_id, sms_type, phone) VALUES (?, 'adv', ?)",
            $userId,
            $phone
        );
    }

    public function unblockAdvForUser(int $userId): void
    {
        $this->db->query(
            "DELETE FROM s_block_sms_adv WHERE user_id = ?",
            $userId
        );
    }

}


