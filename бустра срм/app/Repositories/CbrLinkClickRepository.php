<?php

namespace App\Repositories;

use App\Core\Database\SimplaDatabase;

class CbrLinkClickRepository
{
    private $db;

    public function __construct()
    {
        $this->db = SimplaDatabase::getInstance()->db();
    }

    public function count($dateFrom, $dateTo)
    {
        $query = "SELECT COUNT(*) as count FROM yametric_logs WHERE ya_action = 'cb' AND created BETWEEN ? AND ?";
        $this->db->query($query, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59');
        $result = $this->db->result();
        return $result ? $result->count : 0;
    }

    public function getClicks($dateFrom, $dateTo, $page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        $query = "SELECT id, created, user_id, ip, ya_action FROM yametric_logs WHERE ya_action = 'cb' AND created BETWEEN ? AND ? ORDER BY created DESC LIMIT ? OFFSET ?";
        $this->db->query($query, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59', $perPage, $offset);
        return $this->db->results();
    }
}