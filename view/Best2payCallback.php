<?php

use App\Core\Application\Application;
use App\Modules\UserOrderGift\Services\UserOrderGiftService;

require_once 'View.php';
require_once dirname(__DIR__) . '/api/addons/TVMedicalApi.php';

class Best2payCallback extends View
{
    private const LOG_FILE = 'b2p_callback.txt';
    /**
     * @var mixed|null
     */
    private UserOrderGiftService $userOrderGiftService;

    public function __construct()
    {
        $app = Application::getInstance();
        $this->userOrderGiftService = $app->make(UserOrderGiftService::class);
        parent::__construct();
    }

    public function fetch()
    {
        if ($this->show_unaccepted_agreement_modal() && $this->request->get('action', 'string') != 'payment')
        {
            header('Location: '.$this->config->root_url.'/user');
            exit();
        }

        switch ($this->request->get('action', 'string')):
            case 'add_card':
                $this->add_card_action();
                $this->design->assign('page_type', 'card');
                break;

            case 'payment':
                $this->payment_action();
                $this->design->assign('page_type', 'payment');
                break;

            case 'payment_sbp':
                $this->payment_sbp();
                $this->design->assign('page_type', 'payment_sbp');
                break;

            case 'sbp_token':
                $this->sbp_token();
                break;

            case 'sbp_token_dev':
                $this->sbp_token_dev();
                $this->design->assign('page_type', 'card');
                break;

            case 'recurrent':
                $this->recurrent();
                break;

            default:
                $meta_title = 'Ошибка';
                $this->design->assign('error', 'Ошибка');

        endswitch;

        return $this->design->fetch('best2pay_callback.tpl');
    }

    /** Колбэк после привязки */
    public function sbp_token()
    {
        $logFileName = 'sbp_tokens_callback.txt';
        $subscribe = file_get_contents('php://input');

        $this->logging(__METHOD__, '', 'Получен запрос sbp_token', ['$_REQUEST' => $_REQUEST, '$_SERVER' => $_SERVER], $logFileName);

        $this->addSbpAccount($subscribe);
    }

    private function addSbpAccount($subscribe)
    {
        $logFileName = 'sbp_tokens_callback.txt';

        $subscribe = simplexml_load_string($subscribe);

        $this->logging(__METHOD__, '', '', ['$_REQUEST' => $_REQUEST, 'subscribe' => $subscribe], $logFileName);

        if (empty($subscribe->subscription_state) || (string)$subscribe->subscription_state !== 'ACCEPTED') {
            $this->logging(__METHOD__, '', 'Некорректный subscribe', ['subscribe' => $subscribe], $logFileName);
            return;
        }

        $existingSbpAccount = $this->best2pay->get_sbp_account([
            'qrcId' => $subscribe->qrcId,
            'token' => $subscribe->token,
        ]);

        if (!empty($existingSbpAccount)) {
            $this->logging(__METHOD__, '', 'СБП счет уже был добавлен ранее', ['subscribe' => $subscribe, 'existing_sbp_account' => $existingSbpAccount], $logFileName);
            return;
        }

        $transaction = $this->best2pay->get_reference_transaction($subscribe->qrcId);

        if (empty($transaction)) {
            $this->logging(__METHOD__, '', 'Не найдена транзакция', ['subscribe' => $subscribe, 'transaction' => $transaction], $logFileName);
            return;
        }

        $recurring_consent = isset($transaction->recurring_consent) ? (int)$transaction->recurring_consent : 1;

        $sbp_account_id = $this->best2pay->add_sbp_account([
            'user_id' => $transaction->user_id,
            'order_id' => $subscribe->order_id,
            'qrcId' => $subscribe->qrcId,
            'subscription_state' => $subscribe->subscription_state,
            'token' => $subscribe->token,
            'member_id' => $subscribe->member_id,
            'signature' => $subscribe->signature,
            'created_at' => date('Y-m-d H:i:s'),
            'deleted' => 0,
            'autodebit' => $recurring_consent,
            'autodebit_changed_at' => date('Y-m-d H:i:s')
        ]);
        if (empty($sbp_account_id)) {
            $this->logging(__METHOD__, '', 'Не удалось добавить СБП счет', ['subscribe' => $subscribe, 'sbp_account_id' => $sbp_account_id], $logFileName);
            return;
        } else {
            $this->logging(__METHOD__, '', 'Добавлен СБП счет',
                ['user_id' => $transaction->user_id, 'sbp_account_id' => $sbp_account_id], $logFileName);
        }

        $this->best2pay->add_sbp_log([
            'card_id' => $sbp_account_id,
            'action' => Best2pay::CARD_ACTIONS['SUCCESS_ATTACH_SBP'],
            'date' => date('Y-m-d H:i:s')
        ]);

        $user = $this->users->get_user((int)$transaction->user_id);

        if ( $user->utm_source === 'test123') {
            if (!empty($user)) {
                if (empty($user->card_added)) {
                    $this->users->update_user((int)$transaction->user_id, [
                        'card_added'        => 1,
                        'card_added_date'   => date('Y-m-d H:i:s')
                    ]);
                }

                // Флаг регистрации устанавливаем всегда для test123
                $this->user_data->set((int)$transaction->user_id, $this->user_data::IS_ADDED_SBP_DURING_REGISTRATION, 1);

                $this->logging(__METHOD__, '', 'Успешно привязан счет СБП при регистрации',
                    ['user_id' => $transaction->user_id, 'sbp_account_id' => $sbp_account_id], $logFileName);
            }

        } else {
            // Если не добавлена карта, значит СБП добавляется при регистрации (а не в ЛК)
            if (!empty($user) && empty($user->card_added)) {
                $this->users->update_user((int)$transaction->user_id, [
                    'card_added'        => 1,
                    'card_added_date'   => date('Y-m-d H:i:s')
                ]);

                $this->user_data->set((int)$transaction->user_id, $this->user_data::IS_ADDED_SBP_DURING_REGISTRATION, 1);

                $this->logging(__METHOD__, '', 'Успешно привязан счет СБП при регистрации.',
                    ['user_id' => $transaction->user_id, 'sbp_account_id' => $sbp_account_id], $logFileName);
            }

        }

        $newOrderStatuses = [
            $this->orders::STATUS_NEW,
            $this->orders::STATUS_APPROVED,
            $this->orders::ORDER_STATUS_CRM_CORRECTION,
            $this->orders::ORDER_STATUS_CRM_CORRECTED,
        ];

        $newOrders = $this->orders->get_orders([
            'user_id' => (int)$transaction->user_id
        ]);

        if (empty($newOrders)) {
            $newOrders = [];
        }

        foreach ($newOrders as $order) {
            if (in_array($order->status, $newOrderStatuses)) {
                $this->orders->update_order((int)$order->id, ['card_id' => $sbp_account_id, 'card_type' => $this->orders::CARD_TYPE_SBP]);

                $b2pSbpBank = $this->b2p_bank_list->getOne([
                    'id' => $subscribe->member_id
                ]) ?: null;

                $this->addClientLoggingForSbp($order, (int)$sbp_account_id, $b2pSbpBank);
                $this->addOrderLoggingForSbp($order, (int)$sbp_account_id, $b2pSbpBank);
                $this->logging(__METHOD__, '', 'Обновлен card_id у заявки',
                    ['order' => $order, 'card_id' => $sbp_account_id], $logFileName);

            }
        }
    }

    private function addClientLoggingForSbp(stdClass $order, int $sbpAccountId, ?stdClass $b2pSbpBank): void
    {
        $this->comments->add_comment([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'block' => 'choose_sbp',
            'text' => 'Клиент выбрал СБП счет в ' . ($b2pSbpBank->title ?? '') . ' (id счета - ' . $sbpAccountId  . ')',
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

    private function addOrderLoggingForSbp(stdClass $order, int $sbpAccountId, ?stdClass $b2pSbpBank): void
    {
        $this->changelogs->add_changelog([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'type' => 'choose_sbp',
            'old_values' => $order->card_id ? $order->card_id . '(' . $order->card_type .')' : '',
            'new_values' => 'Клиент выбрал СБП счет в ' . ($b2pSbpBank->title ?? '') . ' (id счета - ' . $sbpAccountId  . ')',
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Привязка СБП на тестовых контурах
     *
     * Вариант 1 (с ручным добавления СБП в БД) - ТЕКУЩАЯ РЕАЛИЗАЦИЯ
     *   1. Получение ссылки и qrc_id на привязку СБП с помощью вызова send_request_get_sbp_link (webapi/GetSBPSubscription)
     *   2. Ручной вызов Best2payCallback - addSbpAccount
     *
     * Вариант 2 (с ручным вызовом коллбека)
     *  1. Получение ссылки и qrc_id на привязку СБП с помощью вызова send_request_get_sbp_link (webapi/GetSBPSubscription)
     *  2. Ручной curl вызов коллбека /best2pay_callback/sbp_token
     * ПРОБЛЕМА: вызвать с помощью curl /best2pay_callback/sbp_token тоже можно, но ошибка авторизации, нужно тогда ip всех девов прописывать в конфиге
     *
     * Вариант 3 (с автоматическим вызовом коллбека бест2пеем)
     * 1. Получение ссылки и qrc_id на привязку СБП с помощью вызова send_request_get_sbp_link (webapi/GetSBPSubscription)
     * 2. Отправка qrc_id по test/SBPTestCase для запуска тестового сценария (Best2pay - runTestScenarioToAddSbp)
     * 3. b2p автоматически вызывает коллбек, который у них будет захаркожен (/best2pay_callback/sbp_token)
     * ПРОБЛЕМА: url должен быть захаркожен на стороне b2p, т.е. например запрос с дев1 будет идти на дев3. Можно указать несколько url (до 255 символов (5 url)), но коллбек будет вызываться подряд по всем девам
     */
    private function sbp_token_dev(): void
    {
        $logFileName = 'sbp_tokens_callback.txt';

        $this->logging(__METHOD__, '', 'Вызван sbp_token_dev', ['$_REQUEST' => $_REQUEST, '$_SERVER' => $_SERVER, 'user' => $this->user], $logFileName);

        if (!$this->helpers->isDev() || empty($this->user)) {
            $this->logging(__METHOD__, '', 'Не дев окружение или не найден пользователь', '', $logFileName);
            return;
        }

        // 1. Получаем ссылку для привязки СБП
        $subscribe_response_object = $this->best2pay->send_request_get_sbp_link((int)$this->user->id);

        $this->logging(__METHOD__, '', 'Получена ссылка на привязку СБП счета', ['subscribe_response_object' => $subscribe_response_object], $logFileName);

        if (empty($subscribe_response_object)) {
            $this->logging(__METHOD__, '', 'Пустой subscribe_response_object', ['subscribe_response_object' => $subscribe_response_object], $logFileName);
            return;
        }

        $qrcId = (string)$subscribe_response_object->qrcId;

        // Ставим рандомный токен
        $token = 'SBP' . uniqid();

        // Ставим рандомный банк
        $sbpBanks = $this->b2p_bank_list->getSbpBanks();
        $randomBank = $sbpBanks[array_rand($sbpBanks)];
        $member_id = $randomBank->id;

        $subscribe = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
                    <SBPSubscriptionState>
                    <date>2025.10.28 15:50:45.467</date>
                    <qrcId>' . $qrcId . '</qrcId>
                    <subscription_state>ACCEPTED</subscription_state>
                    <token>' . $token . '</token>
                    <member_id>' . $member_id . '</member_id>
                    <signature>YmY2ZTRhMWM1NzVlYTg5YzRlYzE5ZTI5NTk1Mzk0NDA=</signature>
                </SBPSubscriptionState>';

        // 2. Добавляем СБП
        $this->addSbpAccount($subscribe);

        if ($this->users->isRegistrationCompleted($this->user)) {
            header('Location: ' . $this->config->root_url . '/user');
        } else {
            header('Location: ' . $this->config->root_url . '/account');
        }

        exit;
    }

    /** Вебхук после привязки счёта СБП */
    public function payment_sbp()
    {
        $this->logging(__METHOD__, 'Best2payCallback', $_REQUEST, file_get_contents('php://input'), 'sbp_tokens.txt');

        $b2p_payment = $this->request->get('b2p_payment', 'integer');
        $user_id = $this->request->get('user_id', 'integer');
        if (empty($b2p_payment) && empty($user_id)) {
            $this->design->assign('error', 'Не передан обязательный параметр');
        }

        $payment = $this->best2pay->get_payment($b2p_payment);

        /** Что то реально пошло не так */
        if (empty($payment->id)) {
            if (!empty($user_id)) {
                $this->design->assign('success', 'Счёт успешно привязан.');

                $user = $this->users->get_user($user_id);

                // Обновляем card_added сразу при успешной привязке СБП
                // Это предотвращает редирект на страницу привязки до получения sbp_token
                if (!empty($user) && empty($user->card_added) && $user->utm_source === 'test123') {
                    $this->users->update_user($user_id, [
                        'card_added' => 1,
                        'card_added_date' => date('Y-m-d H:i:s')
                    ]);
                }

                if ($this->users->isRegistrationCompleted($user)) {
                    $this->design->assign('redirect_link', '/user');
                } else {
                    $this->design->assign('redirect_link', '/account');
                }
            } else {
                $this->design->assign('error', 'Счёт не привязан!');
            }
        } else {
            if ($payment->reason_code == 909) {
                $this->design->assign('error', 'Счёт не привязан!');
            } else {
                /** Привязку клиент начал делать, но колбэк sbp_token мы ещё не получили  */
                if (empty($payment->register_id)) {
                    $this->design->assign('success', 'Счёт успешно привязан!');
                }

                // Для случаев когда есть payment но нет register_id - тоже обновляем card_added
                // Только для utm_source='test123'
                if (!empty($payment->user_id)) {
                    $user = $this->users->get_user($payment->user_id);
                    if (!empty($user) && empty($user->card_added) && $user->utm_source === 'test123') {
                        $this->users->update_user($payment->user_id, [
                            'card_added' => 1,
                            'card_added_date' => date('Y-m-d H:i:s')
                        ]);
                    }
                }

                /** Получили колбэк sbp_token и провели оплату */
                if (!empty($payment->register_id) && !empty($payment->operation_id)) {
                    $this->design->assign('success', 'Оплата прошла успешно!');
                }
            }
        }
    }

    /**
     * @throws DateMalformedStringException
     * @throws SoapFault
     * @throws Exception
     */
    public function payment_action($type = 'pay', $data = [])
    {
        if ($type == 'sbp' && !empty($data)) {
            $register_id = (int)$data['register_id'];
            $operation = (int)$data['operation'];
            $reference = $data['reference'];
            $error = $data['error'];
            $code = $data['code'];
        } else {
            $register_id = $this->request->get('id', 'integer');
            $operation = $this->request->get('operation', 'integer');
            $reference = $this->request->get('reference', 'integer');
            $error = $this->request->get('error', 'integer');
            $code = $this->request->get('code', 'integer');
        }

        $sector = $this->best2pay->get_sector('PAYMENT');
        $asp_code = $_SESSION['sms'] ?? '';

        if (!empty($register_id)) {
            if ($payment = $this->best2pay->get_register_id_payment($register_id)) {

                if ($payment->is_sbp == 1) {
                    $sector = $this->best2pay->get_sector('AKVARIUS_PAY_CREDIT_SBP');
                }

                if ($payment->reason_code == 1) {
                    $meta_title = 'Оплата уже принята';
                    if(!empty($_SESSION['full_payment_amount'])) {
                        unset($_SESSION['full_payment_amount']);
                    }
                    $this->design->assign('error', 'Оплата уже принята.');
                } else {
                    if (empty($operation)) {
                        $register_info = $this->best2pay->get_register_info($payment->sector, $register_id);
                        $xml = simplexml_load_string($register_info);

                        foreach ($xml->operations as $xml_operation)
                            if ($xml_operation->operation->state == 'APPROVED')
                                $operation = (string)$xml_operation->operation->id;
                    }

                    if (!empty($operation)) {
                        $operation_info = $this->best2pay->get_operation_info($payment->sector, $register_id, $operation);
                        $xml = simplexml_load_string($operation_info);
                        $reason_code = (string)$xml->reason_code;
                        $payment_amount = strval($xml->amount) / 100;
                        $task_payment = $payment_amount;
                        if (!empty($xml->date)) {
                            $operation_date = date_create_from_format('Y.m.d H:i:s', (string)$xml->date);
                        }
                        if (isset($xml->type) && $xml->type == 'PURCHASE_BY_QR') {
                            $card_pan = (string) $xml->qrcId;
                            if ((string) $xml->state == 'APPROVED') {
                                $reason_code = 1;
                            }
                        } else {
                            if (!($card_pan = (string)$xml->pan)) {
                                $card_pan = (string)$xml->pan2;
                            }
                        }
                        if ($reason_code == 1) {

                            try {
                                if (!empty($card_pan)) {
                                    $this->best2pay->set_success_pay_event_in_card($card_pan, $payment->user_id);
                                }
                            } catch (Throwable $exception) {}

                            $orderId = (int) $payment->order_id;
                            $balance = $this->users->get_user_balance($payment->user_id);
                            $order = $this->orders->get_order($orderId);

                            $update = array(
                                'operation_id' => $operation,
                                'callback_response' => serialize($operation_info),
                                'card_pan' => empty($card_pan) ? '' : $card_pan,
                                'operation_date' => $operation_date->format('Y-m-d H:i:s'),
                            );

                            $this->best2pay->update_payment($payment->id, $update);

                            // запись в базе не успевает обновиться до следующего селекта
                            sleep(2);

                            try {
                                $this->userOrderGiftService->updateUsersGiftStatus($balance, $payment, $orderId);
                            } catch (Throwable $ex) {}

                            $bodyData = (object)unserialize($payment->body);
                            if ($bodyData->recurring_data && $bodyData->recurring_data['star_oracle']) {
                                $this->recurring_so_payment($payment);
                            }

                            $meta_title = 'Оплата прошла успешно';
                            $this->design->assign('success', 'Оплата прошла успешно.');
                            $this->design->assign('grace', 'true');
                            $this->design->assign('payment_id', $payment->id);

                            if ((isset($_SESSION['restricted_mode']) && $_SESSION['restricted_mode'] == 1) ||
                                (isset($_SESSION['restricted_mode_logout_hint']) && $_SESSION['restricted_mode_logout_hint'] == 1)
                            ) {
                                if (isset($_SESSION['restricted_mode'])) {
                                    unset($_SESSION['restricted_mode']);
                                }
                                if (isset($_SESSION['user_id'])) {
                                    unset($_SESSION['user_id']);
                                }
                                if (isset($_SESSION['restricted_mode_logout_hint'])) {
                                    unset($_SESSION['restricted_mode_logout_hint']);
                                }

                                setcookie('auth_jwt_token', null, time()-1, '/');
                            }

                            $send_payment = $this->best2pay->get_payment($payment->id);

                            if($send_payment->amount == $_SESSION['full_payment_amount']) {
                                $_SESSION['full_payment_amount_done'] = true;
                            }
                            if(!empty($_SESSION['full_payment_amount'])  ) {
                                unset($_SESSION['full_payment_amount']);
                            }

                            $organization_id = $this->get_organization_id_by_payment($send_payment);
                            $send_date = date('Y-m-d H:i:s'); // Дата отправки в 1С

                            // обрабатываем оплату Кредитного рейтинга
                            if (in_array($send_payment->payment_type, array_values($this->best2pay::PAYMENT_TYPE_CREDIT_RATING_MAPPING))) {

                                // добавим задание на отправку чека
                                $receipt_data = [
                                    'user_id' => $send_payment->user_id,
                                    'order_id' => $send_payment->order_id,
                                    'amount' => $send_payment->amount,
                                    'payment_id' => $send_payment->id,
                                    'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                    'payment_type' => $this->receipts::PAYMENT_TYPE_CREDIT_RATING,
                                    'organization_id' => $organization_id,
                                    'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_CREDIT_RATING],
                                ];

                                $this->receipts->addItem($receipt_data);

                                $this->generateCreditRating($send_payment);
                            }

                            if ($send_payment->payment_type === $this->best2pay::PAYMENT_TYPE_REFUSER) {
                                $this->order_data->set((int)$payment->order_id, $this->order_data::PAYMENT_REFUSER, 1);
                                $this->design->assign('payment_refuser', 1);

                                // добавим задание на отправку чека
                                $receipt_data = [
                                    'user_id' => $send_payment->user_id,
                                    'order_id' => $send_payment->order_id,
                                    'amount' => $send_payment->amount,
                                    'payment_id' => $send_payment->id,
                                    'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                    'payment_type' => $this->receipts::PAYMENT_TYPE_REFUSER,
                                    'organization_id' => $organization_id,
                                    'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_REFUSER],
                                ];

                                $this->receipts->addItem($receipt_data);

                                $this->generateRefuserDocuments($payment, $order);

                                $this->best2pay->update_payment($payment->id, ['reason_code' => 1]);

                                $result = $this->soap->send_refuser_payments([$send_payment]);

                                if (!empty($result->return) && $result->return == 'OK') {
                                    $this->best2pay->update_payment($send_payment->id, array(
                                        'sent' => 1,
                                        'send_date' => $send_date,
                                    ));
                                }
                            } else {
                                if ($send_payment->insure > 2000) {
                                    $credit_doctor_id = $this->credit_doctor->getCreditDoctorIdByPenaltyPrice(intval($send_payment->insure));

                                    // создаем ступень ШтрафногоКД
                                    $this->credit_doctor->addUserCreditDoctorData([
                                        'user_id' => $send_payment->user_id,
                                        'order_id' => $send_payment->order_id,
                                        'credit_doctor_condition_id' => $credit_doctor_id,
                                        'amount' => $send_payment->insure,
                                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                        'transaction_id' => $send_payment->id,
                                        'status' => $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS,
                                        'date_added' => date('Y-m-d H:i:s'),
                                        'date_edit' => date('Y-m-d H:i:s'),
                                        'is_penalty' => 1,
                                        'organization_id' => $organization_id,
                                    ]);

                                    // добавим задание на отправку чека
                                    $receipt_data = [
                                        'user_id' => $send_payment->user_id,
                                        'order_id' => $send_payment->order_id,
                                        'amount' => $send_payment->insure,
                                        'payment_id' => $send_payment->id,
                                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                        'payment_type' => $this->receipts::PAYMENT_TYPE_PENALTY_CREDIT_DOCTOR,
                                        'organization_id' => $organization_id,
                                        'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_PENALTY_CREDIT_DOCTOR],
                                    ];

                                    $this->receipts->addItem($receipt_data);

                                    // Generate doc PCD (penalty credit doctor)
                                    $this->documents->create_document(
                                        [
                                            'type' => $this->documents::PENALTY_CREDIT_DOCTOR,
                                            'user_id' => $send_payment->user_id,
                                            'order_id' => $send_payment->order_id,
                                            'contract_number' => $send_payment->contract_number,
                                            'params' => ['pay' => 'full'],
                                        ]
                                    );
                                }
                                if ($send_payment->insure > 0) {
                                    $task_payment -= $send_payment->insure;
                                }

                                // проверим, был ли мультиполис (Консьерж)
                                $filter_data_multipolis = [
                                    'filter_payment_id' => (int)$payment->id,
                                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                ];
                                $multipolis = $this->multipolis->selectAll($filter_data_multipolis, false);
                                if (!empty($multipolis)) {
                                    $send_payment->multipolis = $multipolis;

                                    // добавим задание на отправку чека
                                    $receipt_data = [
                                        'user_id' => $send_payment->user_id,
                                        'order_id' => $send_payment->order_id,
                                        'amount' => $multipolis->amount,
                                        'payment_id' => $send_payment->id,
                                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                        'payment_type' => $this->receipts::PAYMENT_TYPE_MULTIPOLIS,
                                        'organization_id' => $organization_id,
                                        'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_MULTIPOLIS],
                                    ];

                                    $this->receipts->addItem($receipt_data);

                                    $multipolis_key = $this->dop_license->createLicenseWithKey(
                                        $this->dop_license::SERVICE_CONCIERGE,
                                        [
                                            'user_id' => $send_payment->user_id,
                                            'order_id' => $send_payment->order_id,
                                            'service_id' => $multipolis->id,
                                            'organization_id' => $organization_id,
                                            'amount' => $multipolis->amount,
                                        ]
                                    );


                                    $task_payment -= $multipolis->amount;
                                }

                                // проверим была ли куплена телемедицина (Витамед)
                                $filter_data_tv_medical = [
                                    'filter_payment_id' => (int)$payment->id,
                                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                ];
                                $tv_medical_payment = $this->tv_medical->selectPayments($filter_data_tv_medical, false);
                                if (!empty($tv_medical_payment)) {
                                    $send_payment->tv_medical = $tv_medical_payment;

                                    // добавим задание на отправку чека
                                    $receipt_data = [
                                        'user_id' => $send_payment->user_id,
                                        'order_id' => $send_payment->order_id,
                                        'amount' => $tv_medical_payment->amount,
                                        'payment_id' => $send_payment->id,
                                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                        'payment_type' => $this->receipts::PAYMENT_TYPE_TV_MEDICAL,
                                        'organization_id' => $organization_id,
                                        'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_TV_MEDICAL],
                                    ];

                                    $this->receipts->addItem($receipt_data);

                                    $tvmed_key = $this->dop_license->createLicenseWithKey(
                                        $this->dop_license::SERVICE_VITAMED,
                                        [
                                            'user_id' => $send_payment->user_id,
                                            'order_id' => $send_payment->order_id,
                                            'service_id' => $tv_medical_payment->id,
                                            'organization_id' => $organization_id,
                                            'amount' => $tv_medical_payment->amount,
                                        ]
                                    );


                                    $task_payment -= $tv_medical_payment->amount;
                                }

                                // проверим была ли куплена звездный оракул
                                $filter_data_star_oracle = [
                                    'filter_transaction_id' => (int)$payment->id,
                                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                    'filter_action_type' => $this->star_oracle::ACTION_TYPE_PAYMENT,
                                ];

                                $star_oracle = $this->star_oracle->selectAll($filter_data_star_oracle, false);
                                if (!empty($star_oracle)) {
                                    $send_payment->star_oracle = $star_oracle;

                                    // добавим задание на отправку чека
                                    $receipt_data = [
                                        'user_id' => $send_payment->user_id,
                                        'order_id' => $send_payment->order_id,
                                        'amount' => $star_oracle->amount,
                                        'payment_id' => $send_payment->id,
                                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                        'payment_type' => $this->receipts::PAYMENT_TYPE_STAR_ORACLE,
                                        'organization_id' => $organization_id,
                                        'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_STAR_ORACLE],
                                    ];

                                    $this->receipts->addItem($receipt_data);

                                    $star_oracle_key = $this->dop_license->createLicenseWithKey(
                                        $this->dop_license::SERVICE_STAR_ORACLE,
                                        [
                                            'user_id' => $send_payment->user_id,
                                            'order_id' => $send_payment->order_id,
                                            'service_id' => $star_oracle->id,
                                            'organization_id' => $organization_id,
                                            'amount' => $star_oracle->amount,
                                        ]
                                    );


                                    $task_payment -= $star_oracle->amount;
                                }

                                if (!empty($send_payment->multipolis) || !empty($send_payment->tv_medical) || !empty($send_payment->star_oracle)) {
                                    $user = $this->users->get_user((int)$payment->user_id);
                                    $contract_number = $send_payment->contract_number ?: $balance->zaim_number;

                                    // выполним все действия по телемеду
                                    if (!empty($tv_medical_payment)) {
                                        $this->tv_medical->updatePayment(
                                            (int)$tv_medical_payment->id,
                                            ['status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS]
                                        );

                                        $tv_medical = $this->tv_medical->getVitaMedById(
                                            (int)$tv_medical_payment->tv_medical_id
                                        );

                                        $registrationAddress = $user->Regindex . " " . $user->Regregion . " " . $user->Regcity . " " . $user->Regstreet . " " . $user->Regbuilding . " " . $user->Reghousing . " " . $user->Regroom;
                                        $actualAddress = $user->Faktindex . " " . $user->Faktregion . " " . $user->Faktcity . " " . $user->Faktstreet . " " . $user->Faktbuilding . " " . $user->Fakthousing . " " . $user->Faktroom;
                                        $passport_parts = explode("-", $user->passport_serial);
                                        $documentSerial = $passport_parts[0] ?? '';
                                        $documentNumber = $passport_parts[1] ?? '';
                                        $data_order = [
                                            'phone' => $user->phone_mobile,
                                            'firstName' => $user->firstname,
                                            'middleName' => $user->patronymic,
                                            'lastName' => $user->lastname,
                                            'birthday' => (new DateTime($user->birth))->format('Y-m-d'),
                                            //                                        'internalTariffId' => $tv_medical->api_doc_id,
                                            'sex' => strtoupper($user->gender),
                                            'documentSerial' => $documentSerial,
                                            'documentNumber' => $documentNumber,
                                            'documentIssuedBy' => $user->passport_issued,
                                            'documentDepartmentCode' => $user->subdivision_code,
                                            'registrationAddress' => $registrationAddress,
                                            'actualAddress' => $actualAddress,
                                            'email' => $user->email,
                                            'confirmCode' => $asp_code,
                                        ];
                                        $result_order = TVMedicalApi::createOrder($data_order);

                                        if (!empty($result_order['success'])) {
                                            $this->tv_medical->updatePayment(
                                                (int)$tv_medical_payment->id,
                                                ['sent_to_api' => 1]
                                            );

                                            // сгенерируем документы телемеда
                                            $this->tv_medical->generatePayDocs($user, $send_payment, (int)$tv_medical_payment->order_id, $order->organization_id,$tvmed_key);
                                        }
                                    }

                                    // выполним все действия по мультиполису
                                    if (!empty($multipolis)) {
                                        $this->multipolis->updateItem(
                                            (int)$multipolis->id,
                                            ['status' => $this->multipolis::STATUS_SUCCESS]
                                        );

                                        $clear_passport_serial = preg_replace('/[^0-9]/', '', $user->passport_serial);
                                        $passport_serial = substr($clear_passport_serial, 0, 4);
                                        $passport_number = substr($clear_passport_serial, 4);

                                        $params = [
                                            'multipolis_number' => $multipolis->number,
                                            'lastname' => $user->lastname,
                                            'firstname' => $user->firstname,
                                            'patronymic' => $user->patronymic,
                                            'birth' => $user->birth,
                                            'gender' => $user->gender,
                                            'phone_mobile' => $user->phone_mobile,
                                            'passport_serial' => $passport_serial,
                                            'passport_number' => $passport_number,
                                            'passport_original' => $user->passport_serial,
                                            'order_date_end' => $balance->payment_date,
                                            'amount' => $multipolis->amount,
                                            'pay_date' => $send_date,
                                            'payment_id' => $send_payment->id,
                                            'license_key' => $multipolis_key,
                                        ];

                                        $this->documents->create_document(
                                            [
                                                'type' => $this->documents::DOC_MULTIPOLIS,
                                                'user_id' => $multipolis->user_id,
                                                'order_id' => $multipolis->order_id,
                                                'contract_number' => $contract_number,
                                                'params' => $params,
                                                'organization_id' => $order->organization_id,
                                            ]
                                        );

                                        // отправим запрос в 1С на формирование договора по мультиполису
                                        $this->soap->sendMultipolisContract($user->uid);
                                    }

                                    // выполним все действия по звездного оракула
                                    if (!empty($star_oracle)) {
                                        $this->star_oracle->updateStarOracleData(
                                            (int)$star_oracle->id,
                                            ['status' => $this->multipolis::STATUS_SUCCESS]
                                        );


                                        $params = new StdClass();

                                        $params->lastname = $user->lastname;
                                        $params->firstname = $user->firstname;
                                        $params->patronymic = $user->patronymic;
                                        $params->birth = $user->birth;
                                        $params->passport_serial = $user->passport_serial;
                                        $params->passport_issued = $user->passport_issued;
                                        $params->passport_date = $user->passport_date;
                                        $params->subdivision_code = $user->subdivision_code;
                                        $params->phone_mobile = $user->phone_mobile;
                                        $params->accept_sms = $order->accept_sms;
                                        $params->amount = $star_oracle->amount;
                                        $params->license_key = $star_oracle_key;

                                        $this->documents->create_document(
                                            [
                                                'type' => $this->documents::CONTRACT_STAR_ORACLE,
                                                'user_id' => $order->user_id,
                                                'order_id' => $order->id,
                                                'contract_number' => $contract_number,
                                                'params' => $params,
                                                'organization_id' => $order->organization_id,
                                            ]
                                        );

                                        $this->documents->create_document(
                                            [
                                                'type' => $this->documents::STAR_ORACLE_POLICY,
                                                'user_id' => $order->user_id,
                                                'order_id' => $order->id,
                                                'contract_number' => $contract_number,
                                                'params' => $params,
                                                'organization_id' => $order->organization_id,
                                            ]
                                        );
                                    }
                                }

                                $this->best2pay->update_payment($payment->id, ['reason_code' => 1]);

                                // отправляем в 1с платеж
                                $send_payment->organization = $this->organizations->get_organization($organization_id);
                                if (!empty($order) && $order->loan_type == 'IL') {
                                    $result = $this->soap->send_payments_il(array($send_payment));
                                    $this->logging(__METHOD__, 'Best2payCallback.send_payments_il', (array)$send_payment, (array)$result, 'b2p_callback.txt');
                                } else {
                                    $result = $this->soap->send_payments(array($send_payment));
                                    $this->logging(__METHOD__, 'Best2payCallback.send_payments', (array)$send_payment, (array)$result, 'b2p_callback.txt');
                                }

                                if (!empty($result->return) && $result->return == 'OK') {
                                    $this->best2pay->update_payment($send_payment->id, array(
                                        'sent' => 1,
                                        'send_date' => $send_date,
                                    ));
                                }

                                $this->updateBalance($payment, $balance);
                                $this->updateProlongations($balance, $task_payment, $payment);

                                try {
                                    $this->ab_test_service->logPaid($order->user_id);
                                } catch (\Throwable $e) {
                                    $this->logging(__METHOD__, 'AB test logPaid error', ['user_id' => $order->user_id], ['error' => $e->getMessage()], 'ab_test.txt');
                                }

                                try {
                                    $this->overdue_slider_service->logPaid($order->user_id, $order->id);
                                } catch (\Throwable $e) {
                                    $this->logging(__METHOD__, 'Overdue slider logPaid error', ['user_id' => $order->user_id, 'order_id' => $order->id], ['error' => $e->getMessage()], $this->overdue_slider_service::LOG_FILE_NAME);
                                }

                                if (!empty($payment->refinance)) {
                                    $this->logging(
                                        __METHOD__,
                                        'Best2payCallback refinance creation',
                                        'order id - ' . $order->id,
                                        json_encode($payment->refinance),
                                        'refinance.txt'
                                    );

                                    $this->refinance->create($payment);
                                }
                            }

                        } else {

                            try {
                                if (!empty($card_pan)) {
                                    $this->best2pay->set_error_pay_event_in_card($card_pan, $payment->user_id);
                                }
                            } catch (Throwable $exception) {}

                            $update = array(
                                'reason_code' => $reason_code,
                                'operation_id' => $operation,
                                'callback_response' => serialize($operation_info),
                                'card_pan' => empty($card_pan) ? '' : $card_pan,
                                'operation_date' => empty($operation_date) ? NULL : $operation_date->format('Y-m-d H:i:s'),
                            );

                            $this->best2pay->update_payment($payment->id, $update);

                            if (strval($xml->message) == 'Insufficient funds'){
                                $this->soap->PaymentFailed($payment->id);
                            }

                            $reason_code_description = $this->best2pay->get_reason_code_description($code);
                            $this->design->assign('reason_code_description', $reason_code_description);

                            $meta_title = 'Не удалось оплатить';
                            $this->design->assign('error', 'При оплате произошла ошибка.');
                        }
                    } else {
                        $callback_response = $this->best2pay->get_register_info($payment->sector, $register_id, $operation);
                        //echo __FILE__.' '.__LINE__.'<br /><pre>';echo(htmlspecialchars($callback_response));echo '</pre><hr />';
                        $this->best2pay->update_payment($payment->id, array(
                            'operation_id' => 0,
                            'callback_response' => serialize($callback_response)
                        ));

                        $meta_title = 'Не удалось оплатить';
                        $this->design->assign('error', 'При оплате произошла ошибка. Код ошибки: ' . $error);
                    }
                }
            } else {
                $meta_title = 'Ошибка: Оплата не найдена';
                $this->design->assign('error', 'Ошибка: Оплата не найдена');
            }
        } else {
            if(!empty($_SESSION['full_payment_amount'])) {
                unset($_SESSION['full_payment_amount']);
            }
            $meta_title = 'Ошибка запроса';
            $this->design->assign('error', 'Ошибка запроса');
        }
    }

    /**
     * Генерирует Кредитный рейтинг
     * @param $payment
     * @return void
     */
    private function generateCreditRating($payment)
    {
        $user_id = (int)$payment->user_id;
        $user = $this->users->get_user($user_id);

        $this->users->addSkipCreditRating($user_id, 'PAY');
        $this->credit_rating->handle_credit_rating_paid($user, $payment->id, $payment->asp);
        exit();
    }

    public function add_card_action()
    {
        $register_id = $this->request->get('id', 'integer');
        $operation = $this->request->get('operation', 'integer');
        $reference = $this->request->get('reference', 'integer');
        $error = $this->request->get('error', 'integer');
        $code = $this->request->get('code', 'integer');

        if (!empty($register_id)) {
            if ($transaction = $this->best2pay->get_register_id_transaction($register_id)) {
                if (empty($operation)) {
                    $register_info = $this->best2pay->get_register_info($transaction->sector, $register_id);
                    $xml = simplexml_load_string($register_info);
                    foreach ($xml->operations as $xml_operation) {
                        if ($xml_operation->operation->state == 'APPROVED') {
                            $operation = (string)$xml_operation->operation->id;
                        }
                    }
                }


                $addcard_rejected_enabled = $this->settings->addcard_rejected_enabled;
                if (empty($operation) && !empty($addcard_rejected_enabled)) {
                    foreach ($xml->operations as $xml_operation) {
                        $message = (string)$xml_operation->operation->message;
                        if ($message == 'Insufficient funds') {
                            $operation = (string)$xml_operation->operation->id;
                        }
                    }
                }

                if (!empty($operation)) {
                    $operation_info = $this->best2pay->get_operation_info($transaction->sector, $register_id, $operation);
                    $xml = simplexml_load_string($operation_info);
                    $operation_reference = (string)$xml->reference;
                    $reason_code = (string)$xml->reason_code;

                    $addcard_rejected_approve = false;
                    if (!empty($addcard_rejected_enabled)) {
                        $message = (string)$xml->message;
                        $token = (string)$xml->token;
                        if ($message == 'Insufficient funds' && !empty($token)) {
                            $addcard_rejected_approve = true;
                        }
                    }

                    if ($reason_code == 1 || !empty($addcard_rejected_approve)) {
                        $operationCorrect = true;

                        if ($crossOrder = $this->orders->get_last_order_by_status($transaction->user_id, $this->orders::STATUS_WAIT_CARD)) {
                            $crossOrderCard = $this->best2pay->get_card($crossOrder->card_id);
                            if ($crossOrder->status != $this->orders::STATUS_WAIT_CARD && $crossOrderCard->pan != $xml->pan) {
                                $request = [
                                    'crossOrderCard' => (array)$crossOrderCard,
                                    'crossOrder' => (array)$crossOrder,
                                ];
                                $response = [
                                    'xml' => (array)$xml
                                ];

                                $this->logging(__METHOD__, 'add_card_action', $request, $response, 'attach_card.txt');
                                $this->design->assign('error', 'Была привязана другая карта.');
                                $operationCorrect = false;
                            }
                        }

                        if ($operationCorrect) {
                            if ($transaction->sector == $this->best2pay->sectors['AKVARIUS_ADD_CARD']) {
                                $organization_id = $this->organizations::AKVARIUS_ID;
                            } elseif ($transaction->sector == $this->best2pay->sectors['FINLAB_ADD_CARD']) {
                                $organization_id = $this->organizations::FINLAB_ID;
                            } elseif ($transaction->sector == $this->best2pay->sectors['LORD_ADD_CARD']) {
                                $organization_id = $this->organizations::LORD_ID;
                            } elseif ($transaction->sector == $this->best2pay->sectors['RZS_ADD_CARD']) {
                                $organization_id = $this->organizations::RZS_ID;
                            } else {
                                $organization_id = $this->organizations::BOOSTRA_ID;
                            }

                            $countSameCard = $this->best2pay->find_duplicates_for_user((string)$xml->reference,(string)$xml->pan,(string)$xml->expdate, $organization_id);
                            if ($countSameCard > 0) {

                                $meta_title = 'Карта уже привязана';
                                $this->design->assign('error', 'Карта уже привязана.');
                            } else {
                                // Получаем согласие на рекуррент из транзакции (по умолчанию 1 - разрешено)
                                // Это решает проблему потери сессии при переходе на внешний домен Best2Pay
                                $recurring_consent = isset($transaction->recurring_consent) ? (int)$transaction->recurring_consent : 1;

                                $card = array(
                                    'user_id' => (string)$transaction->user_id,
                                    'name' => (string)$xml->name,
                                    'pan' => (string)$xml->pan,
                                    'expdate' => (string)$xml->expdate,
                                    'approval_code' => (string)$xml->approval_code,
                                    'token' => (string)$xml->token,
                                    'operation_date' => str_replace('.', '-', (string)$xml->date),
                                    'created' => date('Y-m-d H:i:s'),
                                    'operation' => (string) $xml->id,
                                    'register_id' => $transaction->register_id,
                                    'transaction_id' => $transaction->id,
                                    'organization_id' => $organization_id,
                                    'autodebit' => $recurring_consent,
                                    'autodebit_changed_at' => date('Y-m-d H:i:s')
                                );

                                $card_id = $this->best2pay->add_card($card);
                                if (!empty($card_id)) {
                                    $this->best2pay->add_sbp_log([
                                        'card_id' => $card_id,
                                        'action' => Best2pay::CARD_ACTIONS['ADD_CARD'],
                                        'date' => date('Y-m-d H:i:s')
                                    ]);
                                }

                                $meta_title = 'Карта успешно привязана';
                                $this->design->assign('success', '');
                                $this->design->assign('card_attach', 'true');
                                $this->design->assign('card_pan', (string)$xml->pan);
                                $this->design->assign('new_card_id', $card_id);

                                // Сохранение события доабвления новой карты
                                $this->changelogs->add_changelog(
                                    [
                                        'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
                                        'created' => date('Y-m-d H:i:s'),
                                        'type' => 'new_card',
                                        'old_values' => '',
                                        'new_values' => (string)$xml->pan,
                                        'user_id' => (string)$transaction->user_id,
                                    ]
                                );

	                            // Временно, пока занято card_id поле в _orders
	                            if ($last_user_order = $this->orders->get_last_order((string)$transaction->user_id)) {
		                            $this->order_data->set($last_user_order->id, 'card_id', $card_id);
	                            }

                                try {
                                    if ($this->short_flow->isShortFlowUser((string)$transaction->user_id)) {
                                        $short_flow_stage = $this->short_flow->getRegisterStage((string)$transaction->user_id);
                                        if (!empty($short_flow_stage) && $short_flow_stage == ShortRegisterView::STAGE_CARD) {
                                            $this->short_flow->setRegisterStage((string)$transaction->user_id, ShortRegisterView::STAGE_FINAL);

                                            if ($last_order = $this->orders->get_last_order((string)$transaction->user_id)) {
                                                $this->orders->update_order($last_order->id, [
                                                    'card_id' => $card_id,
                                                    'card_type' => $this->orders::CARD_TYPE_CARD
                                                ]);
                                            }
                                        }
                                    }
                                }
                                catch (Throwable $e) {}

                                // Если дошли сюда с кросс-ордером - ставим статус заказа на выдачу денег
                                if ($crossOrder = $this->orders->get_last_order_by_status($transaction->user_id, $this->orders::STATUS_WAIT_CARD)) {
                                    $this->orders->update_order($crossOrder->id, [
                                        'status' => $this->orders::STATUS_SIGNED
                                    ]);
                                    $this->logging(__METHOD__, '', 'Клиент перепривязал карту, кросс-ордер переведен из статуса STATUS_WAIT_CARD в статус STATUS_SIGNED', ['cross_order' => $crossOrder, 'card' => $card], self::LOG_FILE);
                                }

                                // временная проверка для тестирования нового флоу (без выбора банка)
                                $userForUtm = $this->users->get_user((int)$transaction->user_id);
                                $utmSource = trim($userForUtm->utm_source ?? '');
                                $disabledUtmSources = array_map('trim', $this->settings->disable_bank_selection_utm_sources ?? []);
                                if (!empty($utmSource) && in_array($utmSource, $disabledUtmSources, true)) {
                                    // Сохраняем bank_id из bin_issuer для СБП выплат
                                    $last_order = $this->orders->get_last_order((string)$transaction->user_id);
                                    $order_id = !empty($last_order) ? $last_order->id : null;
                                    $this->b2p_bank_list->saveBankIdFromBinIssuer(
                                        $operation_info,
                                        (int)$transaction->user_id,
                                        $order_id
                                    );
                                }
                            }
                        }

                    } else {
                        $reason_code_description = $this->best2pay->get_reason_code_description($code);
                        $this->design->assign('reason_code_description', $reason_code_description);

                        $meta_title = 'Не удалось привязать карту';
                        $this->design->assign('error', 'При привязке карты произошла ошибка.');
                    }
                    $this->best2pay->update_transaction($transaction->id, array(
                        'operation' => $operation,
                        'callback_response' => $operation_info,
                        'reason_code' => $reason_code
                    ));

                    $this->best2pay->reverse((array)$transaction);
                } else {
                    $callback_response = $this->best2pay->get_register_info($transaction->sector, $register_id, $operation);
                    $this->transactions->update_transaction($transaction->id, array(
                        'operation' => 0,
                        'callback_response' => $callback_response
                    ));
                    //echo __FILE__.' '.__LINE__.'<br /><pre>';echo(htmlspecialchars($callback_response));echo '</pre><hr />';
                    $meta_title = 'Не удалось привязать карту';

                    $this->design->assign('error', 'При привязке карты произошла ошибка. Код ошибки: ' . $error);

                }
            } else {

                $meta_title = 'Ошибка: Транзакция не найдена';
                $this->design->assign('error', 'Ошибка: Транзакция не найдена');
            }
        } else {

            $meta_title = 'Ошибка запроса';
            $this->design->assign('error', 'Ошибка запроса');
        }

        //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($_GET);echo '</pre><hr />';
    }
    
    /**
     * Обновление баланса клиента
     */
    private function updateBalance($payment, $balance): void
    {
        $this->logging(__METHOD__, 'Best2payCallback.updateBalance', (array)$payment, (array)$balance, 'b2p_callback.txt');

        $payUser = $this->users->get_user((int)$payment->user_id);
        $user_balance_1c = $this->users->get_user_balance_1c($payUser->uid, true);
        $user_balance_1c = $this->users->make_up_user_balance($payUser->id, $user_balance_1c->return);
        $this->logging(__METHOD__, 'Best2payCallback.updateBalance.make_up_user_balance', ['user_id' => $payment->user_id, 'zaim_number' => $payment->contract_number], (array)$user_balance_1c, 'b2p_callback.txt');

        if (empty($balance)) {
            $this->users->add_user_balance($user_balance_1c);
        } else {
            $this->users->update_user_balance($balance->id, $user_balance_1c);
        }

        $balance = $this->users->get_user_balance($payment->user_id, ['zaim_number' => $payment->contract_number]);
        $this->logging(__METHOD__, 'Best2payCallback.updateBalance.get_user_balance', ['user_id' => $payment->user_id, 'zaim_number' => $payment->contract_number], (array)$balance, 'b2p_callback.txt');
    }

    /**
     * Обновление пролонгаций после оплаты
     */
    private function updateProlongations($balance, $payment_amount, $payment): void
    {
        $task = $this->tasks->get_current_pr_task_by_balance_id($balance->id, date('Y-m-d'));

        $this->createLogFolder();
        $this->logging(__METHOD__, '//prolongations_current_task', "user_id: " . $payment->user_id, $task, 'payment_log/b2p_' . date('Y_m_d') . '.txt');

        $total_amount = !empty($balance->sum_with_grace)  ? ($balance->sum_od_with_grace+$balance->sum_percent_with_grace) : $balance->ostatok_od + $balance->ostatok_peni + $balance->ostatok_percents;
        if (!empty($balance->id) && $task) {
            $this->tasks->update_pr_task($task->id, [
                'paid' => $task->paid + $payment_amount,
                'prolongation' => $task->prolongation + $payment->prolongation,
                'close' => $payment_amount >= $total_amount ? 1 : 0,
            ]);
        }

        $contract = $this->contracts->get_contract_by_params(['number' => $payment->contract_number]);

        if ($payment_amount >= $total_amount && $contract) {
            $this->contracts->updateCloseDateInContracts($contract->number);
        }

        $task = $this->tasks->get_current_pr_task_by_balance_id($balance->id, date('Y-m-d'));
        $this->logging(__METHOD__, '//prolongations_current_task_after_update', "user_id: " . $payment->user_id, $task, 'payment_log/b2p_' . date('Y_m_d') . '.txt');
    }

    private function createLogFolder(): void
    {
        if (!is_dir($this->config->root_dir . 'logs/payment_log/')) {
            mkdir($this->config->root_dir . 'logs/payment_log/');
        }
    }

    private function get_organization_id_by_payment($payment)
    {
        //TODO: рефакторинг
        $boostra_sectors = $this->best2pay->get_boostra_sectors();
        $vipzaim_sectors = $this->best2pay->get_vipzaim_sectors();
        $finlab_sectors = $this->best2pay->get_finlab_sectors();
        $rzs_sectors = $this->best2pay->get_rzs_sectors();
        $lord_sectors = $this->best2pay->get_lord_sectors();
        $moredeneg_sectors = $this->best2pay->get_moredeneg_sectors();
        $frida_sectors = $this->best2pay->get_frida_sectors();

        if (!empty($payment->split_data)) {
            $organization_id = $this->receipts::ORGANIZATION_SPLIT_FINTEH;
        } else if (in_array($payment->sector, $finlab_sectors)) {
            $organization_id = $this->receipts::ORGANIZATION_FINLAB;
        } else if (in_array($payment->sector, $vipzaim_sectors)) {
            $organization_id = $this->receipts::ORGANIZATION_VIPZAIM;
        } else if (in_array($payment->sector, $boostra_sectors)) {
            $organization_id = $this->receipts::ORGANIZATION_BOOSTRA;
        } else if (in_array($payment->sector, $rzs_sectors)) {
            $organization_id = $this->receipts::ORGANIZATION_RZS;
        } else if (in_array($payment->sector, $lord_sectors)) {
            $organization_id = $this->receipts::ORGANIZATION_LORD;
        } else if (in_array($payment->sector, $moredeneg_sectors)) {
            $organization_id = $this->receipts::ORGANIZATION_MOREDENEG;
        } else if (in_array($payment->sector, $frida_sectors)) {
            $organization_id = $this->receipts::ORGANIZATION_FRIDA;
        } else {
            $organization_id = $this->receipts::ORGANIZATION_AKVARIUS;
        }

        return $organization_id;
    }

    /**
     * @param $payment
     * @param $order
     * @return void
     */
    public function generateRefuserDocuments($payment, $order): void
    {
        $user = $this->users->get_user((int)$payment->user_id);
        $reason = $this->reasons->get_reason($order->reason_id);

        $params = [
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'patronymic' => $user->patronymic,
            'passport_serial' => $user->passport_serial,
            'passport_number' => $user->passport_number,
            'phone' => $user->phone_mobile,
            'fio' => $this->helpers::getFIO($user),
            'payment_id' => $payment->id,
            'text' => $reason->refusal_note,
            'date' => date('Y-m-d'),
        ];

        $this->documents->create_document(
            [
                'type' => $this->documents::PRICINA_OTKAZA_I_REKOMENDACII,
                'user_id' => $payment->user_id,
                'order_id' => $payment->order_id,
                'params' => $params,
            ]
        );
        $this->documents->create_document(
            [
                'type' => $this->documents::ZAYAVLENIYE_OTKAZA_REKOMENDACII,
                'user_id' => $payment->user_id,
                'order_id' => $payment->order_id,
                'params' => $params,
            ]
        );
        $this->documents->create_document(
            [
                'type' => $this->documents::OFFER_FAST_APPROVAL_SERVICE,
                'user_id' => $payment->user_id,
                'order_id' => $payment->order_id,
                'params' => [],
            ]
        );
    }

    /**
     * Рекуррентный платеж ЗО
     * @param $payment
     * @throws \SoapFault
     */
    private function recurring_so_payment($payment)
    {
        $body = (object)unserialize($payment->body);
        $star_oracle = (object)$body->recurring_data['star_oracle'];
        $user = $this->users->get_user_by_id($payment->user_id);
        $fio = Helpers::getFIO($user);
        $description = "Звездный оракул - к заявке $payment->order_id $fio";
        $order = $this->orders->get_order($payment->order_id);

        // Создаем рекуррентный платеж и получаем ответ от б2п
        $recurring = $this->best2pay->purchase_by_token($payment->card_id, $star_oracle->amount, $description, $payment->organization_id);

        if (empty($recurring)) {
            return false;
        }

        $recurring_payment_data = [
            'user_id' => $payment->user_id,
            'order_id' => $payment->order_id,
            'contract_number' => $payment->contract_number,
            'card_id' => $payment->card_id,
            'amount' => $star_oracle->amount,
            'insure' => 0,
            'fee' => 0,
            'prolongation' => 0,
            'asp' => $order->accept_sms,
            'created' => date('Y-m-d H:i:s'),
            'sector' => $recurring->sector,
            'description' => $description,
            'calc_percents' => 0,
            'chdp' => $payment->chdp,
            'pdp' => $payment->pdp,
            'organization_id' => $payment->organization_id,
            'contract_payment' => 0,
            'is_sbp' => 0,
            'refinance' => 0,
            'discount_amount' => 0,
            'operation_date' => date('Y-m-d H:i:s'),
            'register_id' => $recurring->register_id,
            'operation_id' => $recurring->id,
            'card_pan' => $recurring->pan,
            'payment_type' => $this->best2pay::PAYMENT_TYPE_RECURRING,
            'reference' => $payment->id,
            'reason_code' => $recurring->reason_code,
            'callback_response' => $recurring->callback_response
        ];

        $recurring_payment_id = $this->best2pay->add_payment($recurring_payment_data);

        $data_star_oracle = [
            'user_id' => $payment->user_id,
            'order_id' => $payment->order_id,
            'amount' => $star_oracle->amount,
            'action_type' => $body->recurring_data['action_type'],
            'status' => $this->star_oracle::STAR_ORACLE_STATUS_SUCCESS,
            'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
            'transaction_id' => (int)$recurring_payment_id,
            'organization_id' => $payment->organization_id,
            'date_edit' => date('Y-m-d H:i:s'),
        ];

        $star_oracle_id = $this->star_oracle->addStarOracleData($data_star_oracle);
        $star_oracle = $this->star_oracle->getStarOracleById($star_oracle_id);

        $this->star_oracle->createDocument($order, $star_oracle, $payment->contract_number, $payment->organization_id);

        // добавим задание на отправку чека
        $receipt_data = [
            'user_id' => $payment->user_id,
            'order_id' => $payment->order_id,
            'amount' => $star_oracle->amount,
            'payment_id' => $recurring_payment_id,
            'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
            'payment_type' => $this->receipts::PAYMENT_TYPE_STAR_ORACLE,
            'organization_id' => $payment->organization_id,
            'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_STAR_ORACLE],
        ];

        $this->receipts->addItem($receipt_data);

        $send_payment = $this->best2pay->get_payment($recurring_payment_id);
        $send_payment->star_oracle->id = $star_oracle->id;
        $send_payment->star_oracle->amount = $star_oracle->amount;
        $send_payment->star_oracle->organization_id = $star_oracle->organization_id;

        // отправляем в 1с платеж
        $result = $this->soap->send_payment_recurring($send_payment);

        if (!empty($result->return) && $result->return == 'OK') {
            $this->best2pay->update_payment($send_payment->id, array(
                'sent' => 1,
                'send_date' => date('Y-m-d H:i:s')
            ));
        }
    }
}
