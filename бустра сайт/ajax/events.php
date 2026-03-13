<?php
error_reporting(-1);
ini_set('display_errors', 'On');

date_default_timezone_set('Europe/Moscow');

session_start();
chdir('..');
require_once 'api/Simpla.php';

class EventsAjax extends Simpla
{
    private $response = array();
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
            
        $this->output();
    }
    
    private function run()
    {
        $user_id = $this->request->get('user_id', 'integer');
        $event = $this->request->get('event', 'integer');
        
        $this->response['success'] = $this->events->add_event(array(
            'user_id' => $user_id,
            'event' => $event,
            'created' => date('Y-m-d H:i:s'),
        ));
    }
    
    private function output()
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");		
    
        echo json_encode($this->response);
    }
}
new EventsAjax();