<?php

session_start();
chdir('..');

require 'api/Simpla.php';
$simpla = new Simpla();
define('ROOT', dirname(__DIR__));

$orders = $simpla->orders->get_orders([
    'date_from' => '2024-05-01',
    'date_to' => '2024-06-30',
    'sort' => 'order_id_asc'
]);
foreach ($orders as $order) {
    $filePath = ROOT.'/files/credit_history/'.$order->order_id.'.xml';
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}
exit();