<?php

require_once 'View.php';

class ZaimOnlineView extends View
{
    public function fetch()
    {
        return $this->design->fetch('zaim_online.tpl');
    }
}