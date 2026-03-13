<?php

require_once 'Simpla.php';

class Recurrents extends Simpla
{
    public function get_recurrent($params)
    {
		$filter = [];
        
        if (!empty($params))
            foreach ($params as $param_key => $param_value)
                $filter[] = $this->db->placehold($param_key.' = ?', $param_value);
        $where = implode(' AND ', $filter);
        
        $query = $this->db->placehold("
            SELECT * 
            FROM __recurrents
            WHERE $where
        ");
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;        
    }    
    
    public function add_recurrent($recurrent)
    {
        $this->db->query("
            INSERT INTO s_recurrents
            SET ?%
        ", $recurrent);
        $insert_id = $this->db->insert_id();
        
        return $insert_id;        
    }
    
    public function get_list_for_report()
    {
        $this->db->query("
            SELECT * FROM s_recurrents_list
            WHERE loaded = 2
            AND sent_1c = 0
            AND id NOT IN (
                SELECT list_id FROM s_recurrents WHERE status = 0
            )
            LIMIT 1
        ");
        if ($list = $this->db->result()) {
            $list->recurrents = [];
            
            $this->db->query("
                SELECT * FROM s_recurrents
                WHERE list_id = ?
            ", $list->id);
            foreach ($this->db->results() as $r){
                $r->payments = [];
                $list->recurrents[$r->id] = $r;
            }
            
            if (!empty($list->recurrents)) {
                $this->db->query("
                    SELECT p.* FROM b2p_payments AS p
                    WHERE p.recurrent_id IN (?@)
                ", array_keys($list->recurrents));
            
                foreach ($this->db->results() as $p) {
                    $list->recurrents[$p->recurrent_id]->payments[] = $p;
                }
            }
        }
        
        return $list;
    }
    
    public function get_list($params)
    {
		$filter = [];
        
        if (!empty($params))
            foreach ($params as $param_key => $param_value)
                $filter[] = $this->db->placehold($param_key.' = ?', $param_value);
        $where = implode(' AND ', $filter);
        
        $query = $this->db->placehold("
            SELECT * 
            FROM __recurrents_list
            WHERE $where
        ");
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;        
    }

    public function add_list($list)
    {
		$query = $this->db->placehold("
            INSERT INTO __recurrents_list SET ?%
        ", (array)$list);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_list($id, $list)
    {
		$query = $this->db->placehold("
            UPDATE __recurrents_list SET ?% WHERE id = ?
        ", (array)$list, (int)$id);
        $this->db->query($query);
        
        return $id;
    }

    public function update_reccurent($id, $list)
    {
        $query = $this->db->placehold("
            UPDATE __recurrents SET ?% WHERE id = ?
        ", (array)$list, (int)$id);
        $this->db->query($query);

        return $id;
    }
    
    
}