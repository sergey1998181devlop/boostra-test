<?php

namespace api\traits;

trait UserMessangers {

    public function getMessangersInfoByUserId($id, $phone) {
        $query = $this->db->placehold("
            SELECT 
                * 
            FROM 
                __verify_messangers 
            WHERE 
                user_id = '" . $id . "'
            OR
                phone = '" . $phone . "'
        ");
        $this->db->query($query);
        return $this->db->results();
    }

    public function getUserInfoByUserId($id) {
        $query = $this->db->placehold("
            SELECT 
                * 
            FROM 
                __users,
                __orders,
                __user_balance
            WHERE 
                __users.id = '" . $id . "'
            AND 
                __orders.user_id = __users.id
            AND 
                __user_balance.user_id = __users.id
            AND
                __user_balance.zaim_date > __orders.date
            ORDER 
                BY __orders.date DESC
            LIMIT 1
        ");
        $this->db->query($query);
        return $this->db->result();
    }

}
