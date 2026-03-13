<?php

namespace api\terrorist\models;

/**
 * Работа с таблицей s_terrorist_sources
 */
class TerroristSources
{
    public const MVK_DECISION_CODE = 'mvk_decision';
    public const UN_CONSOLIDATED_CODE = 'un_consolidated';
    public const DEFAULT_CODE = 'default';
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Получить список источников
     *
     * @param array $filter ['code' => 'mvk_decision'|['mvk_decision','un_consolidated']]
     * @return array
     */
    public function getSources(array $filter = []): array
    {
        $codeFilter = '';

        if (!empty($filter['code'])) {
            $codes = (array) $filter['code'];
            $codeFilter = $this->db->placehold("AND code IN (?@)", $codes);
        }

        $query = $this->db->placehold("
            SELECT id, code, name, description, created_at, updated_at
            FROM s_terrorist_sources
            WHERE 1
                $codeFilter
            ORDER BY id
        ");

        $this->db->query($query);

        return (array) $this->db->results();
    }

    /**
     * Получить один источник по коду
     *
     * @param string $code
     * @return object|null
     */
    public function getSourceByCode(string $code)
    {
        $query = $this->db->placehold("
            SELECT id, code, name, description, created_at, updated_at
            FROM s_terrorist_sources
            WHERE code = ?
            LIMIT 1
        ", $code);

        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Получить один источник по id
     *
     * @param int $id
     * @return object|null
     */
    public function getSourceById(int $id)
    {
        $query = $this->db->placehold("
            SELECT id, code, name, description, created_at, updated_at
            FROM s_terrorist_sources
            WHERE id = ?
            LIMIT 1
        ", $id);

        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Получить source.id по коду источника
     */
    public function getIdByCode(string $code): ?int
    {
        $sql = $this->db->placehold(
            "SELECT id FROM s_terrorist_sources WHERE code = ? LIMIT 1",
            $code
        );

        $this->db->query($sql);
        $row = $this->db->result();

        return $row ? (int)$row->id : null;
    }
}
