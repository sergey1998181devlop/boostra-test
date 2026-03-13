<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require_once 'api/Simpla.php';

class DocsAdmin extends Simpla
{
    public function fetch()
    {
    	$filter = array();
   		$filter['page'] = max(1, $this->request->get('page', 'integer')); 		
		$filter['limit'] = 50;
        
        // Поиск
		$keyword = $this->request->get('keyword', 'string');
		if(!empty($keyword))
		{
			$filter['keyword'] = $keyword;
			$this->design->assign('keyword', $keyword);
		}		
		
        if ($f = $this->request->get('filter'))
        {
            switch ($f):
                
                case 'info':
                    $filter['in_info'] = 1;
                break;
                
                case 'register':
                    $filter['in_register'] = 1;
                break;
                
                case 'visible':
                    $filter['visible'] = 1;
                break;
                
                case 'unvisible':
                    $filter['visible'] = 0;
                break;
                
            endswitch;
            
            $this->design->assign('filter', $f);
        }
        
        // Обработка действий
      	if($this->request->method('post'))
      	{
    		// Сортировка
    		$positions = $this->request->post('positions'); 		
     		$ids = array_keys($positions);
    		sort($positions);
    		foreach($positions as $i=>$position)
    			$this->docs->update_doc($ids[$i], array('position'=>$position)); 
    
    		
    		// Действия с выбранными
    		$ids = $this->request->post('check');
    		if(is_array($ids))
    		switch($this->request->post('action'))
    		{
    		    case 'set_visible':
                {
                    foreach($ids as $id)
                        $this->docs->update_doc($id, array('visible'=>1));    
                    break;
                }
    		    case 'unset_visible':
                {
                    foreach($ids as $id)
                        $this->docs->update_doc($id, array('visible'=>0));    
                    break;
                }
    		    case 'set_info':
                {
                    foreach($ids as $id)
                        $this->docs->update_doc($id, array('in_info'=>1));    
                    break;
                }
    		    case 'unset_info':
                {
                    foreach($ids as $id)
                        $this->docs->update_doc($id, array('in_info'=>0));    
                    break;
                }
    		    case 'set_register':
                {
                    foreach($ids as $id)
                        $this->docs->update_doc($id, array('in_register'=>1));    
                    break;
                }
    		    case 'unset_register':
                {
                    foreach($ids as $id)
                        $this->docs->update_doc($id, array('in_register'=>0));    
                    break;
                }
                case 'delete':
    		    {
    			    foreach($ids as $id)
    					$this->docs->delete_doc($id);    
    		        break;
    		    }
    		}		
    		
     	}
        
        $docs_count = $this->docs->count_docs($filter);
		// Показать все страницы сразу
		if($this->request->get('page') == 'all')
			$filter['limit'] = $docs_count;	

		$this->design->assign('pages_count', ceil($docs_count / $filter['limit']));
		$this->design->assign('current_page', $filter['page']);
		
		$this->design->assign('docs_count', $docs_count);
        
        $docs = $this->docs->get_docs($filter);
        $this->design->assign('docs', $docs);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($docs);echo '</pre><hr />';        
        return $this->design->fetch('docs.tpl');
    }
    
}