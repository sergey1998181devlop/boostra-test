<?php

require_once 'AService.php';

class GetScoristaService extends AService
{
    public function __construct()
    {
    	parent::__construct();        
        $this->run();
    }
    
    private function run()
    {
        $id_1c = $this->request->get('id_1c');
        $uid = $this->request->get('uid');

        if ($uid) {
            $user_id = $this->users->get_uid_user_id($uid);
            if ($user_id) {
                $scorista = $this->scorings->get_last_scorista_for_user($user_id, true);
            } else {
                $this->response['error'] = 'NOT_FOUND_USER';
            }
        } elseif ($id_1c) {
            $order_id = $this->orders->get_order_1cid($id_1c);
            if ($order_id) {
                $scorista = $this->scorings->get_last_scorista_for_order($order_id, true);
            } else {
                $this->response['error'] = 'NOT_FOUND_ORDER';
            }
        } else {
            $this->response['error'] = 'EMPTY_ID_OR_UID';
        }

        if ($scorista) {
            if ($scorista->scorista_id) {
                $this->response['result'] = $scorista->scorista_id;
            } else {
                $this->response['error'] = 'NOT_FOUND_SCORISTA_ID';
            }
        }
        
        $this->json_output();
    }
}

new GetScoristaService();
