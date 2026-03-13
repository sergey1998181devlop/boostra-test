<?php

require_once 'View.php';

class IndividualOrdersView extends View
{
    public function fetch()
    {
        $items_per_page = 20;

    	$filter = array();

        $filter['date_from'] = '2021-03-13';
        $filter['dops'] = 1;
        
        if (!($sort = $this->request->get('sort', 'string')))
        {
            $sort = 'date_desc';
        }
        $filter['sort'] = $sort;
        $this->design->assign('sort', $sort);
        
        if ($this->request->get('only') == 'my')
        {
            $this->design->assign('my_orders', 1);
            $filter['manager_id'] = $this->manager->id;
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($this->manager->id);echo '</pre><hr />';        
        }
        elseif ($this->manager->role == 'user')
        {
            $filter['current'] = $this->manager->id;
            $filter['cdoctor'] = 0;
        }
        
        if ($search = $this->request->get('search'))
        {
            $filter['search'] = array_filter($search);
            $this->design->assign('search', array_filter($search));
        }
        
		if ($status = $this->request->get('status'))
        {
            if ($status == 'notreceived')
            {
                $filter['notreceived'] = 1;
                $filter['date_from'] = date('Y-m-d', time() - 86400*25);
            }
            elseif ($status == 'notbusy')
            {
                $filter['notbusy'] = 1;
            }
            elseif ($status == 'inwork')
            {
                $filter['inwork'] = 1;
            }
            elseif ($status == 'issued')
            {
                $filter['issued'] = 1;
            }
            elseif ($status == 'approve')
            {
                $filter['approve'] = 1;
            }
            else
            {
                $filter['status'] = $status;
            }
            
            $this->design->assign('filter_status', $status);
        }
        
        if (!in_array('all_orders', $this->manager->permissions))
        {
            $filter['not_status_1c'] = array('7.Технический отказ');
        }
        
        $filter['stage_completed'] = 1;

		$current_page = $this->request->get('page', 'integer');
		$current_page = max(1, $current_page);
		$this->design->assign('current_page_num', $current_page);

		$orders_count = $this->individuals->count_orders($filter);
		
		$pages_num = ceil($orders_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $orders_count);

		$filter['page'] = $current_page;
		$filter['limit'] = $items_per_page;

        
        
        $individual_orders = array();
        $order_ids = array();
        foreach ($this->individuals->get_orders($filter) as $individual_order)
        {
            $individual_order->scorings = array();
            $individual_orders[$individual_order->id] = $individual_order;
        
            $order_ids[] = $individual_order->order_id;
        }
        
        $source_orders = array();
        foreach ($this->orders->get_orders(array('id' => $order_ids)) as $o)
            $source_orders[$o->order_id] = $o;

        foreach ($individual_orders as $individual_order)
        {
            if (!empty($source_orders[$individual_order->order_id]))
                $individual_order->order = $source_orders[$individual_order->order_id];
        }
        
        
        
        $this->design->assign('individual_orders', $individual_orders);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($orders);echo '</pre><hr />';
        
        $scoring_types = $this->scorings->get_types();
        $this->design->assign('scoring_types', $scoring_types);
        

        return $this->design->fetch('individual_orders.tpl');
    }
    
}