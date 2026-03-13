<?php

namespace App\Repositories;

class UserDataRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    /**
     * Установить значение для user_data
     *
     * @param int $userId
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(int $userId, string $key, $value): void
    {
        $query = $this->db->placehold(
            "REPLACE INTO s_user_data (`user_id`, `key`, `value`) VALUES (?, ?, ?)",
            $userId,
            $key,
            $value
        );
        $this->db->query($query);
    }

    /**
     * Получить значение из user_data
     *
     * @param int $userId
     * @param string $key
     * @return string|null
     */
    public function get(int $userId, string $key): ?string
    {
        $query = $this->db->placehold(
            "SELECT `value` FROM s_user_data WHERE `user_id` = ? AND `key` = ? LIMIT 1",
            $userId,
            $key
        );
        $this->db->query($query);
        $result = $this->db->result();
        return $result ? $result->value : null;
    }

    /**
     * Включить показ дополнительных документов
     *
     * @param int $userId
     * @return void
     */
    public function enableShowExtraDocs(int $userId): void
    {
        $this->set($userId, 'show_extra_docs', 1);
    }
}


