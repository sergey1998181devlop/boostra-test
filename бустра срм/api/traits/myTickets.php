<?php

namespace api\traits;

use api\traits\setPages;

trait myTickets {

    use setPages;

    public function getAnket($id) {
        $query = $this->db->placehold("
            SELECT *
            FROM __myAnket
            WHERE ticketId = ?
        ", (int) $id);
        $this->db->query($query);
        $result = $this->db->results();
        return $result;
    }

    public function getTicketComments($id) {
        $query = $this->db->placehold("
            SELECT *
            FROM __myTicketComments
            WHERE tiсketId = ?
            ORDER BY id DESC
        ", (int) $id);
        $this->db->query($query);
        $result = $this->db->results();
        return $result;
    }

    public function getTicketComment($id) {
        $query = $this->db->placehold("
            SELECT *
            FROM __myTicketComments
            WHERE tiсketId = ?
            ORDER BY id DESC
        ", (int) $id);
        $this->db->query($query);
        $result = $this->db->result();
        return $result;
    }

    public function getMyTicket($id) {
        $query = $this->db->placehold("
            SELECT *
            FROM __myTicket
            WHERE id = ?
        ", (int) $id);
        $this->db->query($query);
        $result = $this->db->result();
        return $result;
    }

    public function getMyTickets($managerId) {
        $query = $this->db->placehold("
            SELECT *
            FROM __myTicket
            WHERE managerId = ?
            ORDER BY id DESC 
        ", (int) $managerId);
        $query .= $this->getLimit($query);
        $this->db->query($query);
        $result = $this->db->results();
        return $result;
    }

    public function getMyTicketByAppeal($appealNumber) {
        $query = $this->db->placehold("
            SELECT *
            FROM __myTicket
            WHERE appealNumber = ?
            ORDER BY id DESC 
        ", (int) $appealNumber);
        $query .= $this->getLimit($query);
        $this->db->query($query);
        $result = $this->db->result();
        return $result;
    }

    public function createNewTicket($data) {
        $sourche = $this->getMyTicketByAppeal($data['appealNumber']);
        if (!$sourche) {
            $query = $this->db->placehold("
            INSERT INTO __myTicket SET ?%
        ", (array) $data);
            $this->db->query($query);
            $id = $this->db->insert_id();
            return $id;
        }
        return $sourche->id;
    }

    public function addMyTicketComment($data) {
        $query = $this->db->placehold("
            INSERT INTO __myTicketComments SET comment='" .
                $data['comment'] . "', managerId='" .
                $data['managerId'] . "', tiсketId='" . $data['tiсketId'] . "'");
        $this->db->query($query);
        $id = $this->db->insert_id();
        return $id;
    }

    public function updateMyTicket($ticket, $id) {
        $query = $this->db->placehold("
            UPDATE __myTicket SET ?% WHERE id = ?
        ", (array) $ticket, (int) $id);
        $this->db->query($query);
        return $id;
    }

}
