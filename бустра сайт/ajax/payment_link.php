<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(dirname(__DIR__) . '/api/Simpla.php');
require_once(dirname(__DIR__) . '/api/addons/TinkoffMerchantAPI.php');

$simpla = new Simpla();

$api = new TinkoffMerchantAPI(
    "1556097708543AFT",
    "a56zc57338umq6f1",
    'https://securepay.tinkoff.ru/v2'
);

$params = [
    'OrderId' => $_POST['order_id'] . rand(0, 999999999999999999999),
    'Amount' => $_POST['amount'] * 100,
    //				'CustomerKey' => $_POST['customer'],
];

if ($_POST['success_url'])
{
    $params['SuccessURL'] = $_POST['success_url'];
}

$api->init($params);
$response = json_decode(htmlspecialchars_decode($api->response), true);
setcookie("payment_amount", $_POST['amount'] * 100, time() + 3600, '/');
setcookie("PaymentId", $response['PaymentId'], time() + 3600, '/');
if ($_POST['payment_method'] == 'tinkoff')
{
    echo $response['PaymentURL'];
}
else
{
    echo $response['PaymentURL'];
    // $simpla->users->set_charge($response['PaymentId'],$_POST['payment_method']);
    //		echo 'http://boostra.ru/user?status=success';
}
