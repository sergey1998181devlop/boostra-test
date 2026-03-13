<?php

//error_reporting(-1);
//ini_set('display_errors', 'On');

require_once 'AService.php';

class AspZaimService extends AService
{
    private $limits = [
        'calls' => [
            'day' => 1,
            'week' => 25,
            'month' => 50,
        ],
        'messages' => [
            'day' => 1,
            'week' => 20,
            'month' => 40,
        ],
    ];
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    private function run()
    {
        if ($number = $this->request->get('number'))
        {
            $this->db->query("
                SELECT * FROM s_asp_to_zaim
                WHERE zaim_number = ?
            ", $number);
            $result = $this->db->result();
            
            if (empty($result))
            {
                $this->response['status'] = 0;                
            }
            else
            {
                $this->response['status'] = 1;
                $this->response['date'] = $result->date_added;
                $this->response['asp'] = $result->sms_code;
                
                $this->response['crm'] = array_map(function($var){
                    foreach ($var as &$v)
                        $v = 0;
                    return $var;
                }, $this->limits);

                $this->response['limits'] = $this->limits;
            }
        }
        else
        {
            $this->response['error'] = 'EMPTY_CONTRACT_NUMBER';
        }

        $this->json_output();
    }
}

new AspZaimService();
