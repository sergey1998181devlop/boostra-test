<?php

error_reporting(-1);
ini_set('display_errors', 'On');

chdir(dirname(__FILE__) . '/../');
require_once 'api/Simpla.php';

use App\Modules\OrderData\Application\Service\OrderDataService;
use App\Modules\OrderData\Infrastructure\Adapter\OrderDataAdapter;
use App\Modules\OrderData\Infrastructure\Repository\OrderDataRepository;

/**
 * B2pSendOnecCron
 *
 * Скрипт отправляет в 1с выдачи займов, оплаты, возвраты доп услуг
 *
 * @author Ruslan Kopyl
 * @copyright 2021
 * @version $Id$
 * @access public
 */
class B2pSendOnecCron extends Simpla
{
    private $organizations_list = [];

    private OrderDataAdapter $orderDataAdapter;

    public function __construct()
    {
        parent::__construct();
        $this->init_organizations();
        $this->initOrderDataAdapter();

        if ($this->request->get('test')) {
            $this->send_credit_rating();
        } else {
            $this->run();
        }
    }

    private function run()
    {
        $this->send('send_rcl_contracts');
        $this->send('send_contracts');
        $this->send('send_payments');
        $this->send('send_refuser_payments');
        $this->send('send_return_credit_doctor');
        $this->send('send_return_multipolis');
        $this->send('send_return_tv_medical');
        $this->send('send_return_star_oracle');
        $this->send('send_return_safe_deal');
        $this->send('send_payments_recurring');

        // не используется
        //$this->send('send_credit_rating');

        // только для очень старых займов
        // $this->send('send_loans');

    }

    private function send($methodname)
    {
        $i = 10;
        do {
            $run_result = $this->$methodname();
            $i--;
        } while ($i > 0 && !empty($run_result));
    }

    /**
     * B2pSendOnecCron::send_return_credit_doctor()
     * Отправляет в 1c возвраты по доп услуге кредитный доктор
     *
     * @return array
     */
    private function send_return_credit_doctor(): array
    {
        if ($items = $this->credit_doctor->getReturnUserCreditDoctorForSend()) {
            foreach ($items as $item) {
                $transaction = $item->is_penalty ? $this->best2pay->get_payment($item->transaction_id) : $this->best2pay->get_transaction($item->transaction_id);
                $return_transaction = $this->best2pay->get_transaction($item->return_transaction_id);
                $operation = $item->is_penalty ? $transaction->operation_id : $transaction->operation;

                if ($return_transaction->type == 'RECOMPENSE_CREDIT_DOCTOR') {
                    $params = [
                        'operation' => $operation,
                        'type' => 'КредитныйДоктор',
                        'amount' => round($return_transaction->amount / 100, 2),
                        'return_transaction_id' => $return_transaction->id,
                        'operation_date' => $return_transaction->operation_date,
                    ];
                    $resp = $this->soap1c->recompense($params);
                } else {
                    $params = [
                        'register_id' => $transaction->register_id,
                        'insure_operation' => $operation,
                        'return_register_id' => $return_transaction->register_id,
                        'return_operation' => $return_transaction->operation,
                        'date' => $return_transaction->created,
                        'amount' => round($return_transaction->amount / 100, 2),
                        'card_pan' => $return_transaction->card_pan,
                        'sector' => $return_transaction->sector,
                    ];

                    $resp = $this->soap->return_credit_doctor($params);
                }


                if (!empty($resp->return) && in_array($resp->return, ['ОК', 'Проведено ранее'])) {
                    $this->credit_doctor->updateUserCreditDoctorData((int)$item->id, ['return_sent' => 2]);
                } else {
                    $this->credit_doctor->updateUserCreditDoctorData((int)$item->id, ['return_sent' => 3]);
                }
            }
        }

        return $items;
    }

    /**
     * B2pSendOnecCron::send_return_multipolis()
     * Отправляет в 1c возвраты по доп услуге мультиполис
     *
     * @return array
     */
    private function send_return_multipolis()
    {
        if ($items = $this->multipolis->getReturnMultipolisForSend()) {
            foreach ($items as $item) {
                $transaction = $this->best2pay->get_payment($item->payment_id);
                $return_transaction = $this->best2pay->get_transaction($item->return_transaction_id);

                if ($return_transaction->type == 'RECOMPENSE_MULTIPOLIS') {
                    $params = [
                        'operation' => $transaction->operation_id,
                        'type' => 'Мультиполис',
                        'amount' => round($return_transaction->amount / 100, 2),
                        'return_transaction_id' => $return_transaction->id,
                        'operation_date' => $return_transaction->operation_date,
                    ];
                    $resp = $this->soap1c->recompense($params);
                } else {
                    $params = [
                        'register_id' => $transaction->register_id,
                        'insure_operation' => $transaction->operation_id,
                        'return_register_id' => $return_transaction->register_id,
                        'return_operation' => $return_transaction->operation,
                        'date' => $return_transaction->created,
                        'amount' => round($return_transaction->amount / 100, 2),
                        'card_pan' => $return_transaction->card_pan,
                        'sector' => $return_transaction->sector,
                    ];

                    $resp = $this->soap->return_multipolis($params);
                }

                if (!empty($resp->return) && in_array($resp->return, ['ОК', 'Проведено ранее'])) {
                    $this->multipolis->update_multipolis((int)$item->id, ['return_sent' => 2]);
                } else {
                    $this->multipolis->update_multipolis((int)$item->id, ['return_sent' => 3]);
                }
            }
        }

        return $items;
    }

    /**
     * B2pSendOnecCron::send_return_tv_medical()
     * Отправляет в 1c возвраты по доп услуге телемедицина
     *
     * @return array
     */
    private function send_return_tv_medical()
    {
        if ($items = $this->tv_medical->getReturnPaymentsForSend()) {
            foreach ($items as $item) {
                // Определяем источник по action_type: issuance -> transaction, остальное -> payment
                $transaction = ($item->action_type === 'issuance') ? $this->best2pay->get_transaction($item->payment_id) : $this->best2pay->get_payment($item->payment_id);
                $return_transaction = $this->best2pay->get_transaction($item->return_transaction_id);
                $operation = ($item->action_type === 'issuance') ? $transaction->operation : $transaction->operation_id;

                if ($return_transaction->type == 'RECOMPENSE_TV_MEDICAL') {
                    $params = [
                        'operation' => $operation,
                        'type' => 'Телемедицина',
                        'amount' => round($return_transaction->amount / 100, 2),
                        'return_transaction_id' => $return_transaction->id,
                        'operation_date' => $return_transaction->operation_date,
                    ];
                    $resp = $this->soap1c->recompense($params);
                } else {
                    $params = [
                        'register_id' => $transaction->register_id,
                        'insure_operation' => $operation,
                        'return_register_id' => $return_transaction->register_id,
                        'return_operation' => $return_transaction->operation,
                        'date' => $return_transaction->created,
                        'amount' => round($return_transaction->amount / 100, 2),
                        'card_pan' => $return_transaction->card_pan,
                        'sector' => $return_transaction->sector,
                    ];

                    $resp = $this->soap->return_tv_medical($params);
                    $this->logging('send_return_tv_medical-конец', json_encode($params, JSON_UNESCAPED_UNICODE), $return_transaction->type, $resp, 'send_return_tv_medical');
                }

                if (!empty($resp->return) && in_array($resp->return, ['ОК', 'Проведено ранее'])) {
                    $this->tv_medical->updatePayment((int)$item->id, ['return_sent' => 2]);
                } else {
                    $this->tv_medical->updatePayment((int)$item->id, ['return_sent' => 3]);
                }
            }
        }

        return $items;
    }

    /**
     * B2pSendOnecCron::send_payments()
     * Отправляет в 1с оплаты
     *
     * @return array
     */
    private function send_payments()
    {
        $params = [
            'sent' => 0,
            'reason_code' => 1,
            'limit' => 5,
            'payment_type' => 'debt',
        ];
        if ($payments = $this->best2pay->get_payments($params)) {
            array_walk($payments, function ($payment) {
                // проверим был ли куплен мультиполис
                $filter_data_multipolis = [
                    'filter_payment_id' => (int)$payment->id,
                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                ];

                if ($multipolis = $this->multipolis->selectAll($filter_data_multipolis, false)) {
                    $payment->multipolis = $multipolis;
                }

                // проверим была ли куплена телемедицина
                $filter_data_tv_medical = [
                    'filter_payment_id' => (int)$payment->id,
                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                ];

                if ($tv_medical_payment = $this->tv_medical->selectPayments($filter_data_tv_medical, false)) {
                    $payment->tv_medical = $tv_medical_payment;
                }

                // проверим была ли куплена звездный оракул
                $filter_data_star_oracle = [
                    'filter_transaction_id' => (int)$payment->id,
                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                    'filter_status' => $this->star_oracle::STAR_ORACLE_STATUS_SUCCESS,
                    'filter_action_type' => $this->star_oracle::ACTION_TYPE_PAYMENT,
                ];

                if ($star_oracle = $this->star_oracle->selectAll($filter_data_star_oracle, false)) {
                    $payment->star_oracle = $star_oracle;
                }

                $payment->organization = $this->organizations_list[$payment->organization_id];

                $payment->order = $this->orders->get_order((int)$payment->order_id);
            });

            $payments_il = [];
            foreach ($payments as $key => $payment) {
                if (!empty($payment->order) && $payment->order->loan_type == 'IL') {
                    $payments_il[] = $payment;
                    unset($payments[$key]);
                }
            }

            if (!empty($payments)) {
                $result = $this->soap->send_payments($payments);
                $isSuccessfulResponse = !empty($result->return) && $result->return === 'OK';
                if ($isSuccessfulResponse) {
                    $this->updatePaymentsAsSent($payments);
                }
            }

            if (!empty($payments_il)) {
                $result = $this->soap->send_payments_il($payments_il);
                $isSuccessfulResponse = !empty($result->return) && $result->return === 'OK';
                if ($isSuccessfulResponse) {
                    $this->updatePaymentsAsSent($payments_il);
                }
            }

        }

        return $payments;
    }

    /**
     * @throws SoapFault
     * @throws JsonException
     */
    private function send_refuser_payments()
    {
        $params = [
            'sent' => 0,
            'reason_code' => 1,
            'limit' => 5,
            'payment_type' => 'refuser',
        ];

        if (($payments = $this->best2pay->get_payments($params)) && !empty($payments)) {
            $soapResponse = $this->soap->send_refuser_payments($payments);

            $isSuccessfulResponse = !empty($soapResponse->return) && $soapResponse->return === 'OK';
            if ($isSuccessfulResponse) {
                $this->updatePaymentsAsSent($payments);
            }
        }

        return $payments;
    }

    /**
     * DEPRECATED
     * B2pSendOnecCron::send_loans()
     * Отправляет в 1с выдачи займов
     *
     * @return array
     */
    private function send_loans()
    {
        $params = [
            'status' => 'APPROVED',
            'sent' => 0,
            'date_from' => date('2023-05-01 00:00:00'),
            'date_to' => date('2023-06-01 00:00:00'),
            'limit' => 3,
            'sort' => 'id ASC',
        ];
        $p2pcredits = $this->best2pay->get_p2pcredits($params);
        if (!empty($p2pcredits)) {
            foreach ($p2pcredits as $p2pcredit) {
                $order_ids[] = $p2pcredit->order_id;
            }
            if (!empty($order_ids)) {
                $orders = array();
                foreach ($this->orders->get_orders(array('id' => $order_ids)) as $o)
                    $orders[$o->order_id] = $o;

                foreach ($p2pcredits as $p2pcredit) {
                    $orders[$p2pcredit->order_id]->p2pcredit = $p2pcredit;
                }

                foreach ($orders as $order) {
                    if ($order_insures = $this->best2pay->get_order_insures($order->order_id))
                        $order->insure = reset($order_insures);

                    $scorista_params = [
                        'order_id' => $order->order_id,
                        'type' => $this->scorings::TYPE_SCORISTA,
                        'status' => $this->scorings::STATUS_COMPLETED
                    ];
                    if ($order_scorista_items = $this->scorings->get_scorings($scorista_params))
                        $order->scorista = end($order_scorista_items);

                }

                if (!empty($orders)) {
                    $result = $this->soap->send_contracts($orders);

                    echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
                    var_dump($result);
                    echo '</pre><hr />';
                    if ($result->return == 'OK') {
                        foreach ($p2pcredits as $p2pcredit) {
                            $this->best2pay->update_p2pcredit($p2pcredit->id, array('sent' => 2, 'send_date' => date('Y-m-d H:i:s')));
                        }
                    }
                }

            }
        }

        return $p2pcredits;
    }

    /**
     * B2pSendOnecCron::send_loans()
     * Отправляет в 1с выдачи займов
     *
     * @return array
     */
    private function send_contracts()
    {
        $params = [
            'status' => 'APPROVED',
            'sent' => 0,
            'date_to' => date('Y-m-d H:i:s', time() - 45), // задержка, не успевают списываться и сохранятся допы
            'date_from' => date('2023-06-01 00:00:00'),
            'limit' => 5,
        ];
        $p2pcredits = $this->best2pay->get_p2pcredits($params);
        if (!empty($p2pcredits)) {
            foreach ($p2pcredits as $p2pcredit) {
                $order_ids[] = $p2pcredit->order_id;
            }
            if (!empty($order_ids)) {
                $orders = array();
                foreach ($this->orders->get_orders(array('id' => $order_ids)) as $o)
                    $orders[$o->order_id] = $o;

                foreach ($p2pcredits as $p2pcredit) {
                    $orders[$p2pcredit->order_id]->p2pcredit = $p2pcredit;
                }

                $orders = $this->orderDataAdapter->addRefererId($orders);

                foreach ($orders as $key => $order) {
                    if ($order->contract = $this->contracts->get_contract($order->contract_id)) {
                        $order->test_user = $this->userData->read($order->user_id, $this->userData::TEST_USER);

                        if ($order_insures = $this->best2pay->get_order_insures($order->order_id))
                            $order->insure = reset($order_insures);

                        if ($order->utm_source == $this->orders::UTM_SOURCE_CRM_AUTO_APPROVE)
                            $order->scorista = $this->scorings->get_scoring_by_order_id($order->order_id, [
                                'type' => $this->scorings::TYPE_AXILINK,
                            ]);
                        else
                            $order->scorista = $this->scorings->get_scoring_by_order_id($order->order_id, [
                                'type' => $this->scorings::TYPE_SCORISTA,
                            ]);

                        $order->user = $this->users->get_user($order->user_id);
                        $order->card = $this->best2pay->get_card($order->card_id);
                        $order->organization = $this->organizations_list[$order->organization_id];

                        $credit_doctor = $this->credit_doctor->getUserCreditDoctor((int)$order->order_id, (int)$order->user_id, $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS);
                        if (!empty($credit_doctor->transaction_id)) {

                            $credit_doctor->transaction = $this->best2pay->get_transaction($credit_doctor->transaction_id);
                            if (!empty($credit_doctor) && !empty($credit_doctor->transaction)) {
                                $order->credit_doctor = $credit_doctor;
                            }
                        }

                        $filter_data_star_oracle = [
                            'filter_order_id' => (int)$order->order_id,
                            'filter_user_id' => (int)$order->user_id,
                            'filter_status' => $this->star_oracle::STAR_ORACLE_STATUS_SUCCESS,
                            'filter_action_type' => [$this->star_oracle::ACTION_TYPE_ISSUANCE],
                        ];
                        $star_oracle = $this->star_oracle->selectAll($filter_data_star_oracle, false);

                        if (!empty($star_oracle) && !empty($star_oracle->transaction_id)) {
                            $star_oracle->transaction = $this->best2pay->get_transaction($star_oracle->transaction_id);

                            if (!empty($star_oracle->transaction)) {
                                $order->star_oracle = $star_oracle;
                            }
                        }

                        $filter_data_tv_medical = [
                            'filter_order_id' => (int)$order->order_id,
                            'filter_user_id' => (int)$order->user_id,
                            'filter_status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS,
                            'filter_action_type' => [$this->star_oracle::ACTION_TYPE_ISSUANCE],
                        ];
                        $tv_medical = $this->tv_medical->selectPayments($filter_data_tv_medical, false);

                        if (!empty($tv_medical) && !empty($tv_medical->payment_id)) {
                            $tv_medical->transaction = $this->best2pay->get_transaction($tv_medical->payment_id);
                            if (!empty($tv_medical->transaction)) {
                                $order->tv_medical = $tv_medical;
                            }
                        }

                        $filter_data_safe_deal = [
                            'filter_order_id' => (int)$order->order_id,
                            'filter_user_id' => (int)$order->user_id,
                            'filter_status' => $this->safe_deal::STATUS_SUCCESS
                        ];
                        $safe_deal = $this->safe_deal->selectAll($filter_data_safe_deal, false);

                        if (!empty($safe_deal) && !empty($safe_deal->transaction_id)) {
                            $safe_deal->transaction = $this->best2pay->get_transaction($safe_deal->transaction_id);

                            if (!empty($safe_deal->transaction)) {
                                $order->safe_deal = $safe_deal;
                            }
                        }

                        // Получатель платежа (телефон/карта) из фактического ответа s_b2b_p2pcredits.response
                        $this->fillRecipientSourceFromP2pResponse($order);
                    } else {
                        unset($orders[$key]);
                        $this->best2pay->update_p2pcredit($p2pcredit->id, array('sent' => 6));
                    }
                }

                if (!empty($orders)) {
                    $result = $this->soap->send_contracts_new($orders);

                    echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
                    var_dump($result);
                    echo '</pre><hr />';
                    if ($result->return == 'OK') {
                        foreach ($orders as $order) {
                            $this->best2pay->update_p2pcredit($order->p2pcredit->id, array('sent' => 2, 'send_date' => date('Y-m-d H:i:s')));
                            if (!empty($order->is_user_credit_doctor))
                                $this->soap1c->send_credit_doctor($order->id_1c);
                        }
                    }
                }

            }
        }

        return $p2pcredits;
    }

    public function send_credit_rating()
    {
        $this->db->query("
            SELECT 
                bp.id,
                bp.register_id,
                bp.operation_id,
                bp.amount,
                bp.created,
                bp.user_id,
                bp.card_pan
            FROM b2p_payments AS bp
            WHERE bp.sent = 0
            AND bp.reason_code = 1
            AND bp.payment_type IN (?@)
            ORDER BY bp.id ASC
            LIMIT 5
        ", $this->best2pay::PAYMENT_TYPE_CREDIT_RATING_MAPPING_ALL);

        $credit_ratings = $this->db->results();
        if (!empty($credit_ratings)) {
            foreach ($credit_ratings as $credit_rating) {
                $user_ids[] = $credit_rating->user_id;
            }
            if (!empty($user_ids)) {
                $users = array();
                foreach ($this->users->get_users(array('id' => $user_ids)) as $u)
                    $users[$u->id] = $u;

                foreach ($credit_ratings as $credit_rating) {
                    $credit_rating->uid = $users[$credit_rating->user_id]->UID;

                    $scorista_params = [
                        'user_id' => $credit_rating->user_id,
                        'type' => $this->scorings::TYPE_SCORISTA,
                        'status' => $this->scorings::STATUS_COMPLETED
                    ];
                    if ($scorista_items = $this->scorings->get_scorings($scorista_params)) {
                        $scorista = end($scorista_items);
                        $credit_rating->agrid = $scorista->scorista_id;
                    }


                }


                if (!empty($credit_ratings)) {
                    foreach ($credit_ratings as $credit_rating) {
                        $result = $this->soap->send_credit_rating((array)$credit_rating);

                        if ($result == 'OK') {
                            $this->best2pay->update_payment($credit_rating->id, array('sent' => 2, 'send_date' => date('Y-m-d H:i:s')));
                        }

                    }
                }

            }
        }

        return $credit_ratings;
    }

    private function init_organizations()
    {
        foreach ($this->organizations->getList() as $org) {
            $this->organizations_list[$org->id] = $org;
        }
    }

    /**
     * Updates payments as successfully sent.
     *
     * @param array $payments
     * @return void
     */
    private function updatePaymentsAsSent(array $payments): void
    {
        foreach ($payments as $payment) {
            $this->best2pay->update_payment($payment->id, [
                'sent' => 1,
                'send_date' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * B2pSendOnecCron::send_return_star_oracle()
     * Отправляет в 1c возвраты по доп услуге Звездный Оракул
     *
     * @return array
     */
    private function send_return_star_oracle(): array
    {
        if ($items = $this->star_oracle->getReturnStarOracleForSend()) {
            foreach ($items as $item) {
                $transaction = (empty($item->action_type) || $item->action_type === $this->star_oracle::ACTION_TYPE_ISSUANCE) ? $this->best2pay->get_transaction($item->transaction_id) : $this->best2pay->get_payment($item->transaction_id);
                $return_transaction = $this->best2pay->get_transaction($item->return_transaction_id);
                $operation = (empty($item->action_type) || $item->action_type === $this->star_oracle::ACTION_TYPE_ISSUANCE) ? $transaction->operation : $transaction->operation_id;

                if ($return_transaction->type === 'RECOMPENSE_STAR_ORACLE') {
                    $params = [
                        'operation' => $operation,
                        'type' => 'ЗвездныйОракул',
                        'amount' => round($return_transaction->amount / 100, 2),
                        'return_transaction_id' => $return_transaction->id,
                        'operation_date' => $return_transaction->operation_date,
                    ];
                    $resp = $this->soap->recompense($params);
                } else {
                    $params = [
                        'register_id' => $transaction->register_id,
                        'insure_operation' => $operation,
                        'return_register_id' => $return_transaction->register_id,
                        'return_operation' => $return_transaction->operation,
                        'date' => $return_transaction->created,
                        'amount' => round($return_transaction->amount / 100, 2),
                        'card_pan' => $return_transaction->card_pan,
                        'sector' => $return_transaction->sector,
                    ];

                    $resp = $this->soap->return_star_oracle($params);
                }


                if (!empty($resp->return) && in_array($resp->return, ['ОК', 'Проведено ранее'])) {
                    $this->star_oracle->updateStarOracleData((int)$item->id, ['return_sent' => 2]);
                } else {
                    $this->star_oracle->updateStarOracleData((int)$item->id, ['return_sent' => 3]);
                }
            }
        }

        return $items;
    }

    /**
     * B2pSendOnecCron::send_return_safe_deal()
     * Отправляет в 1c возвраты по доп услуге Безопасная сделка
     *
     * @return array
     */
    private function send_return_safe_deal(): array
    {
        if ($items = $this->safe_deal->getReturnSafeDealForSend()) {
            foreach ($items as $item) {
                $transaction = $this->best2pay->get_transaction($item->transaction_id);
                $return_transaction = $this->best2pay->get_transaction($item->return_transaction_id);
                $operation = $transaction->operation;

                if ($return_transaction->type === 'RECOMPENSE_SAFE_DEAL') {
                    $params = [
                        'operation' => $operation,
                        'type' => 'БезопаснаяСделка',
                        'amount' => round($return_transaction->amount / 100, 2),
                        'return_transaction_id' => $return_transaction->id,
                        'operation_date' => $return_transaction->operation_date,
                    ];
                    $resp = $this->soap->recompense($params);
                } else {
                    $params = [
                        'register_id' => $transaction->register_id,
                        'insure_operation' => $operation,
                        'return_register_id' => $return_transaction->register_id,
                        'return_operation' => $return_transaction->operation,
                        'date' => $return_transaction->created,
                        'amount' => round($return_transaction->amount / 100, 2),
                        'card_pan' => $return_transaction->card_pan,
                        'sector' => $return_transaction->sector,
                    ];

                    $resp = $this->soap->return_safe_deal($params);
                }


                if (!empty($resp->return) && in_array($resp->return, ['ОК', 'Проведено ранее'])) {
                    $this->safe_deal->update((int)$item->id, ['return_sent' => 2]);
                } else {
                    $this->safe_deal->update((int)$item->id, ['return_sent' => 3]);
                }
            }
        }

        return $items;
    }

    /**
     * B2pSendOnecCron::send_payments_recurring()
     * Отправляет в 1с оплаты по реккурентным платежам
     *
     * @return array
     * @throws \SoapFault
     */
    private function send_payments_recurring()
    {
        $params = [
            'sent' => 0,
            'reason_code' => 1,
            'limit' => 5,
            'payment_type' => $this->best2pay::PAYMENT_TYPE_RECURRING,
        ];

        if ($payments = $this->best2pay->get_payments($params)) {
            array_walk($payments, function ($payment) {
                // проверим была ли куплена звездный оракул
                $filter_data_star_oracle = [
                    'filter_transaction_id' => (int)$payment->id,
                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                    'filter_status' => $this->star_oracle::STAR_ORACLE_STATUS_SUCCESS,
                    'filter_action_type' => $this->star_oracle::ACTION_TYPE_PAYMENT,
                ];

                if ($star_oracle = $this->star_oracle->selectAll($filter_data_star_oracle, false)) {
                    $payment->star_oracle = $star_oracle;
                }
            });
        }

        if (!empty($payments)) {
            $result = $this->soap->send_payments_recurring($payments);
            $isSuccessfulResponse = !empty($result->return) && $result->return === 'OK';
            if ($isSuccessfulResponse) {
                $this->updatePaymentsAsSent($payments);
            }
        }

        return $payments;
    }

    private function initOrderDataAdapter()
    {
        $orderDataRepository = new OrderDataRepository($this->db);
        $orderDataService = new OrderDataService($orderDataRepository);
        $this->orderDataAdapter = new OrderDataAdapter($orderDataService);
    }

    /**
     * Разбирает XML-ответ Best2Pay операции выдачи/оплаты и извлекает данные получателя платежа.
     * Возвращает:
     * - phone: телефон получателя (для СБП по телефону)
     * - pan: маскированный PAN карты получателя
     * - external_id: идентификатор операции для СБП (если присутствует в XML)
     *
     * @return array ['phone' => '009201112233'|null, 'pan' => '2200******1234'|null, 'external_id' => '... '|null]
     */
    private function parsePayerSourceFromXml(string $xml): array
    {
        $result = ['phone' => null, 'pan' => null, 'external_id' => null];
        if ($xml === '' || $xml === null) {
            return $result;
        }

        // Если строка сохранена как serialized-like: s:592:"...";
        if (preg_match('/^s:\d+:"(.*)";$/s', $xml, $m)) {
            $xml = stripcslashes($m[1]);
        }

        // Уберём лишние управляющие символы/пробелы
        $xml = trim($xml, "\xEF\xBB\xBF\x00..\x1F\x20\x7F\r\n\t ");

        libxml_use_internal_errors(true);
        $sx = simplexml_load_string($xml);
        if ($sx === false) {
            libxml_clear_errors();
            return $result;
        }

        $phone = isset($sx->phone) ? trim((string)$sx->phone) : '';
        if ($phone !== '') {
            $result['phone'] = $phone;
        }

        $pan2 = isset($sx->pan2) ? trim((string)$sx->pan2) : '';
        if ($pan2 !== '') {
            $result['pan'] = $pan2;
        }

        // external_id может присутствовать только для СБП/переводов по телефону
        $externalId = isset($sx->external_id) ? trim((string)$sx->external_id) : '';
        if ($externalId !== '') {
            $result['external_id'] = $externalId;
        }

        libxml_clear_errors();
        return $result;
    }

    /**
     * Заполняет у заказа данные получателя платежа из XML-ответа Best2Pay (s_b2b_p2pcredits.response)
     * - Телефон получателя -> $order->recipient_phone
     * - Маскированный PAN карты получателя -> $order->recipient_card_pan
     * - External ID (для СБП по телефону) -> $order->transaction_external_id
     *
     * @param object $order
     * @return void
     */
    private function fillRecipientSourceFromP2pResponse(object $order): void
    {
        if (empty($order->p2pcredit) || empty($order->p2pcredit->response)) {
            return;
        }
        $source = $this->parsePayerSourceFromXml((string)$order->p2pcredit->response);
        if (!empty($source['phone'])) {
            $order->recipient_phone = $source['phone'];
        }
        if (!empty($source['pan'])) {
            $order->recipient_card_pan = $source['pan'];
        }
        if (!empty($source['external_id'])) {
            $order->transaction_external_id = $source['external_id'];
        }
    }

    /**
     * B2pSendOnecCron::send_rcl_contracts()
     * Отправляет в 1c контракты RCL (Revolving Credit Line)
     */
    private function send_rcl_contracts()
    {
        $contracts = $this->rcl->get_contracts([
            'status' => $this->rcl::STATUS_APPROVED,
            'sent_onec' => $this->rcl::SENT_ONEC_NEW,
            'limit' => 5
        ]);

        if (empty($contracts)) {
            return;
        }

        foreach ($contracts as $contract) {
            $contract->user = $this->users->get_user($contract->user_id);
            $contract->organization = $this->organizations->get_organization($contract->organization_id);
            $tranche = $this->rcl->get_first_tranche($contract->id);
            $contract->order = $this->orders->get_order($tranche->order_id);

            if ($contract->pdn_calculation_id) {
                $pdnRow = $this->pdnCalculation->getPdnRowById($contract->pdn_calculation_id);
                if ($pdnRow && $pdnRow->result) {
                    $result = json_decode($pdnRow->result);
                    $contract->pdn = $result->dbi ?? '';
                    $contract->calculation_type = $result->calculation_type ?? '';
                }
            }

            $response = $this->soap1c->SendRcl($contract);

            if (!empty($response->return) && $response->return === 'OK') {
                $this->rcl->mark_sent_onec($contract->id, $this->rcl::SENT_ONEC_SUCCESS);
            } else {
                $this->rcl->mark_sent_onec($contract->id, $this->rcl::SENT_ONEC_ERROR);
            }
        }
    }
}

$cron = new B2pSendOnecCron();
