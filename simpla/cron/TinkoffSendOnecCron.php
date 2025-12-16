<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir(__DIR__.'/../..');

require_once 'api/Simpla.php';

class TinkoffSendOnecCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $i = 0;
        while ($i < 1)
        {
            $this->run();
            $i++;
        }
    }
    
    private function run()
    {
        $p2pcredits = $this->tinkoff->get_p2pcredits(array('status' => 'COMPLETED', 'sent' => 0));
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($p2pcredits);echo '</pre><hr />';
        //exit;
        if (!empty($p2pcredits))
        {
            foreach ($p2pcredits as $p2pcredit)
            {
                $order_ids[] = $p2pcredit->order_id;
            }
            if (!empty($order_ids))
            {
                $orders = array();
                foreach ($this->orders->get_crm_orders(array('id' => $order_ids)) as $o)
                    $orders[$o->order_id] = $o;
                
                foreach ($p2pcredits as $p2pcredit)
                {
                    $orders[$p2pcredit->order_id]->p2pcredit = $p2pcredit;
                }
                
                foreach ($orders as $order)
                {
                    //TODO get_order_insure
                    $order->insure = $this->tinkoff->get_order_insure($order->order_id);
                }

                if (!empty($orders))
                {
                    $result = $this->soap->send_contracts($orders);

                    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';
                    if ($result->return == 'OK')
                    {
                        foreach ($p2pcredits as $p2pcredit)
                        {
                            $this->tinkoff->update_p2pcredit($p2pcredit->id, array('sent' => 1, 'send_date'=> date('Y-m-d H:i:s')));
                        }
                    }
                }

                echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($orders);echo '</pre><hr />';
            }
        }
    }
}

//$cron = new TinkoffSendOnecCron();