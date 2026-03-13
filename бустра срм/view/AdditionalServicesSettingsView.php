<?php

require_once 'View.php';

class AdditionalServicesSettingsView extends View
{
    public function fetch()
    {
    	if ($this->request->method('post'))
        {
            $this->settings->additional_services_settings = $this->request->post('additional_services_settings');
        }
        
        return $this->design->fetch('additional_services_settings.tpl');
    }
    
}