<?php
error_reporting(-1);

session_start();

ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__).'/../api/Simpla.php';

class SendContractsCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    public function run()
    {
        $query = $this->db->placehold("
            SELECT *
            FROM b2p_p2pcredits
            WHERE sent = 0
            AND status = 'COMPLETED'
            ORDER BY id DESC
            LIMIT 1
        ");
        $this->db->query($query);
        
        if ($p2pcredits = $this->db->results())
        {
            $items = array();
            
            foreach ($p2pcredits as $p2pcredit)
            {
                $item = $this->orders->get_order($p2pcredit->order_id);
                $item->p2pcredit = $p2pcredit;
                $item->contactpersons = $this->contactpersons->get_contactpersons(array('user_id'=>$item->user_id));
                $item->insure = $this->best2pay->get_order_insure($item->order_id);
            
                $items[] = $item;
            }
            
            $resp = $this->soap->send_contracts($items);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($resp);echo '</pre><hr />';            
        }
    }
        
}

new SendContractsCron();