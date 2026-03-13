<?php

require_once 'View.php';

class FDSettingsView extends View
{
    public function fetch()
    {
        $conditions = $this->credit_doctor->getCreditDoctorConditionsList(['price_group' => 'safety_flow_prices']);
        $this->design->assign('conditions', $conditions);
        return $this->design->fetch('fd_settings.tpl');
    }
}