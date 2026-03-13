<?php

require_once 'View.php';

class NbkiReportsView extends View
{
    public function fetch()
    {
        if (!in_array('nbki_reports', $this->manager->permissions))
       	    return $this->design->fetch('403.tpl');
        
        if ($this->request->method('post')) {
            $action = $this->request->post('action', 'string');
            if (method_exists($this, $action)) {
                $this->$action();
            } else {
                $this->json_output([
                    'error' => 'undefined action'
                ]);
            }
            
            switch ($this->request->post('action')):
                
                case 'set_sent':
                    $this->set_sent();
                
            endswitch;
        }
        
        $filter = [];
        $filter['limit'] = 20;

        if (!($filter['page'] = $this->request->get('page'))) {
            $filter['page'] = 1;
        }

        $orders_count = $this->nbki_report->count_reports($filter);
        $pages_num = ceil($orders_count / $filter['limit']);
        $this->design->assign('total_pages_num', $pages_num);
        $this->design->assign('total_orders_count', $orders_count);
        $this->design->assign('current_page_num', $filter['page']);

        $reports = array();
        foreach ($this->nbki_report->get_reports($filter) as $report) {
            $reports[] = $report;
        }

        $this->design->assign('reports', $reports);

        return $this->design->fetch('nbki_reports.tpl');
    }
    
    private function set_sent()
    {
        $sent = $this->request->post('sent', 'integer');
        $report_id = $this->request->post('report_id', 'integer');
        
        if (empty($report_id)) {
            $this->json_output([
                'error' => 'undefined report'
            ]);
        } else {
            $this->nbki_report->update_report($report_id, [
                'sent' => $sent
            ]);
            $this->json_output([
                'success' => 'Отчет успешно изменен!'
            ]);
        }
    }
}