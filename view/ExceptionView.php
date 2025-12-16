<?php

require_once 'View.php';

class ExceptionView extends Simpla
{
    public function fetch()
    {
    	if ($this->request->get('technical'))
            $this->design->assign('technical', 1);
        
        
        return $this->design->fetch('exception.tpl');
    }
    
}