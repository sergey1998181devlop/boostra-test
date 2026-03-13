<?php

chdir('..');

require_once 'api/Simpla.php';

abstract class AService extends Simpla
{
    protected $response = array();
    
    private $password = 'BSTR123987';
    
    public function __construct()
    {
    	parent::__construct();
        
        $password = $this->request->get('password');
        
        if ($password != $this->password)
        {
            $this->response['error'] = 'INCORRECT_PASSWORD';
            $this->json_output();
        }
    }
    
    public function json_output()
    {
    	header('Content-type:application/json');
        echo json_encode($this->response);
        exit;
    }

    /** Если нужна кириллица */
    public function json_output_unicode()
    {
    	header('Content-type:application/json');
        echo json_encode($this->response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
}