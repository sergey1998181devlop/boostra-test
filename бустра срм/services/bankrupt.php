<?php

require_once 'AService.php';

class BankruptService extends AService
{
    public function __construct()
    {
    	parent::__construct();
        
//        $this->response['info'] = array(
//            
//        );
        
        $this->run();
    }
    
    private function run()
    {
        if ($uid = $this->request->get('uid'))
        {
            if ($user_id = $this->users->get_uid_user_id($uid))
            {
                $scoring = $this->scorings->get_last_type_scoring($this->scorings::TYPE_EFRSB, $user_id);
                
                if (empty($scoring))
                {
                    $this->response['success'] = 0;
                }
                else
                {
                    $this->response['success'] = 1;
                    $this->response['date'] = $scoring->created;
                    
                    if (empty($scoring->success))
                    {
                        $this->response['bankrupt'] = 1;
                        $this->response['links'] = unserialize($scoring->body);
                    }
                    else
                    {
                        $this->response['bankrupt'] = 0;                        
                    }
                }
                
                
            }
            else
            {
                $this->response['error'] = 'USER_NOT_FOUND';
            }
        }
        else
        {
            $this->response['error'] = 'EMPTY_UID';
        }
        
        $this->json_output();
    }
}
new BankruptService();