<?php

require_once 'View.php';

class ChangelogsView extends View
{
    public const MANAGERS_TO_HIDE_LOGS = [
        50,
        240,
        325,
        404,
        471
    ];

    public const LOGS_TYPE_TO_HIDE_LOGS = [
        'work', // в s_comments
        'workdata', // в s_changelogs
        'regaddress',
        'faktaddress'
    ];

    public function fetch()
    {
        if (!in_array('logs', $this->manager->permissions))
        	return $this->design->fetch('403.tpl');
        
        $types = $this->changelogs->get_types();
        $this->design->assign('types', $types);

        $items_per_page = 20;

    	$filter = array();

        if (!($sort = $this->request->get('sort', 'string')))
        {
            $sort = 'date_desc';
        }
        $filter['sort'] = $sort;
        $this->design->assign('sort', $sort);
        
        if ($search = $this->request->get('search'))
        {
            $filter['search'] = array_filter($search);
            $this->design->assign('search', array_filter($search));
        }
        
        if ($this->manager->role == 'verificator_minus') {
            if (isset($filter['search']['order'])) {
                $order = $this->orders->get_order((int)$filter['search']['order']);
                if ($order->organization_id != $this->organizations::AKVARIUS_ID) {
                    $filter['search']['order'] = 2;
                }
            }
            $filter['type'] = array_keys($types);
            $filter['organization_id'] = $this->organizations::AKVARIUS_ID;
        }
        
		$current_page = $this->request->get('page', 'integer');
		$current_page = max(1, $current_page);
		$this->design->assign('current_page_num', $current_page);

		$changelogs_count = $this->changelogs->count_changelogs($filter);
		
		$pages_num = ceil($changelogs_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $changelogs_count);

		$filter['page'] = $current_page;
		$filter['limit'] = $items_per_page;



        $users_ids = array();
        $changelogs = array();
        foreach ($this->changelogs->get_changelogs($filter) as $changelog)
        {
            if (!empty($changelog->user_id))
                $users_ids[] = $changelog->user_id;
            $changelogs[] = $changelog;
        }
        
        $users = array();
        if (!empty($users_ids))
        {
            foreach ($this->users->get_users(array('id'=>$users_ids)) as $u)
                $users[$u->id] = $u;
        }
        
        $managers = array();
        foreach ($this->managers->get_managers() as $m)
            $managers[$m->id] = $m;
        
        foreach ($changelogs as $key => $changelog)
        {
            if (in_array($changelog->type, self::LOGS_TYPE_TO_HIDE_LOGS) && in_array($changelog->manager_id, self::MANAGERS_TO_HIDE_LOGS)) {
                unset($changelogs[$key]);
                continue;
            }
            
            if (!empty($changelog->user_id) && !empty($users[$changelog->user_id]))
                $changelog->user = $users[$changelog->user_id];
            if (!empty($changelog->manager_id) && !empty($managers[$changelog->manager_id]))
                $changelog->manager = $managers[$changelog->manager_id];
        }
        
        $this->design->assign('changelogs', $changelogs);
        
        $managers = $this->managers->get_managers();
        $this->design->assign('managers', $managers);
        
        $order_statuses = $this->orders->get_statuses();
        $this->design->assign('order_statuses', $order_statuses);
        
        return $this->design->fetch('changelogs.tpl');
    }
    
}