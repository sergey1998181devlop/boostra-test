<?php

require_once 'View.php';

class VerificatorsView extends View
{
    public function fetch()
    {
        if (!in_array('verificators', $this->manager->permissions))
        	return $this->design->fetch('403.tpl');

        $verificators = $this->managers->get_verificators();
        array_filter($verificators, function($var){
            return empty($var->blocked);
        });
        $this->design->assign('verificators', $verificators);
        
        return $this->design->fetch('verificators.tpl');
    }
    
}