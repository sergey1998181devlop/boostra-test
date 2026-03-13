<?php
session_start();

error_reporting(0);
ini_set('display_errors', 'Off');

chdir('..');
require_once 'api/Simpla.php';

class FinroznicaCallback extends Simpla
{
    private $token = 'v0hCHeE5mJRHcZ5PphlJiIOpYQLyJjHOWJTLZBYiULYv2F5vzoSQ1RZr4yU89OVT';

    public function __construct()
    {
        parent::__construct();

        if ($this->request->post('token') === $this->token)
            $this->run();
    }

    private function run()
    {
        $method = $this->request->post('method');
        if ($method == 'get_user')
        {
            $phone = $this->request->post('phone');
            $user_id = $this->users->get_phone_user($phone);
            if (empty($user_id))
                exit;
            exit(json_encode($this->users->get_user($user_id)));
        }
    }

}

new FinroznicaCallback();