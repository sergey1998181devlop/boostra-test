<?php

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

chdir('..');

require_once 'api/Simpla.php';



$simpla = new Simpla();

$callback = $simpla->request->post('callback');
$type = $simpla->request->post('type');

if (!$type) {
    echo "OK";
}

$data = simplexml_load_string($callback);

switch ($type) {
    case "credit":
        if ($data->state == Best2pay::STATUS_APPROVED && $data->order_state == Best2pay::STATUS_ORDER_COMPLETED) {
            $p2p_credit = $simpla->best2pay->getP2PCreditBy('register_id', $data->order_id);
            if ($p2p_credit && $p2p_credit->status != Best2pay::STATUS_APPROVED) {
                //Если у нас в БД есть выдача, но она не прошла
                $order = $simpla->orders->get_order($p2p_credit->order_id);
                if ($order) {
                    $operation_date = date_create_from_format('Y.m.d H:i:s', (string)$data->date);
                    $simpla->best2pay->update_p2pcredit($p2p_credit->id, [
                        'response' => $callback,
                        'status' => $data->state,
                        'operation_id' => $data->id,
                        'complete_date' => $operation_date->format('Y-m-d H:i:s'),
                    ]);

                    $simpla->issuance->issuanceByStatus($data->state, $order, $data);
                } else {
                    //На случай если где-то теряется заявка, можно будет поискать
                    $simpla->logging(__METHOD__, 'Best2payCallback_CREDIT_NO_ORDER', $_REQUEST, $data, 'change_b2p_statuses_credit.txt');
                }
            } else {
                //Если есть какая-то выдача в b2p, но у нас в БД её нет
                $simpla->logging(__METHOD__, 'Best2payCallback_CREDIT', $_REQUEST, $data, 'change_b2p_statuses_credit.txt');
            }
        }
        break;
    case "payment":
        if ($data->state == Best2pay::STATUS_APPROVED && $data->order_state == Best2pay::STATUS_ORDER_COMPLETED) {
            $payment = $simpla->best2pay->get_payment($data->reference);
            if ($payment && $payment->reason_code != 1 && $payment->sent != 1) {
                //Если оплата в БД есть, и она не успешная, и в 1С не отправлена, то вызываем коллбэек с сайта
                $callbackLink = "{$simpla->config->front_url}/best2pay_callback/payment?id={$data->order_id}";
                if (!empty($data->id)) {
                    $callbackLink .= "&operation={$data->id}";
                }
                file_get_contents($callbackLink);
            } else {
                //Если не знаем что за оплата, пишем в лог, и будем искать её
                $simpla->logging(__METHOD__, 'Best2payCallback_Purchase', $_REQUEST, $data, 'change_b2p_statuses_payment.txt');
            }
        }
        break;
    default: break;
}


echo "ok";
