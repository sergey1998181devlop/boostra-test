<?php

require_once 'Simpla.php';

class Complaint extends Simpla
{
    public function add_complaint(array $complaint)
    {
        $query = $this->db->placehold('INSERT INTO __complaint SET ?%', $complaint);
        if (!$this->db->query($query)) {
            return false;
        }

        return $this->db->insert_id();
    }

    public function get_limit(string $name, string $phone, string $email, string $birth): ?object
    {
        $query = $this->db->placehold("
            SELECT COUNT(DISTINCT id) as count, MAX(created) as created
            FROM __complaint
            WHERE fio = ? AND phone = ? AND email = ? AND birth = ?
        ", $name, $phone, $email, $birth);

        $this->db->query($query);

        return $this->db->result();
    }

    public function get_topics(?int $organization_id = null): array
    {
        if ($organization_id !== null) {
            $query = $this->db->placehold("
                SELECT * FROM __complaint_topics
                WHERE is_active = 1
                AND (organization_id = ? OR organization_id IS NULL)
                ORDER BY sort_order ASC, id ASC
            ", (int) $organization_id);
        } else {
            $query = $this->db->placehold("
                SELECT * FROM __complaint_topics
                WHERE is_active = 1
                AND organization_id IS NULL
                ORDER BY sort_order ASC, id ASC
            ");
        }

        $this->db->query($query);

        return $this->db->results();
    }

    public function get_topic(int $id): ?object
    {
        $query = $this->db->placehold("
            SELECT * FROM __complaint_topics
            WHERE id = ?
        ", (int) $id);

        $this->db->query($query);

        return $this->db->result();
    }
}