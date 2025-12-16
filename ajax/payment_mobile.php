<?php

error_reporting(-1);
ini_set('display_errors', 'On');

date_default_timezone_set('Etc/GMT-1');
session_start();

require_once('../api/Simpla.php');
require_once('../api/addons/TinkoffMerchantAPI.php');


define('TINKOFF_TERMINAL_KEY', "1556097708543AFT");
define('TINKOFF_SECRET_ID', "a56zc57338umq6f1");

$simpla = new Simpla();
$response = array();

$code = $simpla->request->get('token', 'string');

if ($code == '51996Code')
{
    $user_id = $simpla->request->get('user_id', 'string');

    $user = $simpla->users->get_user((int) $user_id);
    if (empty($user))
    {
        $response['error'] = 'UNKNOWN_USER';
    }
    else
    {
        $action = $simpla->request->get('action', 'string');

        switch ($action):

            case 'create_transaction_ip':

                $amount = $simpla->settings->individual_settings['cost']; // стоимость индивидуального рассмотрения

                $individual_order_id = $simpla->request->get('order_id', 'integer');

                if (empty($individual_order_id))
                {
                    $response['error'] = 'EMPTY_ORDER_ID';
                }
                else
                {
                    $response = $simpla->tinkoff->init_payment_ip($individual_order_id, $amount);

                    if (!empty($response['Success']))
                    {
                        $simpla->transactions->add_transaction(array(
                            'user_id' => $user->id,
                            'individual_id' => $individual_order_id,
                            'uid' => $user->uid,
                            'order_id' => $response['OrderId'],
                            'card_id' => '',
                            'amount' => $response['Amount'],
                            'payment_id' => $response['PaymentId'],
                            'terminal_type' => 'IP',
                            'payment_link' => $response['PaymentURL'],
                            'sended' => 0,
                            'status' => $response['Status'],
                            'prolongation' => 0,
                            'code_sms' => 0,
                            'insure_amount' => 0,
                        ));
                    }

                }

                break;

            
            // создаем в тинькове транзацию и записываем ее в базу
            case 'create_transaction':
                $payment_type_debt = 'debt';

                $amount = $simpla->request->get('amount');
                $insure = $simpla->request->get('insure', 'integer');
                $prolongation = $simpla->request->get('prolongation', 'integer');
                $code_sms = $simpla->request->get('code_sms', 'string');
                $payment_type = $simpla->request->get('payment_type', 'string');
                $payment_type = empty($payment_type) ? $payment_type_debt : $payment_type;

                if (empty($amount))
                {
                    $response['error'] = 'EMPTY_AMOUNT';
                }
                else
                {
                    $balance = $simpla->users->get_user_balance($user->id);

                    if (!empty($insure) && !empty($prolongation))
                    {
                        $insure_amount = $balance->prolongation_summ_insurance;
                    }
                    else
                    {
                        $insure_amount = 0;
                    }

                    $zayavka = $balance->zayavka;
                    $zaim_number = $balance->zaim_number;

                    $response = $simpla->tinkoff->init_payment_atop($user->id, $amount);

                    if (!empty($response['Success']))
                    {
                        $simpla->transactions->add_transaction(array(
                            'user_id' => $user->id,
                            'uid' => $user->uid,
                            'order_id' => $response['OrderId'],
                            'card_id' => '',
                            'amount' => $response['Amount'],
                            'payment_id' => $response['PaymentId'],
                            'terminal_type' => 'ATOP',
                            'payment_link' => $response['PaymentURL'],
                            'sended' => 0,
                            'status' => $response['Status'],
                            'prolongation' => $prolongation,
                            'code_sms' => $code_sms,
                            'insure_amount' => $insure_amount,
                            'loan_id' => $zayavka,
                            'contract_number' => $zaim_number,
                            'payment_type' => $payment_type,
                        ));
                    }
                }

            break;

            case 'test':

                $amount = $simpla->request->get('amount');
                $insure = $simpla->request->get('insure', 'integer');
                $prolongation = $simpla->request->get('prolongation', 'integer');
                $code_sms = $simpla->request->get('code_sms', 'string');

                if (empty($amount))
                {
                    $response['error'] = 'EMPTY_AMOUNT';
                }
                else
                {
                    if (!empty($insure) && !empty($prolongation))
                    {
                        $balance = $simpla->users->get_user_balance($user->id);
                        $insure_amount = $balance->prolongation_summ_insurance;
                    }
                    else
                    {
                        $insure_amount = 0;
                    }

                    $response = $simpla->tinkoff->init_payment_atop($user->id, $amount);

                    $response['Success'] = 'Success';
                    $response['OrderId'] = 'order_id';
                    $response['Amount'] = 'amount';
                    $response['PaymentId'] = 'payment_id';
                    $response['PaymentURL'] = 'payment_link';
                    $response['Status'] = 'status';
                }

            break;

            // получаем из тинькова статус транзакции, обновляем в базе и в случае успеха отправляем в 1с
            case 'get_state':

                $payment_id = $simpla->request->get('payment_id', 'string');

                if (empty($payment_id))
                {
                    $response['error'] = 'EMPTY_PAYMENT_ID';
                }
                elseif (!is_numeric($payment_id))
                {
                    $response['error'] = 'Не удалось выполнить оплату.<br />'.$payment_id;
                }
                else
                {
                    $transaction = $simpla->transactions->get_payment_id_transaction($payment_id);
                    $response = $simpla->transactions->update_transaction_state($transaction);
                }

                break;

            //
            case 'send_payment_attach':
                $payment_type_debt = 'debt';

                $card_id = $simpla->request->get('card_id', 'integer');
                $amount = $simpla->request->get('amount');
                $insure = $simpla->request->get('insure', 'integer');
                $prolongation = $simpla->request->get('prolongation', 'integer');
                $code_sms = $simpla->request->get('code_sms', 'string');
                $payment_type = $simpla->request->get('payment_type', 'string');
                $payment_type = empty($payment_type) ? $payment_type_debt : $payment_type;


                if (empty($card_id))
                {
                    $response['error'] = 'EMPTY_CARD_ID';
                }
                elseif (empty($amount))
                {
                    $response['error'] = 'EMPTY_AMOUNT';
                }
                else
                {
                    $summ = $simpla->tinkoff->format_summ($amount);

                    $result = $simpla->soap->send_payment_from_attach_card($card_id, $summ, $user->uid);

                    if (empty($result->return))
                    {
                        $response['error'] = 'NO_PAYMENT_ID';
                    }
                    else
                    {
                        $response['PaymentId'] = $result->return;

                        $zayavka = null;
                        $zaim_number = null;
                        if ($payment_type === $payment_type_debt)
                        {
                            $balance = $simpla->users->get_user_balance($user->id);

                            $zayavka = $balance->zayavka;
                            $zaim_number = $balance->zaim_number;
                        }

                        $transaction_id = $simpla->transactions->add_transaction(array(
                            'user_id' => $user->id,
                            'uid' => $user->uid,
                            'order_id' => '',
                            'card_id' => $card_id,
                            'amount' => $summ,
                            'payment_id' => is_numeric($response['PaymentId']) ? $response['PaymentId'] : 0,
                            'payment_link' => '',
                            'sended' => 0,
                            'status' => '',
                            'prolongation' => $prolongation,
                            'code_sms' => $code_sms,
                            'terminal_type' => 'ATOP',
                            'insure_amount' => 0,
                            'loan_id' => $zayavka,
                            'contract_number' => $zaim_number,
                            'payment_type' => $payment_type
                        ));

                        if (!empty($insure) && isset($balance))
                        {
                            $insure_amount = $balance->prolongation_summ_insurance;

                            $simpla->transactions->update_transaction($transaction_id, array('insure_amount' => $insure_amount ));
                        }
                    }
                }

                break;

            default:
                $response['error'] = 'UNDEFINED_ACTION';

        endswitch;
    }

}
else
{
    $response['error'] = 'UNDEFINED_USER_OR_CODE';
}
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

echo json_encode($response);
