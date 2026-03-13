<?php

require_once 'View.php';

class VitamedBaseTariffsView extends View
{
    public function fetch()
    {
        $conditions = $this->tv_medical->getTVMedicalConditionsList();
        $this->design->assign('conditions', $conditions);
        return $this->design->fetch('vitamed_base_tariffs_settings.tpl');
    }
}