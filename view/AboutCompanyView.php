<?php

require_once 'View.php';

class AboutCompanyView extends View
{
    public function fetch()
    {
        return $this->design->fetch('about_company.tpl');
    }
}
