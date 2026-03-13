<?php

require_once 'View.php';

class LeadReportView extends View
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
            
            $query_scorista = '';
            if ($filter_scorista = $this->request->get($this->scorings::TYPE_SCORISTA))
            {
                $this->design->assign('filter_scorista', $filter_scorista);
                
                switch ($filter_scorista):
                    
                    case '0-449':
                        $query_scorista = $this->db->placehold("
                            AND o.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball < 450
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                    case '450-599':
                        $query_scorista = $this->db->placehold("
                            AND o.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 449
                                AND scorista_ball < 600
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                    case '600-699':
                        $query_scorista = $this->db->placehold("
                            AND o.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 599
                                AND scorista_ball < 700
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                    case '700+':
                        $query_scorista = $this->db->placehold("
                            AND o.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 699
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                endswitch;
                
                
            }
            
            $report = array();        
            $dfo = new DateTime($date_from);
            $dto = new DateTime($date_to);

            while ($dfo <= $dto)
            {
                $report[$dfo->format('Ymd')] = new StdClass();
                $report[$dfo->format('Ymd')]->u_date = $dfo->format('d.m.Y');
                $dfo->add(new DateInterval('P1D'));
            }

            
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.accept_date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) >= ?
                AND DATE(o.accept_date) <= ?
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->in_work = $result->o_total;
            }

            
            // фссп 
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.accept_date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) >= ?
                AND DATE(o.accept_date) <= ?
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->fssp = $result->o_total;
            }
            
            // contact
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.accept_date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) >= ?
                AND DATE(o.accept_date) <= ?
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->contact = $result->o_total;
            }
            
            
            // actual
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.accept_date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) >= ?
                AND DATE(o.accept_date) <= ?
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                AND (o.reason_id IS NULL OR o.reason_id != 4)
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->actual = $result->o_total;
            }
            
    
            // scorista
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.accept_date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) >= ?
                AND DATE(o.accept_date) <= ?
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                AND (o.reason_id IS NULL OR o.reason_id != 4)
                AND (o.reason_id IS NULL OR o.reason_id != 5)
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->scorista = $result->o_total;
            }
            
    
            // цель
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.accept_date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.approve_date) >= ?
                AND DATE(o.approve_date) <= ?
                AND DATE(o.accept_date) >= ?
                AND DATE(o.accept_date) <= ?
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to, $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->target = $result->o_total;
            }
            
    
            // выдача
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.accept_date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.confirm_date) >= ?
                AND DATE(o.confirm_date) <= ?
                AND DATE(o.accept_date) >= ?
                AND DATE(o.accept_date) <= ?
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to, $date_from, $date_to);

            $this->db->query($query);

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->credit = $result->o_total;
            }
            
            // выдача
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.confirm_date) >= ?
                AND DATE(o.confirm_date) <= ?
                AND DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to, $date_from, $date_to);

            $this->db->query($query);

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->today = $result->o_total;
            }
            
            
            // не отказ и не выдача
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.accept_date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) >= ?
                AND DATE(o.accept_date) <= ?
                AND (DATE(o.confirm_date) != DATE(o.accept_date) OR o.confirm_date IS NULL)
                AND (DATE(o.reject_date) != DATE(o.accept_date) OR o.reject_date IS NULL)
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);

            $this->db->query($query);

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->notend = $result->o_total;
            }
            
    
            $this->design->assign('report', $report);
    
        }
        elseif ($day = $this->request->get('day'))
        {

            $day_format = date('Y-m-d', strtotime($day));
        	
            $query_scorista = '';
            if ($filter_scorista = $this->request->get($this->scorings::TYPE_SCORISTA))
            {
                $this->design->assign('filter_scorista', $filter_scorista);
                
                switch ($filter_scorista):
                    
                    case '0-449':
                        $query_scorista = $this->db->placehold("
                            AND o.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball < 450
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                    case '450-599':
                        $query_scorista = $this->db->placehold("
                            AND o.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 449
                                AND scorista_ball < 600
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                    case '600-699':
                        $query_scorista = $this->db->placehold("
                            AND o.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 599
                                AND scorista_ball < 700
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                    case '700+':
                        $query_scorista = $this->db->placehold("
                            AND o.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 699
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                endswitch;
                
                
            }

            $report = array();

            $this->db->query("
                SELECT 
                    o.utm_source,
                    o.webmaster_id,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) = ?
                $query_scorista
                GROUP BY o.utm_source, o.webmaster_id
            ", $day_format);
                        
            if ($results = $this->db->results())
            {
                foreach ($results as $result)
                {
                    if ($result->utm_source == 'sms')
                        $result->utm_source = $result->utm_source.'_'.$result->webmaster_id;
                    
                    if (!isset($report[$result->utm_source]))
                    {
                        $report[$result->utm_source] = new StdClass();
                        $report[$result->utm_source]->in_work = 0;
                    }
                    
                    $report[$result->utm_source]->in_work += $result->o_total;
                }                
            }

            $this->db->query("
                SELECT 
                    o.utm_source,
                    o.webmaster_id,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) = ?
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                $query_scorista
                GROUP BY o.utm_source, o.webmaster_id
            ", $day_format);

            if ($results = $this->db->results())
            {
                foreach ($results as $result)
                {
                    if ($result->utm_source == 'sms')
                        $result->utm_source = $result->utm_source.'_'.$result->webmaster_id;
                    
                    if (!isset($report[$result->utm_source]))
                        $report[$result->utm_source] = new StdClass();
                    if (!isset($report[$result->utm_source]->fssp))
                        $report[$result->utm_source]->fssp = 0;
                    
                    $report[$result->utm_source]->fssp += $result->o_total;
                }
            }
            
            $this->db->query("
                SELECT 
                    o.utm_source,
                    o.webmaster_id,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) = ?
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                $query_scorista
                GROUP BY o.utm_source, o.webmaster_id
            ", $day_format);

            if ($results = $this->db->results())
            {
                foreach ($results as $result)
                {
                    if ($result->utm_source == 'sms')
                        $result->utm_source = $result->utm_source.'_'.$result->webmaster_id;
                    
                    if (!isset($report[$result->utm_source]))
                        $report[$result->utm_source] = new StdClass();
                    if (!isset($report[$result->utm_source]->contact))
                        $report[$result->utm_source]->contact = 0;
                    
                    $report[$result->utm_source]->contact += $result->o_total;
                }
            }

            $this->db->query("
                SELECT 
                    o.utm_source,
                    o.webmaster_id,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) = ?
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                AND (o.reason_id IS NULL OR o.reason_id != 4)
                $query_scorista
                GROUP BY o.utm_source, o.webmaster_id
            ", $day_format);

            if ($results = $this->db->results())
            {
                foreach ($results as $result)
                {
                    if ($result->utm_source == 'sms')
                        $result->utm_source = $result->utm_source.'_'.$result->webmaster_id;
                    
                    if (!isset($report[$result->utm_source]))
                        $report[$result->utm_source] = new StdClass();
                    if (!isset($report[$result->utm_source]->actual))
                        $report[$result->utm_source]->actual = 0;
                    
                    $report[$result->utm_source]->actual += $result->o_total;
                }
            }

            $this->db->query("
                SELECT 
                    o.utm_source,
                    o.webmaster_id,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) = ?
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                AND (o.reason_id IS NULL OR o.reason_id != 4)
                AND (o.reason_id IS NULL OR o.reason_id != 5)
                $query_scorista
                GROUP BY o.utm_source, o.webmaster_id
            ", $day_format);

            if ($results = $this->db->results())
            {
                foreach ($results as $result)
                {
                    if ($result->utm_source == 'sms')
                        $result->utm_source = $result->utm_source.'_'.$result->webmaster_id;
                    
                    if (!isset($report[$result->utm_source]))
                        $report[$result->utm_source] = new StdClass();
                    if (!isset($report[$result->utm_source]->scorista))
                        $report[$result->utm_source]->scorista = 0;
                    
                    $report[$result->utm_source]->scorista += $result->o_total;
                }
            }


            $this->db->query("
                SELECT 
                    o.utm_source,
                    o.webmaster_id,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.approve_date) = ?
                AND DATE(o.accept_date) = ?
                $query_scorista
                GROUP BY o.utm_source, o.webmaster_id
            ", $day_format, $day_format);

            if ($results = $this->db->results())
            {
                foreach ($results as $result)
                {
                    if ($result->utm_source == 'sms')
                        $result->utm_source = $result->utm_source.'_'.$result->webmaster_id;
                    
                    if (!isset($report[$result->utm_source]))
                        $report[$result->utm_source] = new StdClass();
                    if (!isset($report[$result->utm_source]->target))
                        $report[$result->utm_source]->target = 0;
                    
                    $report[$result->utm_source]->target += $result->o_total;
                }
            }


            $query = $this->db->placehold("
                SELECT 
                    o.utm_source,
                    o.webmaster_id,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.confirm_date) = ?
                AND DATE(o.accept_date) = ?
                $query_scorista
                GROUP BY o.utm_source, o.webmaster_id
            ", $day_format, $day_format);
            $this->db->query($query);
            if ($results = $this->db->results())
            {
                foreach ($results as $result)
                {
                    if ($result->utm_source == 'sms')
                        $result->utm_source = $result->utm_source.'_'.$result->webmaster_id;
                    
                    if (!isset($report[$result->utm_source]))
                        $report[$result->utm_source] = new StdClass();
                    if (!isset($report[$result->utm_source]->credit))
                        $report[$result->utm_source]->credit = 0;
                    
                    $report[$result->utm_source]->credit += $result->o_total;
                }
            }
            
            $query = $this->db->placehold("
                SELECT 
                    o.utm_source,
                    o.webmaster_id,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) = ?
                AND DATE(o.confirm_date) = ?
                $query_scorista
                GROUP BY o.utm_source, o.webmaster_id
            ", $day_format, $day_format);
            $this->db->query($query);
            if ($results = $this->db->results())
            {
                foreach ($results as $result)
                {
                    if ($result->utm_source == 'sms')
                        $result->utm_source = $result->utm_source.'_'.$result->webmaster_id;
                    
                    if (!isset($report[$result->utm_source]))
                        $report[$result->utm_source] = new StdClass();
                    if (!isset($report[$result->utm_source]->today))
                        $report[$result->utm_source]->today = 0;
                    
                    $report[$result->utm_source]->today += $result->o_total;
                }
            }
            
            // не отказ и не выдача
            $query = $this->db->placehold("
                SELECT 
                    o.utm_source,
                    o.webmaster_id,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.accept_date) = ?
                AND (DATE(o.confirm_date) != DATE(o.accept_date) OR o.confirm_date IS NULL)
                AND (DATE(o.reject_date) != DATE(o.accept_date) OR o.reject_date IS NULL)
                $query_scorista
                GROUP BY o.utm_source, o.webmaster_id
            ", $day_format, $day_format);

            $this->db->query($query);

            if ($results = $this->db->results())
            {
                foreach ($results as $result)
                {
                    if ($result->utm_source == 'sms')
                        $result->utm_source = $result->utm_source.'_'.$result->webmaster_id;
                    
                    if (!isset($report[$result->utm_source]))
                        $report[$result->utm_source] = new StdClass();
                    if (!isset($report[$result->utm_source]->notend))
                        $report[$result->utm_source]->notend = 0;
                    
                    $report[$result->utm_source]->notend += $result->o_total;
                }
            }

            
            $this->design->assign('details', $report);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($report);echo '</pre><hr />';
        }
        
        return $this->design->fetch('lead_report.tpl');
    }
    
}