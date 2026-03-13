<?php

namespace App\Repositories;

use Database;

/**
 * Репозиторий для таблицы s_user_dnc.
 */
class UserDncRepository
{
    /** @var Database */
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Активная DNC-запись по паре (user_id, site_id). Записи с site_id IS NULL не учитываются.
     *
     * @param int $userId
     * @param string $siteId
     * @return object|null id, date_end, phones (json string), dnc_contact_ids (json string)
     */
    public function findActiveByUserIdAndSiteId(int $userId, string $siteId): ?object
    {
        if ($siteId === '') {
            return null;
        }
        $this->db->query(
            'SELECT id, date_end, phones, dnc_contact_ids FROM s_user_dnc WHERE user_id = ? AND site_id = ? AND date_end > NOW() ORDER BY date_end DESC LIMIT 1',
            $userId,
            $siteId
        );
        $row = $this->db->result();
        return $row ?: null;
    }

    /**
     * Закрыть активную DNC-запись (установить date_end = NOW()).
     *
     * @param int $id
     * @return bool
     */
    public function expireById(int $id): bool
    {
        $this->db->query(
            'UPDATE s_user_dnc SET date_end = NOW() WHERE id = ? AND date_end > NOW()',
            $id
        );
        return (bool) $this->db->affected_rows();
    }

    /**
     * Удалить DNC-запись по идентификатору.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        $this->db->query(
            'DELETE FROM s_user_dnc WHERE id = ?',
            $id
        );
        return (bool) $this->db->affected_rows();
    }

    /**
     * Создать запись DNC.
     *
     * @param array $data user_id, phones (json string), days, date_start, date_end, manager_id, dnc_contact_ids (json string), site_id (optional)
     * @return int id созданной записи
     */
    public function create(array $data): int
    {
        $fields = [
            'user_id' => (int)$data['user_id'],
            'phones' => $data['phones'],
            'days' => (int)$data['days'],
            'date_start' => $data['date_start'],
            'date_end' => $data['date_end'],
            'manager_id' => (int)$data['manager_id'],
            'dnc_contact_ids' => $data['dnc_contact_ids'],
        ];
        if (array_key_exists('site_id', $data) && $data['site_id'] !== null && $data['site_id'] !== '') {
            $fields['site_id'] = (string)$data['site_id'];
        }
        $query = $this->db->placehold('INSERT INTO s_user_dnc SET ?%', $fields);
        $this->db->query($query);
        return (int)$this->db->insert_id();
    }
}
