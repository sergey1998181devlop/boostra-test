<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__).'/../api/Simpla.php';

class SendLptLeadCron extends Simpla
{
    public function __construct()
    {
        parent::__construct();

        $this->run();

        echo 'отправлено';
    }

    public function run() {
        $lptCollection = $this->lpt->get_lead_for_lpt(date('Y-m-d'));

        foreach ($lptCollection as $lpt) {
            $result = $this->lpt->send_lead($lpt->id);
            if ($result) {
                $this->users->update_user_balance($lpt->id, ['lpt_lead' => 1]);
            }
        }
    }
}

new SendLptLeadCron();