<?php

require_once 'View.php';

class FDBaseTariffsView extends View
{
    public function fetch()
    {
        $conditions = $this->credit_doctor->getCreditDoctorConditionsList();
        $this->design->assign('conditions', $conditions);
        return $this->design->fetch('fd_base_tariffs.tpl');
    }
}