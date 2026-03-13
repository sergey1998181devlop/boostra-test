<?php

namespace App\Modules\Faq\Repositories;

use Database;
use InvalidArgumentException;

/**
 * FAQ Repository для всех сайтов
 */
class FaqRepository
{
    private Database $db;
    private string $siteId;

    /**
     * @param Database $db
     * @param string $siteId Идентификатор сайта из s_sites (boostra, soyaplace, etc.)
     */
    public function __construct(Database $db, string $siteId)
    {
        $this->db = $db;
        $this->siteId = $siteId;
    }

    public function getAllBlocksWithFaq(): array
    {
        $query = "
            SELECT
                fb.id AS block_id,
                fb.name AS block_name,
                fb.type,
                fb.yandex_goal_id AS block_yandex_goal_id,
                s.id AS section_id,
                s.name AS section_name,
                s.sequence,
                f.id AS faq_id,
                f.question,
                f.answer,
                f.yandex_goal_id
            FROM s_faq_blocks fb
            LEFT JOIN s_faq_sections s
                ON s.block_id = fb.id
                AND s.site_id = ?
            LEFT JOIN s_faq f
                ON f.section_id = s.id
                AND f.site_id = ?
            WHERE fb.site_id = ?
            ORDER BY fb.id, s.sequence, f.id
        ";

        $this->db->query(
            $query,
            $this->siteId,
            $this->siteId,
            $this->siteId
        );
        return $this->db->results() ?: [];
    }

    public function getAll(): array
    {
        $this->db->query(
            "SELECT * FROM s_faq WHERE site_id = ?",
            $this->siteId
        );
        return $this->db->results() ?: [];
    }

    public function createBlock(array $data): int
    {
        unset($data['site_id']);
        $data['site_id'] = $this->siteId;

        $this->db->query("INSERT INTO s_faq_blocks SET ?%", $data);
        return (int)$this->db->insert_id();
    }

    public function updateBlock(array $data): void
    {
        unset($data['site_id']);

        $this->db->query(
            "UPDATE s_faq_blocks
             SET ?%
             WHERE id = ?
               AND site_id = ?
             LIMIT 1",
            $data,
            (int)$data['id'],
            $this->siteId
        );
    }

    public function deleteBlock(int $id): void
    {
        $this->db->query(
            "DELETE f FROM s_faq f
             INNER JOIN s_faq_sections s ON f.section_id = s.id
             WHERE s.block_id = ?
               AND s.site_id = ?
               AND f.site_id = ?",
            $id,
            $this->siteId,
            $this->siteId
        );

        $this->db->query(
            "DELETE FROM s_faq_sections
             WHERE block_id = ?
               AND site_id = ?",
            $id,
            $this->siteId
        );

        $this->db->query(
            "DELETE FROM s_faq_blocks
             WHERE id = ?
               AND site_id = ?
             LIMIT 1",
            $id,
            $this->siteId
        );
    }

    public function getBlockById(int $id): ?object
    {
        $this->db->query(
            "SELECT * FROM s_faq_blocks
             WHERE id = ?
               AND site_id = ?
             LIMIT 1",
            $id,
            $this->siteId
        );
        $result = $this->db->result();

        return $result ?: null;
    }

    public function getSectionsByBlock(int $blockId): array
    {
        $this->db->query(
            "SELECT * FROM s_faq_sections
             WHERE block_id = ?
               AND site_id = ?
             ORDER BY sequence",
            $blockId,
            $this->siteId
        );
        return $this->db->results() ?: [];
    }

    public function createSection(array $data): int
    {
        $data['site_id'] = $this->siteId;

        $this->db->query("INSERT INTO s_faq_sections SET ?%", $data);
        return (int)$this->db->insert_id();
    }

    public function updateSection(array $data): void
    {
        $this->validateOwnership('s_faq_sections', (int)$data['id']);

        unset($data['site_id']);

        $this->db->query(
            "UPDATE s_faq_sections
             SET ?%
             WHERE id = ?
               AND site_id = ?
             LIMIT 1",
            $data,
            (int)$data['id'],
            $this->siteId
        );
    }

    public function deleteSection(int $id): void
    {
        $this->validateOwnership('s_faq_sections', $id);

        $this->db->query(
            "DELETE FROM s_faq
             WHERE section_id = ?
               AND site_id = ?",
            $id,
            $this->siteId
        );

        $this->db->query(
            "DELETE FROM s_faq_sections
             WHERE id = ?
               AND site_id = ?
             LIMIT 1",
            $id,
            $this->siteId
        );
    }

    public function updateSectionSequence(int $sectionId, int $sequence): bool
    {
        $this->validateOwnership('s_faq_sections', $sectionId);

        $query = "UPDATE s_faq_sections
                  SET sequence = ?
                  WHERE id = ?
                    AND site_id = ?";
        return $this->db->query($query, $sequence, $sectionId, $this->siteId) !== false;
    }

    public function create(array $data): void
    {
        $this->validateOwnership('s_faq_sections', (int)$data['section_id']);

        $data['site_id'] = $this->siteId;

        $this->db->query("INSERT INTO s_faq SET ?%", $data);
    }

    public function update(array $data): void
    {
        $this->validateOwnership('s_faq', (int)$data['id']);

        if (isset($data['section_id'])) {
            $this->validateOwnership('s_faq_sections', (int)$data['section_id']);
        }

        unset($data['site_id']);

        $this->db->query(
            "UPDATE s_faq
             SET ?%
             WHERE id = ?
               AND site_id = ?
             LIMIT 1",
            $data,
            (int)$data['id'],
            $this->siteId
        );
    }

    public function delete(int $id): void
    {
        $this->validateOwnership('s_faq', $id);

        $this->db->query(
            "DELETE FROM s_faq
             WHERE id = ?
               AND site_id = ?
             LIMIT 1",
            $id,
            $this->siteId
        );
    }

    /**
     * Проверяет, что запись принадлежит текущему сайту
     *
     * @throws InvalidArgumentException если запись не найдена или принадлежит другому сайту
     */
    protected function validateOwnership(string $table, int $id): void
    {
        $this->db->query(
            "SELECT site_id FROM {$table} WHERE id = ? LIMIT 1",
            $id
        );
        $result = $this->db->result();

        if (!$result) {
            throw new InvalidArgumentException(
                "Record with id={$id} not found in {$table}"
            );
        }

        if ($result->site_id !== $this->siteId) {
            throw new InvalidArgumentException(
                "Cannot access {$table} record from site '{$result->site_id}' " .
                "while operating as '{$this->siteId}'"
            );
        }
    }
}
