<?php

require_once 'View.php';

class OrdersReportView extends View
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
            
            $query_source = '';
            $query_visitor_source = '';
            if ($filter_source = $this->request->get('filter_source'))
            {
                $query_source_array = array();
                $query_visitor_source_array = array();
                foreach ($filter_source as $filter_source_item)
                {
                    $q = explode('-', $filter_source_item);
                    $utm_source = $q[0];
                    $webmaster_id = isset($q[1]) ? $q[1] : '';
                    
                    $query_source_array_item = $this->db->placehold('o.utm_source = ?', $utm_source);
                    if (!empty($webmaster_id))
                        $query_source_array_item .= $this->db->placehold(' AND o.webmaster_id = ? ', $webmaster_id);
                    $query_source_array[] = '('.$query_source_array_item.')';
                
                    $query_visitor_source_array_item = $this->db->placehold('v.utm_source = ?', $utm_source);
                    if (!empty($webmaster_id))
                        $query_visitor_source_array_item .= $this->db->placehold(' AND v.webmaster_id = ? ', $webmaster_id);
                
                    $query_visitor_source_array[] = '('.$query_visitor_source_array_item.')';
                }
                if (!empty($query_source_array))
                    $query_source = "AND (".implode(' OR ', $query_source_array).')';
                if (!empty($query_visitor_source_array))
                    $query_visitor_source = "AND (".implode(' OR ', $query_visitor_source_array).')';
                                
                $this->design->assign('filter_source', $filter_source);
            }
            
            
            $report = array();        
            
            $period_from = $date_from;
            $period_to = $date_to;

            do {
                $index = date('Ymd', strtotime($period_from));
                $report[$index] = new StdClass();
                $report[$index]->u_date = $period_from;
                $period_from = date('Y-m-d', strtotime($period_from) + 86400);
            } while (strtotime($period_from) <= strtotime($period_to));

            
            $total = array(
                'orders' => 0,
                'region' => 0,
                'localtime' => 0,
                'age' => 0,
                'blacklist' => 0,
                'juicescore' => 0,
                'fms' => 0,
                'fns' => 0,
                'efrsb' => 0,
                'fssp' => 0,
                'fssp46' => 0,
                'anketa' => 0,
                'scorista550' => 0,
                'scorista550plus' => 0,
                'card550' => 0,
                'card550plus' => 0,
                'getted550' => 0,
                'getted550plus' => 0,
            );
            
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->orders = $result->o_total;
                $total['orders'] += $result->o_total;
            }


            // регион
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14)
                    OR o.reason_id IS NULL)
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->region = $result->o_total;
                $total['region'] += $result->o_total;
            }

            // локальное время
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25)
                    OR o.reason_id IS NULL)
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->localtime = $result->o_total;
                $total['localtime'] += $result->o_total;
            }

            // возраст
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23)
                    OR o.reason_id IS NULL)
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->age = $result->o_total;
                $total['age'] += $result->o_total;
            }

            // ЧС
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2)
                    OR o.reason_id IS NULL)
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->blacklist = $result->o_total;
                $total['blacklist'] += $result->o_total;
            }

            // juicescore
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21)
                    OR o.reason_id IS NULL)
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->juicescore = $result->o_total;
                $total['juicescore'] += $result->o_total;
            }

            // фмс
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9)
                    OR o.reason_id IS NULL)
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->fms = $result->o_total;
                $total['fms'] += $result->o_total;
            }

            // фнс
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9)
                    OR o.reason_id IS NULL)
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->fns = $result->o_total;
                $total['fns'] += $result->o_total;
            }
            
            // банкротство
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND o.have_close_credits = 0
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9, 22)
                    OR o.reason_id IS NULL)
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->efrsb = $result->o_total;
                $total['efrsb'] += $result->o_total;
            }
            
            // фссп
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9, 22, 1)
                    OR o.reason_id IS NULL)
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->fssp = $result->o_total;
                $total['fssp'] += $result->o_total;
            }
            
            // фссп 46
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND o.have_close_credits = 0
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9, 22, 1, 24)
                    OR o.reason_id IS NULL)
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->fssp46 = $result->o_total;
                $total['fssp46'] += $result->o_total;
            }

            // anketa
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9, 22, 1, 24, 12, 7, 19)
                    OR o.reason_id IS NULL)
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->anketa = $result->o_total;
                $total['anketa'] += $result->o_total;
            }

            // scorista
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9, 22, 1, 24, 12, 7, 19)
                    OR o.reason_id IS NULL)
                AND (
                    SELECT scorista_ball 
                    FROM __scorings AS s
                    WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                    AND status = ".$this->scorings::STATUS_COMPLETED."
                    AND s.user_id = o.user_id
                    ORDER BY s.id DESC
                    LIMIT 1
                ) < 550       
                AND (
                    SELECT scorista_ball 
                    FROM __scorings AS s
                    WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                    AND status = ".$this->scorings::STATUS_COMPLETED."
                    AND s.user_id = o.user_id
                    ORDER BY s.id DESC
                    LIMIT 1
                ) > 469
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->scorista550 = $result->o_total;
                $total['scorista550'] += $result->o_total;
            }
            
            // scorista
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9, 22, 1, 24, 12, 7, 19)
                    OR o.reason_id IS NULL)
                AND (
                    SELECT scorista_ball 
                    FROM __scorings AS s
                    WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                    AND status = ".$this->scorings::STATUS_COMPLETED."
                    AND s.user_id = o.user_id
                    ORDER BY s.id DESC
                    LIMIT 1
                ) >= 550
                
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->scorista550plus = $result->o_total;
                $total['scorista550plus'] += $result->o_total;
            }
            
            // card
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9, 22, 1, 24, 12, 7, 19, 18, 5)
                    OR o.reason_id IS NULL)
                AND (
                    SELECT scorista_ball 
                    FROM __scorings AS s
                    WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                    AND status = ".$this->scorings::STATUS_COMPLETED."
                    AND s.user_id = o.user_id
                    ORDER BY s.id DESC
                    LIMIT 1
                ) < 550       
                AND (
                    SELECT scorista_ball 
                    FROM __scorings AS s
                    WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                    AND status = ".$this->scorings::STATUS_COMPLETED."
                    AND s.user_id = o.user_id
                    ORDER BY s.id DESC
                    LIMIT 1
                ) > 469
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->card550 = $result->o_total;
                $total['card550'] += $result->o_total;
            }

            // card
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9, 22, 1, 24, 12, 7, 19, 18, 5)
                    OR o.reason_id IS NULL)
                AND (
                    SELECT scorista_ball 
                    FROM __scorings AS s
                    WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                    AND status = ".$this->scorings::STATUS_COMPLETED."
                    AND s.user_id = o.user_id
                    ORDER BY s.id DESC
                    LIMIT 1
                ) >= 550
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->card550plus = $result->o_total;
                $total['card550plus'] += $result->o_total;
            }

            // кредит получен
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9, 22, 1, 24, 12, 7, 19, 18, 5)
                    OR o.reason_id IS NULL)
                AND o.credit_getted = 1
                AND (
                    SELECT scorista_ball 
                    FROM __scorings AS s
                    WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                    AND status = ".$this->scorings::STATUS_COMPLETED."
                    AND s.user_id = o.user_id
                    ORDER BY s.id DESC
                    LIMIT 1
                ) < 550       
                AND (
                    SELECT scorista_ball 
                    FROM __scorings AS s
                    WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                    AND status = ".$this->scorings::STATUS_COMPLETED."
                    AND s.user_id = o.user_id
                    ORDER BY s.id DESC
                    LIMIT 1
                ) > 469
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->getted550 = $result->o_total;
                $total['getted550'] += $result->o_total;
            }
            
            // кредит получен
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.have_close_credits = 0
                AND (o.reason_id NOT IN (14, 25, 23, 2, 21, 9, 22, 1, 24, 12, 7, 19, 18, 5)
                    OR o.reason_id IS NULL)
                AND o.credit_getted = 1
                AND (
                    SELECT scorista_ball 
                    FROM __scorings AS s
                    WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                    AND status = ".$this->scorings::STATUS_COMPLETED."
                    AND s.user_id = o.user_id
                    ORDER BY s.id DESC
                    LIMIT 1
                ) >= 550
                $query_source
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->getted550plus = $result->o_total;
                $total['getted550plus'] += $result->o_total;
            }
            


    
            

    
    


//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($report);echo '</pre><hr />';
    
            $this->design->assign('total', $total);
            $this->design->assign('report', $report);
    
        
            $query = $this->db->placehold("
                SELECT DISTINCT o.utm_source, o.webmaster_id
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $sources = array();
            if ($results = $this->db->results())
            {
                foreach ($results as $result)
                {
                    if ($result->utm_source == 'sms')
                        $sources[] = 'sms-'.$result->webmaster_id;
                    if ($result->utm_source == 'leadgid')
                    {
                        if (!in_array('leadgid', $sources))
                            $sources[] = 'leadgid';
                        $sources[] = 'leadgid-'.$result->webmaster_id;
                    }
                    else
                        $sources[] = $result->utm_source;
                }
            }
            $sources = array_unique($sources);
            sort($sources);
            $this->design->assign('sources', $sources);
        }
        else
        {
            if (!empty($_SESSION['filter_scorista']))
            {
                $filter_scorista = unserialize($_SESSION['filter_scorista']);
            }
            else
            {
                $filter_scorista = array(
                    'from' => array(
                        '0',
                        '450',
                        '600',
                        '700',
                    ),
                    'to' => array(
                        '449',
                        '599',
                        '699',
                        '1000',
                    ),
                );
            }
            $this->design->assign('filter_scorista', $filter_scorista);
        }
        
        
        return $this->design->fetch('orders_report.tpl');
    }
    
}