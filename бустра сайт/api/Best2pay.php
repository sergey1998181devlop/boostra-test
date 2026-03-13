<?php


use boostra\domains\Card;
use boostra\domains\extraServices\extraService;
use boostra\domains\Transaction\GatewayResponse;

class Best2pay extends Simpla
{
    /**
     * Время жизни ссылки на оплату в секундах
     */
    private $life_period = 3600;

    /**
     * Тип оплаты для Кредитного рейтинга
     */
    public const PAYMENT_TYPE_CREDIT_RATING_ORIGIN = 'credit_rating';
    public const PAYMENT_TYPE_CREDIT_RATING_FOR_NK = 'credit_rating_for_nk';
    public const PAYMENT_TYPE_CREDIT_RATING_AFTER_REJECTION = 'credit_rating_after_rejection';
    public const PAYMENT_TYPE_REFUSER = 'refuser';
    public const PAYMENT_TYPE_CREDIT_RATING_MAPPING = [
        1 => self::PAYMENT_TYPE_CREDIT_RATING_FOR_NK,
        2 => self::PAYMENT_TYPE_CREDIT_RATING_AFTER_REJECTION
    ];

    public const PAYMENT_TYPE_CREDIT_RATING_MAPPING_ALL = [
        0 => self::PAYMENT_TYPE_CREDIT_RATING_ORIGIN,
        1 => self::PAYMENT_TYPE_CREDIT_RATING_FOR_NK,
        2 => self::PAYMENT_TYPE_CREDIT_RATING_AFTER_REJECTION
    ];

    public const PAYMENT_TYPE_RECURRING = 'recurring';

    public const ALLOWED_UTM_SOURCES_TO_ADD_SBP_ACCOUNT = [
        'test123',
    ];

    /**
     * Код успешной оплаты Б2П
     */
    public const REASON_CODE_SUCCESS = 1;

    public const SUCCESS_RETURN_STATUS = 2;

    private $currency_code = 643;
    
    private $fee = 0.075;
    private $extra_fee_pdl = 0.1;
    private $extra_fee_il = 0.15;
    private $min_fee = 30;

    // work
    private $url = 'https://pay.best2pay.net/';

    public $sectors = [];

    private $passwords = [];

    private $split_id = [
        'BOOSTRA' => '1',
        'FINTEH' => '2',
    ];

    /**
     * Статус успешной транзакции
     */
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';

    public const CARD_ACTIONS = [
        'ADD_CARD' => 'binding',
        'SUCCESS_PAYMENT_CARD' => 'success_payment_card',
        'ERROR_PAYMENT_CARD' => 'error_payment_card',
        'DELETE_CARD_CLIENT' => 'delete_card_client',
        'DELETE_CARD_MANAGER' => 'delete_card_manager',
        'AUTODEBIT_ON' => 'autodebit_on',
        'AUTODEBIT_OFF' => 'autodebit_off',
        'SUCCESS_ATTACH_SBP' => 'success_attach_sbp',
        'ERROR_ATTACH_SBP' => 'error_attach_sbp',
        'RECURRING_ON_SBP' => 'recurring_on_sbp',
        'RECURRING_OFF_SBP' => 'recurring_off_sbp',
        'SUCCESS_PAYMENT_SBP' => 'success_payment_sbp',
        'SUCCESS_PAYMENT_SBP_RECURRING' => 'success_payment_sbp_recurring',
        'ERROR_PAYMENT_SBP' => 'error_payment_sbp',
        'ERROR_PAYMENT_SBP_RECURRING' => 'error_payment_sbp_recurring',
    ];

    public const B2P_PAYMENTS_COLUMNS_LIST = [
        'id' => 'int',
        'user_id' => 'int',
        'order_id' => 'int',
        'contract_number' => 'string',
        'card_id' => 'int',
        'amount' => 'float',
        'split_data' => 'string',
        'insure' => 'float',
        'fee' => 'float',
        'body_summ' => 'float',
        'percents_summ' => 'float',
        'prolongation' => 'bool',
        'calc_percents' => 'bool',
        'asp' => 'string',
        'created' => 'string',
        'payment_type' => 'string',
        'organization_id' => 'int',
        'sector' => 'int',
        'register_id' => 'int',
        'operation_id' => 'int',
        'reference' => 'string',
        'description' => 'string',
        'payment_link' => 'string',
        'reason_code' => 'int',
        'body' => 'string',
        'callback_response' => 'string',
        'sent' => 'bool',
        'send_date' => 'string',
        'recurrent_id' => 'int',
        'nbki_ready' => 'bool',
        'grace_payment' => 'bool',
        'card_pan' => 'string',
        'operation_date' => 'string',
        'dop1c_sent' => 'bool',
        'chdp' => 'bool',
        'pdp' => 'bool',
        'contract_payment' => 'bool',
        'is_sbp' => 'bool',
        'refinance' => 'int',
        'create_from' => 'string',
        'boostra_coins' => 'int',
        'discount_amount' => 'int'
    ];

    private const LOG_FILE = 'best2pay.txt';

    public function __construct()
    {
        parent::__construct();
        $this->url = $this->config->B2PAY_URL;
        $this->sectors = $this->config->B2PAY_SECTORS;
        $this->passwords = $this->config->B2P_PASSWORDS;
    }

    public function get_sectors()
    {
    	return $this->sectors;
    }
    
    public function get_sector($type)
    {
    	return isset($this->sectors[$type]) ? $this->sectors[$type] : null;
    }
    
    public function get_boostra_sectors()
    {
        return [
            $this->sectors['PAYMENT'],
            $this->sectors['RECURRENT_ADVANCED'],
            $this->sectors['RECURRENT'],
            $this->sectors['PAY_CREDIT'],
            $this->sectors['ADD_CARD'],
            $this->sectors['RETURN_BOOSTRA'],
            $this->sectors['AKVARIUS_PAY_CREDIT_SBP'],
        ];
    }

    public function get_finlab_sectors()
    {
        return [
            $this->sectors['FINLAB_PAYMENT'],
            $this->sectors['FINLAB_ADD_CARD'],
        ];
    }

    public function get_vipzaim_sectors()
    {
        return [
            $this->sectors['VIPZAIM_PAYMENT'],
        ];
    }

    public function get_rzs_sectors()
    {
        return [
            $this->sectors['RZS_PAYMENT'],
            $this->sectors['RZS_ADD_CARD'],
            $this->sectors['RZS_PAY_CREDIT'],
            $this->sectors['RZS_PAY_CREDIT_SBP'],
            $this->sectors['RZS_PAY_CREDIT_SBP_FEE_10'],
            $this->sectors['RZS_PAY_CREDIT_SBP_FEE_15'],
        ];
    }

    public function get_moredeneg_sectors()
    {
        return [
            $this->sectors['MOREDENEG_PAYMENT'],
            $this->sectors['MOREDENEG_SBP_TOKEN'],
            $this->sectors['MOREDENEG_PAY_CREDIT'],
            $this->sectors['MOREDENEG_PAY_CREDIT_SBP'],
            $this->sectors['MOREDENEG_PAY_CREDIT_SBP_FEE_10'],
            $this->sectors['MOREDENEG_PAY_CREDIT_SBP_FEE_15'],
        ];
    }

    public function get_lord_sectors()
    {
        return [
            $this->sectors['LORD_PAYMENT'],
            $this->sectors['LORD_ADD_CARD'],
            $this->sectors['LORD_PAY_CREDIT'],
            $this->sectors['LORD_PAY_CREDIT_SBP'],
            $this->sectors['LORD_PAY_CREDIT_SBP_FEE_10'],
            $this->sectors['LORD_PAY_CREDIT_SBP_FEE_15'],
        ];
    }

    public function get_frida_sectors()
    {
        return [
            $this->sectors['FRIDA_PAYMENT'],
            $this->sectors['FRIDA_SBP_TOKEN'],
            $this->sectors['FRIDA_PAY_CREDIT'],
            $this->sectors['FRIDA_PAY_CREDIT_SBP'],
            $this->sectors['FRIDA_PAY_CREDIT_SBP_FEE_10'],
            $this->sectors['FRIDA_PAY_CREDIT_SBP_FEE_15'],
        ];
    }

    public function get_fastfinance_sectors()
    {
        return [
            $this->sectors['FASTFINANCE_PAYMENT'],
            $this->sectors['FASTFINANCE_SBP_TOKEN'],
            $this->sectors['FASTFINANCE_PAY_CREDIT'],
            $this->sectors['FASTFINANCE_PAY_CREDIT_SBP'],
            $this->sectors['FASTFINANCE_PAY_CREDIT_SBP_FEE_10'],
            $this->sectors['FASTFINANCE_PAY_CREDIT_SBP_FEE_15'],
        ];
    }

    public function checkDebtAndPromo($user_balance, $promo, $amount, $prolongation)
    {
        if (!$user_balance) return false;

        $debt = (int) $user_balance->ostatok_od + (int) $user_balance->ostatok_percents;
        $correctDate = $user_balance->discount_date && strtotime($user_balance->discount_date) > time();

        // Проверяем, что идёт полное погашение долга
        // и что сумма скидки меньше суммы платежа
        // И что сумма скидки не превышает начисленный процент скидки
        // И что продление не выбрано
        // И что дата скидки не истекла
        return $debt <= $amount
            && $amount > $promo
            && $user_balance->allready_added > $promo
            && !$prolongation
            && $correctDate;
    }

    /**
     * Best2pay::get_payment_link()
     *
     * Метод возвращает ссылку для оплаты любой картой
     *
     * @param $params
     * @return string
     */
    public function get_payment_link($params)
    {
        $sbp = (isset($params['payment_method']) && $params['payment_method'] == 'sbp') ? 1 : 0;
        $order = $this->orders->get_order((int)$params['order_id']);
        
        $user = $this->users->get_user((int)$params['user_id']);
        
        $fee_rate = $this->calculateFee($order, $user, $params['action_type']);
        $fee = round(max($this->min_fee, $params['amount'] * $fee_rate), 2);

        $sector = $this->getSector($fee_rate, $params['organization_id'], $order, $sbp);

        $password = $this->passwords[$sector];

        $fio = $user->lastname . ' ' . $user->firstname . ' ' . $user->patronymic;

        if ($params['payment_type'] == self::PAYMENT_TYPE_REFUSER) {
            $description = 'Узнай причину отказа ' . $fio . ' ' . $user->birth;
        } elseif (in_array($params['payment_type'], array_values(self::PAYMENT_TYPE_CREDIT_RATING_MAPPING))) {
            $description = 'Оплата услуги Кредитный рейтинг';
        } elseif (!empty($params['prolongation'])) {
            $description = 'Пролонгация по договору ' . $params['number'];
        } else {
            $description = 'Оплата по договору ' . $params['number'];
        }

        if ($sbp) {
            $description .= ' (СБП)';
        }

        $data_pay = [
            'user_id' => empty($params['user_id']) ? 0 : $params['user_id'],
            'order_id' => empty($params['order_id']) ? 0 : $params['order_id'],
            'contract_number' => empty($params['number']) ? '' : $params['number'],
            'card_id' => $sbp ? 0 : (empty($params['card_id']) ? 0 : $params['card_id']),
            'amount' => empty($params['amount']) ? 0 : $params['amount'],
            'insure' => empty($params['insure']) ? 0 : $params['insure'],
            'fee' => empty($fee) ? 0 : $fee,
            'prolongation' => empty($params['prolongation']) ? 0 : $params['prolongation'],
            'prolongation_day' => empty($params['prolongation_day']) ? 0 : $params['prolongation_day'],
            'asp' => empty($params['asp']) ? '' : $params['asp'],
            'created' => date('Y-m-d H:i:s'),
            'sector' => $sector,
            'description' => $description,
            'payment_type' => $params['payment_type'],
            'calc_percents' => $params['calc_percents'],
            'grace_payment' => $params['grace_payment'],
            'chdp' => $params['chdp'],
            'pdp' => $params['pdp'],
            'organization_id' => $params['organization_id'] ?? 0,
            'contract_payment' => $params['contract_payment'] ?? false,
            'is_sbp' => $sbp ? 1 : 0,
            'refinance' => $params['refinance'] ?? 0,
            'create_from' => $params['create_from'] ?? '',
            'discount_amount' => $params['discount_amount'] ?? 0,
            'referral_discount_amount' => $params['referral_discount_amount'] ?? 0,
            'operation_date' => date('Y-m-d H:i:s'),
        ];

        $payment_id = $this->best2pay->add_payment($data_pay);
        $userId = (int) ($order ? $order->user_id : $this->user->id);
        $usesTSbp = $this->users->user_uses_sbp_tbank($userId);
        $service_organization_id = $data_pay['organization_id'];

        if (!empty($payment_id))
        {
            // Если sbp = true и пользователь не использует СБП в ТБанк
            if (($sbp && !$usesTSbp) || !$sbp) {
                // регистрируем оплату
                $data = array(
                    'sector' => $sector,
                    'amount' => round($params['amount'] * 100),
                    'currency' => $this->currency_code,
                    'reference' => $payment_id,
                    'description' => $description,
                    'mode' => 1,
                    'url' => $this->config->root_url.'/best2pay_callback/payment',
                    'life_period' => $this->life_period,
                );

                if (!empty($params['recurring_data'])) {
                    $data['recurring_data'] = [
                        'action_type' => $params['action_type'],
                        'star_oracle' => $params['recurring_data']['star_oracle']
                    ];
                }

                if (!empty($fee)) {
                    $data['fee'] = $fee * 100;
                }

                $data['signature'] = $this->get_signature(array(
                    $data['sector'],
                    $data['amount'],
                    $data['currency'],
                    $password
                ));
                $b2p_order_id = $this->send('Register', $data);
                if (!empty($b2p_order_id))
                {

                    $this->createDops($params, $payment_id, $service_organization_id);

                    // получаем длинную ссылку на оплату
                    $link_data = array(
                        'sector' => $sector,
                        'id' => $b2p_order_id,
                    );

                    if (!$sbp) {
                        $link_data['action'] = 'pay';
                        $purchase_type = 'webapi/Purchase?';
                    } else {
                        $purchase_type = 'webapi/PurchaseSBP?';
                    }

                    $link_data['signature'] = $this->get_signature(array($sector, $b2p_order_id, $password));

                    if (!$sbp && !empty($params['card_id']))
                    {
                        if ($card = $this->get_card($params['card_id']))
                        {
                            $link_data['token'] = $card->token;
                        }
                    }

                    $link = $this->url.$purchase_type.http_build_query($link_data);

                    $update = array(
                        'register_id' => $b2p_order_id,
                        'reference' => $payment_id,
                        'payment_link' => $link,
                        'body' => serialize($data),
                    );
                    $this->update_payment($payment_id, $update);

                    return $payment_id;
                }
            } else {
                $orderId = (int) ($order ? $order->order_id : $params['order_id']);
                $initParams = [];

                try {
                    $initParams = [
                        'payment_id' => $payment_id,
                        'amount' => $data_pay['amount'],
                        'is_attach_account' => !$this->TBankDatabaseService->checkActiveAccountByUserId($userId),
                        'user_id' => $userId,
                    ];

                    $this->TBankService->init($initParams);
                } catch (\Throwable $e) {
                    $this->logging(__METHOD__, 'Tinkoff.Init', $initParams, ['error' => $e->getMessage()], date('d-m-Y').'-t-bank-error.txt');
                    return false;
                }

                $this->createDops($params, $payment_id, $service_organization_id);

                try {
                    if ($requestKey = $this->TBankService->getQr($payment_id)) {
                        $updateData = ['request_key' => $requestKey];

                        if ($orderId) {
                            $updateData['order_id'] = $orderId;
                        }

                        $this->TBankDatabaseService->updateAccount($updateData, [
                            'user_id' => $userId,
                            'status' => 'ACTIVE'
                        ]);
                    } else {
                        return false;
                    }
                } catch (\Throwable $e) {
                    $this->logging(__METHOD__, 'Tinkoff.getQr', ['payment_id' => $payment_id], ['error' => $e->getMessage()], date('d-m-Y').'-t-bank-error.txt');
                    return false;
                }

                return $payment_id;
            }

        }

        return false;
    }

    public function purchaseBySectorCard(array $data, array $additionalData = [])
    {
        $sector = $this->sectors['RZS_CREDIT_REFINANCE'] ?? null;
        $password = $this->passwords[$sector] ?? null;

        if (empty($password) || empty($sector)) {
            throw new \Exception('Сектор и пароль для сектора не указаны');
        }

        $registerData = [
            'sector' => $sector,
            'amount' => $data['amount'],
            'currency' => $this->currency_code,
        ];

        if (!empty($additionalData['number'])) {
            $registerData['description'] = 'Оплата по договору ' . $additionalData['number'];
        }

        if (!empty($additionalData['reference'])) {
            $registerData['reference'] = $additionalData['reference'];
        }

        $registerData = array_merge($registerData, [
            'mode' => 1,
            'url' => $this->config->root_url.'/best2pay_callback/payment',
        ]);

        $registerData = array_merge($registerData, $data);

        $registerData['signature'] = $this->get_signature([
            $registerData['sector'],
            $registerData['amount'],
            $registerData['currency'],
            $password
        ]);

        if ($b2p_order_id = $this->send('Register', $registerData)) {
            $this->logging(__METHOD__, 'Register', $registerData, ['b2p_order_id' => $b2p_order_id], 'refinance.txt');
            $request = [
                'sector' => $sector,
                'id' => $b2p_order_id,
            ];

            $request['signature'] = $this->get_signature([
                $sector,
                $b2p_order_id,
                $password,
            ]);

            $response = $this->send('PurchaseBySectorCard', $request);

            $this->logging(__METHOD__, 'purchaseBySectorCard', $request, $response, 'refinance.txt');

            return $response;
        }

        $this->logging(__METHOD__, 'purchaseBySectorCard', ['b2p_order_id' => $b2p_order_id], ['error' => 'empty b2p_order_id'], 'refinance.txt');

        return false;
    }

    /**
     * @param $params
     * @param $payment_id
     * @param $service_organization_id
     * @return void
     */
    public function createDops($params, $payment_id, $service_organization_id): void
    {
        if (!empty($params['multipolis']) || !empty($params['tv_medical']) || !empty($params['star_oracle'])) {
            $user_id = (int)($params['user_id'] ?: $_SESSION['user_id']);
            $order_id = $params['order_id'];

            //добавляем мультиполис (Консьерж)
            if (!empty($params['multipolis'])) {
                $data_multipolis = [
                    'user_id' => $user_id,
                    'order_id' => $order_id,
                    'status' => $this->multipolis::STATUS_NEW,
                    'amount' => (float)$params['multipolis_amount'],
                    'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                    'payment_id' => (int)$payment_id,
                    'organization_id' => $service_organization_id,
                    'action_type' => $params['action_type'] ?? '',
                ];

                $this->multipolis->addItem($data_multipolis);
            }

            //добавляем телемедицину (Витамед)
            if (!empty($params['tv_medical'])) {
                $data_tv_medical_payment = [
                    'tv_medical_id' => $params['tv_medical_id'],
                    'amount' => $params['tv_medical_amount'],
                    'user_id' => $user_id,
                    'order_id' => $order_id,
                    'status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_NEW,
                    'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                    'payment_id' => (int)$payment_id,
                    'organization_id' => $params['action_type'] === $this->star_oracle::ACTION_TYPE_PARTIAL_PAYMENT ? $this->organizations::FINTEHMARKET_ID : $service_organization_id,
                    'action_type' => $params['action_type'] ?? '',
                ];

                $this->tv_medical->addPayment($data_tv_medical_payment);
            }

            //добавляем Звездный оракул
            if (!empty($params['star_oracle'])) {
                $isIssuance = $params['payment_type'] === $this->star_oracle::ACTION_TYPE_ISSUANCE;

                $organization_id = $isIssuance
                    ? $this->organizations::FINTEHMARKET_ID
                    : $service_organization_id;

                $data_star_oracle = [
                    'amount' => $params['star_oracle_amount'],
                    'user_id' => $user_id,
                    'order_id' => $order_id,
                    'status' => $this->star_oracle::STAR_ORACLE_STATUS_NEW,
                    'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                    'transaction_id' => (int)$payment_id,
                    'organization_id' => $organization_id,
                    'action_type' => $params['action_type'],
                ];

                $this->star_oracle->addStarOracleData($data_star_oracle);
            }
        }
    }

    /**
     * Получение комиссии на секторе
     * @param $sector
     * @param $password
     * @param $amount
     * @param $ps
     * @return false|string
     */
    public function checkFee($sector, $password, $amount, $ps = 11)
    {
        $data = array(
            'sector' => $sector,
            'amount' => $amount * 100,
            'ps' => $ps,
            'currency' => $this->currency_code,
        );

        $data['signature'] = $this->get_signature(array(
            $data['sector'],
            $data['amount'],
            $ps,
            $password
        ));

        return $this->send('PaymentFee', $data);
    }


    private function get_split_data($params)
    {
        $split = [
            'BOOSTRA' => $params['amount'] * 100,
            'FINTEH' => 0,
        ];
        if (!empty($params['multipolis'])) {
            $split['BOOSTRA'] -= round($params['multipolis_amount']*100);
            $split['FINTEH'] += round($params['multipolis_amount']*100);
        }
        if (!empty($params['tv_medical'])) {
            $split['BOOSTRA'] -= round($params['tv_medical_amount']*100);
            $split['FINTEH'] += round($params['tv_medical_amount']*100);
        }
        if ($split['BOOSTRA'] != ($params['amount'] * 100)) {
            return 'receiverList:'.implode(' ', $this->split_id).',amountList:'.implode(' ', $split);
        }
        
        return false;
    }
    
    public function get_split_payment_link($params)
    {
        $sector = $this->sectors['PAYMENT'];


        if (in_array($params['payment_type'], array_values(self::PAYMENT_TYPE_CREDIT_RATING_MAPPING))) {
            $description = 'Оплата услуги Кредитный рейтинг';
        } elseif (!empty($params['prolongation'])) {
            $description = 'Пролонгация по договору ' . $params['number'];
        } else {
            $description = 'Оплата по договору ' . $params['number'];
        }

        if ($split_data = $this->get_split_data($params)) {
            $sector = $this->sectors['SPLIT_FINTEH'];

            $fee = 0.055;
            $min_fee = 30;    
            $fee = round(max($min_fee, floatval($params['amount'] * $fee)), 2);
        } else {
            $fee = round(max($this->min_fee, floatval($params['amount'] * $this->fee)), 2);
        }
        
        $password = $this->passwords[$sector];

        $data_pay = [
            'user_id' => empty($params['user_id']) ? 0 : $params['user_id'],
            'order_id' => empty($params['order_id']) ? 0 : $params['order_id'],
            'contract_number' => empty($params['number']) ? '' : $params['number'],
            'card_id' => empty($params['card_id']) ? 0 : $params['card_id'],
            'amount' => empty($params['amount']) ? 0 : $params['amount'],
            'insure' => empty($params['insure']) ? 0 : $params['insure'],
            'fee' => empty($fee) ? 0 : $fee,
            'prolongation' => empty($params['prolongation']) ? 0 : $params['prolongation'],
            'asp' => empty($params['asp']) ? '' : $params['asp'],
            'created' => date('Y-m-d H:i:s'),
            'sector' => $sector,
            'description' => $description,
            'payment_type' => $params['payment_type'],
            'calc_percents' => $params['calc_percents'],
            'split_data' => $split_data ?? '',
        ];
        $payment_id = $this->best2pay->add_payment($data_pay);
        
        if (!empty($payment_id))
        {
            // регистрируем оплату
            $data = array(
                'sector' => $sector,
                'amount' => $params['amount'] * 100,
                'currency' => $this->currency_code,
                'reference' => $payment_id,
                'description' => $description,
                'mode' => 1,
                'url' => $this->config->root_url.'/best2pay_callback/payment',
            );
            if (!empty($fee))
                $data['fee'] = $fee * 100;

            if (!empty($split_data)) {
                $data['split_data'] = base64_encode($split_data);
            }
            
            $data['signature'] = $this->get_signature(array(
                $data['sector'], 
                $data['amount'], 
                $data['currency'], 
                $password
            ));
            
            $b2p_order_id = $this->send('Register', $data);

            if (!empty($b2p_order_id))
            {

                if (!empty($params['prolongation']) && (!empty($params['multipolis']) || !empty($params['tv_medical']))) {
                    $user_id = (int)($data_pay['user_id'] ?: $_SESSION['user_id']);
                    $order_id = $data_pay['order_id'];

                    //добавляем мультиполис
                    if (!empty($params['multipolis'])) {
                        $data_multipolis = [
                            'user_id' => $user_id,
                            'order_id' => $order_id,
                            'status' => $this->multipolis::STATUS_NEW,
                            'amount' => (float)$params['multipolis_amount'],
                            'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                            'payment_id' => (int)$payment_id,
                            'organization_id' => 5,
                        ];

                        $this->multipolis->addItem($data_multipolis);
                    }

                    //добавляем телемедицину
                    if (!empty($params['tv_medical'])) {
                        $data_tv_medical_payment = [
                            'tv_medical_id' => $params['tv_medical_id'],
                            'amount' => $params['tv_medical_amount'],
                            'user_id' => $user_id,
                            'order_id' => $order_id,
                            'status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_NEW,
                            'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                            'payment_id' => (int)$payment_id,
                            'organization_id' => 5,
                        ];

                        $this->tv_medical->addPayment($data_tv_medical_payment);
                    }
                }

                // получаем длинную ссылку на оплату
                $link_data = array(
                    'sector' => $sector,
                    'action' => 'pay',
                    'id' => $b2p_order_id
                );
                $link_data['signature'] = $this->get_signature(array($sector, $b2p_order_id, $password));
                
                if (!empty($params['card_id']))
                {
                    if ($card = $this->get_card($params['card_id']))
                    {
                        $link_data['token'] = $card->token;
                    }
                }
                
                $link = $this->url.'webapi/Purchase?'.http_build_query($link_data);
        
                $update = array(
                    'register_id' => $b2p_order_id,
                    'reference' => $payment_id,
                    'payment_link' => $link,
                    'body' => serialize($data),
                );
                $this->update_payment($payment_id, $update);
                
                return $payment_id;
            }

        }

        return false;
    }

    /**
     * Best2pay::add_card()
     *
     * Метод возврашает ссылку для привязки карты
     *
     * @param integer $user_id
     * @param integer $sector
     * @return string $link
     */
    public function get_link_add_card($user_id, $sector = NULL, $cardId = null, $recurring_consent = 1, $params = [])
    {
        if (empty($sector))
            $sector = $this->sectors['ADD_CARD'];
        $password = $this->passwords[$sector];

        $amount = 100;
        $description = 'Привязка карты'; // описание операции

        if (!($user = $this->users->get_user((int)$user_id)))
            return false;

        $card = $cardId ? $this->get_card($cardId) : null;

        $user_address = $user->Regstreet_shorttype.' '.$user->Regstreet.', д.'.$user->Reghousing;
        if (!empty($user->Regbuilding))
            $user_address .= ', стр.'.$user->Regbuilding;
        if (!empty($user->Regroom))
            $user_address .= ', кв.'.$user->Regroom;

        $user_city = $user->Regregion_shorttype.' '.$user->Regregion.' '.$user->Regcity_shorttype.' '.$user->Regcity;
        $user_city = str_replace(['"', "'"], ['', ''], $user_city);

        // регистрируем оплату
        $data = array(
            'sector' => $sector,
            'amount' => $amount,
            'currency' => $this->currency_code,
            'reference' => $user_id,
            'client_ref' => $user_id,
            'description' => $description,
            'address' => $user_address,
            'city' => $user_city,
//            'phone' => $user->phone_mobile,
//            'email' => $user->email,
            'first_name' => $user->firstname,
            'last_name' => $user->lastname,
            'patronymic' => $user->patronymic,
            'url' => $this->config->root_url . (!empty($params['url']) ? $params['url'] : '/best2pay_callback/add_card'),
            'failurl' => $this->config->root_url . (!empty($params['failurl']) ? $params['failurl'] : '/best2pay_callback/add_card'),
//            'recurring_period' => 0,
//            'mode' => 1
            'life_period' => $this->life_period,
        );
        $data['signature'] = $this->get_signature(array($data['sector'], $data['amount'], $data['currency'], $password));

        $b2p_order = $this->send('Register', $data);

        $this->logging(__METHOD__, '', 'Получена ссылка для привязки карты', ['data' => $data, 'b2p_order' => $b2p_order], self::LOG_FILE);

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($b2p_order);echo '</pre><hr />';
        $xml = simplexml_load_string($b2p_order);
        $b2p_order_id = (string)$xml->id;

        $this->add_transaction(array(
            'user_id' => $user_id,
            'amount' => $amount,
            'sector' => $sector,
            'register_id' => $b2p_order_id,
            'reference' => $user_id,
            'description' => $description,
            'created' => date('Y-m-d H:i:s'),
            'recurring_consent' => $recurring_consent,
        ));

            // получаем ссылку на оплату 10руб для привязки карты
        $data = array(
            'sector' => $sector,
            'id' => $b2p_order_id,
            'get_token' => 1,
        );
        $data['signature'] = $this->get_signature(array($sector, $b2p_order_id, $password));

        if ($card) $data['token'] = $card->token;

        $link = $this->url.'webapi/CardEnroll?'.http_build_query($data);

        return $link;

    }

    /**
     * Best2pay::get_link_add_sbp()
     *
     * Метод возвращает ссылку для привязки счёта СБП
     *
     * @param integer $user_id
     * @return string $link
     */
    public function get_link_add_sbp($user_id, $recurring_consent = 1)
    {
        if (!($user = $this->users->get_user((int)$user_id)))
            return false;

        // Если дев окружение, то ссылка b2p на привязку сбп счета не нужна
        // Просто возвращаем url для вызова Best2payCallback - sbp_token_dev и ручного добавления счета в БД
        if ($this->helpers->isDev()) {
            return $this->config->root_url . '/best2pay_callback/sbp_token_dev';
        }

        $subscribe_response_object = $this->send_request_get_sbp_link((int)$user->id, $recurring_consent);

        if (empty($subscribe_response_object)) {
            return false;
        }

        return (string)$subscribe_response_object->payload;
    }

    public function send_request_get_sbp_link(int $user_id, int $recurring_consent = 1)
    {
        $sector = $this->sectors['RZS_SBP_TOKEN'];
        $password = $this->passwords[$sector];

        $description = 'Привязка счёта СБП';


        // получаем длинную ссылку на привязку счёта СБП
        $link_data = array(
            'sector' => $sector,
            'description' => $description,
            'url' => $this->config->root_url.'/best2pay_callback/payment_sbp?user_id='.$user_id,
        );
        $link_data['signature'] = $this->get_signature(array($sector, $description, $password));

        $subscribe_response = $this->send('GetSBPSubscription', $link_data);

        $logFileName = 'sbp_tokens_get_link.txt';
        //$this->logging(__METHOD__, 'Best2payCallback', $_REQUEST, file_get_contents('php://input'), $logFileName);
        $this->logging(__METHOD__, 'GetSBPSubscription', $link_data, $subscribe_response, $logFileName);

        $subscribe_response_object = simplexml_load_string($subscribe_response);
        if (isset($subscribe_response_object->state) && $subscribe_response_object->state == "CREATED") {

            // recurring_consent сохраняется в транзакцию для использования в callback
            // Это решает проблему потери сессии при переходе на внешний домен Best2Pay
            $this->add_transaction(array(
                'user_id' => $user_id,
                'amount' => 0,
                'sector' => $sector,
                'register_id' => (int)$subscribe_response_object->id,
                'reference' => (string)$subscribe_response_object->qrcId,
                'description' => $description,
                'created' => date('Y-m-d H:i:s'),
                'recurring_consent' => $recurring_consent,
            ));

            return $subscribe_response_object;
        }

        return null;
    }

    // Не используется
    public function runTestScenarioToAddSbp(string $qrcId)
    {
        if ($this->helpers->isDev()) {
            $case_id = 154; // Уведомление об успешном статусе Операции СБП; (привязка СБП)

            $sector = $this->sectors['RZS_SBP_TOKEN'];
            $password = $this->passwords[$sector];

            $data = [
                'sector' => $sector,
                'case_id' => $case_id,
                'qrc_id' => $qrcId,
                'signature' => $this->best2pay->get_signature([$sector, $case_id, $qrcId, $password])
            ];

            $response = $this->best2pay->send('SBPTestCase', $data, 'test');

            $this->logging(__METHOD__, 'test/SBPTestCase', 'Отправлен запрос для запуска тестового сценария', ['data' => $data, 'response' => $response], self::LOG_FILE);
        }
    }



    /**
     * Запуск тестового сценария для оплаты по СБП
     *
     * Оплата по СБП
     * 1. ajax/b2p_payment.php:get_payment_link() - ajax запрос
     * 2. api/Best2pay.php:get_payment_link() - получение ссылки на оплату
     * 3. Пользователь переход по ссылке (это ОБЯЗАТЕЛЬНО, иначе при вызове runTestScenarioToAddSbp будет ошибка, что заказ в b2p не найден)
     * 4. api/Best2pay.php:runTestScenarioToAddSbp() - после вызова runTestScenarioToAddSbp пользователь обязательно должен находиться на странице https://test.best2pay.net/webapi/PurchaseSBP? чтобы произошел редирект на /best2pay_callback/payment
     * 5. view/Best2payCallback.php:payment_action() (URL был передан при Register)
     *
     * @param int $sector
     * @param string $b2p_order_id
     * @return void
     */
    public function runTestScenarioSbpPayment(int $sector, string $b2p_order_id): bool
    {
        if (!$this->helpers->isDev() || empty($sector) || empty($b2p_order_id)) {
            return false;
        }

        $password = $this->passwords[$sector];

        $case_id = 150; // Уведомление об успешном статусе Операции СБП; (оплата по СБП)

        $data = [
            'sector' => $sector,
            'case_id' => $case_id,
            'order_id' => $b2p_order_id,
            'signature' => $this->best2pay->get_signature([$sector, $case_id, $b2p_order_id, $password])
        ];

        $response = $this->best2pay->send('SBPTestCase', $data, 'test');

        $this->logging(__METHOD__, 'test/SBPTestCase', 'Отправлен запрос для запуска тестового сценария', ['data' => $data, 'response' => $response], self::LOG_FILE);
        return true;
    }


    /**
     * Рекуррентный платеж по токену карты
     *
     * @param $card_id
     * @param $amount
     * @param $description
     * @param $organization_id
     * @return false|mixed
     */
    public function purchase_by_token($card_id, $amount, $description, $organization_id)
    {
        if ($organization_id == $this->organizations::AKVARIUS_ID) {
            $sector = $this->sectors['AKVARIUS_RECURRENT_BY_TOKEN'];
        } elseif ($organization_id == $this->organizations::FINLAB_ID) {
            $sector = $this->sectors['FINLAB_RECURRENT_BY_TOKEN'];
        } else {
            return false;
        }

        $password = $this->passwords[$sector];

        $card = $this->get_card($card_id);
        if (empty($card)) {
            return false;
        }

        $user = $this->users->get_user((int)$card->user_id);
        if (empty($user)) {
            return false;
        }

        $data = [
            'sector' => $sector,
            'amount' => $amount * 100,
            'currency' => $this->currency_code,
            'reference' => $user->id,
            'description' => $description,
        ];

        $data['signature'] = $this->get_signature(
            [
                $data['sector'],
                $data['amount'],
                $data['currency'],
                $password
            ]
        );

        // регистрируем оплату
        $b2p_order = $this->send('Register', $data);

        $xml = simplexml_load_string($b2p_order);
        $b2p_order_id = (string)$xml->id;

        $data = [
            'sector' => $sector,
            'id' => $b2p_order_id,
            'token' => $card->token,
        ];

        $data['signature'] = $this->get_signature(
            [
                $data['sector'],
                $data['id'],
                $data['token'],
                $password
            ]
        );

        // оплата по токену карты
        $recurring_xml = $this->send('PurchaseByToken', $data);
        $recurring = simplexml_load_string($recurring_xml);
        $result = json_decode(json_encode($recurring));

        if (empty($result->id)) {
            $this->logging(__METHOD__, 'Recurring error from b2p', $data, $result, 'b2p_payment.txt');

            return false;
        }

        $result->register_id = $b2p_order_id;
        $result->sector = $sector;
        $result->callback_response = $recurring_xml;

        return $result;
    }

    public function purchase_by_sbp_token($b2p_payment, $token)
    {
        $logFileName = 'sbp_tokens_'.date('Y-m-d').'.log';
        $sector = $this->sectors['AKVARIUS_PAY_CREDIT_SBP'];
        $password = $this->passwords[$sector];

        $user = $this->users->get_user((int)$b2p_payment->user_id);

        if ($user) {
            // регистрируем оплату
            $data = array(
                'sector' => $sector,
                'amount' => $b2p_payment->amount * 100,
                'currency' => $this->currency_code,
                'reference' => $b2p_payment->id,
                'description' => $b2p_payment->description,
                'fee' => $b2p_payment->fee * 100,
                'first_name' => $user->firstname,
                'last_name' => $user->lastname,
                'patronymic' => $user->patronymic,
                'fio' => $user->lastname . ' ' . $user->firstname . ' ' . $user->patronymic,
                'phone' => $user->phone_mobile,
            );
            $data['signature'] = $this->get_signature(array($data['sector'], $data['amount'], $data['currency'], $password));
            $this->logging(__METHOD__, 'Best2pay', ['registerData'], $data, $logFileName);

            $b2p_order = $this->send('Register', $data);
            $this->logging(__METHOD__, 'Best2pay', ['register'], $b2p_order, $logFileName);

            $xml = simplexml_load_string($b2p_order);
            $b2p_order_id = (string)$xml->id;
            $data = array(
                'sector' => $sector,
                'id' => $b2p_order_id,
                'token' => (string)$token,
            );
            $data['signature'] = $this->get_signature(array(
                $data['sector'],
                $data['id'],
                $data['token'],
                $password
            ));
            $this->logging(__METHOD__, 'Best2pay', ['recurring_data'], $data, $logFileName);

            $recurring = $this->send('PurchaseSBPByToken', $data);
            $this->logging(__METHOD__, 'Best2pay', ['recurring'], $recurring, $logFileName);
            return simplexml_load_string($recurring);
        }
        return false;
    }

    public function reverse($params)
    {
        $sector = $params['sector'];
        $password = $this->passwords[$sector];
        
        $data = array(
            'sector' => $sector,
            'id' => $params['register_id'],
            'amount' => $params['amount'],
            'currency' => $this->currency_code,
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'],
            $data['id'],
            $data['amount'],
            $data['currency'],
            $password
        ));

    	$reverse = $this->send('Reverse', $data);
$this->logging('Reverse', '', (array)$data, $reverse, 'b2p_reverse.txt');
        
        return $reverse;
    }

    /**
     * Best2pay::pay_contract()
     * Переводит сумму займа на карту клиенту
     * @param integer $contract_id
     * @return string - статус перевода COMPLETE при успехе или пустую строку
     */
    public function pay_contract($contract_id)
    {
        $sector = $this->sectors['PAY_CREDIT'];
//        $password = $this->settings->apikeys['best2pay'][$sector];
        $password = $this->passwords[$sector];
                        
        if (!($contract = $this->contracts->get_contract($contract_id)))
            return false;
        
        if ($contract->status != 1)
            return false;
        
        $this->contracts->update_contract($contract->id, array('status' => 9));
        
        if (!($user = $this->users->get_user((int)$contract->user_id)))
            return false;

        if (!($card = $this->cards->get_card((int)$contract->card_id)))
            return false;

        $data = array(
            'sector' => $sector,
            'amount' => $contract->amount * 100,
            'currency' => $this->currency_code,
//            'pan' => $card->pan,
            'reference' => $contract->id,
            'token' => $card->token,
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'], 
            $data['amount'], 
            $data['currency'], 
            $data['pan'], 
            $data['token'], 
            $password
        ));
        
        $p2pcredit = array(
            'contract_id' => $contract->id,
            'user_id' => $user->id,
            'date' => date('Y-m-d H:i:s'),
            'body' => $data
        );
        if ($p2pcredit_id = $this->add_p2pcredit($p2pcredit))
        {
            $response = $this->send('P2PCredit', $data, 'gateweb');
            
            $xml = simplexml_load_string($response);
            $status = (string)$xml->order_state;
            
            $this->update_p2pcredit($p2pcredit_id, array(
                'response' => $response, 
                'status' => $status,
                'register_id' => (string)$xml->order_id,
                'operation_id' => (string)$xml->id,
                'complete_date' => date('Y-m-d H:i:s'),
            ));
    
            return $status;
        }
    }
        
    public function recurrent_pay($card_id, $amount, $description, $contract_id = null)
    {
        $sector = $this->sectors['RECURRENT'];
        $password = $this->passwords[$sector];
        
//        $fee = max($this->min_fee, floatval($amount * $this->fee));
                
        if (!($card = $this->cards->get_card($card_id)))
            return false;
    
        if (!($user = $this->users->get_user((int)$card->user_id)))
            return false;
        
        $data = array(
            'sector' => $sector,
            'id' => $card->register_id,
            'amount' => $amount,
            'currency' => $this->currency_code,
//            'fee' => $fee
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'], 
            $data['id'], 
            $data['amount'], 
//            $data['fee'], 
            $data['currency'], 
            $password
        ));

        $transaction_id = $this->transactions->add_transaction(array(
            'user_id' => $user->id,
            'amount' => $amount,
            'sector' => $sector,
            'register_id' => $card->register_id,
            'reference' => $user->id,
            'description' => $description,
            'created' => date('Y-m-d H:i:s'),
        ));
        
        $recurring = $this->send('Recurring', $data);
        $xml = simplexml_load_string($recurring);
        $status = (string)$xml->state;


        if ($status == 'APPROVED')
        {
            
            $contract = $this->contracts->get_contract($contract_id);
            
            $payment_amount = $amount / 100;
            
            $this->operations->add_operation(array(
                'contract_id' => $contract->id,
                'user_id' => $contract->user_id,
                'order_id' => $contract->order_id,
                'type' => 'RECURRENT',
                'amount' => $payment_amount,
                'created' => date('Y-m-d H:i:s'),
            ));
            
            // списываем долг
            if ($contract->loan_percents_summ > $payment_amount)
            {
                $new_loan_percents_summ = $contract->loan_percents_summ - $payment_amount;
                $new_loan_body_summ = $contract->loan_body_summ;
            }
            else
            {
                $new_loan_percents_summ = 0;
                $new_loan_body_summ = ($contract->loan_body_summ + $contract->loan_percents_summ) - $payment_amount;
            }
            
            $this->contracts->update_contract($contract->id, array(
                'loan_percents_summ' => $new_loan_percents_summ,
                'loan_body_summ' => $new_loan_body_summ
            ));
            
            // закрываем кредит
            if ($new_loan_body_summ <= 0)
            {
                $this->contracts->update_contract($contract->id, array(
                    'status' => 3, 
                ));
                
                $this->orders->update_order($contract->order_id, array(
                    'status' => 7
                ));
            }
            
            
            return true;
//echo b2p_FILEb2p_.' '.b2p_LINEb2p_.'<br /><pre>';echo(htmlspecialchars($recurring));echo $contract_id.'</pre><hr />';exit;
            
        }
        else
        {
            return false;
        }
        
    }
    
    public function recurrent($card_id, $amount, $description)
    {
        $sector = $this->sectors['RECURRENT'];
//        $password = $this->settings->apikeys['best2pay'][$sector];
        $password = $this->passwords[$sector];
        
//        $fee = max($this->min_fee, floatval($amount * $this->fee));
                
        if (!($card = $this->cards->get_card($card_id)))
            return false;
    
        if (!($user = $this->users->get_user((int)$card->user_id)))
            return false;
        
        // Увеличиваем сумму заказа
        $data = array(
            'sector' => $sector,
            'id' => $card->register_id,
            'amount' => $amount + 100,
            'currency' => $this->currency_code,
            'recurring_period' => 0,
            'error_period' => 1,
            'error_number' => 3,
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'], 
            $data['id'], 
            $data['amount'], 
            $data['currency'], 
            $password
        ));
        $change_rec = $this->send('ChangeRec', $data);
//echo b2p_FILEb2p_.' '.b2p_LINEb2p_.'<br /><pre>';var_dump('$change_rec', $change_rec);echo '</pre><br /><hr /><br />';

        $data = array(
            'sector' => $sector,
            'id' => $card->register_id,
            'amount' => $amount,
            'currency' => $this->currency_code,
//            'fee' => $fee
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'], 
            $data['id'], 
            $data['amount'], 
//            $data['fee'], 
            $data['currency'], 
            $password
        ));

        $recurring = $this->send('Recurring', $data);

        $xml = simplexml_load_string($recurring);
//echo b2p_FILEb2p_.' '.b2p_LINEb2p_.'<br /><pre>';var_dump('$recurring', $recurring);echo '</pre><hr />';        
        $transaction_id = $this->transactions->add_transaction(array(
            'user_id' => $user->id,
            'amount' => $amount,
            'sector' => $sector,
            'register_id' => $card->register_id,
            'operation' => (string)$xml->id,
            'reason_code' => (string)$xml->reason_code,
            'reference' => $user->id,
            'description' => $description,
            'created' => date('Y-m-d H:i:s'),
            'callback_response' => $recurring
        ));

        return $recurring;

        
    }


    public function get_operation_info($sector, $register_id, $operation_id)
    {
        if ($sector == 2975){
            $this->url = 'https://test.best2pay.net/';
        }
        $password = $this->passwords[$sector];
               
        $data = array(
            'sector' => $sector,
            'id' => $register_id,
            'operation' => $operation_id,
            'get_token' => 1
        );
        $data['signature'] = $this->get_signature(array($sector, $register_id, $operation_id, $password));

        $info = $this->send('Operation', $data);
    
        return $info;
    }
        
    public function get_register_info($sector, $register_id, $get_token = 0)
    {
        if ($sector == 2975){
            $this->url = 'https://test.best2pay.net/';
        }
        
        $password = $this->passwords[$sector];
                
        $data = array(
            'sector' => $sector,
            'id' => $register_id,
            'mode' => 0,
            'get_token' => $get_token
        );
        $data['signature'] = $this->get_signature(array($sector, $register_id, $password));
        
        $info = $this->send('Order', $data);
    
        return $info;
    }
    
    
    private function send($method, $data, $type = 'webapi')
    {
        $string_data = http_build_query($data);
        
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n"
                    . "Content-Length: " . strlen($string_data) . "\r\n",
                'method'  => 'POST',
                'content' => $string_data
            )
        ));
        $b2p = file_get_contents($this->url.$type.'/'.$method, false, $context);
        return $b2p;
    }
    
    private function get_signature($data)
    {
    	$str = '';
        foreach ($data as $item)
            $str .= $item;
        
        $md5 = md5($str);
        $signature = base64_encode($md5);
        
        return $signature;
    }
    
    public function get_reason_code_description($code)
    {
        $descriptions = array(
            2 => 'Неверный срок действия Банковской карты. <br />Платёж отклонён. Возможные причины: недостаточно средств на счёте, были указаны неверные реквизиты карты, по Вашей карте запрещены расчёты через Интернет. Пожалуйста, попробуйте выполнить платёж повторно или обратитесь в Банк, выпустивший Вашу карту. ',
            3 => 'Неверный статус Банковской карты на стороне Эмитента. <br />Платёж отклонён. Пожалуйста, обратитесь в Банк, выпустивший Вашу карту. ',
            4 => 'Операция отклонена Эмитентом. <br />Платёж отклонён. Пожалуйста, обратитесь в Банк, выпустивший Вашу карту. ',
            5 => 'Операция недопустима для Эмитента. Платёж отклонён. Пожалуйста, обратитесь в Банк, выпустивший Вашу карту. ',
            6 => 'Недостаточно средств на счёте Банковской карты. <br />Платёж отклонён. Возможные причины: недостаточно средств на счёте, были указаны неверные реквизиты карты, по Вашей карте запрещены расчёты через Интернет. Пожалуйста, попробуйте выполнить платёж повторно или обратитесь в Банк, выпустивший Вашу карту. ',
            7 => 'Превышен установленный для ТСП лимит на сумму операций (дневной, недельный, месячный) или сумма операции выходит за пределы установленных границ. <br />Платёж отклонён. Пожалуйста, обратитесь в Контактный центр. ',
            8 => 'Операция отклонена по причине срабатывания системы предотвращения мошенничества. <br />Платёж отклонён. Пожалуйста, обратитесь в Контактный центр. ',
            9 => 'Заказ уже находится в процессе оплаты. Операция, возможно, задублировалась. <br />Платёж отклонён. Пожалуйста, обратитесь в Контактный центр. ',
            10 => 'Системная ошибка. <br />Платёж отклонён. Пожалуйста, обратитесь в Контактный центр. ',
            11 => 'Ошибка 3DS аутентификации. <br />Платёж отклонён. Пожалуйста, обратитесь в Контактный центр. ',
            12 => 'Указано неверное значение секретного кода карты. <br />Платёж отклонён. Возможные причины: недостаточно средств на счёте, были указаны неверные реквизиты карты, по Вашей карте запрещены расчёты через Интернет. Пожалуйста, попробуйте выполнить платёж повторно или обратитесь в Банк, выпустивший Вашу карту. ',
            13 => 'Операция отклонена по причине недоступности Эмитента и/или Банка- эквайрера. <br />Платёж отклонён. Пожалуйста, попробуйте выполнить платёж позднее или обратитесь в Контактный центр. ',
            14 => 'Операция отклонена оператором электронных денег. <br />Платёж отклонён. Пожалуйста, обратитесь в платёжную систему, электронными деньгами которой Вы пытаетесь оплатить Заказ. ',
            15 => 'BIN платёжной карты присутствует в черных списках. <br />Платёж отклонён. Пожалуйста, обратитесь в Контактный центр. ',
            16 => 'BIN 2 платёжной карты присутствует в черных списках. <br />Платёж отклонён. Пожалуйста, обратитесь в Контактный центр. ',
            0 => 'Операция отклонена по другим причинам. Требуется уточнение у ПЦ.<br />Платёж отклонён. Пожалуйста, попробуйте выполнить платёж позднее или обратитесь в Контактный центр. '
        );
        
        return isset($descriptions[$code]) ? $descriptions[$code] : '';
    }
    
    
    
	public function get_contract_p2pcredit($contract_id)
    {
        $query = $this->db->placehold("
            SELECT *
            FROM b2p_p2pcredits
            WHERE contract_id = ?
            ORDER BY id DESC
            LIMIT 1
        ", (int)$contract_id);
        $this->db->query($query);
//echo b2p_FILEb2p_.' '.b2p_LINEb2p_.'<br /><pre>';var_dump($query);echo '</pre><hr />';        
        return $this->db->result();
    }
    
	public function get_p2pcredit($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM b2p_p2pcredits
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        if ($result = $this->db->result())
        {
            $result->body = unserialize($result->body);
            $result->response = unserialize($result->response);
        }
	
        return $result;
    }

    /**
     * @param array $filter
     * @param bool $all
     * @return array|false|int
     */
    public function get_p2pcredits(array $filter = array(), bool $all = true)
	{
		$id_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['order_id'])) {
            $id_filter = $this->db->placehold("AND order_id = ?",  (int)$filter['order_id']);
        }

		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_p2pcredits
            WHERE 1
                $id_filter
 	           $keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);

        if ($all) {
            if ($results = $this->db->results()) {
                foreach ($results as $result) {
                    $result->body = unserialize($result->body);
                    $result->response = unserialize($result->response);
                }
            }

            return $results;
        }

        if ($result = $this->db->result()) {
            $result->body = unserialize($result->body);
            $result->response = unserialize($result->response);
        }
        return $result;
    }
    
	public function count_p2pcredits($filter = array())
	{
        $id_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM b2p_p2pcredits
            WHERE 1
                $id_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_p2pcredit($p2pcredit)
    {
        $p2pcredit = (array)$p2pcredit;
        
        if (isset($p2pcredit['body']))
            $p2pcredit['body'] = serialize($p2pcredit['body']);
        if (isset($p2pcredit['response']))
            $p2pcredit['response'] = serialize($p2pcredit['response']);
        
		$query = $this->db->placehold("
            INSERT INTO b2p_p2pcredits SET ?%
        ", $p2pcredit);
        $this->db->query($query);
        $id = $this->db->insert_id();
        return $id;
    }
    
    public function update_p2pcredit($id, $p2pcredit)
    {
        $p2pcredit = (array)$p2pcredit;
        
        if (isset($p2pcredit['body']))
            $p2pcredit['body'] = serialize($p2pcredit['body']);
        if (isset($p2pcredit['response']))
            $p2pcredit['response'] = serialize($p2pcredit['response']);
        
		$query = $this->db->placehold("
            UPDATE b2p_p2pcredits SET ?% WHERE id = ?
        ", $p2pcredit, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_p2pcredit($id)
    {
		$query = $this->db->placehold("
            DELETE FROM b2p_p2pcredits WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
        


    public function find_duplicates($user_id, $pan, $expdate)
    {
    	$query = $this->db->placehold("
            SELECT *
            FROM b2p_cards
            WHERE user_id != ?
            AND expdate = ?
            AND pan = ?
        ", $user_id, $expdate, $pan);
        $this->db->query($query);
        
        return $this->db->results();
    }

    public function find_duplicates_for_user($user_id, $pan, $expdate, $organization_id = 1)
    {
        $query = $this->db->placehold("
            SELECT  COUNT(id) AS count
            FROM b2p_cards
            WHERE user_id = ?
            AND expdate = ?
            AND pan = ?
            AND deleted = 0
            AND deleted_by_client = 0
            AND organization_id = ?
        ", $user_id, $expdate, $pan, $organization_id);
        $this->db->query($query);

        return $this->db->result('count');
    }
    
    
	public function get_card($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM b2p_cards
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }

    public function set_success_pay_event_in_card($pan, $user_id)
    {
        $user_id = $this->db->placehold("AND user_id = ?", (int)$user_id);
        $pan = $this->db->placehold("AND pan = ?", (string)$pan);

        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_cards
            WHERE 1
            $user_id
            $pan
            ORDER BY id DESC 
        ");
        $this->db->query($query);
        $card = $this->db->result();

        if (!empty($card)) {
            $this->best2pay->add_sbp_log([
                'card_id' => $card->id,
                'action' => Best2pay::CARD_ACTIONS['SUCCESS_PAYMENT_CARD'],
                'date' => date('Y-m-d H:i:s')
            ]);
        }

    }

    public function set_error_pay_event_in_card($pan, $user_id)
    {
        $user_id = $this->db->placehold("AND user_id = ?", (int)$user_id);
        $pan = $this->db->placehold("AND pan = ?", (string)$pan);

        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_cards
            WHERE 1
            $user_id
            $pan
            ORDER BY id DESC 
        ");
        $this->db->query($query);
        $card = $this->db->result();

        if (!empty($card)) {
            $this->best2pay->add_sbp_log([
                'card_id' => $card->id,
                'action' => Best2pay::CARD_ACTIONS['ERROR_PAYMENT_CARD'],
                'date' => date('Y-m-d H:i:s')
            ]);
        }

    }

	public function get_cards($filter = array())
	{
		$id_filter = '';
        $user_id_filter = '';
        $transaction_id_filter = '';
        $organization_id_filter = '';
        $deleted_filter = '';
        $deleted_client_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int)$filter['user_id']);
        
        if (!empty($filter['transaction_id']))
            $transaction_id_filter = $this->db->placehold("AND transaction_id = ?", (int)$filter['transaction_id']);
        
        if (!empty($filter['organization_id']))
            $organization_id_filter = $this->db->placehold("AND organization_id = ?", (int)$filter['organization_id']);
        
        if (isset($filter['deleted']))
            $deleted_filter = $this->db->placehold("AND deleted = ?", (int)$filter['deleted']);

        if (isset($filter['deleted_by_client']))
            $deleted_client_filter = $this->db->placehold("AND deleted_by_client = ?", (int)$filter['deleted_by_client']);
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_cards
            WHERE 1
                $id_filter
                $user_id_filter
                $transaction_id_filter
                $organization_id_filter
                $deleted_filter
                $deleted_client_filter
                $keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_cards($filter = array())
	{
        $id_filter = '';
        $transaction_id_filter = '';
        $organization_id_filter = '';
        $keyword_filter = '';
        $deleted_filter = '';
        $user_id_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (isset($filter['deleted']))
            $deleted_filter = $this->db->placehold("AND `deleted` = ?", (int)$filter['deleted']);

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND `user_id` = ?", (int)$filter['user_id']);

        if (!empty($filter['transaction_id']))
            $transaction_id_filter = $this->db->placehold("AND transaction_id = ?", (int)$filter['transaction_id']);

        if (!empty($filter['organization_id']))
            $organization_id_filter = $this->db->placehold("AND organization_id = ?", (int)$filter['organization_id']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM b2p_cards
            WHERE 1
                $id_filter
                $transaction_id_filter
                $organization_id_filter
                $keyword_filter
                $deleted_filter
                $user_id_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_card($card)
    {
		$query = $this->db->placehold("
            INSERT INTO b2p_cards SET ?%
        ", (array)$card);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_card($id, $card)
    {
		$query = $this->db->placehold("
            UPDATE b2p_cards SET ?% WHERE id = ?
        ", (array)$card, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_card($id)
    {
		$query = $this->db->placehold("
            DELETE FROM b2p_cards WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }




	public function get_transaction($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM b2p_transactions
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }

    public function get_transaction_by_order_id($order_id)
    {
        $query = $this->db->placehold("
        SELECT * 
        FROM b2p_transactions
        WHERE order_id = ?
    ", (int)$order_id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_register_id_transaction($register_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_transactions
            WHERE register_id = ?
        ", (int)$register_id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_reference_transaction($reference)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_transactions
            WHERE reference = ?
        ", (string)$reference);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }
    
	public function get_transactions($filter = array())
	{
		$id_filter = '';
        $type_filter = '';
        $order_id_filter = '';
        $user_id_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type = ?", $filter['type']);
        
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id = ?", (int)$filter['order_id']);
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int)$filter['user_id']);
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_transactions
            WHERE 1
                $id_filter
                $type_filter
                $order_id_filter
                $user_id_filter
                $keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_transactions($filter = array())
	{
        $id_filter = '';
        $type_filter = '';
        $order_id_filter = '';
        $user_id_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type = ?", $filter['type']);
            
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id = ?", (int)$filter['order_id']);
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int)$filter['user_id']);
		
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM b2p_transactions
            WHERE 1
                $id_filter
                $type_filter
                $order_id_filter
                $user_id_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_transaction($transaction)
    {
		$query = $this->db->placehold("
            INSERT INTO b2p_transactions SET ?%
        ", (array)$transaction);
        $this->db->query($query);
        $id = $this->db->insert_id();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';        
        return $id;
    }
    
    public function update_transaction($id, $transaction)
    {
		$query = $this->db->placehold("
            UPDATE b2p_transactions SET ?% WHERE id = ?
        ", (array)$transaction, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_transaction($id)
    {
		$query = $this->db->placehold("
            DELETE FROM b2p_transactions WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }


	public function get_order_insure($order_id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM b2p_insures
            WHERE order_id = ?
        ", (int)$order_id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_insure($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM b2p_insures
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_insures($filter = array())
	{
		$id_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_insures
            WHERE 1
                $id_filter
				$keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_insures($filter = array())
	{
        $id_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM b2p_insures
            WHERE 1
                $id_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_insure($insure)
    {
		$query = $this->db->placehold("
            INSERT INTO b2p_insures SET ?%
        ", (array)$insure);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_insure($id, $insure)
    {
		$query = $this->db->placehold("
            UPDATE b2p_insures SET ?% WHERE id = ?
        ", (array)$insure, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_insure($id)
    {
		$query = $this->db->placehold("
            DELETE FROM b2p_insures WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    public function get_sbp_account($filter)
    {
        $qrcId_filter = '';
        $token_filter = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['qrcId']))
            $qrcId_filter = $this->db->placehold("AND qrcId = '".$filter['qrcId']."'");

        if (!empty($filter['token']))
            $token_filter = $this->db->placehold("AND token = '".$filter['token']."'");

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_sbp_accounts
            WHERE 1
                $qrcId_filter
				$token_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function getSbpAccount(array $filters = [], array $selectFields = ['*'], array $orderBy = ['id ASC'])
    {
        $selectFields = implode(',', $selectFields);
        $filtersString = $this->getWhereParams($filters);
        $orderString = implode(', ', $orderBy);

        $query = $this->db->placehold("
            SELECT $selectFields
            FROM b2p_sbp_accounts
            WHERE 1 $filtersString
            ORDER BY $orderString
        ");

        $this->db->query($query);

        return $this->db->result();
    }

    public function getSbpAccounts(array $filters = [], array $selectFields = ['*'], array $orderBy = ['id ASC'])
    {
        $selectFields = implode(',', $selectFields);
        $filtersString = $this->getWhereParams($filters);
        $orderString = implode(', ', $orderBy);

        $this->db->query("
            SELECT $selectFields
            FROM b2p_sbp_accounts
            WHERE 1 $filtersString
            ORDER BY $orderString
        ");

        return $this->db->results();
    }


    public function add_sbp_account($sbp_account)
    {
        $query = $this->db->placehold("
            INSERT INTO b2p_sbp_accounts SET ?%
        ", (array)$sbp_account);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_sbp_account($id, $sbp_account)
    {
        $query = $this->db->placehold("
            UPDATE b2p_sbp_accounts SET ?% WHERE id = ?
        ", (array)$sbp_account, (int)$id);
        $this->db->query($query);
        return $id;
    }

	public function get_register_id_payment($register_id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM b2p_payments
            WHERE register_id = ?
        ", (int)$register_id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }

    public function get_payment_by_params(array $params)
    {
        $filter = [];
        if (!empty($params)) {
            foreach ($params as $paramKey => $paramValue) {
                if (array_key_exists($paramKey, static::B2P_PAYMENTS_COLUMNS_LIST)) {
                    $castedValue = $this->castValue($paramValue, static::B2P_PAYMENTS_COLUMNS_LIST[$paramKey]);
                    $filter[] = $this->db->placehold('`' . $paramKey . '` = ?', $castedValue);
                }
            }
        }

        $where = '';
        if (!empty($filter)) {
            $where = 'WHERE ' . implode(' AND ', $filter);
        }

        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_payments
            $where
        ");
        
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Приведение значения к нужному типу
     */
    private function castValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return (bool) $value;
            case 'string':
                return (string) $value;
            default:
                return $value;
        }
    }

    public function get_payment($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_payments
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_payment_by_reference($reference)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_payments
            WHERE reference = ?
        ", (string)$reference);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_payments($filter = array(), bool $getAll = true)
	{
		$id_filter = '';
		$not_in_id_filter = '';
        $keyword_filter = '';
        $is_sbp_filter = '';
        $order_id_filter = '';
        $is_prolongation_filter = '';
        $reason_code_filter = '';
        $from_filter = '';
        $to_filter = '';
        $user_id_filter = '';
        $limit = 1000;
		$page = 1;

        if (!empty($filter['not_in_id'])) {
            $not_in_id_filter =
                $this->db->placehold('AND id NOT IN (?@)', array_map('intval', (array)$filter['not_in_id']));
        }

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}

        if (isset($filter['user_id'])) {
            $user_id_filter = $this->db->placehold("AND user_id = {$filter['user_id']}");
        }
        if (isset($filter['order_id'])) {
            $order_id_filter = $this->db->placehold("AND order_id = ?", (int)$filter['order_id']);
        }

        if (isset($filter['reason_code'])) {
            $reason_code_filter = $this->db->placehold("AND reason_code = ?", (int)$filter['reason_code']);
        }

        if (isset($filter['is_sbp']) && $filter['is_sbp']) {
            $is_sbp_filter = $this->db->placehold("AND is_sbp = 1");
        }

        if (isset($filter['prolongation']) && $filter['prolongation']) {
            $is_prolongation_filter = $this->db->placehold("AND prolongation = 1");
        }

        if (isset($filter['from'])) {
            $from_filter = $this->db->placehold("AND created >= '{$filter['from']}'");
        }

        if (isset($filter['to'])) {
            $to_filter = $this->db->placehold("AND created <= '{$filter['to']}'");
        }
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_payments
            WHERE 1
                $id_filter
				$keyword_filter
                $is_sbp_filter
                $from_filter
                $to_filter
                $user_id_filter
                $order_id_filter
                $is_prolongation_filter
                $reason_code_filter
                $not_in_id_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);

        if ($getAll) {
            return $this->db->results();
        }

        return $this->db->result();
	}
    
	public function count_payments($filter = array())
	{
        $id_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM b2p_payments
            WHERE 1
                $id_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_payment($payment)
    {
		$query = $this->db->placehold("
            INSERT INTO b2p_payments SET ?%
        ", (array)$payment);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_payment($id, $payment)
    {
		$query = $this->db->placehold("
            UPDATE b2p_payments SET ?% WHERE id = ?
        ", (array)$payment, (int)$id);
        $this->db->query($query);
        
        return $id;
    }

    public function update_payment_where(array $data, array $where)
    {
        if (!$data || !$where) return false;

        $conditions = [];
        foreach ($where as $condition => $value) {
            $conditions[] = $this->db->placehold("`$condition` = ?", $value);
        }

        $where = implode(" AND ", $conditions);

        $query = $this->db->placehold("UPDATE b2p_payments SET ?% WHERE ".$where, $data);
        $this->db->query($query);

        return $this->db->affected_rows();
    }

    public function delete_payment($id)
    {
		$query = $this->db->placehold("
            DELETE FROM b2p_payments WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    //Возврат страховки по договору (скопировано с нал+)
    public function return_insure($insure)
    {
        $transaction = $this->get_transaction($insure->transaction_id);
        $order = $this->orders->get_order($insure->order_id);
        
        $sector = $transaction->sector;
        $password = $this->passwords[$sector];
        
        $data = array(
            'sector' => $sector,
            'id' => $transaction->register_id,
            'amount' => $insure->amount * 100,
            'currency' => $this->currency_code,
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'],
            $data['id'],
            $data['amount'],
            $data['currency'],
            $password
        ));
        
    	$b2p_order = $this->send('Reverse', $data);

        $xml = simplexml_load_string($b2p_order);
        $b2p_status = (string)$xml->state;

        $transaction_id = $this->add_transaction(array(
            'user_id' => $transaction->user_id,
            'amount' => $insure->amount,
            'sector' => $sector,
            'register_id' => $transaction->register_id,
            'operation' => (string)$xml->id,
            'reason_code' => (string)$xml->reason_code,
            'reference' => $transaction->id,
            'description' => 'Возврат страховки по договору',
            'created' => date('Y-m-d H:i:s'),
            'body' => serialize($data),
            'callback_response' => $b2p_order,
        ));
        
        if (!empty($b2p_status) && $b2p_status == 'APPROVED')
        {
            $this->update_insure($insure->id, [
                'return_status' => 2,
                'return_date' => date('Y-m-d H:i:s'),
            ]);
        }
        
        return $b2p_status;
    }
    
    /**
     * Возврат средств за дополнительные услуги
     *  Поддерживается:
     *      - телемед
     *      - мультиполис
     *      - кредитный доктор
     *
     * Есть возможность возврата на определенную карту при указании её ID
     *
     * @param extraService $service
     * @param float        $amount
     * @param Card|null    $card
     *
     * @return GatewayResponse
     *
     * @throws Exception
     */
    public function refundExtraService( extraService $service, float $amount, Card $card = null )
    {
        $description = "Возврат денежных средств за услугу $service->title по Договору займа " . $service->loan->number;
        
        /** Register order */
        if( $card ){
            $sector      = $this->getReturnSector( $service->transaction->sector );
            
            $data                 = $this->compileRequestData(
                'Register',
                [
                    'amount'      => $amount * 100,
                    'currency'    => $this->currency_code,
                    'description' => $description,
                    'sector'      => $sector,
                ]
            );
            $gateway_response_raw = $this->send( 'Register', $data );
            $gateway_response     = new GatewayResponse( $gateway_response_raw );
            $transaction_id       = $this->add_transaction( [
                'user_id'           => $service->user_id,
                'order_id'          => $service->order_id,
                'amount'            => $amount * 100,
                'reference'         => $service->transaction->id,
                'sector'            => $data['sector'],
                'register_id'       => $gateway_response->id,
                'contract_number'   => $service->loan->number,
                'reason_code'       => $gateway_response->reason_code,
                'description'       => $description,
                'created'           => date( 'Y-m-d H:i:s' ),
                'body'              => serialize( $data ),
                'callback_response' => $gateway_response_raw,
            ] );
            
            $gateway_response->isError( 'webapi/Register', 'REGISTERED' );
            
            $data                 = $this->compileRequestData(
                'P2PCredit',
                [
                    'amount'   => $amount * 100,
                    'currency' => $this->currency_code,
                    'token'    => $card->token,
                    'id'       => $gateway_response->id,
                    'sector'   => $sector,
                ]
            );
            $gateway_response_raw = $this->send( 'P2PCredit', $data, 'gateweb' );
            $gateway_response     = new GatewayResponse( $gateway_response_raw );
            $operation_date = date_create_from_format('Y.m.d H:i:s', $gateway_response->date);
            
            $this->update_transaction( $transaction_id, [
                'callback_response' => $gateway_response_raw,
                'reason_code'       => $gateway_response->reason_code,
                'operation'         => $gateway_response->id,
                'card_pan'          => empty($gateway_response->pan) ? $gateway_response->pan2 : $gateway_response->pan,
                'operation_date'    => is_object($operation_date) ? $operation_date->format('Y-m-d H:i:s') : NULL,
            ] );
            $gateway_response->isError( 'gateweb/P2PCredit', 'APPROVED' );
            /** Reverse by B2P */
        }else{
            $data                 = $this->compileRequestData(
                'Reverse',
                [
                    'id'          => $service->transaction->register_id,
                    'sector'      => $service->transaction->sector,
                    'amount'      => $amount * 100,
                    'currency'    => $this->currency_code,
                    'description' => $description,
                ]
            );
            $gateway_response_raw = $this->send( 'Reverse', $data );
            $gateway_response     = new GatewayResponse( $gateway_response_raw );
            $transaction_id       = $this->add_transaction( [
                'user_id'           => $service->transaction->user_id,
                'order_id'          => $service->order_id,
                'amount'            => $amount * 100,
                'sector'            => $service->transaction->sector,
                'register_id'       => $service->transaction->id,
                'contract_number'   => $service->loan->number,
                'reference'         => $service->transaction->id,
                'operation'         => $gateway_response->id,
                'reason_code'       => $gateway_response->reason_code,
                'description'       => $description,
                'created'           => date( 'Y-m-d H:i:s' ),
                'body'              => serialize( $data ),
                'callback_response' => $gateway_response_raw,
            ] );
            $gateway_response->isError( 'webapi/Reverse', 'APPROVED' );
        }
        
        $service->return_status         = 2;
        $service->return_date           = date( 'Y-m-d H:i:s' );
        $service->return_amount         = $service->discount_refunded ? $amount * 2 : $amount;
        $service->return_transaction_id = $transaction_id;
        $service->return_sent           = 0;
        
        $service->save();
        
        return $gateway_response;
    }

    /**
     * Compiles request data for Best2Pay payment gateway
     *
     * @param string $method
     * @param array  $data
     *
     * @return array
     * @throws Exception
     */
    private function compileRequestData( string $method, array $data ): array
    {
        // Cast amount to INT
        if( isset( $data['amount'] ) ){
            $data['amount'] = (int) $data['amount'];
        }
        
        switch( $method ){
            
            case 'Reverse':
                $data['signature'] = $this->get_signature( [
                    $data['sector'],
                    $data['id'],
                    $data['amount'],
                    $data['currency'],
                    $this->passwords[ $data['sector'] ],
                ] );
                return $data;
                
            case 'Register':
                $data['signature'] = $this->get_signature( [
                    $data['sector'],
                    $data['amount'],
                    $data['currency'],
                    $this->passwords[ $data['sector'] ],
                ] );
                return $data;
        
            case 'P2PCredit':
                $data['signature'] = $this->get_signature( [
                    $data['sector'],
                    $data['id'],
                    $data['amount'],
                    $data['currency'],
                    $data['token'],
                    $this->passwords[ $data['sector'] ],
                ] );
        return $data;
            
            default:
                throw new \Exception( "Неизвестный метод: $method" );
        }
    }

    /**
     * Преобразует сектор покупки в сектор возврата
     * @param $purchase_sector
     *
     * @return string
     */
    public function getReturnSector( $purchase_sector )
    {
        $boostra_sectors = $this->get_boostra_sectors();
        if ($purchase_sector == $this->sectors['SPLIT_FINTEH']) {
            return $this->sectors['RETURN_SPLIT_FINTEH'];
        } elseif (in_array($purchase_sector, $boostra_sectors)) {
            return $this->sectors['RETURN_BOOSTRA'];
        } elseif ($purchase_sector == $this->sectors['FINLAB_PAYMENT']) {
            return $this->sectors['RETURN_FINLAB'];
        } elseif ($purchase_sector == $this->sectors['VIPZAIM_PAYMENT']) {
            return $this->sectors['RETURN_VIPZAIM'];
        } else {
            return $this->sectors['RETURN_AKVARIUS'];
        }
    }
    
    public function update_card_file($user_id, $cardPan,$file_id){
        $query = $this->db->placehold("
            UPDATE b2p_cards SET file_id = ? WHERE user_id = ? AND pan = ?
        ", (int)$file_id, (int)$user_id,(string)$cardPan);
        $this->db->query($query);

    }


    public function add_sbp_log($sbp_account_log_data)
    {
        $query = $this->db->placehold("
            INSERT INTO `b2p_sbp_accounts_logs` SET ?%
        ", (array)$sbp_account_log_data);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function add_source_log($data)
    {
        $query = $this->db->placehold("
            INSERT INTO `payment_resource_log` SET ?%
        ", (array)$data);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    /**
     * @param $userId
     * @param $orderId
     * @return false|int
     */
    public function getSbpStatus($userId,$orderId)
    {
                $query = $this->db->placehold("
            SELECT *
            FROM b2p_payments
            WHERE is_sbp = ? 
              AND user_id = ? 
              AND order_id = ?
            ORDER BY id Desc
        ", 1,$userId, $orderId);
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * @param $order
     * @param $user
     * @param $action_type
     * @return float
     */
    public function calculateFee($order,$user,$action_type): float
    {
        if ($this->users->isSafetyFlow($user)) {
            return $this->fee;
        }
        
        $not_rate_organizations = [
            $this->organizations::RZS_ID,
            $this->organizations::LORD_ID,
        ];
        
        if ($order->loan_type === 'IL' || in_array($order->organization_id, $not_rate_organizations)) {
            return $this->fee;
        }

        $fee = $this->extra_fee_pdl;
        
        $this->logging(__METHOD__, 'accept_credit.php', [$fee,$action_type], null, 'b2p_payment.txt');

        if ($action_type === $this->star_oracle::ACTION_TYPE_PROLONGATION &&
            !$order->additional_service_tv_med &&
            !$order->additional_service_multipolis) {
            return $fee;
        }

        if ($action_type === $this->star_oracle::ACTION_TYPE_PARTIAL_PAYMENT &&
            !$order->additional_service_partial_repayment &&
            !$order->half_additional_service_partial_repayment &&
            !$order->additional_service_so_partial_repayment &&
            !$order->half_additional_service_so_partial_repayment) {
            return $fee;
        }

        if ($action_type === $this->star_oracle::ACTION_TYPE_FULL_PAYMENT &&
            !$order->additional_service_repayment &&
            !$order->half_additional_service_repayment &&
            !$order->additional_service_so_repayment &&
            !$order->half_additional_service_so_repayment) {
            return $fee;
        }

        return $this->fee;
    }

    /**
     * @param float $fee_rate
     * @param $organization_id
     * @param $order
     * @param int $sbp
     * @return mixed
     */
    public function getSector(float $fee_rate, $organization_id, $order, int $sbp)
    {
        $feeSuffixMap = [
            (string)$this->extra_fee_pdl => '_FEE_10',
            (string)$this->extra_fee_il => '_FEE_15',
        ];
        $sector_fee = $feeSuffixMap[(string)$fee_rate] ?? '';

        $this->logging(__METHOD__, 'accept_credit.php', [$fee_rate, $organization_id, $sbp, $sector_fee, $feeSuffixMap], null, 'b2p_payment.txt');

        $organizationData = $this->organizations->get_organization($organization_id);

        if ($sbp && isset($this->sectors[$organizationData->b2p_prefix.'_PAY_CREDIT_SBP'.$sector_fee])) {
            return $this->sectors[$organizationData->b2p_prefix.'_PAY_CREDIT_SBP'.$sector_fee];
        }

        if ($organization_id == $this->organizations::BOOSTRA_ID) {
            return $this->sectors['PAYMENT'];
        }

        if ($organization_id == $this->organizations::FINLAB_ID) {
            return $this->sectors['FINLAB_PAYMENT' . $sector_fee];
        }

        if ($organization_id == $this->organizations::FRIDA_ID) {
            return $this->sectors['FRIDA_PAYMENT' . $sector_fee];
        }

        if ($organization_id == $this->organizations::RZS_ID) {
            return $this->sectors['RZS_PAYMENT'];
        }

        if ($organization_id == $this->organizations::MOREDENEG_ID) {
            return $this->sectors['MOREDENEG_PAYMENT'];
        }

        if ($organization_id == $this->organizations::LORD_ID) {
            return $this->sectors['LORD_PAYMENT'];
        }

        if ($organization_id == $this->organizations::FASTFINANCE_ID) {
            return $this->sectors['FASTFINANCE_PAYMENT'];
        }

        if ($organization_id == $this->organizations::VIPZAIM_ID) {
            return $this->sectors['VIPZAIM_PAYMENT'];
        }

        if (!empty($order) && $order->organization_id == $this->organizations::BOOSTRA_ID) {
            return $this->sectors['AKVARIUS_CESSION'];
        }

        return $this->sectors['AKVARIUS_PAYMENT' . $sector_fee];
    }

    public function canAddSbpAccount(int $userId): bool
    {
        $isEnabled = $this->settings->sbp_issuance_enabled;
        if ($isEnabled) {
            return true;
        }

        $user = $this->users->get_user($userId);
        if (!empty($user)) {
            $utm_source = $user->utm_source ?: 'Boostra';
        } else {
            $utm_source = $_COOKIE["utm_source"] ?: 'Boostra';
        }
        return in_array($utm_source, self::ALLOWED_UTM_SOURCES_TO_ADD_SBP_ACCOUNT);
    }

    /** Возвращает строку с условиями WHERE */
    public function getWhereParams(array $filters): string
    {
        $filtersString = '';
        foreach ($filters as $filter) {
            $columnName = $filter[0];
            $operator = $filter[1];
            $value = $filter[2];

            if (strtoupper($operator) === 'IN') {
                $filtersString .=
                    $filtersString
                    . $this->db->placehold(" AND {$columnName} IN (?@)", $value);

                continue;
            }

            $filtersString .= $filtersString . $this->db->placehold(" AND {$columnName} = ?", $value);
        }

        return $filtersString;
    }

    /**
     * Получает баланс сектора
     * @return false|string
     */
    public function getBalance(string $sector_name = 'AKVARIUS_PAY_CREDIT')
    {
        $sector = $this->sectors[$sector_name];
        $password = $this->passwords[$sector];
        $nonce = time();

        $data = array(
            'sector' => $sector,
            'nonce' => $nonce,
            'signature' => $this->get_signature(compact('sector', 'nonce', 'password')),
        );

        return $this->send('P2PCreditBalance', $data);
    }
}
