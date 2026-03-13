<?php

require_once 'AService.php';

class GetPhoneUidService extends AService
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    private function run()
    {
        if ($phone = $this->request->get('phone')) {
            if ($user_id = $this->users->get_phone_user($phone))
            {
                $this->response['user_id'] = $user_id;
                $user = $this->users->get_user((int)$user_id);
                
                $this->response['success'] = 1;
                $this->response['uid'] = $user->UID;
            }
            else
            {
                $this->response['error'] = 'NOT_FOUND';
            }
            
                
        } else {
            $this->response['error'] = 'EMPTY_PHONE';
        }
        $this->json_output();
    }
}

new GetPhoneUidService();
