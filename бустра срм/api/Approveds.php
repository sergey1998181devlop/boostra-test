<?php

require_once 'Simpla.php';

use api\traits\setPages;
use api\traits\Sorts;

class Approveds extends Simpla {

    use setPages,
        Sorts;

    public function approvedLoans($date_from, $date_to) {
        $query = $this->db->placehold("
            SELECT *
            FROM __orders
            WHERE `approve_date` IS NOT NULL
            AND DATE(date) >= ?
            AND DATE(date) <= ?
            ORDER BY `date` DESC
        ", $date_from, $date_to);

        $query .= $this->getLimit($query);
        $this->db->query($query);
        return $this->db->results();
    }
}
