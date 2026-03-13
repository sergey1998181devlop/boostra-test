<?php

require_once 'View.php';

class ExitpoolsView extends View
{
    public function fetch()
    {
        if (!in_array('exitpools', $this->manager->permissions))
        	return $this->design->fetch('403.tpl');
        
        $items_per_page = 100;

    	$filter = array();

        if (!($sort = $this->request->get('sort', 'string')))
        {
            $sort = 'id_desc';
        }
        $filter['sort'] = $sort;
        $this->design->assign('sort', $sort);
        
        if ($search = $this->request->get('search'))
        {
            $filter['search'] = array_filter($search);
            $this->design->assign('search', array_filter($search));
        }
        
		$current_page = $this->request->get('page', 'integer');
		$current_page = max(1, $current_page);
		$this->design->assign('current_page_num', $current_page);

		$clients_count = $this->exitpools->count_exitpools($filter);
		
		$pages_num = ceil($clients_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $clients_count);

		$filter['page'] = $current_page;
		$filter['limit'] = $items_per_page;
    	
        
        $grouping_exitpools = array();
        if ($exitpools = $this->exitpools->get_exitpools($filter))
        {
            foreach ($exitpools as $exitpool)
            {
                $group_name = $exitpool->user_id.strtotime($exitpool->date);
                if (!isset($grouping_exitpools[$group_name]))
                {
                    $grouping_exitpools[$group_name] = $exitpool;
                    $grouping_exitpools[$group_name]->items = array();
                    $grouping_exitpools[$group_name]->client = $this->users->get_user((int)$exitpool->user_id);
                }
                $grouping_exitpools[$group_name]->items[] = $exitpool;
            }
            
            foreach ($grouping_exitpools as $grouping_exitpool)
            {
                $mediana = 0;
                foreach ($grouping_exitpool->items as $item)
                {
                    $mediana += $item->response;
                }
                $grouping_exitpool->mediana = round($mediana / count($grouping_exitpool->items), 1);
            }
            
            $this->design->assign('grouping_exitpools', $grouping_exitpools);
        }
        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($grouping_exitpools);echo '</pre><hr />';
        
        
        $this->design->assign('exitpools', $exitpools);
        
        return $this->design->fetch('exitpools.tpl');
    }
    
}