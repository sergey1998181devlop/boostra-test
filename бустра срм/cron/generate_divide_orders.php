<?php
error_reporting(E_ERROR);
ini_set('display_errors', 'Off');
set_time_limit(7200);

require_once dirname(__FILE__) . '/../api/Simpla.php';

/**
 * class GenerateDivideOrders
 * генерирует разделенные займы
 */
class GenerateDivideOrders extends Simpla {
    /**
     * @throws Exception
     */
    public function run()
    {
        $data_filter = [
            'filter_status' => $this->orders::DIVIDE_ORDER_STATUS_NEW,
            'filter_max_date_added' => (new DateTime())->modify('- 1 day')->format('Y-m-d 23:59:59'),
            'filter_limit' => [
                'limit' => 200,
                'offset' => 0,
            ],
        ];

        $divide_orders = $this->orders->getDivideOrders($data_filter);

        foreach ($divide_orders as $divide_order) {
            $this->orders->generate_divide_order($divide_order->id);
        }
    }
}
$start = microtime(true);

(new GenerateDivideOrders())->run();

$end = microtime(true);
$time_worked =  $end - $start;

exit(date('c', $start) . ' - ' . date('c', $end) . ' :: script ' . __FILE__ . ' work ' . $time_worked . '  s.');
