<?php

namespace App\Repositories;

class SmsTemplateRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    /**
     * Получить шаблон по ID и типу
     *
     * @param int $id
     * @param string $type
     * @return object|null
     */
    public function findByIdAndType(int $id, string $type = 'from_tech'): ?object
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM s_sms_templates 
            WHERE id = ? AND type = ?
            LIMIT 1
        ", $id, $type);

        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * @param string $type
     * @return array
     */
    public function findAllByType(string $type = 'from_tech'): array
    {
        $query = $this->db->placehold("
            SELECT id, name, template 
            FROM s_sms_templates 
            WHERE type = ?
        ", $type);

        $this->db->query($query);
        $results = $this->db->results();
        return $results ?: [];
    }
}