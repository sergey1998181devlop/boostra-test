<?php

namespace App\Repositories;

class CommentRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    /**
     * @param array $data
     * @return int
     */
    public function insert(array $data): int
    {
        $query = $this->db->placehold("
            INSERT INTO s_comments SET ?%
        ", $data);

        $this->db->query($query);
        return $this->db->insert_id();
    }
}