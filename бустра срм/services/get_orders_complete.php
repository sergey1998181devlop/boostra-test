<?php

require_once 'AService.php';

class GetOrderCompleteService extends AService
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
        if ($id_1c = $this->request->get('id_1c'))
        {
            if ($id = $this->orders->get_order_1cid($id_1c))
            {
                $order = $this->orders->get_order($id);

                if (
                    $order->personal_data_added &&
                    $order->additional_data_added &&
                    $order->card_added &&
                    $order->files_added
                    ) {
                    $this->response['data'] = array(
                        //'id_1c' => $id_1c,
                        'complete' => 1,
                        'crm_status' => $order->status,
                    );
                } elseif ($order->complete) {
                    $this->response['data'] = array(
                        //'id_1c' => $id_1c,
                        'complete' => 1,
                        'crm_status' => $order->status,
                    );
                } else {
                    $this->response['data'] = array(
                        //'id_1c' => $id_1c,
                        'complete' => 0,
                        'crm_status' => $order->status,
                    );
                }

            }
            else
            {
                $this->response['data'] = array(
                    //'id_1c' => $id_1c,
                    'complete' => false,
                    'error' => 'order not found',
                );
            }
            $this->response['success'] = 1;
        }
        else
        {
            $this->response['error'] = 'EMPTY_ID';
        }
        
        $this->json_output();
    }
}
new GetOrderCompleteService();
