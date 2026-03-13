<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__).'/../api/Simpla.php';

class UpdateLptCron extends Simpla
{
    public function __construct()
    {
        parent::__construct();

        $this->run();

        echo 'Обновлено';
    }

    private function run()
    {
        $this->lpt->update_all_in_working_lpt();
    }
}

new UpdateLptCron();