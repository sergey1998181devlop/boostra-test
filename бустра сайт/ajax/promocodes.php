<?php

use boostra\domains\Order;

error_reporting(-1);
ini_set('display_errors', 'On');

date_default_timezone_set('Europe/Moscow');
session_start();

header('Access-Control-Allow-Origin: *');

require_once('../api/Simpla.php');

$simpla   = new Simpla();
$response = ['success' => false];

if (!($user_id = $simpla->request->get('user_id'))) {
    if (!empty($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
}

if (!empty($user_id)) {
    $code = trim($simpla->request->post('code', 'string'));
    if(strlen($code) == $simpla->promocodes::PROMO_LENGTH
        && $user = $simpla->users->get_user((int)$user_id)) {
            $promocode = $simpla->promocodes->getInfoByCode($code);
            if($simpla->promocodes->checkAvailability($promocode, $user->phone_mobile)) {
                $last_order = $simpla->orders->get_last_order((int)$user_id);

                // Проверка если займ новый и не одобренный
                if ($last_order->status == Orders::STATUS_NEW && $last_order->first_loan) {
                    $response['success'] = $simpla->promocodes->addFirstLoanPromocode($last_order, $promocode);
                } else {
                    $apply_result = $simpla->promocodes->apply($last_order, $promocode);

                    if ($apply_result) {
                        $contract = $simpla->orders->check_order_1c($last_order->{'1c_id'});
                        $response['success'] = true;
                        $response['promocode'] = [
                            'id' => $promocode->id,
                            'percent' => $promocode->rate,
                            'limit_sum' => $promocode->limit_sum,
                            'limit_term' => $promocode->limit_term,
                            'contract' => $contract->return->Файл,
                        ];
                    } else {
                        $response['success'] = false;
                        $response['error'] = 'Не удалось применить промокод.';
                    }
                }

                if (($_COOKIE['promocode'] ?? '') == $code) {
                    setcookie('promocode', null, time() - 1, '/', $simpla->config->main_domain);
                }
            }
    }
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

echo json_encode($response);
