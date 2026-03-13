<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__FILE__).'/../api/Simpla.php';

class sendIssuedLoansPostbacks extends Simpla
{
    private const LOG_FILE = self::class . '.txt';
    private $args;

     public function __construct($args)
    {
        $this->args = $args ?? [];
        parent::__construct();
    }

   private function getGenericOrders() {
        $this->db->query("SET @postbacks_last_hour := NOW() - INTERVAL 1 HOUR");
        $query = $this->db->placehold("SELECT aq.order_id
                                        FROM __external_api_queue aq
                                        JOIN __orders o
                                            ON o.id = aq.order_id
                                            AND o.confirm_date < @postbacks_last_hour
                                        WHERE
                                            aq.executed_date IS NULL
                                            AND aq.api = 'send_issued_loans_generic'
                                        GROUP BY aq.order_id
                                        LIMIT 50");
        $this->db->query($query);
        return $this->db->results('order_id');
    }
    
    private function getBononOrders() {
        $query = $this->db->placehold("SELECT aq.order_id
                                        FROM __external_api_queue aq
                                        WHERE
                                            aq.executed_date IS NULL
                                            AND aq.api = 'send_issued_loans_bonon'
                                        GROUP BY aq.order_id
                                        LIMIT 50");
        $this->db->query($query);
        return $this->db->results('order_id');
    }
    
    public function run()
    {
        array_shift($this->args);
        $command = (php_sapi_name() == 'cli' ? reset($this->args) : ($_GET['command'] ?? ''));

        if($command == 'force_kill') {
            $this->db->query("SELECT GET_LOCK('sendIssuedLoansPostbacks_EXIT', 0) postback_mutex");
            $this->logging('INITIATE FORCED EXIT', '', '', 'Push forced exit command at ' . (new DateTime('now'))->format('Y-m-d H:i:s'), self::LOG_FILE);
            sleep(30);
            $this->db->query("DO RELEASE_LOCK('sendIssuedLoansPostbacks_EXIT')");
            exit;
        }
        
        $this->db->query("SELECT GET_LOCK('sendIssuedLoansPostbacks', 0) postback_mutex");
        if(!$this->db->result('postback_mutex')) {
            return;
        }

        $this->logging('SENDING STARTED', '', '', 'Sending started at ' . (new DateTime('now'))->format('Y-m-d H:i:s'), self::LOG_FILE);
        while($order_ids = $this->getGenericOrders()) {
            $orders = $this->orders->get_orders(['id' => $order_ids]);
            foreach ($orders as $order) {
                $this->db->query("SELECT IS_USED_LOCK('sendIssuedLoansPostbacks_EXIT') stop_worker");
                if($this->db->result('stop_worker')) {
                    $this->logging('FORCED EXIT', '', '', 'Forced exit at ' . (new DateTime('now'))->format('Y-m-d H:i:s'), self::LOG_FILE);
                    exit;
                }
                $has_sent = $this->post_back->hasPostBackByOrderId($order->order_id, $this->post_back::TYPE_SALE);
                if(!empty($order->confirm_date) && !$has_sent) {
                    $this->post_back->sendSaleOrder($order);
                } else {
                    $reason = empty($order->confirm_date) ? 'не было выдачи' : 'постбэк уже отправлен';
                    $this->logging('getGenericOrders', '', '', "По ордеру {$order->order_id} $reason. UTM: {$order->utm_source}. Stream: generic", self::LOG_FILE);
                }
                $query = $this->db->placehold("UPDATE __external_api_queue
                                               SET executed_date = NOW()
                                               WHERE
                                                api = 'send_issued_loans_generic'
                                                AND order_id = ?", $order->order_id);
                $this->db->query($query);
            }
        }

        while($order_ids = $this->getBononOrders()) {
            foreach ($order_ids as $order_id) {
                $this->db->query("SELECT IS_USED_LOCK('sendIssuedLoansPostbacks_EXIT') stop_worker");
                if($this->db->result('stop_worker')) {
                    $this->logging('FORCED EXIT', '', '', 'Forced exit at ' . (new DateTime('now'))->format('Y-m-d H:i:s'), self::LOG_FILE);
                    exit;
                }
                $has_sent = $this->post_back->hasPostBackByOrderId($order_id, $this->post_back::TYPE_SALE);
                $order = $this->orders->get_order($order_id);
                if(!empty($order->confirm_date) && !$has_sent) {
                    $this->bonondo->sendIssuedLoan($order_id);
                } else {
                    $reason = empty($order->confirm_date) ? 'не было выдачи' : 'постбэк уже отправлен';
                    $this->logging('getGenericOrders', '', '', "По ордеру {$order->order_id} $reason. UTM: {$order->utm_source}. Stream: bonon", self::LOG_FILE);
                }
                $query = $this->db->placehold("UPDATE __external_api_queue
                                               SET executed_date = NOW()
                                               WHERE
                                                api = 'send_issued_loans_bonon'
                                                AND order_id = ?", $order_id);
                $this->db->query($query);
            }
        }

        $this->logging('SENDING FINISHED', '', '', 'Sending finished at ' . (new DateTime('now'))->format('Y-m-d H:i:s'), self::LOG_FILE);
        $this->db->query("DO RELEASE_LOCK('sendIssuedLoansPostbacks')");
    }
}

set_time_limit(0);
$cron = new sendIssuedLoansPostbacks($argv);
$cron->run();