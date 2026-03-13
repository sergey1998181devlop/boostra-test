<?php

namespace api;
use Simpla;

class MyTicketsReportTemplates extends Simpla
{
    /**
     * Получить все шаблоны
     */
    public function getTemplates(): array
    {
        $this->db->query("
            SELECT id, name, created_at, updated_at
            FROM s_mytickets_report_templates 
            ORDER BY name ASC
        ");

        return $this->db->results();
    }

    /**
     * Получить шаблон по ID
     */
    public function getTemplate(int $id): ?object
    {
        $this->db->query("
            SELECT * FROM s_mytickets_report_templates 
            WHERE id = ?
        ", $id);

        return $this->db->result();
    }

    /**
     * Создать новый шаблон
     */
    public function createTemplate(string $name, array $data): int
    {
        $this->db->query("
            INSERT INTO s_mytickets_report_templates (name, data) 
            VALUES (?, ?)
        ", $name, json_encode($data, JSON_UNESCAPED_UNICODE));

        return $this->db->insert_id();
    }

    /**
     * Обновить шаблон
     */
    public function updateTemplate(int $id, string $name, array $data): bool
    {
        $this->db->query("
            UPDATE s_mytickets_report_templates 
            SET name = ?, data = ? 
            WHERE id = ?
        ", $name, json_encode($data, JSON_UNESCAPED_UNICODE), $id);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Удалить шаблон
     */
    public function deleteTemplate(int $id): bool
    {
        $this->db->query("
            DELETE FROM s_mytickets_report_templates 
            WHERE id = ?
        ", $id);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Проверить уникальность имени шаблона
     */
    public function isNameUnique(string $name, int $excludeId = null): bool
    {
        $excludeCondition = $excludeId ? "AND id != $excludeId" : "";

        $this->db->query("
            SELECT COUNT(*) as count 
            FROM s_mytickets_report_templates 
            WHERE name = ? $excludeCondition
        ", $name);

        $result = $this->db->result();
        return $result->count == 0;
    }
}
