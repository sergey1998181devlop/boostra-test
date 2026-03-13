<?php

require_once 'View.php';

class CallsReportView extends View
{
    public function fetch()
    {
    	if ($daterange = $this->request->get('daterange'))
        {
            list($from, $to) = explode('-', $daterange);
            
        	$date_from = date('Y-m-d', strtotime($from));
        	$date_to = date('Y-m-d', strtotime($to));
            
            if (strtotime($to) < strtotime('2021-08-28 00:00:00') || strtotime($from) < strtotime('2021-08-28 00:00:00'))
                $this->design->assign('message_error', 'Отчет по звонкам доступен начиная с 28.08.2021');
            
            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);
            
            $report = new StdClass();        
            
            


            $managers = array();
            foreach ($this->managers->get_managers() as $m)
            {
                $m->total_seconds = 0;
                $m->total_calls = 0;
                $m->total_missings = 0;
                $m->first_call = NULL;
                $m->last_call = NULL;
                $m->calls = array();
                
                $managers[$m->id] = $m;
            }
            $this->design->assign('managers', $managers);

            $query = $this->db->placehold("
                SELECT 
                    *
                FROM __mangocalls
                WHERE DATE(created) >= ?
                AND DATE(created) <= ?
                AND manager_id > 0
                ORDER BY created ASC
            ", $date_from, $date_to);
            $this->db->query($query);            
            $results = $this->db->results();
            
            foreach ($results as $call)
            {
                if (!empty($call->manager_id))
                {
                    $managers[$call->manager_id]->total_seconds += $call->duration;
                    $managers[$call->manager_id]->total_calls++;
                    if ($call->duration < 30)
                        $managers[$call->manager_id]->total_missings++;
                    
                    if (empty($managers[$call->manager_id]->first_call) || strtotime($managers[$call->manager_id]->first_call) > strtotime($call->created))
                        $managers[$call->manager_id]->first_call = $call->created;
                    if (empty($managers[$call->manager_id]->last_call) || strtotime($managers[$call->manager_id]->last_call) < strtotime($call->created))
                        $managers[$call->manager_id]->last_call = $call->created;
                    
                    $managers[$call->manager_id]->calls[] = $call;
                }
            }

            $verificator_calls = array();
            $verificator_total_seconds = 0;
            $verificator_total_calls = 0;
            $verificator_total_missings = 0;

            $cc_calls = array();
            $cc_total_seconds = 0;
            $cc_total_calls = 0;
            $cc_total_missings = 0;

            foreach ($managers as $m)
            {
                if (in_array($m->role, ['verificator', 'edit_verificator']) && !empty($m->calls))
                {
                    $verificator_total_seconds += $m->total_seconds;
                    $verificator_total_calls += $m->total_calls;
                    $verificator_total_missings += $m->total_missings;
                    
                    $verificator_calls[$m->id] = $m;
                }
                if ($m->role == 'contact_center' && !empty($m->calls))
                {
                    $cc_total_seconds += $m->total_seconds;
                    $cc_total_calls += $m->total_calls;
                    $cc_total_missings += $m->total_missings;
                    
                    $cc_calls[$m->id] = $m;
                }
            }
            
            $this->design->assign('verificator_calls', $verificator_calls);
            $this->design->assign('verificator_total_seconds', $verificator_total_seconds);
            $this->design->assign('verificator_total_calls', $verificator_total_calls);
            $this->design->assign('verificator_total_missings', $verificator_total_missings);

            $this->design->assign('cc_calls', $cc_calls);
            $this->design->assign('cc_total_seconds', $cc_total_seconds);
            $this->design->assign('cc_total_calls', $cc_total_calls);
            $this->design->assign('cc_total_missings', $cc_total_missings);
            
            
        }
        
        return $this->design->fetch('calls_report.tpl');
    }
    
}