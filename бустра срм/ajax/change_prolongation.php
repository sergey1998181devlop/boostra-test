<?php

use api\handlers\ChangeProlongationHandler;

error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('memory_limit', '256M');

require_once dirname(__DIR__) . '/api/Simpla.php';

class ChangeProlongation extends Simpla
{
    public function run()
    {
        $orderID = $this->request->get('orderID');
        $managerID = $this->request->get('managerID');
        $value = $this->request->get('value');

        $result = (new ChangeProlongationHandler())->handle($orderID, $managerID, $value);

        $this->response->json_output($result);
    }
}

(new ChangeProlongation())->run();