<?php

require_once 'View.php';

class PaymentExitpoolsView extends View
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

		$clients_count = $this->payment_exitpools->count_exitpools($filter);
		
		$pages_num = ceil($clients_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $clients_count);

		$filter['page'] = $current_page;
		$filter['limit'] = $items_per_page;
    	
        
        $grouping_exitpools = array();
        if ($exitpools = $this->payment_exitpools->get_exitpools($filter))
        {
            foreach ($exitpools as $exitpool)
            {
                $exitpool->client = $this->users->get_user((int)$exitpool->user_id);
            }
                        
            $this->design->assign('grouping_exitpools', $grouping_exitpools);
        }
        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($grouping_exitpools);echo '</pre><hr />';
        
        
        $this->design->assign('exitpools', $exitpools);
        
        return $this->design->fetch('payment_exitpools.tpl');
    }
    
}