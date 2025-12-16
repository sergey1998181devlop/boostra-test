<?php

require_once('Simpla.php');

class PromoEvents extends Simpla
{
    public function saveEvent(int $userId, string $action): int
    {
        $now = date('Y-m-d H:i:s');

        $query = $this->db->placehold("
        INSERT INTO promo_events
            SET user_id = ?, action = ?, created_at = ?
    ", $userId, $action, $now);

        $this->db->query($query);

        return (int)$this->db->insert_id();
    }
}