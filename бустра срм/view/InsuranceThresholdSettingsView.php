<?php

require_once 'View.php';

class InsuranceThresholdSettingsView extends View
{
    public function fetch()
    {
    	if ($this->request->method('post'))
        {
            $insurance_threshold_settings = [];
            $insurance_threshold = $this->request->post('insurance_threshold_settings');
            $order = $this->request->post('order');
            
            asort($order);
            foreach ($order as $key => $item)
                $insurance_threshold_settings[$key] = $insurance_threshold[$key];

            $this->settings->insurance_threshold_settings = $insurance_threshold_settings;
        }
        
        $this->design->assign('companies', $this->insurances->insurers);
        
        return $this->design->fetch('insurance_threshold_settings.tpl');
    }
    
}