<?php

/**
 * Description of Chats
 *
 * @author alexey
 */
use Simpla;

class Chats extends Simpla {

    public function addMessage($data) {
        $query = $this->db->placehold("
            INSERT INTO __chats
            SET ?%
        ", (array) $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

}
