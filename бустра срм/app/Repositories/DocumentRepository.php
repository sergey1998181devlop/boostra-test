<?php

namespace App\Repositories;

use App\Dto\DocumentDto;

class DocumentRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    public function getById(int $id): ?DocumentDto
    {
        $query = "SELECT * FROM s_documents WHERE id = ?";
        $this->db->query($query, $id);
        $row = $this->db->result();
        return $row ? DocumentDto::fromDbRow($row) : null;
    }

    /**
     * @return DocumentDto[]
     */
    public function getByOrderAndType(int $orderId, string $type): array
    {
        $query = "SELECT * FROM s_documents WHERE order_id = ? AND type = ?";
        $this->db->query($query, $orderId, $type);
        $rows = $this->db->results();
        if (!$rows) {
            return [];
        }
        return array_map(static fn($r) => DocumentDto::fromDbRow($r), $rows);
    }
}
