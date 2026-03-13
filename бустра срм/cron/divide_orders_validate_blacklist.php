<?php

/**
 * Проводит проверку на ЧС для автоделенных заявок
 */

error_reporting(E_ERROR);
ini_set('display_errors', 'on');

require_once dirname(__DIR__) . '/api/Simpla.php';

class DivideOrdersValidateBL extends Simpla
{

    /**
     * Лимит заявок в цикле
     */
    public const LIMIT = 100;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        ini_set("log_errors", 1);
        ini_set("error_log", __DIR__ . "/php-error.log");
        set_time_limit(0);
    }

    public function run()
    {
        $orders = $this->getOrders();
        foreach ($orders as $key => $order) {
            echo "Process: " . ($key + 1) . " FROM " . count($orders) . " (" . $order->order_id . ")" . PHP_EOL;

            // проверим актуальный статус 1С
            $order_data = $this->orders->get_order($order->order_id);
            if (!in_array($order_data->status_1c, [
                $this->orders::ORDER_UPDATE_1C_STATUS_NEW,
                $this->orders::ORDER_1C_STATUS_APPROVED,
            ])) {
                $this->addCronItem((int)$order->order_id);
                continue;
            }

            // добавляем проверку на ЧС
            $scoring_data = [
                'user_id' => $order->user_id,
                'status' => $this->scorings::STATUS_NEW,
                'order_id' => $order->order_id,
                'type' => $this->scorings::TYPE_BLACKLIST,
                'created' => date('Y-m-d H:i:s'),
            ];

            if($scoring_id = $this->scorings->add_scoring($scoring_data)) {
                $this->blacklist->run_scoring($scoring_id);
                $this->addCronItem((int)$order->order_id);
            }
        }
    }

    /**
     * Добавляет запись о том что заявка обработана
     * @param int $order_id
     * @return void
     */
    private function addCronItem(int $order_id): void
    {
        $query = $this->db->placehold("INSERT INTO s_cron_validate_blacklist SET ?%", compact('order_id'));
        $this->db->query($query);
        $this->db->insert_id();
    }

    private function getOrders()
    {
        $filter = [
            'divide_statuses' => [
                $this->orders::DIVIDE_ORDER_STATUS_NEW,
                $this->orders::DIVIDE_ORDER_STATUS_ADD_NEW_ORDER,
                $this->orders::DIVIDE_ORDER_STATUS_APPROVED,
            ],
            '1c_statuses' => [
                $this->orders::ORDER_UPDATE_1C_STATUS_NEW,
                $this->orders::ORDER_1C_STATUS_APPROVED,
            ],
        ];

        $sql = "SELECT 
                    do.status,
                    do.divide_order_id,
                    o.user_id,
                    o.id as order_id,
                    o.`1c_id` as id_1c,
                    o.`1c_status` as status_1c
                FROM 
                s_divide_order do 
                LEFT JOIN s_orders o ON o.id = do.divide_order_id
                LEFT JOIN s_cron_validate_blacklist cvd on cvd.order_id = o.id
                WHERE 1
                    AND cvd.id IS NULL 
                    AND do.auto_generate = 1 
                    AND do.status IN (?@)
                    AND o.`1c_status` IN (?@)
                ORDER BY do.id ASC   
                LIMIT " . self::LIMIT;

        $query = $this->db->placehold(
            $sql,
            $filter['divide_statuses'],
            $filter['1c_statuses'],
        );
        $this->db->query($query);

        return $this->db->results();
    }
}

$start = microtime(true);
(new DivideOrdersValidateBL())->run();
$end = microtime(true);

$time_worked = microtime(true) - $start;
exit(date('c', $start) . ' - ' . date('c', $end) . ' :: script ' . __FILE__ . ' work ' . $time_worked . '  s.');
