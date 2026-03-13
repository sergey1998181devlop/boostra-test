<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__).'/../api/Simpla.php';

class CleanUpLptDebtorsCron extends Simpla
{
    public function __construct()
    {
        parent::__construct();

        $this->run();

        echo 'отправлено';
    }

    public function run() {
        $this->lpt->update_going_out_lpt_by_user_balance(isset($_GET['amount']) ? (int) $_GET['amount'] : 9);
    }
}

new CleanUpLptDebtorsCron();