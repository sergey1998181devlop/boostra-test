<?php

error_reporting(0);
ini_set('display_errors', 'Off');

session_start();

chdir('..');
require 'api/Simpla.php';

class RegisterShortSelfVerification extends Simpla
{
    public function run()
    {
        $action = $this->request->get('action');

        switch ($action):

            // 0 этап
            case 'access_token':
                return $this->short_flow->getAccessToken();

            // 2 этап
            case 'started_application':
                return $this->short_flow->changeApplicationStatus($this->cyberity::STATUS_STARTED_APPLICATION);

            // 3 этап
            case 'added_passport':
                return $this->short_flow->changeApplicationStatus($this->cyberity::STATUS_ADDED_PASSPORT);

            // 4 этап
            case 'added_selfie':
                return $this->short_flow->changeApplicationStatus($this->cyberity::STATUS_ADDED_SELFIE);

            default:
                return json_encode(['success' => true]);
        endswitch;
    }
}

$selfVerification = new RegisterShortSelfVerification();
$result = $selfVerification->run();

header('Content-type: application/json; charset=UTF-8');
echo($result);
