<?php

include_once 'inc/header.php';

use Messengers\Main;

$simpla = new Simpla();
$action = $simpla->request->get('action');
$messenger = $simpla->request->get('message');

/**
 * Отправить смс
 * @param string $phone
 * @param string $message
 * @return void
 */
function sendSms(string $phone, string $message):void{
    $msg = iconv('utf8', 'cp1251', $message);
    var_dump($message);
    $simpla = new Simpla();
    $simpla->notify->send_sms($phone, $msg);
}


try {
    $messenger = Main::getProvider($messenger);
    if($messenger && method_exists($messenger, $action)){
        $response = $messenger->$action();
        $result = (object)[
            'body' => $response,
            'code' => 200
        ];
        header('HTTP/1.1 200 Ok');
        header('Content-Type: application/json');
        echo json_encode($result, JSON_THROW_ON_ERROR);
    }
} catch (JsonException $e) {

}