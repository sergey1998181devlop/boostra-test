<?php

require_once 'View.php';

class ManagersView extends View
{    
    public function fetch()
    {

        if (!in_array('managers', $this->manager->permissions))
        	return $this->design->fetch('403.tpl');
        
        $managers = $this->managers->get_managers();
        $managers = array_filter($managers, function($var){
            return empty($var->blocked);
        });
        
        $this->design->assign('managers', $managers);
        
        $roles = $this->managers->get_roles();
        $this->design->assign('roles', $roles);

        return $this->design->fetch('managers.tpl');
    }
    
}