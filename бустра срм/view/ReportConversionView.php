<?php
ini_set('max_execution_time', 10);
require_once 'View.php';

class ReportConversionView extends View
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
            
            
            
            $reports = [];
            
            $report = new StdClass;
            $report->weeks = $this->get_weeks($date_from, $date_to);            
            $report->type = 'nk';
            $this->calc_issuance($report);
            $reports[] = $report;

            $report = new StdClass;
            $report->weeks = $this->get_weeks($date_from, $date_to);            
            $report->type = 'pk';
            $this->calc_issuance($report);
            $reports[] = $report;

            $report = new StdClass;
            $report->weeks = $this->get_weeks($date_from, $date_to);            
            $report->type = 'prolongation';
            $this->calc_prolongations($report);
            $reports[] = $report;


            $this->design->assign('reports', $reports);
    
        }
        
        return $this->design->fetch('report_conversion.tpl');
    }
    
    private function calc_issuance(&$report)
    {
        $have_close_credits = $report->type == 'nk' ? 0 : 1;
        
        foreach ($report->weeks as &$week)
        {
            $week['total_clients'] = 0;
            $week['agreed_clients'] = 0;
            $week['cv_agreed_clients'] = 0;
            $week['complete_clients'] = 0;
            $week['cv_complete_clients'] = 0;
            $week['total_amount'] = 0;
            $week['average_amount'] = 0;
            
            
            $this->db->query("
                SELECT 
                    COUNT(p2p.id) AS total_clients
                FROM b2p_p2pcredits AS p2p
                INNER JOIN s_orders AS o
                ON o.id = p2p.order_id
                WHERE 
                    p2p.status = 'APPROVED'
                    AND DATE(p2p.complete_date) >= ?
                    AND DATE(p2p.complete_date) <= ?
                    AND o.have_close_credits = ?
                    AND o.b2p = 1
            ", $week['start'], $week['end'], $have_close_credits);
            $result = $this->db->result();
            
            $week['total_clients'] = $result->total_clients;

            $query = $this->db->placehold("
                SELECT 
                    COUNT(p2p.id) AS agreed_clients
                FROM b2p_p2pcredits AS p2p
                LEFT JOIN s_orders AS o
                ON o.id = p2p.order_id
                LEFT JOIN s_credit_doctor_to_user AS cd
                ON p2p.order_id = cd.order_id 
                WHERE 
                    p2p.status = 'APPROVED'
                    AND DATE(p2p.complete_date) >= ?
                    AND DATE(p2p.complete_date) <= ?
                    AND o.have_close_credits = ?
                    AND o.b2p = 1
                    AND cd.id IS NOT NULL
            ", $week['start'], $week['end'], $have_close_credits);
            $this->db->query($query);
            $result = $this->db->result();

            $week['agreed_clients'] = $result->agreed_clients;
            $week['cv_agreed_clients'] = round($week['agreed_clients'] / $week['total_clients'] * 100, 2);

            
            
            $query = $this->db->placehold("
                SELECT 
                    COUNT(p2p.id) AS complete_clients,
                    SUM(cd.amount) AS total_amount
                FROM b2p_p2pcredits AS p2p
                LEFT JOIN s_orders AS o
                ON o.id = p2p.order_id
                LEFT JOIN s_credit_doctor_to_user AS cd
                ON p2p.order_id = cd.order_id 
                WHERE 
                    p2p.status = 'APPROVED'
                    AND DATE(p2p.complete_date) >= ?
                    AND DATE(p2p.complete_date) <= ?
                    AND o.have_close_credits = ?
                    AND o.b2p = 1
                    AND cd.id IS NOT NULL
                    AND cd.status = 'SUCCESS'
            ", $week['start'], $week['end'], $have_close_credits);
            $this->db->query($query);
            $result = $this->db->result();

            $week['complete_clients'] = $result->complete_clients;
            $week['cv_complete_clients'] = round($week['complete_clients'] / $week['total_clients'] * 100, 2);
            $week['total_amount'] = $result->total_amount;
            $week['average_amount'] = round($result->total_amount / $week['complete_clients']);
            
        }
        
    }
    
    
    private function calc_prolongations(&$report)
    {
        foreach ($report->weeks as &$week)
        {
            $week['total_clients'] = 0;
            $week['agreed_clients'] = 0;
            $week['cv_agreed_clients'] = 0;
            $week['complete_clients'] = 0;
            $week['cv_complete_clients'] = 0;
            $week['total_amount'] = 0;
            $week['average_amount'] = 0;

            $query = $this->db->placehold("
                SELECT 
                    COUNT(pay.id) AS total_clients
                FROM b2p_payments AS pay
                WHERE 
                    pay.reason_code = 1
                    AND pay.prolongation = 1
                    AND DATE(pay.created) >= ?
                    AND DATE(pay.created) <= ?
            ", $week['start'], $week['end']);
            $this->db->query($query);
            $result = $this->db->result();

            $week['total_clients'] = $result->total_clients;

            $query = $this->db->placehold("
                SELECT 
                    COUNT(pay.id) AS agreed_clients
                FROM b2p_payments AS pay
                LEFT JOIN s_multipolis AS m
                ON pay.id = m.payment_id
                AND m.payment_method = 'B2P'
                WHERE 
                    pay.reason_code = 1
                    AND pay.prolongation = 1
                    AND DATE(pay.created) >= ?
                    AND DATE(pay.created) <= ?
                    AND m.id IS NOT NULL
            ", $week['start'], $week['end']);
            $this->db->query($query);
            $result = $this->db->result();

            $week['agreed_clients'] = $result->agreed_clients;
            $week['cv_agreed_clients'] = round($week['agreed_clients'] / $week['total_clients'] * 100, 2);

            $query = $this->db->placehold("
                SELECT 
                    COUNT(pay.id) AS complete_clients,
                    SUM(m.amount) AS total_amount
                FROM b2p_payments AS pay
                LEFT JOIN s_multipolis AS m
                ON pay.id = m.payment_id
                AND m.payment_method = 'B2P'
                WHERE 
                    pay.reason_code = 1
                    AND pay.prolongation = 1
                    AND DATE(pay.created) >= ?
                    AND DATE(pay.created) <= ?
                    AND m.id IS NOT NULL
                    AND m.status = 'SUCCESS'
            ", $week['start'], $week['end']);
            $this->db->query($query);
            $result = $this->db->result();

            $week['complete_clients'] = (int)$result->complete_clients;
            $week['cv_complete_clients'] = $week['total_clients'] > 0 ? round($week['complete_clients'] / $week['total_clients'] * 100, 2) : 0;
            $week['total_amount'] = $result->total_amount;
            $week['average_amount'] = $week['complete_clients'] > 0 ? round($result->total_amount / $week['complete_clients']) : 0;
        }
    }
    
    private function get_weeks($date_from, $date_to)
    {
        $weeks = [];
        $dt_from = new DateTime($date_from);
        $dt_to = new DateTime($date_to);
        
        
        while ($dt_from <= $dt_to)
        {
            $week = ['start' => $dt_from->format('Y-m-d')];
            if ($dt_from->format('l') != 'Sunday')
            {
                $dt_from->modify('next sunday');
                if ($dt_from > $dt_to)
                    $dt_from->setDate($dt_to->format('Y'), $dt_to->format('m'), $dt_to->format('d'));
            }
            $week['end'] = $dt_from->format('Y-m-d');
            $dt_from->modify('next day');                    
            $weeks[] = $week;
        }
        
        return $weeks;
    }
    
}