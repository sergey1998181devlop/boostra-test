<?php

namespace App\Repositories;

use Database;

class ApplicationTokenRepository
{
    /** @var Database */
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Проверка: существует ли активный токен с данным значением и не истёкший.
     *
     * @param string $token
     * @return bool
     */
    public function isValidToken(string $token): bool
    {
        if ($token === '') {
            return false;
        }
        $this->db->query(
            'SELECT 1 FROM application_tokens WHERE token = ? AND (expired_at IS NULL OR expired_at > NOW()) AND enabled = 1 LIMIT 1',
            $token
        );
        $row = $this->db->result();
        return $row !== null && $row !== false;
    }
}
