<?php

require_once 'Simpla.php';

use api\traits\setPages;
use api\traits\Sorts;

class Appeals extends Simpla {

    use setPages,
        Sorts;

    public function chekAppeal($data) {
        $query = $this->db->placehold("
            SELECT
                *
            FROM
                __appeals
            WHERE
                Them = '" . str_replace('Re: ', '', $data['Them']) . "'
            AND
                Email = '" . $data['Email'] . "'
            AND
                ToEmail = '" . $data['ToEmail'] . "'
            AND 
                AppealDate > '" . date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 30) . "'
            "
        );
        $this->db->query($query);
        return $this->db->result();
    }

    public function getApeals() {
        $query = $this->db->placehold("
            SELECT
                *
            FROM
                __appeals
            " . $this->sortApeals()
        );
        $query .= $this->getLimit($query);
        $this->db->query($query);
        return $this->db->results();
    }

    public function getApeal($id) {
        $query = $this->db->placehold("
            SELECT
                *
            FROM
                __appeals
            WHERE
                Id = " . (int) $id . "
            "
        );
        $query .= $this->getLimit($query);
        $this->db->query($query);
        return $this->db->result();
    }

    public function updateApeal($filds, $id) {
        $query = $this->db->placehold("
            UPDATE __appeals SET ?% WHERE id = ?
        ", (array) $filds, (int) $id);
        $this->db->query($query);
        return $id;
    }

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
