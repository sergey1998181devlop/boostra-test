<?php

require_once 'View.php';

class TrafficReportView extends View
{
    private $prices = array(
        'leadgid' => array(
            array('from' => 0, 'to' => 599, 'price' => 1800),
            array('from' => 600, 'to' => 699, 'price' => 2400),
            array('from' => 700, 'to' => 749, 'price' => 3000),
            array('from' => 750, 'to' => 999, 'price' => 3300),
        )
    );
    
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
            
            $utm_source = 'leadgid';
            
            // кол-во посетителей
            $query = $this->db->placehold("
                SELECT 
                    COUNT(v.id) AS totals
                FROM __referrals AS v
                WHERE DATE(v.created) >= ?
                AND DATE(v.created) <= ?
                AND v.utm_source = ?
            ", $date_from, $date_to, $utm_source);
            $this->db->query($query);            
            $report->visitors = $this->db->result('totals');


            // кол-во заявок
            $query = $this->db->placehold("
                SELECT 
                    COUNT(o.id) AS totals
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND o.utm_source = ?
            ", $date_from, $date_to, $utm_source);
            $this->db->query($query);            
            $report->orders = $this->db->result('totals');


            // кол-во выдач
            $query = $this->db->placehold("
                SELECT 
                    COUNT(o.id) AS totals
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND o.utm_source = ?
                AND leadgid_postback_date IS NOT NULL
            ", $date_from, $date_to, $utm_source);
            $this->db->query($query);            
            $report->getted = $this->db->result('totals');

            
/*            
            $query = $this->db->placehold("
                SELECT 
                    o.*,
                    s.scorista_ball
                FROM __orders AS o
                RIGHT JOIN __scorings AS s
                ON s.user_id = o.user_id
                AND s.type = 'scorista'
                AND s.status = 'completed'
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND o.utm_source = ?
                AND leadgid_postback_date IS NOT NULL
                GROUP BY o.id
            ", $date_from, $date_to, $utm_source);
            $this->db->query($query);            
            $results = $this->db->results();
*/            
            $query = $this->db->placehold("
                SELECT 
                    o.*,
                    (
                        SELECT scorista_ball
                        FROM __scorings AS s
                        WHERE s.order_id = o.id
                        AND s.type = ".$this->scorings::TYPE_SCORISTA."
                        AND s.status = ".$this->scorings::STATUS_COMPLETED."
                        ORDER BY id DESC
                        LIMIT 1
                    ) AS scorista_ball
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND o.utm_source = ?
                AND leadgid_postback_date IS NOT NULL
                GROUP BY o.id
            ", $date_from, $date_to, $utm_source);
            $this->db->query($query);            
            $results = $this->db->results();
            
            
            $report->total_paid = 0;
            $report->totals = array();
            foreach ($this->prices[$utm_source] as &$pr)
            {
                $pr['orders'] = array();
                $pr['count_totals'] = 0;
                $pr['price_totals'] = 0;
            }
            foreach ($results as $order)
            {
                foreach ($this->prices[$utm_source] as &$price_item)
                {
                    $order->scorista_ball = intval($order->scorista_ball);
                    if ($price_item['from'] < $order->scorista_ball && $price_item['to'] >= $order->scorista_ball)
                    {
                        $price_item['orders'][] = $o;
                        $price_item['count_totals']++;
                        $price_item['price_totals'] += $price_item['price'];
                        $report->total_paid += $price_item['price'];
                    }
                }
            }
            
            $this->design->assign('total_report', $this->prices[$utm_source]);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($results);echo '</pre><hr />';            
            

            $this->design->assign('report', $report);
    
        }
        
        return $this->design->fetch('traffic_report.tpl');
    }
    
}