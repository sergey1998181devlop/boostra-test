<?php

namespace App\Modules\Manager\Repository;

use Database;

/**
 * Class ManagerRoleRepository
 * Репозитория для работы с менеджерами.
 */
class ManagerRepository
{
    private Database $db;

    /**
     * ManagerRoleRepository constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Находит ID всех менеджеров по списку ролей.
     *
     * @param string[] $roleKeys Массив строковых ключей ролей (например, ['developer', 'admin'])
     * @return int[] Массив ID менеджеров
     */
    public function findManagerIdsByRoleIds(array $roleKeys): array
    {
        if (empty($roleKeys)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($roleKeys), '?'));

        $sql = "
            SELECT id 
            FROM s_managers 
            WHERE role IN ({$placeholders})
        ";

        $this->db->query($sql, $roleKeys);

        $results = $this->db->results();

        return array_map(fn($row) => (int)$row->id, $results);
    }}

