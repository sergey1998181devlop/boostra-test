<?php

require_once 'AService.php';

class IssuanceService extends AService
{
    public function __construct()
    {
        parent::__construct();

//        $this->response['info'] = array(
//            
//        );

        $this->run();
    }

    private function run()
    {
        if ($uid = $this->request->get('uid')) {
            if ($user_id = $this->users->get_uid_user_id($uid)) {
                $balance = json_decode($this->request->get('balance'));

                $this->logging(__METHOD__, 'DATA', $uid, $this->request->get('balance'), 'issuance.txt');

                $format_balance = $this->users->make_up_user_balance($user_id, $balance);

                if (!empty($balance->НомерЗаявки)) {
                    if ($order_id = $this->orders->get_order_1cid($balance->НомерЗаявки)) {
                        $this->orders->update_order($order_id, array(
                            '1c_status' => '5.Выдан',
                            'credit_getted' => 1,
                        ));

                        $order = $this->orders->get_order((int)$order_id);

                        $user = $this->users->get_user($order->user_id);

                        // страховка
                        if (!empty($balance->НомерПолиса)) {
                            $insurance_period = $this->insurances->get_insurance_period();
                            $start_date = date("Y-m-d 00:00:00", time() + 86400);
                            $end_date = date("Y-m-d 00:00:00", time() + (1 + $insurance_period) * 86400);

                            $insurer = $this->insurances->get_insurer($balance->НомерПолиса);
                            echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
                            var_dump($order);
                            echo '</pre><hr />';
                            $insurance_id = $this->insurances->add_insurance(array(
                                 'number' => $balance->НомерПолиса,
                                 'amount' => $order->insure_amount,
                                 'user_id' => $user->id,
                                 'order_id' => empty($order_id) ? 0 : (int)$order_id,
                                 'create_date' => date('Y-m-d H:i:s'),
                                 'start_date' => $start_date,
                                 'end_date' => $end_date,
                                 'transaction_id' => 0,
                                 'contract_number' => $balance->НомерЗайма,
                                 'insurer' => $insurer,
                             ));

                            $this->insurances->create_insurance_documents($insurance_id);
                        }

                        if (empty($order->b2p) || $order->utm_source == 'bankiros') {
                            $this->post_back->pushIssuedLoanToQueue($order);
                            //$this->post_back->sendSaleOrder($order);
                        }

                        // Кредитный доктор
                        if (!empty($balance->КредитныйДоктор)) {
                            $credit_doctor = $this->credit_doctor->getUserCreditDoctor((int)$order_id, (int)$order->user_id);
                            if (!empty($credit_doctor)) {
                                // проставляем оплату КД
                                $this->credit_doctor->updateUserCreditDoctorData($credit_doctor->id, ['status' => $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS]);

                                // генерируем документ
                                $this->credit_doctor->createDocument($order, (int)$credit_doctor->amount, $balance->НомерЗайма);
                            }
                        }

                        // если этот займ разделенный, то создадим задание на генерацию новой заявки
                        if ($this->orders->getDividePreOrder((int)$order_id)) {
                            $data_divide_order = [
                                'user_id' => (int)$order->user_id,
                                'main_order_id' => (int)$order_id,
                                'status' => $this->orders::DIVIDE_ORDER_STATUS_NEW,
                            ];

                            $this->orders->addDivideOrder($data_divide_order);
                        } else {
                            if ($divide_order = $this->orders->getDivideOrderByOrderId((int)$order_id)) {
                                // проверим вторая ли это половина от разделенного займа и обновим статус если так
                                if ($divide_order->divide_order_id == $order_id) {
                                    $this->orders->updateDivideOrder(
                                        $divide_order->id,
                                        ['status' => $this->orders::DIVIDE_ORDER_STATUS_ISSUED]
                                    );
                                }
                            }
                        }
                    }
                }

                //if (!empty($format_balance->zayavka))
                //{
                //    if ($order_id = $this->orders->get_order_1cid($format_balance->zayavka)) {
                //        $this->orders->update_order($order_id, array('status' => 2, '1c_status' => '5.Выдан'));
                //    }
                //}

                $user_balance = $this->users->get_user_balance($user_id);
                if (empty($user_balance)) {
                    $this->users->add_user_balance($format_balance);
                } else {
                    $this->users->update_user_balance($user_balance->id, $format_balance);
                }

                $this->logging(__METHOD__, $uid, $balance, $order, 'issuance.txt');

                $this->response['success'] = 1;
            } else {
                $this->response['error'] = 'USER_NOT_FOUND';
            }
        } else {
            $this->response['error'] = 'EMPTY_UID';
        }

        $this->json_output();
    }
}

new IssuanceService();