<?php

require_once 'View.php';

class CvReportView extends View
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
            $query_scorista_referrals = '';
            if ($filter_scorista = $this->request->get('scorista'))
            {
                $this->design->assign('filter_scorista', $filter_scorista);
                
                switch ($filter_scorista):
                    
                    case '0-449':
                        $query_scorista = $this->db->placehold("
                            AND u.id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball < 450
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                        $query_scorista_referrals = $this->db->placehold("
                            AND r.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball < 450
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                    case '450-599':
                        $query_scorista = $this->db->placehold("
                            AND u.id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 449
                                AND scorista_ball < 600
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                        $query_scorista_referrals = $this->db->placehold("
                            AND r.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 449
                                AND scorista_ball < 600
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                    case '600-699':
                        $query_scorista = $this->db->placehold("
                            AND u.id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 599
                                AND scorista_ball < 700
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                        $query_scorista_referrals = $this->db->placehold("
                            AND r.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 599
                                AND scorista_ball < 700
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                    case '700+':
                        $query_scorista = $this->db->placehold("
                            AND u.id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 699
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                        $query_scorista_referrals = $this->db->placehold("
                            AND r.user_id IN (
                                SELECT user_id FROM __scorings
                                WHERE type = ?
                                AND scorista_ball > 699
                            )
                        ", $this->scorings::TYPE_SCORISTA);
                    break;
                    
                endswitch;
                
                
            }


            $report = array();        
            
            $query = $this->db->placehold("
                SELECT 
                    DATE(r.created) AS u_date,
                    COUNT(r.id) AS r_total
                FROM __referrals AS r
                WHERE DATE(r.created) >= ?
                AND DATE(r.created) <= ?
                $query_scorista_referrals
                GROUP BY u_date
            ", $date_from, $date_to);
/*
            $query = $this->db->placehold("
                SELECT 
                    DATE(u.created) AS u_date,
                    COUNT(u.id) AS u_total,
                    COUNT(o.id) AS o_total
                FROM __users AS u
                LEFT JOIN __orders AS o
                ON u.id = o.user_id
                WHERE DATE(u.created) >= ?
                AND DATE(u.created) <= ?
                GROUP BY u_date
            ", $date_from, $date_to);
*/
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                if (!isset($report[$index]))
                    $report[$index] = $result;
            }
            

            $query = $this->db->placehold("
                SELECT 
                    DATE(u.created) AS u_date,
                    COUNT(u.id) AS u_total,
                    COUNT(o.id) AS o_total
                FROM __users AS u
                LEFT JOIN __orders AS o
                ON u.id = o.user_id
                WHERE DATE(u.created) >= ?
                AND DATE(u.created) <= ?
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->o_total = $result->o_total;
                $report[$index]->u_total = $result->u_total;
            }


            $query = $this->db->placehold("
                SELECT 
                    DATE(u.created) AS u_date,
                    COUNT(u.id) AS u_total,
                    COUNT(o.id) AS o_total
                FROM __users AS u
                LEFT JOIN __orders AS o
                ON u.id = o.user_id
                AND (o.have_close_credits = 0)
                WHERE DATE(u.created) >= ?
                AND DATE(u.created) <= ?
                AND u.card_added = 1
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->o_total = $result->o_total;
            }

            // повторные заявки
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM  __orders AS o
                LEFT JOIN __users AS u
                ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.have_close_credits = 1)
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->o_total_repeat = $result->o_total;
            }



            // автопроверки
            $query = $this->db->placehold("
                SELECT 
                    DATE(u.created) AS u_date,
                    COUNT(u.id) AS u_total,
                    COUNT(o.id) AS o_total
                FROM __users AS u
                LEFT JOIN __orders AS o
                ON u.id = o.user_id
                WHERE DATE(u.created) >= ?
                AND DATE(u.created) <= ?
                AND (o.have_close_credits = 0)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND u.card_added = 1
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->auto = $result->o_total;
            }

            // повторные заявки
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM  __orders AS o
                LEFT JOIN __users AS u
                ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.have_close_credits = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->auto_repeat = $result->o_total;
            }

            
            // фссп 
            $query = $this->db->placehold("
                SELECT 
                    DATE(u.created) AS u_date,
                    COUNT(u.id) AS u_total,
                    COUNT(o.id) AS o_total
                FROM __users AS u
                LEFT JOIN __orders AS o
                ON u.id = o.user_id
                WHERE DATE(u.created) >= ?
                AND DATE(u.created) <= ?
                AND (o.have_close_credits = 0)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND u.card_added = 1
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->fms = $result->o_total;
            }

            // повторные заявки
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM  __orders AS o
                LEFT JOIN __users AS u
                ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.have_close_credits = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->fms_repeat = $result->o_total;
            }

            
            // contact
            $query = $this->db->placehold("
                SELECT 
                    DATE(u.created) AS u_date,
                    COUNT(u.id) AS u_total,
                    COUNT(o.id) AS o_total,
                    AVG(TIMESTAMP(o.call_date) - TIMESTAMP(o.accept_date)) AS diff
                FROM __users AS u
                LEFT JOIN __orders AS o
                ON u.id = o.user_id
                WHERE DATE(u.created) >= ?
                AND DATE(u.created) <= ?
                AND (o.have_close_credits = 0)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                AND u.card_added = 1
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->contact = $result->o_total;
                $diff_arr = $this->secToArray($result->diff);
                $report[$index]->diff = $diff_arr['hours'].':'.$diff_arr['minutes'].':'.$diff_arr['secs'];
            }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($report[$index]->diff);echo '</pre><hr />';            
            // повторные заявки
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM  __orders AS o
                LEFT JOIN __users AS u
                ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.have_close_credits = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
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
                $report[$index]->contact_repeat = $result->o_total;
            }

            
            // actual
            $query = $this->db->placehold("
                SELECT 
                    DATE(u.created) AS u_date,
                    COUNT(u.id) AS u_total,
                    COUNT(o.id) AS o_total
                FROM __users AS u
                LEFT JOIN __orders AS o
                ON u.id = o.user_id
                WHERE DATE(u.created) >= ?
                AND DATE(u.created) <= ?
                AND (o.have_close_credits = 0)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                AND (o.reason_id IS NULL OR o.reason_id != 4)
                AND u.card_added = 1
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
            
            // повторные заявки
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM  __orders AS o
                LEFT JOIN __users AS u
                ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.have_close_credits = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
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
                $report[$index]->actual_repeat = $result->o_total;
            }

    
            // scorista
            $query = $this->db->placehold("
                SELECT 
                    DATE(u.created) AS u_date,
                    COUNT(u.id) AS u_total,
                    COUNT(o.id) AS o_total
                FROM __users AS u
                LEFT JOIN __orders AS o
                ON u.id = o.user_id
                WHERE DATE(u.created) >= ?
                AND DATE(u.created) <= ?
                AND (o.have_close_credits = 0)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                AND (o.reason_id IS NULL OR o.reason_id != 4)
                AND (o.reason_id IS NULL OR o.reason_id != 5)
                AND u.card_added = 1
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
            
            // повторные заявки
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM  __orders AS o
                LEFT JOIN __users AS u
                ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.have_close_credits = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
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
                $report[$index]->scorista_repeat = $result->o_total;
            }

    
            // цель
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(u.id) AS u_total,
                    COUNT(o.id) AS o_total
                FROM __users AS u
                LEFT JOIN __orders AS o
                ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND DATE(o.approve_date) >= ?
                AND DATE(o.approve_date) <= ?
                AND (o.have_close_credits = 0)
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

            // повторные заявки
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM  __orders AS o
                LEFT JOIN __users AS u
                ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.have_close_credits = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                AND (o.reason_id IS NULL OR o.reason_id != 4)
                AND (o.reason_id IS NULL OR o.reason_id != 5)
                AND DATE(o.approve_date) >= ?
                AND DATE(o.approve_date) <= ?
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to, $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->target_repeat = $result->o_total;
            }
            
    
            // выдача
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(u.id) AS u_total,
                    COUNT(o.id) AS o_total
                FROM __users AS u
                LEFT JOIN __orders AS o
                ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND DATE(o.confirm_date) >= ?
                AND DATE(o.confirm_date) <= ?
                AND (o.have_close_credits = 0)
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
            
            // повторные заявки
            $query = $this->db->placehold("
                SELECT 
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM  __orders AS o
                LEFT JOIN __users AS u
                ON u.id = o.user_id
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.have_close_credits = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                AND (o.reason_id IS NULL OR o.reason_id != 4)
                AND (o.reason_id IS NULL OR o.reason_id != 5)
                AND DATE(o.approve_date) >= ?
                AND DATE(o.approve_date) <= ?
                AND DATE(o.confirm_date) >= ?
                AND DATE(o.confirm_date) <= ?
                $query_scorista
                GROUP BY u_date
            ", $date_from, $date_to, $date_from, $date_to, $date_from, $date_to);
            $this->db->query($query);
            
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->credit_repeat = $result->o_total;
            }

    
            $this->design->assign('report', $report);
    
        }
        
        return $this->design->fetch('cv_report.tpl');
    }
    
    function secToArray($secs)
    {
    	$res = array();
    	    	
    	$res['hours'] = floor($secs / 3600);
    	if (strlen($res['hours']) == 1)
            $res['hours'] = '0'.$res['hours'];
        $secs = $secs % 3600;
        
     
    	$res['minutes'] = floor($secs / 60);
    	if (strlen($res['minutes']) == 1)
            $res['minutes'] = '0'.$res['minutes'];

    	$res['secs'] = $secs % 60;
    	if (strlen($res['secs']) == 1)
            $res['secs'] = '0'.$res['secs'];
     
    	return $res;
    }
 
}