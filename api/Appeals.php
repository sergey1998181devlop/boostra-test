<?php

require_once 'Simpla.php';

class Appeals extends Simpla {

    public function addAppeal($data) {
        $query = $this->db->placehold("
            INSERT INTO 
                __appeals
            SET 
            ?%
        ", (array) $data);
        $this->db->query($query);
        $id = $this->db->insert_id();
        return $id;
    }

}
