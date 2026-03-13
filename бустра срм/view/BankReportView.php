<?php

require_once 'View.php';

class BankReportView extends View
{
    public function fetch()
    {
        
        
        return $this->design->fetch('bank_report.tpl');
    }
    
}