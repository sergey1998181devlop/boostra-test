<?php


require_once 'Simpla.php';

class Issuance extends Simpla
{
    public function issuanceByStatus($p2p_status, $order, $res)
    {
        if ($p2p_status == 'APPROVED')
        {
            $order_id = $order->order_id;
            $contract_number = $this->contracts->create_number($order);

            $this->orders->update_order($order->order_id, array(
                'status' => 10,
                'credit_getted' => 1
            ));

            $operation_date = date_create_from_format('Y.m.d H:i:s', (string)$res->date);

            $this->contracts->make_issuance(
                $order->contract_id,
                is_object($operation_date) ? $operation_date->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'));

            if ($order->card_type === $this->orders::CARD_TYPE_CARD) {
                // включанием после выдачи автосписание для всех карт
                $this->add_autodebit($order);
            }

            if ($this->order_data->read($order->order_id, $this->order_data::RCL_LOAN)) {
                $this->rcl->create_tranche($order);
            }

            // выписываем КД
            $credit_doctor = $this->credit_doctor->getUserCreditDoctor($order->order_id, $order->user_id);
            if (!empty($credit_doctor) && $credit_doctor->status !== $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS) {
                $fio = Helpers::getFIO($order);
                $cd_description = "Кредитный доктор - $credit_doctor->credit_doctor_condition_id к заявке $order->order_id $fio";
                $cd_amount = $credit_doctor->amount * 100;

                $operation_date = date_create_from_format('Y.m.d H:i:s', (string)$res->date);

                // Создание транзакции для КД
                $transaction_id = $this->best2pay->purchaseDOP(
                    $order->card_id,
                    $cd_amount,
                    $cd_description,
                    [
                        'order_id' => $order->order_id,
                        'register_id' => (string)$res->order_id,
                        'contract_number' => $contract_number,
                        'operation' => (string)$res->id,
                        'reason_code' => (string)$res->reason_code,
                        'operation_date' => is_object($operation_date) ? $operation_date->format('Y-m-d H:i:s') : null
                    ],
                    $order->card_type
                );

                $payment_status = (string)$res->state;

                if ($payment_status == 'APPROVED') {

                    // проставляем оплату КД
                    $this->credit_doctor->updateUserCreditDoctorData(
                        $credit_doctor->id,
                        [
                            'status' => $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS,
                            'transaction_id' => $transaction_id,
                        ]
                    );

                    // добавим задание на отправку чека
                    $receipt_data = [
                        'user_id' => $order->user_id,
                        'order_id' => $order->order_id,
                        'amount' => $credit_doctor->amount,
                        'transaction_id' => $transaction_id,
                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                        'payment_type' => $this->receipts::PAYMENT_TYPE_CREDIT_DOCTOR,
                        'organization_id' => $this->receipts::ORGANIZATION_FINTEHMARKET,
                        'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_CREDIT_DOCTOR],
                    ];

                    $this->receipts->addItem($receipt_data);

                    // генерируем документ
                    $this->credit_doctor->createDocument($order, (int)$credit_doctor->amount, $contract_number);

                    $this->operations->add_operation([
                        'user_id' => $order->user_id,
                        'order_id' => $order->order_id,
                        'contract_id' => $order->contract_id,
                        'operation_type_id' => $this->operations->get_operation_type_id('CREDIT_DOCTOR'),
                        'transaction_id' => $credit_doctor->id,
                        'create_date' => date('Y-m-d H:i:s'),
                        'operation_date' => date('Y-m-d'),
                        'amount' => $credit_doctor->amount
                    ]);

                }

            }

            // выписываем телемедицину
            $tv_medical = $this->tv_medical->getTVMedical(
                $order->order_id,
                $order->user_id,
                null,
                null,
                null,
                $this->star_oracle::ACTION_TYPE_ISSUANCE
            );
            if (!empty($tv_medical) && $tv_medical->status !== $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS) {
                $fio = Helpers::getFIO($order);
                $tv_medical_description = "Телемедицина - к заявке $order->order_id $fio";
                $tv_medical_amount = $tv_medical->amount * 100;

                // Создание транзакции для Телемедицины
                $tv_medical_transaction_id = $this->best2pay->purchaseDOP(
                    $order->card_id,
                    $tv_medical_amount,
                    $tv_medical_description,
                    [
                        'order_id' => $order->order_id,
                        'register_id' => (string)$res->order_id,
                        'contract_number' => $contract_number,
                        'operation' => (string)$res->id,
                        'reason_code' => (string)$res->reason_code,
                        'operation_date' => is_object($operation_date) ? $operation_date->format('Y-m-d H:i:s') : null
                    ],
                    $order->card_type
                );

                $tv_medical_payment_status = (string)$res->state;

                if ($tv_medical_payment_status === 'APPROVED') {
                    $this->tv_medical->updatePayment(
                        $tv_medical->id,
                        [
                            'status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS,
                            'payment_id' => $tv_medical_transaction_id,
                            'action_type' => $this->star_oracle::ACTION_TYPE_ISSUANCE,
                        ]
                    );

                    $tv_medical_receipt_data = [
                        'order_id' => $order->order_id,
                        'user_id' => $order->user_id,
                        'amount' => $tv_medical->amount,
                        'transaction_id' => $tv_medical_transaction_id,
                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                        'payment_type' => $this->receipts::PAYMENT_TYPE_TV_MEDICAL,
                        'organization_id' => $this->receipts::ORGANIZATION_FINTEHMARKET,
                        'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_TV_MEDICAL],
                    ];

                    $this->receipts->addItem($tv_medical_receipt_data);

                    $this->tv_medical->createDocument($order, $tv_medical, $contract_number);


                    $tv_medical_operation_type_id = $this->operations->get_operation_type_id('TV_MEDICAL');
                    if (!empty($tv_medical_operation_type_id)) {
                        $this->operations->add_operation([
                            'user_id' => $order->user_id,
                            'order_id' => $order->order_id,
                            'contract_id' => $order->contract_id,
                            'operation_type_id' => $tv_medical_operation_type_id,
                            'transaction_id' => $tv_medical->id,
                            'create_date' => date('Y-m-d H:i:s'),
                            'operation_date' => date('Y-m-d'),
                            'amount' => $tv_medical->amount
                        ]);
                    }
                }
            } else {
                // выписываем SO (fallback для старых заявок)
                $star_oracle = $this->star_oracle->getStarOracle($order->order_id, $order->user_id);
                if (!empty($star_oracle)) {
                    $fio = Helpers::getFIO($order);
                    $so_description = "Звездный оракул - к заявке $order->order_id $fio";
                    $so_amount = $star_oracle->amount * 100;

                    // Создание транзакции для SO
                    $star_oracle_transaction_id = $this->best2pay->purchaseDOP(
                        $order->card_id,
                        $so_amount,
                        $so_description,
                        [
                            'order_id' => $order->order_id,
                            'register_id' => (string)$res->order_id,
                            'contract_number' => $contract_number,
                            'operation' => (string)$res->id,
                            'reason_code' => (string)$res->reason_code,
                            'operation_date' => is_object($operation_date) ? $operation_date->format('Y-m-d H:i:s') : null
                        ],
                        $order->card_type
                    );

                    $star_oracle_payment_status = (string)$res->state;

                    if ($star_oracle_payment_status == 'APPROVED') {
                        $this->star_oracle->updateStarOracleData(
                            $star_oracle->id,
                            [
                                'status' => $this->star_oracle::STAR_ORACLE_STATUS_SUCCESS,
                                'transaction_id' => $star_oracle_transaction_id,
                            ]
                        );

                        $star_oracle_receipt_data = [
                            'order_id' => $order->order_id,
                            'user_id' => $order->user_id,
                            'amount' => $star_oracle->amount,
                            'transaction_id' => $star_oracle_transaction_id,
                            'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                            'payment_type' => $this->receipts::PAYMENT_TYPE_STAR_ORACLE,
                            'organization_id' => $this->receipts::ORGANIZATION_FINTEHMARKET,
                            'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_STAR_ORACLE],
                        ];

                        $this->receipts->addItem($star_oracle_receipt_data);

                    // генерируем документ
                        $this->star_oracle->createDocument($order, $star_oracle, $contract_number);

                        $this->operations->add_operation([
                            'user_id' => $order->user_id,
                            'order_id' => $order->order_id,
                            'contract_id' => $order->contract_id,
                            'operation_type_id' => $this->operations->get_operation_type_id('STAR_ORACLE'),
                            'transaction_id' => $star_oracle->id,
                            'create_date' => date('Y-m-d H:i:s'),
                            'operation_date' => date('Y-m-d'),
                            'amount' => $star_oracle->amount
                        ]);
                    }
                }
            }

            // выписываем безопасную сделку
            $safe_deal = $this->safe_deal->get($order->order_id, $order->user_id);

            if (!empty($safe_deal)) {
                $fio = Helpers::getFIO($order);
                $sd_description = "Безопасная сделка - к заявке $order->order_id $fio";
                $sd_amount = $safe_deal->amount * 100;

                // Создание транзакции для безопасной сделки
                $safe_deal_transaction_id = $this->best2pay->purchaseDOP(
                    $order->card_id,
                    $sd_amount,
                    $sd_description,
                    [
                        'order_id' => $order->order_id,
                        'register_id' => (string)$res->order_id,
                        'contract_number' => $contract_number,
                        'operation' => (string)$res->id,
                        'reason_code' => (string)$res->reason_code,
                        'operation_date' => is_object($operation_date) ? $operation_date->format('Y-m-d H:i:s') : null
                    ],
                    $order->card_type
                );

                $safe_deal_payment_status = (string)$res->state;

                if ($safe_deal_payment_status == 'APPROVED') {
                    // проставляем оплату безопасная сделка
                    $this->safe_deal->update(
                        $safe_deal->id,
                        [
                            'status' => $this->safe_deal::STATUS_SUCCESS,
                            'transaction_id' => $safe_deal_transaction_id,
                        ]
                    );

                    // добавим задание на отправку чека
                    $safe_deal_receipt_data = [
                        'order_id' => $order->order_id,
                        'user_id' => $order->user_id,
                        'amount' => $safe_deal->amount,
                        'transaction_id' => $safe_deal_transaction_id,
                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                        'payment_type' => $this->receipts::PAYMENT_TYPE_SAFE_DEAL,
                        'organization_id' => $this->receipts::ORGANIZATION_MOREDENEG,
                        'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_SAFE_DEAL],
                    ];

                    $this->receipts->addItem($safe_deal_receipt_data);

                    // генерируем документы
                    $this->safe_deal->createDocuments($order, $safe_deal, $contract_number, $this->organizations::MOREDENEG_ID);

                    $this->operations->add_operation([
                        'user_id' => $order->user_id,
                        'order_id' => $order->order_id,
                        'contract_id' => $order->contract_id,
                        'operation_type_id' => $this->operations->get_operation_type_id('SAFE_DEAL'),
                        'transaction_id' => $safe_deal->id,
                        'create_date' => date('Y-m-d H:i:s'),
                        'operation_date' => date('Y-m-d'),
                        'amount' => $safe_deal->amount
                    ]);
                }
            }

            //$this->post_back->sendSaleOrder($order);
            $this->post_back->pushIssuedLoanToQueue($order);
            // если этот займ разделенный, то создадим задание на генерацию новой заявки
            if ($this->orders->getDividePreOrder((int)$order->order_id)) {
                $data_divide_order = [
                    'user_id' => (int)$order->user_id,
                    'main_order_id' => (int)$order->order_id,
                    'status' => $this->orders::DIVIDE_ORDER_STATUS_NEW,
                ];

                $this->orders->addDivideOrder($data_divide_order);
            } else {
                if ($divide_order = $this->orders->getDivideOrderByOrderId((int)$order->order_id)) {
                    // проверим вторая ли это половина от разделенного займа и обновим статус если так
                    if ($divide_order->divide_order_id == $order->order_id) {
                        $this->orders->updateDivideOrder(
                            $divide_order->id,
                            ['status' => $this->orders::DIVIDE_ORDER_STATUS_ISSUED]
                        );
                    }
                }
            }
//                        $this->pdnCalculation->run($order->order_id);
        }
        elseif ($p2p_status == 'TIMEOUT')
        {
            $this->orders->update_order($order->order_id, array('status' => 11, 'pay_result'=>'TIMEOUT'));
        }
        elseif ($p2p_status == 'PENDING')
        {
            $this->orders->update_order($order->order_id, array('status' => 11, 'pay_result'=>'PENDING'));
        }
        elseif ($res == 'ORDER UNREGISTERED') {
            $message = 'Не удалось зарегистрировать выдачу';
            $this->orders->update_order($order->order_id, array('status' => 11, 'pay_result'=>'Ошибка: '.$message));
        }
        elseif ($p2p_status == false)
        {
            $description = (string)$res->description;
            if (!empty($description))
            {
                $this->orders->update_order($order->order_id, array('status' => 11, 'pay_result'=>'Ошибка выдачи: '.$description));
                $this->virtualCard->forUser($order->user_id)->delete();
            }
            else
            {
                $issued_p2pcredits = $this->best2pay->get_p2pcredits(['order_id' => $order->order_id]);
                if (empty($issued_p2pcredits))
                {
                    $this->orders->update_order($order->order_id, array('status' => 11, 'pay_result'=>'Нет ответа от Бест2пей'));
                }
            }
        }
        else
        {
            $message = (string)$res->message;

            if ($message == 'Insufficient funds' || $message == 'Invalid bankacct balance') {

                if ($order->organization_id == $this->organizations::FINLAB_ID) {
                    $response_b2p = $this->best2pay->getBalance('FINLAB_PAY_CREDIT');
                } elseif ($order->organization_id == $this->organizations::VIPZAIM_ID) {
                    $response_b2p = $this->best2pay->getBalance('VIPZAIM_PAY_CREDIT');
                } else {
                    $response_b2p = $this->best2pay->getBalance();
                }
                $xml = simplexml_load_string($response_b2p);
                $b2p_amount = intval($xml->amount) / 100;
                if ($b2p_amount >= $order->amount) {
                    $this->orders->update_order($order->order_id, array('status' => 11, 'pay_result'=>'Ошибка: '.$message.'. Требуется замена карты'));
                } else {
                    $this->orders->update_order($order->order_id, array('status' => 13));
                }

            }
            else
            {
                $this->orders->update_order($order->order_id, array('status' => 11, 'pay_result'=>'Ошибка: '.$message));
                $this->virtualCard->forUser($order->user_id)->delete();
            }
        }
    }

    private function add_autodebit($order)
    {
        $log_old = [];
        $log_update = [];
        $cards = $this->best2pay->get_cards(['user_id' => $order->user_id]);
        foreach ($cards as $c) {
            $log_old[$c->pan] = $c->autodebit;
            $log_update[$c->pan] = 1;
            $this->best2pay->update_card($c->id, ['autodebit' => 1]);
        }
        $this->changelogs->add_changelog([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'autodebit',
            'old_values' => serialize($log_old),
            'new_values' => serialize($log_update),
            'order_id' => $order->order_id,
            'user_id' => $order->user_id,
        ]);
    }

}
