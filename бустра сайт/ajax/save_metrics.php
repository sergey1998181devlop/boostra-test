<?php

session_start();

require_once '../api/Simpla.php';

class SaveMetrics extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    private function run()
    {
        $type = $this->request->get('type');
        $action = $this->request->get('action');
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_id = $_SESSION['user_id'] ?? ($_COOKIE['user_id'] ?? null);
        
        $this->db->query("
            INSERT INTO yametric_logs
            SET ?%
        ", [
            'ya_type' => $type,
            'ya_action' => $action,
            'ip' => $ip,
            'user_id' => $user_id,
            'visit_id' => $_SESSION['vid'] ?? null,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }
}
new SaveMetrics();