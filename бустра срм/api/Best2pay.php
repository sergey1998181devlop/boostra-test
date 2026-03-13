<?php


use boostra\domains\Card;
use boostra\domains\extraServices\extraService;
use boostra\domains\Transaction\GatewayResponse;

class Best2pay extends Simpla
{

    /**
     * Тип оплаты для Кредитного рейтинга
     */
    public const PAYMENT_TYPE_CREDIT_RATING_ORIGIN = 'credit_rating';
    public const PAYMENT_TYPE_CREDIT_RATING_FOR_NK = 'credit_rating_for_nk';
    public const PAYMENT_TYPE_CREDIT_RATING_AFTER_REJECTION = 'credit_rating_after_rejection';
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

    /**
     * Код успешной оплаты Б2П
     */
    public const REASON_CODE_SUCCESS = 1;

    public const SUCCESS_RETURN_STATUS = 2;

    private $currency_code = 643;

    private $fee = 0.05;
    private $min_fee = 3000;

    /** пары сектор => пароль
    private $sectors = array(
    'PAY_CREDIT' => '2241', //сектор для отправки кредита на карту клиента
    'RECURRENT' => '2516', // сектор для совершения рекурентных платежей
    'ADD_CARD' => '2516', // сектор для привязки карты
    'PAYMENT' => '2242' // сектор для оплаты любой картой
    );
     */

    /* test
    private $url = 'https://test.best2pay.net/';
    private $sectors = array(
        'PAY_CREDIT' => '2977', //сектор для отправки кредита на карту клиента 
        'RECURRENT' => '2978', // сектор для совершения рекурентных платежей
        'ADD_CARD' => '2976', // сектор для привязки карты
        'PAYMENT' => '2975' // сектор для оплаты любой картой
    );
    
    private $passwords = array(
        '2977' => 'test', //сектор для отправки кредита на карту клиента 
        '2978' => 'test', // сектор для совершения рекурентных платежей
        '2976' => 'test', // сектор для привязки карты
        '2975' => 'test'// сектор для оплаты любой картой        
    );
    */
    // work
    private $url = 'https://pay.best2pay.net/';
    public $sectors = [

        // Сектора оплаты
        'PAY_CREDIT'          => '8098', //сектор для отправки кредита на карту клиента (p2pcredit)
        'RECURRENT'           => '8099', // сектор для совершения рекурентных платежей ип сектора
        'ADD_CARD'            => '8086', // сектор для привязки карты (token)
        'PAYMENT'             => '8087', // сектор для оплаты любой картой (c2a)
        'RECURRENT_ALFAVIT'   => '10294', // сектор для совершения рекуррентных платежей Алфавит сектора
        'RECURRENT_ADVANCED'  => '10920', // дополнительный сектор Бустра для списания
        'SPLIT_FINTEH'        => '11807', // сектор для оплат через сплит финтех

        // Сектора возврата
        'RETURN_BOOSTRA'      => '11567', // По умолчанию. Сектор Бустра для возвратов переводами (СНГБ) (P2PCredit) (Reverse)
        'RETURN_SPLIT_FINTEH' => '12611', // сектор для возвратов через сплит финтех
        'RETURN_AKVARIUS' => '12930', // Аквариус сектор для возвратов через p2p

        'AKVARIUS_PAY_CREDIT' => '11825', // Аквариус выплата займа на карту
        'AKVARIUS_ADD_CARD' => '11823', // Аквариус привязка карты
        'AKVARIUS_PAYMENT' => '11827', // Аквариус оплата займа с ФЛ
        'AKVARIUS_RECURRENT' => '11822', // Аквариус безакцепт+доп.услуги
        'AKVARIUS_CESSION' => '12921', // Аквариус оплата договоров выданных в бустре
        'AKVARIUS_RECURRENT_ADVANCED' => '13045', // Аквариус списание реккурентов
        'AKVARIUS_PAY_CREDIT_IL' => '13303', // Аквариус выплата займа на карту Инстолмент
    ];

    public $passwords = array(
        '8098' => '1x19655', //сектор для отправки кредита на карту клиента 
        '8099' => '7o918OB6', // сектор для совершения рекурентных платежей
        '8086' => '0775fy5', // сектор для привязки карты
        '8087' => '812Q4I07',// сектор для оплаты любой картой        
        '8097' => 'T09n23Y6', // сектор ИП Стецюра
        '10294' => '871wgSfM366', // Сектор ИП Алфавит
        '10920' => '03004D9', // дополнительный сектор Бустра для списания
        '11567' => '67B4Hyuu508', // Сектор Бустра для возвратов переводами (СНГБ) (P2PCredit) (Reverse)
        '11807' => '308EYIBD238', //
        '11808' => 'FS84QIQ0', //
        '12611' => 'Cp1a7aY9', // сектор для возвратов через сплит финтех

        '11822' => 'xMl3S26Y228', // Аквариус безакцепт+доп.услуги
        '11823' => 'qc778T61', // Аквариус привязка карты
        '11825' => 'et1x24MD213', // Аквариус выплата займа на карту
        '11826' => 'Xbttw245309', // Аквариус оплата займа с ФЛ (+ сплитование)
        '11827' => 'p05C957', // Аквариус оплата займа с ФЛ
        '12930' => 'u43Ha6V4', // Аквариус сектор для возвратов через p2p
        '12921' => '8a6dFEj2', // Аквариус оплата договоров выданных в бустре
        '13045' => 'LH01tJ5', // Аквариус списание реккурентов
        '13303' => 'RO6kG23', // Аквариус выплата займа на карту Инстолмент
    );

    /**
     * Статус успешной транзакции
     */
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';

    /**
     * Статусы заказов b2p
     */
    public const STATUS_ORDER_COMPLETED = 'COMPLETED';
    public const STATUS_ORDER_REGISTERED = 'REGISTERED';

    /** @var string <pam> тестового пользователя из b2p для тестового сектора */
    private const B2P_TEST_USER_PAM = 'Мария Александровна A.';

    private const LOG_FILE = 'b2p.txt';

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

    public function get_sector(stdClass $order, ?stdClass $sbpAccount = null)
    {
	    if ($order->card_type === $this->orders::CARD_TYPE_VIRT) {
		    return $this->getVirtCardSector($order);
	    }

        if (!empty($sbpAccount)) {

            if ($this->organizations->isFinlab((int)$order->organization_id)) {
                return $this->sectors['FINLAB_SBP_ISSUANCE_LOAN'];
            }

            if ($this->orders->isInstallmentLoan((string)$order->loan_type) ) {
                return $this->sectors['AKVARIUS_SBP_ISSUANCE_LOAN_IL'];
            }

            return $this->sectors['AKVARIUS_SBP_ISSUANCE_LOAN'];
        }

        if ($this->orders->isInstallmentLoan((string)$order->loan_type)) {
            return $this->sectors['AKVARIUS_PAY_CREDIT_IL'];
        }

        return $this->sectors['AKVARIUS_PAY_CREDIT'];
    }

	private function getVirtCardSector(stdClass $order)
	{
		$organizationId = (int) $order->organization_id;
		$loanType = (string) $order->loan_type;

		if ($organizationId === $this->organizations::RZS_ID) {
			if ($loanType === $this->orders::LOAN_TYPE_IL) {
				return $this->sectors['RZS_VIRT_ISSUANCE_LOAN_IL'];
			}

			if ($loanType === $this->orders::LOAN_TYPE_PDL) {
				return $this->sectors['RZS_VIRT_ISSUANCE_LOAN_PDL'];
			}
		}

		if ($organizationId === $this->organizations::LORD_ID) {
			if ($loanType === $this->orders::LOAN_TYPE_IL) {
				return $this->sectors['LORD_VIRT_ISSUANCE_LOAN_IL'];
			}

			if ($loanType === $this->orders::LOAN_TYPE_PDL) {
				return $this->sectors['LORD_VIRT_ISSUANCE_LOAN_PDL'];
			}
		}

		return null;
	}

    /**
     * Возвращает значение сектора для SMAV по organization_id.
     *
     * @param int $organizationId
     * @return string|null
     */
    private function get_smav_sector(int $organizationId): ?string
    {
        $organization = $this->organizations->get_organization($organizationId);

        if (!$organization || empty($organization->b2p_prefix)) {
            return null;
        }

        $key = strtoupper($organization->b2p_prefix) . '_SMAV';

        return $this->sectors[$key] ?? null;
    }
    public function get_boostra_sectors()
    {
        return [
            $this->sectors['PAYMENT'],
            $this->sectors['RECURRENT_ADVANCED'],
            $this->sectors['RECURRENT'],
            $this->sectors['PAY_CREDIT'],
            $this->sectors['AKVARIUS_PAY_CREDIT'],
            $this->sectors['ADD_CARD'],
            $this->sectors['RETURN_BOOSTRA'],
        ];
    }

    /**
     * Best2pay::return_credit_doctor()
     * DEPRECATED
     * @param mixed $credit_doctor
     * @param mixed $amount
     * @return
     */
    public function return_credit_doctor($credit_doctor, $amount)
    {
        if (!($transaction = $this->get_transaction($credit_doctor->transaction_id)))
            return 'Транзакция '.$credit_doctor->transaction_id.' не найдена';

        $order = $this->orders->get_order($credit_doctor->order_id);

        $sector = $transaction->sector;
        $password = $this->passwords[$sector];

        $data = array(
            'sector' => $sector,
            'id' => $transaction->register_id,
            'amount' => $amount * 100,
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

        $return_transaction_id = $this->add_transaction(array(
            'user_id' => $transaction->user_id,
            'amount' => $amount * 100,
            'sector' => $sector,
            'register_id' => $transaction->register_id,
            'operation' => (string)$xml->id,
            'reason_code' => (string)$xml->reason_code,
            'reference' => $transaction->id,
            'description' => 'Возврат Услуги `Кредитный доктор` по договору',
            'created' => date('Y-m-d H:i:s'),
            'body' => serialize($data),
            'callback_response' => $b2p_order,
        ));

        if (!empty($b2p_status) && $b2p_status == 'APPROVED')
        {
            $this->credit_doctor->updateUserCreditDoctorData($credit_doctor->id, [
                'return_status' => 2,
                'return_date' => date('Y-m-d H:i:s'),
                'return_amount' => $amount,
                'return_transaction_id' => $return_transaction_id,
                'return_sent'           => 0,
            ]);
        }

        return $xml;
    }

    /**
     * Best2pay::return_multipolis()
     * DEPRECATED
     * @param mixed $multipolis
     * @param mixed $amount
     * @return
     */
    public function return_multipolis($multipolis, $amount)
    {
        if (!($transaction = $this->get_payment($multipolis->payment_id)))
            return 'Оплата '.$multipolis->payment_id.' не найдена';

        $order = $this->orders->get_order($multipolis->order_id);

        $sector = $transaction->sector;
        $password = $this->passwords[$sector];

        $data = array(
            'sector' => $sector,
            'id' => $transaction->register_id,
            'amount' => $amount * 100,
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

        $return_transaction_id = $this->add_transaction(array(
            'user_id' => $transaction->user_id,
            'amount' => $amount * 100,
            'sector' => $sector,
            'register_id' => $transaction->register_id,
            'operation' => (string)$xml->id,
            'reason_code' => (string)$xml->reason_code,
            'reference' => $transaction->id,
            'description' => 'Возврат Услуги `Консьерж сервис` '.$multipolis->number,
            'created' => date('Y-m-d H:i:s'),
            'body' => serialize($data),
            'callback_response' => $b2p_order,
        ));

        if (!empty($b2p_status) && $b2p_status == 'APPROVED')
        {
            $this->multipolis->update_multipolis($multipolis->id, [
                'return_status' => 2,
                'return_date' => date('Y-m-d H:i:s'),
                'return_amount' => $amount,
                'return_transaction_id' => $return_transaction_id,
                'return_sent'           => 0,
            ]);
        }

        return $xml;
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
     * Best2pay::return_tv_medical()
     * DEPRECATED
     * @param mixed $tv_medical_payment
     * @param mixed $amount
     * @return
     */
    public function return_tv_medical($tv_medical_payment, $amount)
    {
        if (!($payment = $this->get_payment($tv_medical_payment->payment_id)))
        {
            return 'Оплата ' . $tv_medical_payment->payment_id . ' не найдена';
        }

        $sector = $payment->sector;
        $password = $this->passwords[$sector];

        $data = array(
            'sector' => $sector,
            'id' => $payment->register_id,
            'amount' => $amount * 100,
            'currency' => $this->currency_code,
        );
        $data['signature'] = $this->get_signature(
            [
                $data['sector'],
                $data['id'],
                $data['amount'],
                $data['currency'],
                $password
            ]
        );

        $b2p_order = $this->send('Reverse', $data);

        $xml = simplexml_load_string($b2p_order);
        $b2p_status = (string)$xml->state;

        $return_transaction_id = $this->add_transaction(
            [
                'user_id' => $payment->user_id,
                'amount' => $amount * 100,
                'sector' => $sector,
                'register_id' => $payment->register_id,
                'operation' => (string)$xml->id,
                'reason_code' => (string)$xml->reason_code,
                'reference' => $payment->id,
                'description' => 'Возврат Услуги `Телемедецина` ' . $tv_medical_payment->id,
                'created' => date('Y-m-d H:i:s'),
                'body' => serialize($data),
                'callback_response' => $b2p_order,
            ]
        );

        if (!empty($b2p_status) && $b2p_status == 'APPROVED')
        {
            $this->tv_medical->updatePayment((int)$tv_medical_payment->id, [
                'return_status' => 2,
                'return_date' => date('Y-m-d H:i:s'),
                'return_amount' => $amount,
                'return_transaction_id' => $return_transaction_id,
                'return_sent'           => 0,
            ]);
        }

        return $xml;
    }


    /**
     * Best2pay::pay_contract()
     * Переводит сумму займа на карту клиенту
     *
     * Алгоритм выдачи по СБП
     * 1. IDX проверка, что номер телефона принадлежит клиенту (скоринг акси)
     * 2. Привязка счета на сектор списаний через мобильное приложение (производилось при регистрации)
     * 3. Регистрация заказа с получением b2p_order_id (Register)
     * 4. Пречек с отправой номера телефона и id банка и получением precheck_id и <pam> (SBPCreditPrecheck)
     * 5. Проверка, что <pam> соответствует клиенту
     * 6. Выплата с отправкой precheck_id (SBPCredit)
     *
     * @param int $order_id
     * @return string - статус перевода COMPLETE при успехе или пустую строку
     */
    public function pay_contract($order_id)
    {
        if (!($order = $this->orders->get_order($order_id)))
            return false;

        if ($order->status != 8)
            return false;

        if (empty($order->accept_sms))
            return false;

        $this->orders->update_order($order->order_id, array('status' => 9));

        $card = null;
        $sbpAccount = null;
        $isVirtualCardOrder = $this->settings->vc_enabled && $order->card_type === $this->orders::CARD_TYPE_VIRT;

        // Выплата по СБП
        if ($order->card_type === $this->orders::CARD_TYPE_SBP && !empty($order->card_id)) {
            $sbp_issuance_enabled = $this->settings->sbp_issuance_enabled;

            if (empty($sbp_issuance_enabled)) {
                $this->logging(__METHOD__, '', 'Выплаты по СБП отключены', ['order' => $order], self::LOG_FILE);
                return false;
            }

            $sbpAccount = $this->getSbpAccount([
                ['id', '=', (int)$order->card_id],
                ['user_id', '=', (int)$order->user_id],
                ['deleted', '=', 0],
            ]);

            if (empty($sbpAccount)) {
                $this->logging(__METHOD__, '', 'Не найден привязанный счет СБП для заявки', ['order' => $order], self::LOG_FILE);
                return false;
            }
        }

        // Если выбрано СБП И нет card_id И включен функционал выбора банка
        else if (
            $order->card_type === $this->orders::CARD_TYPE_SBP &&
            empty($order->card_id) &&
            $this->settings->show_sbp_banks_for_autoapprove_orders
        ) {
            $sbp_issuance_enabled = $this->settings->sbp_issuance_enabled;

            if (empty($sbp_issuance_enabled)) {
                $this->logging(__METHOD__, '', 'Выплаты по СБП отключены', ['order' => $order], self::LOG_FILE);
                return false;
            }

            $bankId = (int)$this->order_data->read((int)$order->order_id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE);

            if (empty($bankId)) {
                $this->logging(__METHOD__, '', 'Не выбран банк для выплаты', ['order' => $order, 'bank_id' => $bankId], self::LOG_FILE);
                return false;
            }

            $sbpAccount = new stdClass();
            $sbpAccount->member_id = $bankId;
            $sbpAccount->user_id = (int)$order->user_id;
        } else if ($order->card_type === $this->orders::CARD_TYPE_CARD) {
            $card = $this->get_card((int)$order->card_id);

            if (empty($card) || !empty($card->deleted) || !empty($card->deleted_by_client) || $card->user_id != $order->user_id) {
                return false;
            }
        } elseif (!$isVirtualCardOrder) {
            return false;
        }

        $sector = $this->get_sector($order, $sbpAccount);
		if (!$sector) {
			return false;
		}

        $password = $this->passwords[$sector];

        $fio = $order->lastname.' '.$order->firstname.' '.$order->patronymic;

        $params = array('order_id' => $order_id);
        $contract = $this->contracts->get_contract_by_params($params);

        $description = 'Выдача займа по договору '.$contract->number.' '.$fio;

        $contract_amount = $contract->amount;

        $credit_doctor = $this->credit_doctor->getUserCreditDoctor($order->order_id, $order->user_id);

        $star_oracle = $this->star_oracle->getStarOracle($order->order_id, $order->user_id);
        $tv_medical = $this->tv_medical->getTVMedical(
            $order->order_id,
            $order->user_id,
            null,
            null,
            null,
            $this->star_oracle::ACTION_TYPE_ISSUANCE
        );

        $safe_deal = $this->safe_deal->get($order->order_id, $order->user_id);

        if (!empty($credit_doctor)) {
            $credit_doctor_price = $credit_doctor->amount;
            $contract_amount -= $credit_doctor_price;
        }
        if (!empty($tv_medical)) {
            $contract_amount -= $tv_medical->amount;
        } 
        if (!empty($star_oracle)) {
            $contract_amount -= $star_oracle->amount;
        }
        if (!empty($safe_deal)) {
            $contract_amount -= $safe_deal->amount;
        }

        $data = array(
            'sector' => $sector,
            'amount' => $contract_amount * 100,
            'currency' => $this->currency_code,
            'description' => $description,
        );

        $data['signature'] = $this->get_signature(array(
            $data['sector'],
            $data['amount'],
            $data['currency'],
            $password
        ));

        $b2p_order = $this->send('Register', $data);

        // DEV LOGGING: Логирование ответа от Best2Pay Register
        if ($this->config->is_dev) {
            $log_file = $this->config->root_dir . '/logs/b2p.txt';
            $log_message = "\n" . str_repeat("=", 80) . "\n";
            $log_message .= "[" . date('Y-m-d H:i:s') . "] Best2Pay Register Request\n";
            $log_message .= "Order ID: " . $order->order_id . "\n";
            $log_message .= "Request data: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            $log_message .= "Response: " . $b2p_order . "\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);
        }

        $xml = simplexml_load_string($b2p_order);
        $b2p_order_id = (string)$xml->id;

        if (empty($b2p_order_id)) {
            // DEV LOGGING: Логирование причины ошибки
            if ($this->config->is_dev) {
                $log_file = $this->config->root_dir . '/logs/b2p.txt';
                $log_message = "!!! ORDER UNREGISTERED !!!\n";
                $log_message .= "XML object: " . print_r($xml, true) . "\n";
                if (isset($xml->description)) {
                    $log_message .= "Error description: " . (string)$xml->description . "\n";
                }
                if (isset($xml->code)) {
                    $log_message .= "Error code: " . (string)$xml->code . "\n";
                }
                $log_message .= str_repeat("=", 80) . "\n";
                file_put_contents($log_file, $log_message, FILE_APPEND);
            }
            return 'ORDER UNREGISTERED';
        }

        if ($isVirtualCardOrder) {
            $data = array(
	            'sector' => $sector,
                'b2p_order_id' => $b2p_order_id,
                'amount' => $contract_amount,
                'currency' => $this->currency_code,
                'description' => $description,
                'card_id' => $order->card_id
            );
        } elseif (!empty($sbpAccount)) {
            $user = $this->users->get_user((int)$order->user_id);
            $precheckId = $this->getSbpIssuancePrecheckId($sector, $password, $user, $order, $sbpAccount, $b2p_order_id);
            if (empty($precheckId)) {
                return false;
            }

            $data = $this->getDataForIssuanceBySbp($sector, $password, $b2p_order_id, $precheckId);
        } else {
            $data = $this->getDataForIssuanceByCard($sector, $password, $b2p_order_id, $order, (int)$contract_amount, $card);
        }

        $likezaim_enabled = $this->settings->likezaim_enabled
            && $order->organization_id == $this->organizations::AKVARIUS_ID
            && $order->loan_type != 'IL'
            && $order->utm_term != 'akvariusmkk.ru'
            && $order->utm_source != 'cross_order';

        $p2pcredit = array(
            'order_id' => $order->order_id,
            'user_id' => $order->user_id,
            'date' => date('Y-m-d H:i:s'),
            'body' => $data,
            'amount' => $contract_amount,
            'register_id' => $b2p_order_id,
            'likezaim_enabled' => intval($likezaim_enabled),
        );

        if ($p2pcredit_id = $this->add_p2pcredit($p2pcredit)) {
            if ($isVirtualCardOrder) {
                $response = $this->virtualCard->forUser($order->user_id, $order->order_id)->deposit($data);
            } elseif (!empty($sbpAccount)) {
                $response = $this->send('sbp/SBPCredit', $data, 'webapi');
            } else {
                $response = $this->send('P2PCredit', $data, 'gateweb');
            }

            $xml = simplexml_load_string($response);
            $status = (string)$xml->state;

            $this->update_p2pcredit($p2pcredit_id, array(
                'response' => $response,
                'status' => $status,
                'operation_id' => (string)$xml->id,
	            'register_id' => $this->getRegisterId($order, $xml, $b2p_order_id),
                'complete_date' => date('Y-m-d H:i:s'),
            ));

            return $xml;
        }
    }

	private function getRegisterId($order, $xml, $b2p_order_id)
	{
		$isVirtualCard = ($order->card_type === $this->orders::CARD_TYPE_VIRT);
		$xmlOrderId    = (string) $xml->order_id;

		if ($isVirtualCard && $xmlOrderId) {
			return $xmlOrderId;
		}

		return $b2p_order_id;
	}

	private function getDataForIssuanceBySbp(string $sector, string $password, string $b2p_order_id, string $precheckId): array
    {
        $data = array(
            'sector' => $sector,
            'id' => $b2p_order_id,
            'precheck_id' => $precheckId,
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'],
            $data['id'],
            $data['precheck_id'],
            $password
        ));

        return $data;
    }

    private function getDataForIssuanceByCard(string $sector, string $password, string $b2p_order_id, stdClass $order, int $contract_amount, $card): array
    {
        $data = array(
            'sector' => $sector,
            'amount' => $contract_amount * 100,
            'currency' => $this->currency_code,
            'reference' => $order->order_id,
            'token' => $card->token,
            'id' => $b2p_order_id,
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'],
            $data['id'],
            $data['amount'],
            $data['currency'],
            $data['token'],
            $password
        ));

        return $data;
    }

    private function getSbpIssuancePrecheckId(string $sector, string $password, stdClass $user, stdClass $order, stdClass $sbpAccount, string $b2p_order_id)
    {
        $data = [
            'sector' => $sector,
            'id' => $b2p_order_id,
            'recipientBankId' => $sbpAccount->member_id,
            'phone' => $user->phone_mobile,
        ];

        $data['signature'] = $this->get_signature(array(
            $data['sector'],
            $data['id'],
            $data['recipientBankId'],
            $data['phone'],
            $password
        ));

        $result = $this->send('sbp/SBPCreditPrecheck', $data);

        unset($data['sector']);
        unset($data['signature']);

        $b2pSbpIssuanceLog = [
            'user_id' => $user->id,
            'order_id' => $order->order_id,
            'member_id' => $sbpAccount->member_id,
            'phone' => $user->phone_mobile,
            'sbp_account_id' => $sbpAccount->id ?? null,
            'b2p_order_id' => $b2p_order_id,
            'request' => json_encode($data),
            'response' => (string)$result,
        ];

        if (empty($result)) {
            $b2pSbpIssuanceLog['status'] = 'error';
            $b2pSbpIssuanceLog['description'] = 'Нет ответа';
            $this->b2p_sbp_issuance_log->add($b2pSbpIssuanceLog);

            return false;
        }

        $xml = simplexml_load_string($result);

        if (!empty($xml->code)) {
            $b2pSbpIssuanceLog['status'] = 'error';
            $b2pSbpIssuanceLog['description'] = 'Некорректный ответ';
            $this->b2p_sbp_issuance_log->add($b2pSbpIssuanceLog);

            return false;
        }

        $precheckId = (string)$xml->precheck_id;
        $pam = (string)$xml->pam;

        $b2pSbpIssuanceLog['precheck_id'] = $precheckId;
        $b2pSbpIssuanceLog['pam'] = $pam;

        if (empty($precheckId) || empty($pam)) {
            $b2pSbpIssuanceLog['status'] = 'error';
            $b2pSbpIssuanceLog['description'] = 'Отсутствует precheck_id или pam';

            $this->b2p_sbp_issuance_log->add($b2pSbpIssuanceLog);

            return false;
        }

        if (!$this->isPamBelongsToUser($pam, $user) && !$this->user_data->isTestUser((int)$user->id)) {
            $b2pSbpIssuanceLog['status'] = 'error';
            $b2pSbpIssuanceLog['description'] = 'Pam не соответствует ФИО пользователя';

            $this->b2p_sbp_issuance_log->add($b2pSbpIssuanceLog);

            return false;
        }

        $b2pSbpIssuanceLog['status'] = 'success';
        $b2pSbpIssuanceLog['description'] = 'Успешный пречек';
        $this->b2p_sbp_issuance_log->add($b2pSbpIssuanceLog);

        return $precheckId;
    }

    private function replace_letters($string): string
    {
        $replace_letters = [
            'Ё' => 'Е',
            'ё' => 'е',
            'Й' => 'И',
            'й' => 'и',
        ];
        return str_replace(array_keys($replace_letters), array_values($replace_letters), $string);
    }

    private function isPamBelongsToUser(string $pam, stdClass $user): bool
    {
        if (empty($pam)) {
            $this->logging(__METHOD__, '', 'Пустой pam', ['user' => $user], self::LOG_FILE);
            return false;
        }

        if ($pam === self::B2P_TEST_USER_PAM) {
            return true;
        }

        $isPamLatin = preg_match('/[a-zA-Z]/', $pam);

        // Если <pam> на латинице, то пока не выдаем
        if ($isPamLatin) {
            return false;
        }

        $pam = mb_strtolower(trim($this->replace_letters($pam)));

        $firstname = mb_strtolower(trim($this->replace_letters($user->firstname)));
        $patronymic = mb_strtolower(trim($this->replace_letters($user->patronymic)));
        $lastname = mb_strtolower(trim($this->replace_letters($user->lastname)));
        $firstLetterOfLastName = mb_substr($lastname, 0, 1);

        $pamInDb = $firstname . ' ';

        if (!empty($patronymic) && $patronymic != 'нет' && $patronymic != '-') {
            $pamInDb .= $patronymic . ' ';
        }

        $pamInDb .= $firstLetterOfLastName;

        if ($pamInDb !== $pam) {
            $this->logging(__METHOD__, '', 'Pam не совпадает', ['user' => $user, 'pam_in_db' => $pamInDb, 'pam' => $pam], self::LOG_FILE);
            return false;
        }

        return true;
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
        $password = $this->passwords[$sector];

//        $fee = max($this->min_fee, floatval($amount * $this->fee));

        if (!($card = $this->get_card($card_id)))
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
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('$recurring', $recurring);echo '</pre><hr />';
        $transaction_id = $this->add_transaction(array(
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

    /**
     * Идентификация физлица
     *
     * `webapi/b2puser/IdentificationStatus`
     * @param $firstname
     * @param $lastname
     * @param $patronymic
     * @param $birth
     * @param $passport
     * @return false|stdClass
     */
    public function identification_status($firstname, $lastname, $patronymic, $birth, $passport, int $organization_id)
    {
        $sector = $this->get_smav_sector($organization_id);
        if (!$sector) {
            $this->logging(__METHOD__, '', 'Не найден смэв сектор при проверке УПРИД',
                [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'patronymic' => $patronymic,
                    'birth' => $birth,
                    'passport' => $passport,
                    'organization_id' => $organization_id
                ], self::LOG_FILE);
            return false; // для прочих организаций — не поддерживаем
        }
        $password = $this->passwords[$sector];

        $birthDateTime = DateTime::createFromFormat('d.m.Y', $birth);
        if ($birthDateTime)
            $birth = $birthDateTime->format('Y.m.d');

        $passport = str_replace(' ', '', $passport);

        $data = [
            'sector' => $sector,
            'first_name' => $firstname,
            'patronymic' => $patronymic,
            'last_name'=> $lastname,
            'birth_date' => $birth,
            'persondoc_number' => $passport
        ];
        $data['signature'] = $this->get_signature([
            $data['sector'],
            $data['first_name'],
            $data['patronymic'],
            $data['last_name'],
            $data['birth_date'],
            $data['persondoc_number'],
            $password
        ]);

        $rawResponse = $this->send('b2puser/IdentificationStatus', $data);

        $parsed = $this->parseIdentificationResponse($rawResponse);
        if ($parsed === null) {
            $this->logging(
                __METHOD__,
                '',
                'Не удалось распарсить ответ Best2Pay (ни JSON, ни XML)',
                ['response' => $rawResponse],
                self::LOG_FILE
            );
        }

        return $parsed;
    }

    /**
     * Приводит ответ Best2Pay UPRID (JSON или XML) к виду:
     * (object)['identification_data' => (object)[...]]
     */
    private function parseIdentificationResponse(?string $raw): ?stdClass
    {
        if (empty($raw)) {
            return null;
        }

        // 1) JSON
        $json = json_decode($raw);
        if (json_last_error() === JSON_ERROR_NONE && $json !== null) {
            if (isset($json->identification_data)) {
                return $json;
            }

            if (isset($json->request_id, $json->identification_state)) {
                $wrapper = new stdClass();
                $wrapper->identification_data = $json;
                return $wrapper;
            }

            return $json;
        }

        // 2) XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($raw);
        if ($xml === false) {
            libxml_clear_errors();
            return null;
        }
        libxml_clear_errors();

        $node = null;
        if ($xml->getName() === 'identification_data') {
            $node = $xml;
        } elseif (isset($xml->identification_data)) {
            $node = $xml->identification_data;
        }

        if (empty($node)) {
            return null;
        }

        $idData = new stdClass();
        foreach ($node->children() as $key => $value) {
            $idData->{$key} = (string) $value;
        }

        $result = new stdClass();
        $result->identification_data = $idData;

        return $result;
    }

    public function purchase_by_token($card_id, $amount, $description, $advanced_sector = false, array $additional_data = [])
    {

//        $fee = max($this->min_fee, floatval($amount * $this->fee));

        if (!($card = $this->get_card($card_id)))
            return false;

        if (!($user = $this->users->get_user((int)$card->user_id)))
            return false;

        if (!($order = $this->orders->get_order((int)$additional_data['order_id']))) {
            return false;
        }

        if ($order->organization_id == $this->organizations::BOOSTRA_ID) {
            if ($this->settings->b2p_dop_organization == 'AL')
                $sector = $this->sectors['RECURRENT_ALFAVIT'];
            else
                if (empty($advanced_sector))
                    $sector = $this->sectors['RECURRENT'];
                else
                    $sector = $this->sectors['RECURRENT_ADVANCED'];

        } else {
            $sector = $this->sectors['FINTEHMARKET_RECURRENT'];
        }
        $password = $this->passwords[$sector];

        // Регистрируем заказ
        $data = array(
            'sector' => $sector,
            'amount' => $amount,
            'currency' => $this->currency_code,
            'description' => $description,
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'],
            $data['amount'],
            $data['currency'],
            $password
        ));
        $b2p_order = $this->send('Register', $data);
        $xml = simplexml_load_string($b2p_order);
        $b2p_order_id = (string)$xml->id;
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('$b2p_order', $b2p_order);echo '</pre><hr />';

        if (empty($b2p_order_id))
            return false;

        // списываем
        $data = array(
            'sector' => $sector,
            'id' => $b2p_order_id,
            'token' => $card->token,
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'],
            $data['id'],
            $data['token'],
            $password
        ));

        $recurring = $this->send('PurchaseByToken', $data);

        $xml = simplexml_load_string($recurring);
        $operation_date = date_create_from_format('Y.m.d H:i:s', (string)$xml->date);

        $transaction_id = $this->add_transaction(array(
            'user_id' => $user->id,
            'order_id' => $additional_data['order_id'] ?? null,
            'contract_number' => $additional_data['contract_number'] ?? null,
            'amount' => $amount,
            'sector' => $sector,
            'register_id' => (string)$xml->order_id,
            'operation' => (string)$xml->id,
            'reason_code' => (string)$xml->reason_code,
            'reference' => $user->id,
            'description' => $description,
            'created' => date('Y-m-d H:i:s'),
            'callback_response' => $recurring,
            'card_pan' => $card->pan,
            'operation_date' => is_object($operation_date) ? $operation_date->format('Y-m-d H:i:s') : NULL,
        ));

        return $recurring;


    }

    /**
     * Метод осуществляет покупку КД/ЗО(звездный оракул)
     * @param $card_id
     * @param $amount
     * @param $description
     * @param array $additional_data
     * @param string $cardType
     * @return int|false
     */

    public function purchaseDOP($card_id, $amount, $description, array $additional_data = [], string $cardType = 'card'): int
    {
        $card = null;
        $sbpAccount = null;

        $logData = [
            'card_id' => $card_id,
            'amount' => $amount,
            'description' => $description,
            'additional_data' => $additional_data,
            'cardType' => $cardType,
        ];

        // Получаем заказ
        if (!($order = $this->orders->get_order((int)$additional_data['order_id']))) {
            $this->logging(__METHOD__, '', 'Доп не выдан. Нет займа', $logData, self::LOG_FILE);
            return false;
        }

        $user = $this->users->get_user((int)$order->user_id);

        if (!$user) {
            $this->logging(__METHOD__, '', 'Доп не выдан. Нет пользователя', $logData, self::LOG_FILE);
            return false;
        }

        // Получаем карту или СБП счет и пользователя
        if ($cardType === $this->orders::CARD_TYPE_CARD) {
            $card = $this->get_card($card_id);
            if (empty($card)) {
                $this->logging(__METHOD__, '', 'Доп не выдан. Нет карты', array_merge($logData, ['card' => $card]), self::LOG_FILE);
                return false;
            }
        } elseif ($cardType === $this->orders::CARD_TYPE_SBP) {
            $sbpAccount = $this->getSbpAccount([
                ['id', '=', (int)$card_id],
                ['deleted', '=', 0],
            ]);
        } else {
            $this->logging(__METHOD__, '', 'Доп не выдан. Некорректный тип карты', $logData, self::LOG_FILE);
            return false;
        }

        // Определяем сектор
        $sector = $this->determineSector($order, $cardType);

        $reference = '';
        if (!empty($card)) {
            $reference = $order->user_id;
        } else if (!empty($sbpAccount->qrcId)) {
            $reference = $sbpAccount->qrcId;
        }

        // Добавляем транзакцию
        $transaction_id = $this->best2pay->add_transaction(array(
            'user_id' => $user->id,
            'order_id' => $order->order_id ?? null,
            'contract_number' => $additional_data['contract_number'] ?? null,
            'amount' => $amount,
            'sector' => $sector,
            'register_id' => (string)$additional_data['register_id'] ?? null,
            'operation' => (string)$additional_data['operation'] ?? null,
            'reason_code' => (string)$additional_data['reason_code'] ?? null,
            'reference' => $reference,
            'description' => $description,
            'created' => date('Y-m-d H:i:s'),
            'card_pan' => !empty($card) ? $card->pan : '',
            'operation_date' => $additional_data['operation_date'] ?? null,
        ));

        return $transaction_id;
    }

    public function get_operation_info($sector, $register_id, $operation_id)
    {
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

    /**
     * Получает баланс СБП сектора
     * @return false|string
     */
    public function getBalanceSbp(string $sector_name = 'AKVARIUS_PAY_CREDIT')
    {
        $sector = $this->sectors[$sector_name];
        $password = $this->passwords[$sector];
        $nonce = time();

        $data = array(
            'sector' => $sector,
            'nonce' => $nonce,
            'signature' => $this->get_signature(compact('sector', 'nonce', 'password')),
        );

        return $this->send('/sbp/SBPCreditBalance', $data);
    }

    /**
     * @param mixed $params
     * user_id
     * description
     * order_id
     * number
     * card_id
     * card_token
     * amount
     * recurrent_id
     * @return
     */
    public function recurrent_purchase_by_token($params)
    {
        if ($params['organization_id'] == $this->organizations::BOOSTRA_ID) {
            $sector = $this->sectors['RECURRENT_ADVANCED'];
        } else {
            $sector = $this->sectors['AKVARIUS_RECURRENT_ADVANCED'];
        }
        $password = $this->passwords[$sector];

        if ($params['amount'] == 0)
            return false;

        // Регистрируем заказ
        $data = array(
            'sector' => $sector,
            'amount' => $params['amount'] * 100,
            'currency' => 643,
            'description' => $params['description'],
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'],
            $data['amount'],
            $data['currency'],
            $password
        ));
        $b2p_order = $this->send('Register', $data);
        $xml = simplexml_load_string($b2p_order);
        $b2p_order_id = (string)$xml->id;

        if (empty($b2p_order_id))
            return false;

        $payment_id = $this->add_payment([
            'user_id' => $params['user_id'],
            'order_id' => $params['order_id'],
            'contract_number' => $params['number'],
            'card_id' => $params['card_id'],
            'amount' => $params['amount'],
            'insure' => 0,
            'fee' => 0,
            'body_summ' => 0,
            'percents_summ' => 0,
            'prolongation' => $params['prolongation'],
            'created' => date('Y-m-d H:i:s'),
            'payment_type' => 'debt',
            'sector' => $sector,
            'register_id' => $b2p_order_id,
            'description' => $params['description'],
            'payment_link' => '',
            'body' => serialize($data),
            'callback_response' => serialize($b2p_order),
            'recurrent_id' => $params['recurrent_id'],
        ]);
        if (empty($payment_id)) {
            return false;
        }

        // списываем
        $data = array(
            'sector' => $sector,
            'id' => $b2p_order_id,
            'token' => $params['card_token'],
        );
        $data['signature'] = $this->get_signature(array(
            $data['sector'],
            $data['id'],
            $data['token'],
            $password
        ));

        $recurring = $this->send('PurchaseByToken', $data);

        $xml = simplexml_load_string($recurring);
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($xml);echo '</pre><hr />';
        if (!($card_pan = (string)$xml->pan))
            $card_pan = (string)$xml->pan2;
        $operation_date = date_create_from_format('Y.m.d H:i:s', (string)$xml->date);

        $this->update_payment($payment_id, [
            'operation_id' => (string)$xml->id,
            'reason_code' => (string)$xml->reason_code,
            'reference' => $payment_id,
            'callback_response' => $recurring,
            'card_pan' => $card_pan,
            'operation_date' => is_object($operation_date) ? $operation_date->format('Y-m-d H:i:s') : NULL,
        ]);

        $state = (string)$xml->state;
        if ($state == 'APPROVED') {
            $payment = $this->get_payment($payment_id);
            if ($params['recurrent_id'] && $params['prolongation']) {
                $recurrent = $this->recurrents->get_recurrent(['id'=> $params['recurrent_id']]);
                if ($recurrent->getted_amount + $params['amount'] < $recurrent->percents){
                    $payment->prolongation = false;
                }
            }
            $result = $this->soap->send_payments([$payment]);

            if (!empty($result->return) && $result->return == 'OK')
            {
                $this->update_payment($payment_id, array(
                    'sent' => 1,
                    'send_date' => date('Y-m-d H:i:s'),
                ));
            }
            sleep(1);
        }

        return $recurring;
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
        $this->logging($method, $this->url.$type.'/'.$method, (array)$data, $b2p, self::LOG_FILE);
        return $b2p;
    }

    /**
     * Вычисляет подпись в соответствии с документацией Best2Pay
     *
     * @param $data
     *
     * @return string
     */
    private function get_signature($data)
    {
        return base64_encode( md5( implode( '', $data ) ) );
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

    public function add_card_old($user_id)
    {
        $sector = 2243;
        $password = $this->settings->apikeys['best2pay'][2243];

        $amount = 100; // сумма для списания > 100
        $description = 'Привязка карты'; // описание операции
// 812763
        // регистрируем оплату
        $data = array(
            'sector' => $sector,
            'amount' => $amount,
            'currency' => $this->currency_code,
            'reference' => $user_id,
            'description' => $description,
            'url' => 'http://nalic-front.eva-p.ru/best2pay_callback/add_card',
//            'mode' => 1
        );
        $data['signature'] = $this->get_signature(array($data['sector'], $data['amount'], $data['currency'], $password));

        $b2p_order = $this->send('Register', $data);

        $xml = simplexml_load_string($b2p_order);
        $b2p_order_id = (string)$xml->id;

        $transaction_id = $this->best2pay->add_transaction(array(
            'user_id' => $user_id,
            'amount' => $amount,
            'sector' => $sector,
            'register_id' => $b2p_order_id,
            'reference' => $user_id,
            'description' => $description,
            'created' => date('Y-m-d H:i:s'),
        ));

        // получаем ссылку на привязку карты
        $data = array(
            'sector' => $sector,
            'id' => $b2p_order_id
        );
        $data['signature'] = $this->get_signature(array($sector, $b2p_order_id, $password));

        $link = $this->url.'CardEnroll?'.http_build_query($data);

        return $link;
    }


    public function get_contract_p2pcredit($contract_id)
    {
        $this->db->query(
            $this->db->placehold(
                "SELECT *
                    FROM b2p_p2pcredits
                    WHERE contract_id = ?
                    ORDER BY id DESC
                    LIMIT 1",
                (int)$contract_id
            )
        );

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

    public function getP2PCreditBy($field, $value)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_p2pcredits
            WHERE {$field} = ?
        ", $value);
        $this->db->query($query);
        if ($result = $this->db->result())
        {
            $result->body = unserialize($result->body);
            $result->response = unserialize($result->response);
        }

        return $result;
    }

    public function get_p2pcredits($filter = array())
    {
        $id_filter = '';
        $order_id_filter = '';
        $status_filter = '';
        $sent_filter = '';
        $date_from_filter = '';
        $date_to_filter = '';
        $sort = 'id DESC';
        $keyword_filter = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));

        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status IN (?@)", array_map('strval', (array)$filter['status']));

        if (isset($filter['sent']))
            $sent_filter = $this->db->placehold("AND sent = ?", (int)$filter['sent']);

        if (!empty($filter['date_from']))
            $date_from_filter = $this->db->placehold("AND complete_date >= ?", date('Y-m-d H:i:s', strtotime($filter['date_from'])));

        if (!empty($filter['date_to']))
            $date_to_filter = $this->db->placehold("AND complete_date <= ?", date('Y-m-d H:i:s', strtotime($filter['date_to'])));

        if(isset($filter['keyword']))
        {
            $keywords = explode(' ', $filter['keyword']);
            foreach($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
        }

        if (!empty($filter['sort']))
            $sort = $filter['sort'];

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
                $order_id_filter
                $status_filter
                $date_from_filter
                $date_to_filter
                $sent_filter
                $keyword_filter
            ORDER BY $sort
            $sql_limit
        ");
        $this->db->query($query);
        if ($results = $this->db->results())
        {
            foreach ($results as $result)
            {
                $result->body = unserialize($result->body);
                $result->response = unserialize($result->response);
            }
        }

        return $results;
    }

    public function count_p2pcredits($filter = array())
    {
        $id_filter = '';
        $order_id_filter = '';
        $status_filter = '';
        $date_from_filter = '';
        $date_to_filter = '';
        $sent_filter = '';
        $keyword_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));

        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status IN (?@)", array_map('strval', (array)$filter['status']));

        if (isset($filter['sent']))
            $sent_filter = $this->db->placehold("AND sent = ?", (int)$filter['sent']);

        if (!empty($filter['date_from']))
            $date_from_filter = $this->db->placehold("AND complete_date >= ?", date('Y-m-d H:i:s', strtotime($filter['date_from'])));

        if (!empty($filter['date_to']))
            $date_to_filter = $this->db->placehold("AND complete_date <= ?", date('Y-m-d H:i:s', strtotime($filter['date_to'])));

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
                $order_id_filter
                $status_filter
                $date_from_filter 
                $date_to_filter
                $sent_filter
                $keyword_filter
        ");
        $this->db->query($query);

        return $this->db->result('count');
    }

    public function add_p2pcredit($p2pcredit)
    {
        $p2pcredit = (array)$p2pcredit;

        if (isset($p2pcredit['body']))
            $p2pcredit['body'] = serialize($p2pcredit['body']);
        if (isset($p2pcredit['response']))
            $p2pcredit['response'] = serialize($p2pcredit['response']);

        $this->db->query(
            $this->db->placehold(
                "INSERT INTO b2p_p2pcredits
                  SET ?%",
                $p2pcredit
            )
        );

        $this->logging(__METHOD__, '', '', ['data' => $p2pcredit, 'debug' => debug_backtrace(0)], 'p2pcredit.txt');

        return $this->db->insert_id();
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

        $this->logging(__METHOD__, '', '', ['id' => $id, 'data' => $p2pcredit, 'debug' => debug_backtrace(0)], 'p2pcredit.txt');
       
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


    public function get_card($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_cards
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        return $this->db->result();
    }

    public function get_card_by_params(array $params) {
        if (!$params) return null;

        $conditions = [];
        foreach ($params as $condition => $value) {
            $conditions[] = $this->db->placehold("`$condition` = ?", $value);
        }

        $where = implode(" AND ", $conditions);

        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_cards
            WHERE ".$where." LIMIT 1");

        $this->db->query($query);
        return $this->db->result();
    }

    public function get_cards($filter = array())
    {
        $id_filter = '';
        $organization_id_filter = '';
        $user_id_filter = '';
        $keyword_filter = '';
        $deleted_filter = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['organization_id']))
            $organization_id_filter = $this->db->placehold("AND organization_id = ?", (int)$filter['organization_id']);

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int)$filter['user_id']);

        if (isset($filter['deleted']))
            $deleted_filter = $this->db->placehold("AND deleted = ?", (int)$filter['deleted']);

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
                $organization_id_filter
                $keyword_filter
                $deleted_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function get_autodebit_cards(int $userId)
    {
        $limit = 1000;
        $page = 1;
        if(isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if(isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
                    SELECT *
                    FROM b2p_cards AS bc
                        JOIN (SELECT pan,
                                expdate,
                                MAX(created) AS max_created
                        FROM b2p_cards
                        WHERE user_id = $userId
                            AND deleted = 0
                        GROUP BY pan,
                            expdate) AS latest
                        ON bc.pan = latest.pan
                            AND bc.expdate = latest.expdate
                            AND bc.created = latest.max_created
                    WHERE bc.user_id = $userId
                        AND bc.deleted = 0
                        ORDER BY bc.created DESC
                    $sql_limit;
                ");

        $this->db->query($query);
        return $this->db->results();
    }
    public function count_cards($filter = array())
    {
        $id_filter = '';
        $organization_id_filter = '';
        $keyword_filter = '';
        $deleted_filter = '';
        $user_id_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['organization_id']))
            $organization_id_filter = $this->db->placehold("AND organization_id = ?", (int)$filter['organization_id']);

        if(isset($filter['keyword']))
        {
            $keywords = explode(' ', $filter['keyword']);
            foreach($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
        }
        if (isset($filter['deleted']))
            $deleted_filter = $this->db->placehold("AND `deleted` = ?", (int)$filter['deleted']);

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND `user_id` = ?", (int)$filter['user_id']);

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM b2p_cards
            WHERE 1
                $id_filter
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

    public function get_register_id_transaction($register_id, $operation_id = NULL)
    {
        $operation_filter = '';
        if (!empty($operation_id))
            $operation_filter = $this->db->placehold("AND operation = ?", $operation_id);

        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_transactions
            WHERE register_id = ?
            $operation_filter
        ", (int)$register_id);
        $this->db->query($query);
        $result = $this->db->result();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';	
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



    public function get_order_insures($order_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_insures
            WHERE order_id = ?
        ", (int)$order_id);
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
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

    public function get_payment_number($payment)
    {
        return 'PM'.date('y', strtotime($payment->created)).'-'.$payment->id;
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

    public function get_payments($filter = array())
    {
        $id_filter = '';
        $reason_code_filter = '';
        $sent_filter = '';
        $keyword_filter = '';
        $filter_payment_type = '';
        $filter_not_types = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (isset($filter['sent']))
            $sent_filter = $this->db->placehold("AND sent = ?", (int)$filter['sent']);

        if (isset($filter['reason_code']))
            $reason_code_filter = $this->db->placehold("AND reason_code = ?", (int)$filter['reason_code']);

        if (isset($filter['payment_type'])) {
            $filter_payment_type = $this->db->placehold("AND payment_type = ?", (string)$filter['payment_type']);
        }

        if(isset($filter['keyword']))
        {
            $keywords = explode(' ', $filter['keyword']);
            foreach($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
        }

        if (!empty($filter['filter_not_payment_types'])) {
            $filter_not_types = $this->db->placehold("AND payment_type NOT IN (?@)", $filter['filter_not_payment_types']);
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
				$sent_filter
                $reason_code_filter
                $keyword_filter
                $filter_payment_type
                $filter_not_types
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получаем кол-во оплат
     * @param array $filter
     * @return array|false|int
     */
    public function count_payments(array $filter = [])
    {
        $id_filter = '';
        $sent_filter = '';
        $reason_code_filter = '';
        $keyword_filter = '';
        $group_by = '';
        $select = [];
        $where = [];

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (isset($filter['sent']))
            $sent_filter = $this->db->placehold("AND sent = ?", (int)$filter['sent']);

        if (isset($filter['reason_code']))
            $reason_code_filter = $this->db->placehold("AND reason_code = ?", (int)$filter['reason_code']);

        if (!empty($filter['filter_date_created'])) {
            $where[] = $this->db->placehold("created BETWEEN ? AND ?", $filter['filter_date_created']['filter_date_start'] . ' 00:00:00', $filter['filter_date_created']['filter_date_end'] . ' 23:59:59');
        }

        if (!empty($filter['filter_payment_type'])) {
            $where[] = $this->db->placehold("payment_type = ?", trim($filter['filter_payment_type']));
        }

        if(isset($filter['keyword']))
        {
            $keywords = explode(' ', $filter['keyword']);
            foreach($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
        }

        if (!empty($filter['filter_group_by'])) {
            if (in_array($filter['filter_group_by'], ['day', 'month'])) {
                $group_by = "GROUP BY filter_date ASC";
            }

            if ($filter['filter_group_by'] === 'day') {
                $select[] = ", DATE_FORMAT(created, '%Y.%m.%d') as filter_date";
            } elseif ($filter['filter_group_by'] === 'month') {
                $select[] = ", DATE_FORMAT(created, '%Y.%m') as filter_date";
            }
        }

        $query = $this->db->placehold("
            SELECT 
                COUNT(id) AS count
                -- {{select}}
            FROM b2p_payments
            WHERE 1
                $id_filter
                $sent_filter
                $reason_code_filter
                $keyword_filter
            -- {{where}}
            -- {{group_by}}
        ");

        $query = strtr($query, [
            '-- {{select}}' => !empty($select) ? implode("\n", $select) : '',
            '-- {{group_by}}' => $group_by,
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);

        if (!empty($filter['filter_group_by'])) {
            return $this->db->results();
        } else {
            return $this->db->result('count');
        }
    }

    /**
     * Получаем сумму по оплатам
     * @param array $filter_data
     * @return array|false|int
     */
    public function getTotalAmount(array $filter_data = [])
    {
        $group_by = '';
        $select = [];
        $where = [];

        if (!empty($filter_data['filter_date_created'])) {
            $where[] = $this->db->placehold("created BETWEEN ? AND ?", $filter_data['filter_date_created']['filter_date_start'] . ' 00:00:00', $filter_data['filter_date_created']['filter_date_end'] . ' 23:59:59');
        }

        if (!empty($filter_data['filter_payment_type'])) {
            $where[] = $this->db->placehold("payment_type = ?", trim($filter_data['filter_payment_type']));
        }

        if (isset($filter_data['reason_code']))
        {
            $where[] = $this->db->placehold("reason_code = ?", (int)$filter_data['reason_code']);
        }

        if (!empty($filter_data['filter_group_by'])) {
            $group_by = "GROUP BY filter_date ASC";

            if ($filter_data['filter_group_by'] === 'day') {
                $select[] = ", DATE_FORMAT(created, '%Y.%m.%d') as filter_date";
            } elseif ($filter_data['filter_group_by'] === 'month') {
                $select[] = ", DATE_FORMAT(created, '%Y.%m') as filter_date";
            }
        }

        $query = $this->db->placehold("
            SELECT 
                SUM(amount) AS total_amount
                -- {{select}}
            FROM b2p_payments
            WHERE 1
                -- {{where}}
                -- {{group_by}}
        ");

        $query = strtr($query, [
            '-- {{select}}' => !empty($select) ? implode("\n", $select) : '',
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
            '-- {{group_by}}' => $group_by,
        ]);

        $this->db->query($query);

        if (!empty($filter_data['filter_group_by'])) {
            return $this->db->results();
        } else {
            return $this->db->result('total_amount');
        }
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

    public function delete_payment($id)
    {
        $query = $this->db->placehold("
            DELETE FROM b2p_payments WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    /**
     * Совершает возврат покупки дополнительной услуги путем зачисления средств на указанную карту
     *
     * Поддерживаются:
     *      - телемедицина
     *      - мультиполис
     *      - кредитный доктор
     *
     * @param extraService $service
     * @param float        $amount
     * @param Card|null    $card
     *
     * @return GatewayResponse
     *
     * @throws Exception
     */
    public function refundExtraService( extraService $service, float $amount, Card $card )
    {
        $description = "Возврат денежных средств за услугу $service->title по Договору займа " . $service->loan->number ." ". $service->user->fio;
        $sector      = $this->getReturnSector( $service->transaction->sector );

        $data = $this->compileRequestData(
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
            'type'              => $service->return_transaction_slug,
            'amount'            => $amount * 100,
            'reference'         => $service->transaction->id,
            'sector'            => $data['sector'],
            'register_id'       => $gateway_response->id,
            'contract_number'   => $service->loan->number,
            'reason_code'       => $gateway_response->reason_code ?? 0,
            'state'             => $gateway_response->state,
            'description'       => $description,
            'created'           => date( 'Y-m-d H:i:s' ),
            'body'              => serialize( $data ),
            'callback_response' => $gateway_response_raw,
        ] );

        if( !$transaction_id ) {
            throw new \Exception( "Не удалось выполнить операцию возврата. Ошибка: transaction_id. Пожалуйста, попробуйте еще раз." );
        }

        if( $gateway_response->isError( 'webapi/Register', 'REGISTERED' ) ){

            $service->return_transaction_id = $transaction_id;
            $service->save();

            throw new \Exception( $gateway_response->message);
        }

        $data = $this->compileRequestData(
            'P2PCredit',
            [
                'amount'    => $amount * 100,
                'currency'  => $this->currency_code,
                'token'     => $card->token,
                'id'        => $gateway_response->id,
                'sector'    => $sector,
            ]
        );

        $gateway_response_raw = $this->send( 'P2PCredit', $data, 'gateweb' );
        $gateway_response     = new GatewayResponse( $gateway_response_raw );

        $this->update_transaction( $transaction_id, [
            'callback_response' => $gateway_response_raw,
            'reason_code'       => $gateway_response->reason_code ?? 0,
            'operation'         => $gateway_response->id,
            'state'             => $gateway_response->state,
            'card_pan'          => $gateway_response->pan ?: $gateway_response->pan2 ?: '',
            'operation_date'    => ! empty( $gateway_response->date ) ? str_replace( '.', '-', $gateway_response->date ) : NULL,
        ] );

        if( $gateway_response->isError( 'gateweb/P2PCredit', 'APPROVED' ) ){
            throw new \Exception( $gateway_response->message );
        }

        $service->markAsRefunded( $transaction_id, $amount );

        $gateway_response->return_transaction_id = $transaction_id;

        return $gateway_response;
    }

    /**
     * Совершает возврат путем использования метода Reverse
     *
     *  Поддерживаются:
     *       - телемедицина
     *       - мультиполис
     *       - кредитный доктор
     *
     * @param extraService $service
     * @param float        $amount
     *
     * @return GatewayResponse
     * @throws Exception
     */
    public function refundExtraServiceViaReverse( extraService $service, float $amount )
    {
        $description = "Возврат денежных средств за услугу $service->title по Договору займа " . $service->loan->number;

        $data = $this->compileRequestData(
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
            'type'              => $service->return_transaction_slug,
            'amount'            => $amount * 100,
            'sector'            => $service->transaction->sector,
            'register_id'       => $service->transaction->id,
            'contract_number'   => $service->loan->number,
            'reference'         => $service->transaction->id,
            'operation'         => $gateway_response->id,
            'reason_code'       => $gateway_response->reason_code ?? 0,
            'state'             => $gateway_response->state,
            'description'       => $description,
            'created'           => date( 'Y-m-d H:i:s' ),
            'body'              => serialize( $data ),
            'callback_response' => $gateway_response_raw,
            'operation_date'    => ! empty( $gateway_response->date ) ? str_replace( '.', '-', $gateway_response->date ) : NULL,
        ] );

        if( $gateway_response->isError( 'webapi/Reverse', 'APPROVED' ) ){

            $service->return_transaction_id = $transaction_id;
            $service->save();

            throw new \Exception( $gateway_response->message);
        }

        $service->markAsRefunded( $transaction_id, $amount );

        return $gateway_response;
    }

    /**
     * Совершает возврат дополнительной услуги на СБП счет
     * Использует Register + SBPCredit для перевода на СБП
     *
     * @param extraService $service
     * @param float $amount
     * @param int|null $sbp_account_id
     * @return GatewayResponse
     * @throws Exception
     */
    public function refundExtraServiceToSbp(extraService $service, float $amount, ?int $sbp_account_id)
    {
        $description = "Возврат денежных средств за услугу $service->title по Договору займа " . $service->loan->number . " " . $service->user->fio;
        $sector = $this->getReturnSector($service->transaction->sector);

        if (empty($sbp_account_id)) {
            throw new \Exception('Для возврата на СБП необходимо выбрать СБП счет пользователя.');
        }

        $sbpAccount = $this->getSbpAccount([
            ['id', '=', $sbp_account_id],
            ['user_id', '=', $service->user_id],
            ['deleted', '=', 0],
        ]);
        if (!$sbpAccount) {
            throw new \Exception('Выбранный СБП счет не найден или не принадлежит пользователю.');
        }

        $this->logging(
            __METHOD__,
            '',
            [
                'message' => 'Начало возврата СБП',
                'service' => [
                    'id' => $service->id,
                    'user_id' => $service->user_id,
                    'order_id' => $service->order_id,
                    'title' => $service->title,
                    'loan_number' => $service->loan->number,
                ],
                'amount' => $amount,
                'sector' => $sector,
                'description' => $description,
                'sbp_account_id' => $sbp_account_id,
            ],
            [],
            'extra_services_refund.txt'
        );

        $data = $this->compileRequestData(
            'Register',
            [
                'amount' => $amount * 100,
                'currency' => $this->currency_code,
                'description' => $description,
                'sector' => $sector,
            ]
        );

        $gateway_response_raw = $this->send('Register', $data);
        $gateway_response = new GatewayResponse($gateway_response_raw);
        $transaction_id = $this->add_transaction([
            'user_id' => $service->user_id,
            'order_id' => $service->order_id,
            'type' => $service->return_transaction_slug,
            'amount' => $amount * 100,
            'reference' => $service->transaction->id,
            'sector' => $data['sector'],
            'register_id' => $gateway_response->id,
            'contract_number' => $service->loan->number,
            'reason_code' => $gateway_response->reason_code ?? 0,
            'state' => $gateway_response->state,
            'description' => $description,
            'created' => date('Y-m-d H:i:s'),
            'body' => serialize($data),
            'callback_response' => $gateway_response_raw,
        ]);

        if (!$transaction_id) {
            throw new \Exception("Не удалось выполнить операцию возврата. Ошибка: transaction_id. Пожалуйста, попробуйте еще раз.");
        }

        if ($gateway_response->isError('webapi/Register', 'REGISTERED')) {

            $service->return_transaction_id = $transaction_id;
            $service->save();

            throw new \Exception($gateway_response->message);
        }

        $userAdapter = (object)[
            'id' => $service->user->id,
            'phone_mobile' => $service->user->phone_mobile,
            'firstname' => $service->user->firstname,
            'patronymic' => $service->user->patronymic,
            'lastname' => $service->user->lastname
        ];

        $orderAdapter = (object)[
            'order_id' => $service->order->id
        ];

        $precheckId = $this->getSbpIssuancePrecheckId(
            $sector,
            $this->passwords[$sector],
            $userAdapter,
            $orderAdapter,
            $sbpAccount,
            $gateway_response->id
        );

        if (!$precheckId) {
            throw new \Exception('Не удалось получить precheck_id для СБП возврата');
        }

        $data = $this->compileRequestData(
            'SBPCredit',
            [
                'sector' => $sector,
                'id' => $gateway_response->id,
                'precheck_id' => $precheckId,
            ]
        );

        $gateway_response_raw = $this->send('sbp/SBPCredit', $data);
        $gateway_response = new GatewayResponse($gateway_response_raw);

        $this->update_transaction($transaction_id, [
            'callback_response' => $gateway_response_raw,
            'reason_code' => $gateway_response->reason_code ?? 0,
            'operation' => $gateway_response->id,
            'state' => $gateway_response->state,
            'operation_date' => !empty($gateway_response->date) ? str_replace('.', '-', $gateway_response->date) : NULL,
        ]);

        if ($gateway_response->isError('webapi/sbp/SBPCredit', 'APPROVED')) {
            throw new \Exception($gateway_response->message);
        }

        $service->markAsRefunded($transaction_id, $amount);

        $gateway_response->return_transaction_id = $transaction_id;

        $this->logging(
            __METHOD__,
            '',
            [
                'message' => 'СБП возврат завершен',
                'result' => [
                    'operation_id' => $gateway_response->id,
                    'state' => $gateway_response->state,
                    'reason_code' => $gateway_response->reason_code ?? null,
                    'amount' => $amount * 100,
                    'register_id' => $gateway_response->order_id ?? null,
                    'precheck_id' => $data['precheck_id'] ?? null,
                ]
            ],
            [],
            'extra_services_refund.txt'
        );

        return $gateway_response;
    }

    /**
     * Преобразует сектор покупки в сектор возврата
     * @param $purchase_sector
     *
     * @return string
     */
    public function getReturnSector($purchase_sector)
    {
        switch ($purchase_sector):

            case $this->sectors['PAYMENT']:
            case $this->sectors['RECURRENT_ADVANCED']:
            case $this->sectors['RECURRENT']:
            case $this->sectors['PAY_CREDIT']:
                return $this->sectors['RETURN_BOOSTRA'];
                break;

            case $this->sectors['SPLIT_FINTEH']:
                return $this->sectors['RETURN_SPLIT_FINTEH'];
                break;

            case $this->sectors['AKVARIUS_PAY_CREDIT']:
            case $this->sectors['AKVARIUS_PAY_CREDIT_IL']:
            case $this->sectors['FINLAB_PAY_CREDIT']:
            case $this->sectors['VIPZAIM_PAY_CREDIT']:
            case $this->sectors['LORD_PAY_CREDIT']:
            case $this->sectors['RZS_PAY_CREDIT']:
            case $this->sectors['RZS_SBP_ISSUANCE_LOAN']:
            case $this->sectors['RZS_SBP_ISSUANCE_LOAN_IL']:
            case $this->sectors['FRIDA_PAY_CREDIT']:
            case $this->sectors['FRIDA_SBP_ISSUANCE_LOAN']:
            case $this->sectors['FRIDA_SBP_ISSUANCE_LOAN_IL']:
            case $this->sectors['FINLAB_SBP_ISSUANCE_LOAN']:
            case $this->sectors['FINLAB_SBP_ISSUANCE_LOAN_IL']:
            case $this->sectors['LORD_SBP_ISSUANCE_LOAN']:
            case $this->sectors['LORD_SBP_ISSUANCE_LOAN_IL']:
                return $this->sectors['RETURN_FINTEHMARKET'];
                break;

            case $this->sectors['MOREDENEG_SBP_ISSUANCE_LOAN']:
                return $this->sectors['RETURN_MOREDENEG'];
                break;

            case $this->sectors['LORD_PAY_CREDIT_SBP']:
                return $this->sectors['RETURN_LORD_SBP'];
                break;

            case $this->sectors['RZS_PAY_CREDIT_SBP']:
                return $this->sectors['RETURN_RZS_SBP'];
                break;
                
            case $this->sectors['FRIDA_PAY_CREDIT_SBP']:
                return $this->sectors['RETURN_FRIDA_SBP'];
                break;

            case $this->sectors['AKVARIUS_PAYMENT']:
            case $this->sectors['AKVARIUS_CESSION']:
            case $this->sectors['AKVARIUS_PAYMENT_SBP']:
            case $this->sectors['AKVARIUS_PAY_CREDIT_SBP']:
                return $this->sectors['RETURN_AKVARIUS'];
                break;

            case $this->sectors['FINLAB_PAYMENT']:
                return $this->sectors['RETURN_FINLAB'];
                break;

            case $this->sectors['LORD_PAYMENT']:
                return $this->sectors['RETURN_LORD'];
                break;

            case $this->sectors['RZS_PAYMENT']:
                return $this->sectors['RETURN_RZS'];
                break;
                
            case $this->sectors['FRIDA_PAYMENT']:
                return $this->sectors['RETURN_FRIDA'];
                break;

            case $this->sectors['VIPZAIM_PAYMENT']:
                return $this->sectors['RETURN_VIPZAIM'];
                break;

            case $this->sectors['FORINT_PAYMENT']:
                return $this->sectors['RETURN_FORINT'];
                break;

            case $this->sectors['RUBL_PAYMENT']:
                return $this->sectors['RETURN_RUBL'];
                break;

        endswitch;
    }

    /**
     * Формирует данные запроса учитывая специфику конкретных методов
     *
     * Поддерживаются:
     *  - Reverse
     *  - Register
     *  - P2PCredit
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

            case 'SBPCredit':
                $data['signature'] = $this->get_signature( [
                    $data['sector'],
                    $data['id'],
                    $data['precheck_id'],
                    $this->passwords[ $data['sector'] ],
                ] );
                return $data;

            default:
                throw new \Exception( "Неизвестный метод: $method" );
        }
    }

    public function pay_contract_remote($order_id)
    {
        $query_string = http_build_query([
            'site' => 'boostra',
            'order' => $order_id
        ]);
        $url = $this->config->remote_issuance_url.'?'.$query_string;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        $curl_errno = curl_errno($ch);

        $this->logging(__METHOD__, $query_string, $curl_errno, $res, 'remote_issuance.txt');

        if ($curl_errno > 0) {
            return false;
        }

        curl_close($ch);

        return $res;
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

    public function update_cards($user_id, $pan, $expdate, $organization_id,$card)
    {
        $query = $this->db->placehold("
            UPDATE b2p_cards SET ?%
            WHERE user_id = ?
            AND expdate = ?
            AND pan = ?
            AND deleted = 0
            AND organization_id = ?
        ",(array)$card,$user_id, $expdate, $pan, $organization_id);
        $this->db->query($query);

    }

    public function getSbpAccount(array $filters = [], array $selectFields = ['*'])
    {
        $selectFields = implode(',', $selectFields);
        $filtersString = $this->getWhereParams($filters);

        $query = $this->db->placehold("
            SELECT $selectFields
            FROM b2p_sbp_accounts
            WHERE 1 $filtersString
        ");

        $this->db->query($query);

        return $this->db->result();
    }

    public function updateSbpAccount(string $id, array $sbpAccountParams): string
    {
        $query = $this->db->placehold("
            UPDATE b2p_sbp_accounts SET ?% WHERE id = ?
        ", $sbpAccountParams, (int)$id);
        $this->db->query($query);

        return $id;
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

    /** Возвращает сектор в зависимости от типа займа (IL\PDL) и типа карты (card/sbp) */
    private function determineSector($order, string $cardType): int
    {
        if ((int)$order->organization_id === $this->organizations::BOOSTRA_ID) {
            return $this->sectors['PAY_CREDIT'];
        }

        if ($cardType === $this->orders::CARD_TYPE_CARD) {
            return $this->getCardPaymentSector($order);
        }

        if ($cardType === $this->orders::CARD_TYPE_SBP) {
            return $this->getSbpPaymentSector($order);
        }

        return 0;
    }

    /** Возвращает сектор для Card */
    private function getCardPaymentSector($order)
    {
        return ($order->loan_type === 'IL')
            ? $this->sectors['AKVARIUS_PAY_CREDIT_IL']
            : $this->sectors['AKVARIUS_PAY_CREDIT'];
    }

    /** Возвращает сектор для SBP */
    private function getSbpPaymentSector($order)
    {
        $organizationId = (int)$order->organization_id;
        $loanSuffix = $this->orders->isInstallmentLoan($order->loan_type) ? '_IL' : '';

        $sectorMap = [
            $this->organizations::FINLAB_ID => 'FINLAB_SBP_ISSUANCE_LOAN',
            $this->organizations::RZS_ID => 'RZS_SBP_ISSUANCE_LOAN',
            $this->organizations::LORD_ID => 'LORD_SBP_ISSUANCE_LOAN',
            $this->organizations::MOREDENEG_ID => 'MOREDENEG_SBP_ISSUANCE_LOAN',
            $this->organizations::FRIDA_ID => 'FRIDA_SBP_ISSUANCE_LOAN',
            $this->organizations::FASTFINANCE_ID => 'FASTFINANCE_SBP_ISSUANCE_LOAN',
            $this->organizations::FORINT_ID => 'FORINT_PAYMENT',
            $this->organizations::RUBL_ID => 'RUBL_SBP_ISSUANCE_LOAN',
        ];

        if (isset($sectorMap[$organizationId])) {
            return $this->sectors[$sectorMap[$organizationId] . $loanSuffix] ?? 0;
        }

        return 0;
    }
}
