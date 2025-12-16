<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir('../');
require_once 'api/Simpla.php';

class LikeZaimPostback extends Simpla
{
    private $response = [];
    
    public function __construct()
    {
    	$this->logging('postback', 'likezaim', $_GET, $_POST, 'likezaim_postback.txt');
    
        if (!($action = $this->request->get('action', 'string'))) {
            $this->output([
                'success' => 0,
                'error' => 1,
                'msg' => 'empty action'
            ]);
        }
        
        if (!($order_id = $this->request->get('order_id', 'integer'))) {
            $this->output([
                'success' => 0,
                'error' => 1,
                'msg' => 'empty order_id'
            ]);
        }
        
        switch($action):
            
            case 'reject':
                $this->create_postback($order_id, 'reject');
                break;
            
            case 'complete':
                $this->create_postback($order_id, 'complete');
                break;

            default:
                $this->output([
                    'success' => 0,
                    'error' => 1,
                    'msg' => 'undefined action'
                ]);
            
        endswitch;
        
    }
    
    private function create_postback($order_id, $state)
    {
        if ($likezaim_item = $this->likezaim->get_items(['order_id' => $order_id])) {
            $this->likezaim->update_item($likezaim_item->id, [
                'postback_getted' => 1,
                'postback_date' => date('Y-m-d H:i:s'),
                'postback_state' => $state,
            ]);
            $this->output([
                'success' => 1,
            ]);            
        } else {
            $this->output([
                'success' => 0,
                'msg' => 'order_id not found'
            ]);
        }
    }
    
    private function output($data)
    {
        header('Content-type: application/json');
        echo json_encode($data);
        exit;
    }
}
new LikeZaimPostback();