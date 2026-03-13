<?php

//error_reporting(-1);
//ini_set('display_errors', 'On');

require_once 'AService.php';

class PdnNativeService extends AService
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    private function run()
    {
        if ($id_1c = $this->request->get('id_1c')) {
            $id = $this->orders->get_order_1cid($id_1c);
            if ($order = $this->orders->get_order($id)) {
                $pti_val = $order->pti_loan;
                if(!$pti_val && $order->confirm_date) {
                    $pti = new BoostraPTI($order);
                    if($pti->setSource()) {
                        $pti->toggleDetails(true);
                        $dataPTI = $pti->getPTIData();
                        $this->orders->update_order($id, ['pti_loan' => $dataPTI['rosstat_pti'] ?? 0]);
                        $pti_val = $dataPTI['rosstat_pti'] ?? 0;
                    }
                }
                $this->response['result'] = $pti_val;
            } else {
                $this->response['error'] = 'ORDER_NOT_FOUND';                
            }
        } else {
            $this->response['error'] = 'EMPTY_ID';
        }
        $this->json_output();
    }
}

new PdnNativeService();
