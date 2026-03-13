<?php

require_once 'View.php';

class ZaimView extends View
{
    public function fetch()
    {
        return $this->design->fetch('zaim_online.tpl');
    }
}