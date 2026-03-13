<?php

error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require_once 'api/Simpla.php';

class Best2payCallback extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $input = file_get_contents('php://input');

        $this->logging('php://input', '', $input, '', 'best2pay_callback.txt');
    }
    
}
new Best2payCallback();