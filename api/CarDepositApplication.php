<?php

require_once 'Simpla.php';

class CarDepositApplication extends Simpla {

    public function addApplication($data) {
        $query = $this->db->placehold("
            INSERT INTO 
                __car_deposit_applications
            SET 
            ?%
        ", (array) $data);
        $this->db->query($query);
        $id = $this->db->insert_id();
        return $id;
    }

}
