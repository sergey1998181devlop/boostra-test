<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__DIR__) . '/api/Simpla.php';

class LikezaimCron extends Simpla
{
    public function __construct()
    {
        parent::__construct();
    	$this->send_items();
    }
    
    private function send_items()
    {
        if ($items = $this->likezaim->get_items_for_send()) {
            foreach ($items as $item) {
                $this->likezaim->transfer($item);
            }
            
        }
    }
    
}
new LikezaimCron();