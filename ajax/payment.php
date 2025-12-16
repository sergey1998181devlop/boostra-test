<?php

error_reporting(-1);
ini_set('display_errors', 'On');

date_default_timezone_set('Europe/Moscow');
session_start();

header('Access-Control-Allow-Origin: *');

require_once('../api/Simpla.php');
require_once('../api/addons/TinkoffMerchantAPI.php');


define('TINKOFF_TERMINAL_KEY', "1556097708543AFT");
define('TINKOFF_SECRET_ID', "a56zc57338umq6f1");

$simpla = new Simpla();
$response = array();

if (!($user_id = $simpla->request->get('user_id')))
    if (!empty($_SESSION['user_id']))
        $user_id = $_SESSION['user_id'];

if (!empty($user_id))
{
    $user = $simpla->users->get_user((int)$user_id);
    if (empty($user))
    {
        $response['error'] = 'UNKNOWN_USER';
    }
    else
    {

        $action = $simpla->request->get('action', 'string');

        switch ($action):

            case 'hold':

                $card_id = $simpla->request->get('card_id');
                $rebill_id = $simpla->request->get('rebill_id');

                $response = $simpla->tinkoff->hold($user_id, $card_id, $rebill_id);

                if ($order_id = $simpla->request->get('order_id'))
                {
                    if (!empty($response['ErrorCode']) && in_array($response['ErrorCode'], array('1005', '1054', '1057', '1058', '1059')))
                    {
                        $simpla->orders->update_order($order_id, array(
                            'pay_result' => $response['ErrorCode'].' '.$response['Message'],
                        ));
                    }
                }

            break;

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
                        $transaction_id = $simpla->transactions->add_transaction(array(
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
                        if (empty($transaction_id))
                        {
                            $response = ['error' => 'Не удалось создать транзакцию, попробуйте позже'];
                        }
                    }

                }
                
            break;
            
            // создаем в тинькове транзацию и записываем ее в базу
            case 'create_transaction':
                $payment_type_debt = 'debt';

                $amount = $simpla->request->get('amount');
                $insure = $simpla->request->get('insure', 'integer');
                $insurer = $simpla->request->get('insurer', 'string');
                $prolongation = $simpla->request->get('prolongation', 'integer');
                $code_sms = $simpla->request->get('code_sms', 'string');
                $payment_type = $simpla->request->get('payment_type', 'string') ?: $payment_type_debt;
                $order_id = $simpla->request->get('order_id', 'integer');
                $creditRatingType = $simpla->request->get('credit_rating_type', 'integer');

                if (empty($amount))
                {
                    $response['error'] = 'EMPTY_AMOUNT';
                }
                else
                {
                    $balance = $simpla->users->get_user_balance($user->id);

                    if (!empty($insure) && !empty($prolongation))
                    {
                        
                        $insure_amount = $balance->prolongation_summ_insurance + ($amount < $balance->penalty ? $amount : $balance->penalty);
                    }
                    else
                    {
                        if ($balance->penalty > 0)
                            $insure_amount = ($amount < $balance->penalty ? $amount : $balance->penalty);
                        else
                            $insure_amount = 0;
                    }

                    if ($creditRatingType && isset($simpla->transactions::PAYMENT_TYPE_CREDIT_RATING_MAPPING[$creditRatingType])) {
                        $payment_type = $simpla->transactions::PAYMENT_TYPE_CREDIT_RATING_MAPPING[$creditRatingType];
                    }

                    $zayavka = $balance->zayavka;
                    $zaim_number = $balance->zaim_number;

                    $response = $simpla->tinkoff->init_payment_atop($user->id, $amount);

                    if (!empty($response['Success']))
                    {
                        $transaction_data = [
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
                            'insurer' => $insurer,
                            'insure_amount' => $insure_amount,
                            'loan_id' => $zayavka,
                            'contract_number' => $zaim_number,
                            'payment_type' => $payment_type
                        ];

                        if (!empty($order_id)) {
                            $transaction_data['crm_order_id'] = $order_id;
                        }

                        $transaction_id = $simpla->transactions->add_transaction($transaction_data);

                        if (empty($transaction_id) || !is_numeric($transaction_id))
                        {
                            $response = ['error' => 'Не удалось создать транзакцию, попробуйте позже'];
                        }
                    }
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

                    if ($transaction->user_id == $user->id && in_array($response['Status'], ['CONFIRMED', 'AUTHORIZED'])) {

                        $result = $simpla->soap->send_payment_result($transaction, $response['Status'] == 'AUTHORIZED');
                        $simpla->transactions->update_transaction($transaction->id, [
                            'sended' => 1,
                            'send_result' => $result->return ?? serialize($result),
                            'status' => $response['Status'],
                        ]);

                        // если это Кредитный рейтинг, выполним ряд действий
                        if ($transaction->payment_type == 'credit_rating') {
                            //при проверках оплаты Кредитного рейтинга для новых пользователей запишем признак пропуска шага КР
                            if (empty($user->skip_credit_rating)) {
                                $simpla->users->addSkipCreditRating((int)$user->id, 'PAY');
                            }

                            $rating_document_id = $simpla->credit_rating->handle_credit_rating_paid($user, $transaction->id);
                            $response['Message'] = 'Вам предоставлен ваш кредитный рейтинг на ' . (new DateTime(
                                    $transaction->created
                                ))->format('d.m.Y') .
                                '<br>Он всегда доступен вам в разделе Документы вашего личного кабинета, а также по ссылке ' .
                                '<a href="/user/docs?action=credit_rating&rating_id=' . $rating_document_id . '">Кредитный рейтинг</a>';
                        }
                    }
                }

            break;

            //
            case 'send_payment_attach':
                $payment_type_debt = 'debt';

                $card_id = $simpla->request->get('card_id', 'integer');
                $amount = $simpla->request->get('amount');
                $insurer = $simpla->request->get('insurer', 'string');
                $insure = $simpla->request->get('insure', 'integer');
                $prolongation = $simpla->request->get('prolongation', 'integer');
                $code_sms = $simpla->request->get('code_sms', 'string');
                $payment_type = $simpla->request->get('payment_type', 'string') ?: $payment_type_debt;
                $order_id = $simpla->request->get('order_id', 'integer');
                $credit_rating = $this->request->get('credit_rating', 'integer');
                $creditRatingType = $this->request->get('credit_rating_type', 'integer');

                if (
                    $credit_rating
                    && $creditRatingType
                    && $this->best2pay::PAYMENT_TYPE_CREDIT_RATING_MAPPING[$creditRatingType]
                ) {
                    $payment_type = $this->best2pay::PAYMENT_TYPE_CREDIT_RATING_MAPPING[$creditRatingType];
                }

                // Убираем комиссию с привязанных карт для КР, т.к. 1С плюсует 30 руб.
                /*if ($payment_type === 'credit_rating') {
                    $amount = 369;
                }*/

                // todo удалить костыль для проверки оплаты рейтинга
                if ($simpla->users::validateNoSkipUser((int) $user->id, (string) $user->phone_mobile)) {
                    $amount = 1;
                }

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

                        $transaction_data = [
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
                            'insurer' => $insurer,
                            'insure_amount' => $insure,
                            'loan_id' => $zayavka,
                            'contract_number' => $zaim_number,
                            'payment_type' => $payment_type
                        ];

                        if (!empty($order_id)) {
                            $transaction_data['crm_order_id'] = $order_id;
                        }

                        $transaction_id = $simpla->transactions->add_transaction($transaction_data);

                        if (!empty($insure) && isset($balance))
                        {
                            if ($balance->penalty > 0)
                                $insure_amount = $amount < $balance->penalty ? $amount : $balance->penalty;
                            else
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
    $response['error'] = 'UNDEFINED_USER';
}
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

echo json_encode($response);
