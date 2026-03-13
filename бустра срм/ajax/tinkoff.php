<?php

chdir('..');

require 'api/Simpla.php';

class TinkoffAjax extends Simpla
{
    private $response = array();
    
    public function __construct()
    {
    	parent::__construct();
        
//        $password = $this->request->get('password');
//        if ($password != 'AX6768KE')
//            exit('ACCESS DENIED');
        
        
        $this->run();
        
        $this->json_output();
                
    }
    
    
    public function run()
    {
    	$action = $this->request->get('action', 'string');
        
        switch ($action):
            
            case 'get_state_atop':
                
                $payment_id = $this->request->get('payment_id');                
                
                $this->response = $this->tinkoff->get_state_atop($payment_id);

            break;
            
            case 'init_payment_atop':
                
                $user_id = $this->request->get('user_id');
                $amount = $this->request->get('amount');
                
                $this->response = $this->tinkoff->init_payment_atop($user_id, $amount);
                
            break;
            
            case 'add_card':
                
                $customer_id = $this->request->get('customer_id');
                
                $this->response = $this->tinkoff->add_card($customer_id);
                
            break;
            
            case 'get_cardlist':
                
                $customer_id = $this->request->get('customer_id');
                
                $this->response = $this->tinkoff->get_cardlist($customer_id);
                
            break;
            
            case 'hold':
                
                $user_id = $this->request->get('user_id');
                $card_id = $this->request->get('card_id');
                $rebill_id = $this->request->get('rebill_id');
                
                $this->response = $this->tinkoff->hold($user_id, $card_id, $rebill_id);
                
            break;
            
            case 'get_order_info':
                
                $order_id = $this->request->get('order_id');
                
                $this->response = $this->tinkoff->get_order_info($order_id);
                
            break;
            
        endswitch;
    
    }
    
    private function json_output()
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");	
        
        echo json_encode($this->response);
    }
}
new TinkoffAjax();