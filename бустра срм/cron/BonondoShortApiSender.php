<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__).'/../api/Simpla.php';

class BonondoShortApiSender extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
    }

    private function getOrders() {
        $query = $this->db->placehold("SELECT order_id
                                        FROM __external_api_queue
                                        WHERE
                                            executed_date IS NULL
                                            AND api = 'bonon_short_api'
                                        GROUP BY order_id
                                        LIMIT 50");
        $this->db->query($query);
        return $this->db->results('order_id');
    }
    
    public function run()
    {
        $this->db->query("SELECT GET_LOCK('BonondoShortApiSender', 0) bonon_mutex");
        if(!$this->db->result('bonon_mutex')) {
            return;
        }

        $this->db->query("SET @short_api_last_hour := NOW() - INTERVAL 1 HOUR");
        $this->db->query("SELECT COUNT(DISTINCT order_id) cnt
                          FROM s_external_api_queue
                          WHERE
                            api = 'bonon_short_api'
                            AND executed_date >= @short_api_last_hour");
        $total_sent = $this->db->result('cnt');

        while($total_sent <= 1000 && ($orders = $this->getOrders())) {
            foreach ($orders as $order_id) {
                $order = $this->orders->get_order($order_id);
                $moratorium = $this->users->getMoratoriumByUserId((int)$order->user_id);
                if($moratorium) {
                    $response = $this->bonondo->sendShortApi($order_id);
                    $total_sent++;
                }
                $query = $this->db->placehold("UPDATE __external_api_queue
                                               SET executed_date = NOW()
                                               WHERE
                                                api = 'bonon_short_api'
                                                AND order_id = ?", $order_id);
                $this->db->query($query);
                if($total_sent > 1000) {
                    break;
                }
            }
        }

        $this->db->query("DO RELEASE_LOCK('BonondoShortApiSender')");
    }
}

$cron = new BonondoShortApiSender();
$cron->run();
