<?php

use Carbon\Carbon;

require_once dirname(__FILE__).'/../api/Simpla.php';

class ResumeSmsSubscription extends Simpla
{
    public function init()
    {
        $currentDate = Carbon::now()->toDateString();
        
        $this->db->query('DELETE FROM s_block_sms_adv WHERE blocked_until < ?', $currentDate);
    }
}

$cron = new ResumeSmsSubscription();
$cron->init();