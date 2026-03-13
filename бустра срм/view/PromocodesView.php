<?php

require_once 'View.php';

class PromocodesView extends View
{
    public function fetch()
    {
		$current_page = max(1, $this->request->get('page', 'integer'));
		$codes_count  = $this->promocodes->count();
		$pages_num    = ceil($codes_count / $this->promocodes::PAGE_SIZE);
        $promocodes   = $this->promocodes->getList(['start_page' => $current_page]);

		$this->design->assign('current_page_num', $current_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $codes_count);
		$this->design->assign('promocodes', $promocodes);
        
        return $this->design->fetch('promocodes.tpl');
    }
    
}