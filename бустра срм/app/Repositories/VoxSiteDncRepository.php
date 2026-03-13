<?php

namespace App\Repositories;

use Database;

class VoxSiteDncRepository
{
    /** @var Database */
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @param string|null $siteId Фильтр по site_id (опционально), строка например 'boostra'
     * @return array
     */
    public function findAll(?string $siteId = null): array
    {
        if ($siteId !== null) {
            $this->db->query(
                'SELECT * FROM s_vox_site_dnc WHERE site_id = ? ORDER BY id ASC',
                $siteId
            );
        } else {
            $this->db->query(
                'SELECT * FROM s_vox_site_dnc ORDER BY id ASC'
            );
        }
        $rows = $this->db->results();
        return $rows ?: [];
    }

    /**
     * @param int $id
     * @return object|null
     */
    public function findById(int $id): ?object
    {
        $this->db->query('SELECT * FROM s_vox_site_dnc WHERE id = ?', $id);
        $row = $this->db->result();
        return $row ?: null;
    }

    /**
     * Первая активная запись по site_id
     *
     * @param string $siteId
     * @return object|null
     */
    public function findFirstActiveBySiteId(string $siteId): ?object
    {
        if ($siteId === '') {
            return null;
        }
        $this->db->query(
            'SELECT * FROM s_vox_site_dnc WHERE site_id = ? AND is_active = 1 ORDER BY id ASC LIMIT 1',
            $siteId
        );
        $row = $this->db->result();
        return $row ?: null;
    }

    /**
     * Найти запись по паре (site_id, organization_id), активную и с заполненными кредами.
     *
     * @param string $siteId
     * @param int $organizationId
     * @return object|null
     */
    public function findBySiteAndOrganization(string $siteId, int $organizationId): ?object
    {
        $this->db->query(
            'SELECT * FROM s_vox_site_dnc WHERE site_id = ? AND organization_id = ? AND is_active = 1 LIMIT 1',
            $siteId,
            $organizationId
        );
        $row = $this->db->result();
        return $row ?: null;
    }

    /**
     * @param array $data site_id, organization_id, vox_domain?, vox_token?, api_url?, outgoing_calls_dnc_list_id?, is_active?, comment?
     * @return int id созданной записи
     */
    public function create(array $data): int
    {
        $fields = [
            'site_id' => (string)$data['site_id'],
            'organization_id' => (int)$data['organization_id'],
            'vox_domain' => isset($data['vox_domain']) ? (string)$data['vox_domain'] : null,
            'vox_token' => isset($data['vox_token']) ? (string)$data['vox_token'] : null,
            'api_url' => isset($data['api_url']) ? (string)$data['api_url'] : null,
            'outgoing_calls_dnc_list_id' => isset($data['outgoing_calls_dnc_list_id']) ? (int)$data['outgoing_calls_dnc_list_id'] : null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'comment' => isset($data['comment']) ? (string)$data['comment'] : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $query = $this->db->placehold('INSERT INTO s_vox_site_dnc SET ?%', $fields);
        $this->db->query($query);
        return (int)$this->db->insert_id();
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $allowed = ['site_id', 'organization_id', 'vox_domain', 'vox_token', 'api_url', 'outgoing_calls_dnc_list_id', 'is_active', 'comment'];
        $fields = ['updated_at' => date('Y-m-d H:i:s')];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                if ($key === 'site_id') {
                    $fields[$key] = (string)$data[$key];
                } elseif (in_array($key, ['organization_id', 'outgoing_calls_dnc_list_id', 'is_active'], true)) {
                    $fields[$key] = (int)$data[$key];
                } else {
                    $fields[$key] = $data[$key] === null || $data[$key] === '' ? null : (string)$data[$key];
                }
            }
        }
        $query = $this->db->placehold('UPDATE s_vox_site_dnc SET ?% WHERE id = ?', $fields, $id);
        $this->db->query($query);
        return (bool)$this->db->affected_rows();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->db->query('DELETE FROM s_vox_site_dnc WHERE id = ?', $id);
        return (bool)$this->db->affected_rows();
    }

    /**
     * Проверка уникальности пары (site_id, organization_id), исключая id.
     *
     * @param string $siteId site_id из s_sites (например 'boostra')
     * @param int $organizationId
     * @param int|null $excludeId
     * @return bool true если пара уже существует
     */
    public function existsPair(string $siteId, int $organizationId, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $this->db->query(
                'SELECT 1 FROM s_vox_site_dnc WHERE site_id = ? AND organization_id = ? AND id != ? LIMIT 1',
                $siteId,
                $organizationId,
                $excludeId
            );
        } else {
            $this->db->query(
                'SELECT 1 FROM s_vox_site_dnc WHERE site_id = ? AND organization_id = ? LIMIT 1',
                $siteId,
                $organizationId
            );
        }
        $row = $this->db->result();
        return $row !== null && $row !== false;
    }
}
