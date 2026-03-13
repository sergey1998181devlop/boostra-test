<?php

require_once 'View.php';

class IndividualSettingsView extends View
{
    public function fetch()
    {
    	if ($this->request->method('post'))
        {
            $this->settings->individual_settings = $this->request->post('individual_settings');
        }
        
        return $this->design->fetch('individual_settings.tpl');
    }
    
}