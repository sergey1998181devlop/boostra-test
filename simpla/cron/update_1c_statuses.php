<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
header('Expires: '.date('r'));
session_start();
require_once(__DIR__ . '/../../api/Simpla.php');

$simpla = new Simpla();
$orders = [];
$page   = 1;
while(!empty($chunk = $simpla->orders->get_orders(['status_1c' => '3.Одобрено', 'page' => $page]))) {
    $orders = array_merge($orders, $chunk);
    $page++;
}

if(!empty($orders)) {
    $resp = new stdClass;
    $resp->return = new stdClass;
    $orders_1c = $simpla->soap->get_1c_statuses($orders);
    if(!empty($orders_1c)) {
        foreach($orders_1c as $order) {
            $resp->return->Статус = $order->СтатусЗаявки;
            $resp->return->Комментарий = $order->Комментарий ?? '';
            $resp->return->ОфициальныйОтвет = $order->ОфициальныйОтвет ?? '';
            $db_order = reset(array_filter($orders,
                                            function($item) use ($order) {
                                                return $item->id_1c == $order->НомерЗаявки;
                                            }));
            if(!empty($db_order) && $db_order->id) {
                $simpla->orders->update_1c_status($db_order, $resp);
            }
        }
    }
}

session_write_close();
