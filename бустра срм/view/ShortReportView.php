<?php

require_once 'View.php';

class ShortReportView extends View
{
    public function fetch()
    {
        if ($daterange = $this->request->get('daterange'))
        {
            list($from, $to) = explode('-', $daterange);
            
        	$date_from = date('Y-m-d', strtotime($from));
        	$date_to = date('Y-m-d', strtotime($to));
            
            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);
            
            $report = new StdClass();        
            
            $query = $this->db->placehold("
                SELECT 
                    COUNT(o.id) AS totals
                FROM __orders AS o
                LEFT JOIN s_users u ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND u.additional_data_added = 1
                AND DATE(o.date) <= ?

            ", $date_from, $date_to);
            $this->db->query($query);            
            $report->totals = $this->db->result('totals');

            $query = $this->db->placehold("
                SELECT 
                    COUNT(o.id) AS total_pk
                FROM __orders AS o
                LEFT JOIN s_users u ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND o.have_close_credits = 1
                 AND u.additional_data_added = 1
                AND DATE(o.date) <= ?
            ", $date_from, $date_to);
            $this->db->query($query);            
            $report->total_pk = $this->db->result('total_pk');

            
            $query = $this->db->placehold("
                SELECT 
                    COUNT(o.id) AS aproove_pk
                FROM __orders AS o
                LEFT JOIN s_users u ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND u.additional_data_added = 1
                AND o.have_close_credits = 1
                AND DATE(o.date) <= ?
                AND o.status = 2
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $report->aproove_pk = $this->db->result('aproove_pk');


            $query = $this->db->placehold("
                SELECT 
                    COUNT(o.id) AS total_nk
                FROM __orders AS o
                LEFT JOIN s_users u ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND u.additional_data_added = 1
                AND (o.have_close_credits = 0 OR o.have_close_credits IS NULL)
                AND o.first_loan = 1
                AND DATE(o.date) <= ?
            ", $date_from, $date_to);
            $this->db->query($query);            
            $report->total_nk = $this->db->result('total_nk');

            
            $query = $this->db->placehold("
                SELECT 
                    COUNT(o.id) AS aproove_nk
                FROM __orders AS o
                LEFT JOIN s_users u ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND u.additional_data_added = 1
                AND (o.have_close_credits = 0 OR o.have_close_credits IS NULL)
                AND o.first_loan = 1
                AND DATE(o.date) <= ?
                AND o.status = 2
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $report->aproove_nk = $this->db->result('aproove_nk');


            $query = $this->db->placehold("
                SELECT 
                    COUNT(o.id) AS total_repeat
                FROM __orders AS o
                LEFT JOIN s_users u ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND u.additional_data_added = 1
                AND (o.have_close_credits = 0 OR o.have_close_credits IS NULL)
                AND (o.first_loan = 0 OR o.first_loan IS NULL)
                AND DATE(o.date) <= ?
            ", $date_from, $date_to);
            $this->db->query($query);            
            $report->total_repeat = $this->db->result('total_repeat');

            
            $query = $this->db->placehold("
                SELECT 
                    COUNT(o.id) AS aproove_repeat
                FROM __orders AS o
                LEFT JOIN s_users u ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND u.additional_data_added = 1
                AND (o.have_close_credits = 0 OR o.have_close_credits IS NULL)
                AND (o.first_loan = 0 OR o.first_loan IS NULL)
                AND DATE(o.date) <= ?
                AND o.status = 2
            ", $date_from, $date_to);
            $this->db->query($query);
            $report->aproove_repeat = $this->db->result('aproove_repeat');
            

            

            
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($report);echo '</pre><hr />';    
            $this->design->assign('report', $report);
    
        }
        
        return $this->design->fetch('short_report.tpl');
    }
    
}