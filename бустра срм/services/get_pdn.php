<?php

//error_reporting(-1);
//ini_set('display_errors', 'On');

require_once 'AService.php';

class PdnService extends AService
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    private function run()
    {
//        file_put_contents(__DIR__ . '/../files/get_pdn.log', json_encode([$_GET, $_POST]) . "\n", FILE_APPEND);
        if ($id_1c = $this->request->get('id_1c')) {
            $id = $this->orders->get_order_1cid($id_1c);
            if ($order = $this->orders->get_order($id))
            {
                $lastScoring = $this->scorings->getLastScoringOfUser($order->user_id);
                if ($lastScoring) {
                    $this->response['result'] = json_decode($lastScoring->body);
                    if ($lastScoring->type == $this->scorings::TYPE_AXILINK) {
                        $this->response['result'] = $this->response['result']->pdn ?? '';
                    } else {
                        $this->response['result'] = $this->response['result']->additional->pti_RosStat->pti->result ?? '';
                    }
                } else {
                    $this->response['error'] = 'NOT_FOUND';
                }
            } else {
                $this->response['error'] = 'ORDER_NOT_FOUND';                
            }
        } else {
            $this->response['error'] = 'EMPTY_ID';
        }
        $this->json_output();
    }
}

new PdnService();
