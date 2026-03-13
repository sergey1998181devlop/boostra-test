<?php

session_start();

chdir('..');

require 'api/Simpla.php';

class EventlogsAjax extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    
    public function run()
    {
        $event_id = $this->request->get('event', 'integer');
        $manager_id = $this->request->get('manager', 'integer');
        $order_id = $this->request->get('order', 'integer');
        $user_id = $this->request->get('user', 'integer');

        $this->eventlogs->add_log(array(
            'event_id' => $event_id,
            'manager_id' => $manager_id,
            'order_id' => $order_id,
            'user_id' => $user_id,
            'created' => date('Y-m-d H:i:s'),
        ));
    }
    
}
new EventlogsAjax();