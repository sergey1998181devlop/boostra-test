<?php

error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
chdir('..');

require_once 'api/Simpla.php';

$simpla = new Simpla();
$result = '';

if ($simpla->request->post('token') != 'pD5%79ju-') {
    die('Access denied!');
}

$user_id = $simpla->request->post('user_id');

if (!isset($user_id)) {
    die('Need user_id parameter!');
}

$user = $simpla->users->get_user(intval($user_id));

if (!isset($user)) {
    die('Not found user!');
}

$orders = $simpla->orders->get_orders(array('user_id'=>$user_id));

if (!isset($orders)) {
    die('Not found any orders!');
}

foreach ($orders as $order) {
    $simpla->orders->update_1c_status($order);
}

echo 'ok';