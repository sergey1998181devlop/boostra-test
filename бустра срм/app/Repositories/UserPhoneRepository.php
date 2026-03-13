<?php

namespace App\Repositories;

use Database;

/**
 * Репозиторий для таблицы s_user_phones.
 */
class UserPhoneRepository
{
    /** @var Database */
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Список номеров телефонов пользователя (только активные).
     *
     * @param int $userId
     * @return array строки — номера
     */
    public function getPhoneNumbersByUserId(int $userId): array
    {
        $this->db->query(
            'SELECT phone FROM s_user_phones WHERE user_id = ? AND is_active = 1',
            $userId
        );
        $rows = $this->db->results();
        if (empty($rows)) {
            return [];
        }
        $phones = [];
        foreach ($rows as $row) {
            $phones[] = $row->phone ?? '';
        }
        return array_filter($phones);
    }
}
