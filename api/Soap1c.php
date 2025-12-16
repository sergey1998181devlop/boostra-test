<?php


require_once 'Simpla.php';

class Soap1c extends Simpla
{
    /**
     * Soap1c::MaxOverdueByClient()
     * по UID контрагента возвращает максимальное кол-во дней просрочки
     * @param string $user_uid
     * @return int
     */
    public function MaxOverdueByClient($user_uid)
    {
        $request = [
            'UID' => $user_uid
        ];

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $client->__soapCall('MaxOverdueByClient', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl MaxOverdueByClient', (array)$request, (array)$returned);
        return empty($returned->return) ? $returned : json_decode($returned->return);
    }
    
    /**
     * Soap1c::send_payments_il()
     * Отсылает данные по оплатам клиентов
     * 
        WebSignal , метод OplataInstalment - принимает оплату по инстолментам        
        параметр ArrayOplata- массив из структур, содержит:
            Дата - дата оплаты в формате ггггММддЧЧммсс
            НомерЗайма            
            OperationID
            OrderID
            НомерКарты            
            Сумма
            АСП 
            СуммаЧДП
            СуммаПДП
     * @param array $payments
     * @return
     */
    public function send_payments_il($payments)
    {
    	$items = array();
        foreach ($payments as $payment)
        {
            $item = new StdClass();

            $item->Дата = empty($payment->operation_date) ? '' : date('YmdHis', strtotime($payment->operation_date));
            $item->НомерЗайма = $payment->contract_number;
            $item->OperationID = $payment->operation_id;
            $item->OrderID = $payment->register_id;
            $item->НомерКарты = $payment->card_pan;
            $item->Сумма = (float)$payment->amount; 
            $item->АСП = $payment->asp;
            $item->ИсточникОплаты = $payment->create_from;
            $item->НомерСектора = $payment->sector;

            $item->НомерОплаты = 'PM'.date('y', strtotime($payment->created)).'-'.$payment->id;//обязательно в номере ТИРЕ. длина номера 11 символов!!!

            $organization = $this->organizations->get_organization($payment->organization_id);

            $item->ИННОрагнизации = $organization->inn;

            if (!empty($payment->multipolis)) {
                $item->Мультиполис = (object)[
                    'СуммаСтраховки' => $payment->multipolis->amount,
                    'НомерСтраховки' => $payment->multipolis->number,
                    'Organization' => $payment->organization ? $payment->organization->onec_code : '000000005',
                ];
                $item->Сумма -= $payment->multipolis->amount; // (тут без мультиполиса)
            }

            if (!empty($payment->tv_medical)) {
                $item->Телемедицина = (object)[
                    'ID_ВитаМед' => 'ID_'. $payment->tv_medical->tv_medical_id,
                    'Сумма' => $payment->tv_medical->amount,
                    'НомерПолиса' => $payment->tv_medical->id,
                    'insurer' => '',
                    'Organization' => $payment->organization ? $payment->organization->onec_code : '000000005',
                ];
                $item->Сумма -= $payment->tv_medical->amount; // (тут без телемедицины)
            }

            if (!empty($payment->star_oracle)) {
                $item->ЗвездныйОракул = (object)[
//                    'ID_ЗвездныйОракул' => 'ID_' . $payment->star_oracle->id,
                    'Сумма' => $payment->star_oracle->amount,
                    'НомерПолиса' => $payment->star_oracle->id,
                    'Organization' => $this->organizations->get_organization($payment->star_oracle->organization_id)->onec_code ?: '000000005',
                ];
                $item->Сумма -= $payment->star_oracle->amount; // (тут без телемедицины)
            }

            $item->СуммаЧДП = empty($payment->chdp) ? 0 : (float)$item->Сумма;
            $item->СуммаПДП = empty($payment->pdp) ? 0 : (float)$item->Сумма;
            $item->ОплатаПоДоговору  = empty($payment->contract_payment) ? false : true;
            $item->Скидка = $payment->discount_amount;

            $items[] = $item;
        }

        $request = new StdClass();
        $request->ArrayOplata = json_encode($items, JSON_UNESCAPED_UNICODE);

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl OplataInstalment', (array)$request, 'b2p_payment_il.txt');

        try {
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $stat_z_client->__soapCall('OplataInstalment', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl OplataInstalment', (array)$request, (array)$returned, 'b2p_payment_il.txt');

        return $returned;
    }

    public function get_il_details($contract_number)
    {
        $request = [
            'НомерЗайма' => $contract_number
        ];

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $client->__soapCall('DebtIL', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl DebtIL', (array)$request, (array)$returned);
        return empty($returned->return) ? $returned : (array)json_decode($returned->return);
    }
    
    public function get_schedule_payments($contract_number)
    {
        $request = [
            'НомерЗайма' => $contract_number
        ];

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $client->__soapCall('Graphics', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Graphics', (array)$request, (array)$returned);
        return empty($returned->return) ? $returned : (array)json_decode($returned->return);
    }
        
    public function PaymentFailed($payment_id)
    {        
        if ($payment = $this->best2pay->get_payment($payment_id))
        {
            $request = new StdClass();
            $request->НомерЗайма = $payment->contract_number;
            $request->ДатаСписания = date('YmdHis', strtotime($payment->created));
            $request->СуммаБаланса = (float)$payment->amount;

            try {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
                $response = $client->__soapCall('PaymentFailed', [$request]);

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                throw $fault;
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl PaymentFailed', (array)$request, (array)$response, 'PaimentFailed.txt');
            
            return $response->return;
        }
    }


    public function MiniScoringRef($uid)
    {
        $request = new StdClass();
        $request->UID = $uid;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('MiniScoringRef', [$request]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl MiniScoringRef', (array)$request, (array)$response, 'MiniScoringRef.txt');

        return json_decode($response->return, true);
    }

    public function Refinancing(array $refinanceData)
    {
        $contract = $refinanceData['contract'] ?? null;
        $oldContract = $refinanceData['old_contract'] ?? null;
        $payment = $refinanceData['payment'] ?? null;
        $register_id = $refinanceData['register_id'] ?? 0;
        $operation_id = $refinanceData['operation_id'] ?? 0;

        if (empty($contract) || empty($oldContract) || empty($payment)) {
            throw new \Exception('Данные о рефинансировании неполные. Требуется "contract", "oldContract" и "payment".');
        }

        $order = $this->orders->get_order($contract->order_id);
        $inns = $this->organizations->get_inn_for_recurrents(false);

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Refinancing', [
            'confirm_date' => $contract->confirm_date,
            'payment_created' => $payment->created,
            'old_contract_confirm_date' => $oldContract->confirm_date,
        ], [], 'Refinancing.txt');

        /**
         * РефинансируемыйЗайм - этот тот займ, который рефинансируют. РефинансирующийЗайм - это займ,
         * который выдаётся при рефинансе
         */
        $item = [
            'РефинансирующийЗайм' => [
                'Номер' => $contract->number,
                'Дата' => date('YmdHis', $contract->confirm_date ? strtotime($contract->confirm_date) : strtotime(date('Y-m-d H:i:s'))),
                'ОрганизацияИНН' => $inns[$order->organization_id] ?? null,
                'Сумма' => $order->amount,
                'Срок' => $order->period,
                'ПериодичностьПлатежей' => $order->payment_period != 0 ? $order->period / $order->payment_period : 0,
                'ПроцентнаяСтавка' => $order->percent,
            ],
            'РефинансируемыйЗайм' => [
                'Номер' => $oldContract->number,
                'ДатаОплаты' => date('YmdHis', $oldContract->confirm_date ? strtotime($oldContract->confirm_date) : strtotime(date('Y-m-d H:i:s'))),
                'НомерОплаты' => 'PM'.date('y', strtotime($payment->created)).'-'.$payment->id,
            ],
            'Payment' => [
                'Дата' => date('YmdHis'),
                'PaymentId' => $operation_id,
                'OrderId' => $register_id,
                'CardId' => '-',
            ]
        ];

        $request = new StdClass();
        $request->TextJSON = json_encode($item, JSON_UNESCAPED_UNICODE);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Refinancing start', (array)$item, [], 'Refinancing.txt');

            $returned = $client->__soapCall('Refinancing', [$request]);
            // TODO: Записать 1c_id и 1c_status для новой заявки, закрыть старую заявку

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Refinancing end', (array)$item, (array)$returned, 'Refinancing.txt');

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        return json_decode($returned->return, true);
    }

    public function CheckActiveRefinans($uid, $inn)
    {
        $request = new StdClass();
        $request->UID = $uid;
        $request->INN = $inn;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('CheckActiveRefinans', [$request]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl CheckActiveRefinans', (array)$request, (array)$response, 'CheckActiveRefinans.txt');

        return json_decode($response->return, true);
    }

    public function DebtForFIO($params)
    {
        $item = [
            'Фамилия' => $params['lastname'],
            'Имя' => $params['firstname'],
            'Отчество' => $params['patronymic'],
            'ДатаРождения' => date('YmdHis', strtotime($params['birth'])),
            'Телефон' => $params['phone_mobile'],
            'INN' => $this->organizations->get_inn_for_recurrents(),
        ];
        
        $request = new StdClass();
        $request->TextJson = json_encode($item, JSON_UNESCAPED_UNICODE);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $client->__soapCall('DebtForFIO', [$request]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }
        
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl DebtForFIO', (array)$item, (array)$returned, 'debt_fio.txt');
        
        return json_decode($returned->return);
    }

    public function get_loan_details($number)
    {
        if (!empty($number))
        {
            $z = new stdClass(); 
			$z->Number = $number;

            $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebCRM.1cws?wsdl CalculationOfAccruals', (array)$z);

            try {
                $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebCRM.1cws?wsdl");
                $returned = $uid_client->__soapCall('CalculationOfAccruals',array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                throw $fault;
            }

			return $returned->return;
            
        }
    }

    public function get_cession_document($number)
    {
		if(!empty($number))
		{
            $inn_arr =  $this->organizations->get_site_inns();
            if (count($inn_arr) < 1){
                return false;
            }

            $z = new stdClass(); 
			$z->Number = $number;
            $z->Partner = 'Boostra';
            $z->ArrayINN = json_encode($inn_arr, false);

            $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl NotificationCessionsINN', (array)$z);

            try {
                $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                $returned = $uid_client->__soapCall('NotificationCessionsINN',array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                throw $fault;
            }

			return $returned;
		}
		else
			return false;
	}

    /**
     * Soap1c::update_status_1c()
     * 
     * @param mixed $order_id_1c
     * @param mixed $status
     * @param integer $amount
     * @return
     */
    public function update_status_1c($order_id_1c, $status, $manager, $amount = 0, $percent = 1, $reason = '', $cdoctor = 0, $period = 7)
    {
    	$z = new StdClass();
        $z->НомерЗаявки = $order_id_1c;
        $z->Статус = $status;
        $z->Менеджер = $manager;
        $z->СуммаCRM = (float)$amount;
        $z->Срок = (int)$period;
        $z->ПроцентнаяСтавка = (float)$percent;
        $z->ПричинаОтказаCRM = $reason;
        $z->КредитныйДоктор = $cdoctor;
        $z->Периодичность = $period > 27 ? '2 недели' : 'День';

        if(strpos(strtolower(php_sapi_name()), 'cli') === false) {
            $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetStateApplication', (array)$z);
            try {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
                $returned = $client->__soapCall('GetStateApplication', array($z));
            } catch (SoapFault $fault) {
                $this->logging(__METHOD__, 'SoapFault', $z, $fault, 'update_status_1c_faults.txt');
                throw $fault;
            } catch (Exception $fault) {
                $this->logging(__METHOD__, 'Exception', $z, $fault, 'update_status_1c_faults.txt');
                throw $fault;
            }
        } else {
            try {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
                $returned = $client->__soapCall('GetStateApplication', array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                $returned = $fault;
            } catch (Exception $fault) {
                $returned = $fault;
            }
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetStateApplication', (array)$z, (array)$returned, 'state.txt');
        
		return $returned->return;
    }

    public function get_strah_summ($ip_name)
    {
        $z = new stdClass(); 
		$z->insurer = $ip_name;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOtvetZayavki.1cws?wsdl StrahSumm', (array)$z, 'statuses.txt');

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOtvetZayavki.1cws?wsdl");
            $returned = $client->__soapCall('StrahSumm',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOtvetZayavki.1cws?wsdl StrahSumm', (array)$z, (array)$returned, 'statuses.txt');
        
        return $returned->return;
    }
        
    /**
     * Soap1c::send_payments()
     * Отсылает данные по оплатам клиентов
     * 
     * @param array $payments
     * @return
     */
    public function send_payments($payments)
    {
    	$items = array();
        foreach ($payments as $payment)
        {
            $item = new StdClass();

            $item->АСП = $payment->asp;
            $item->НомерЗайма = $payment->contract_number;
            $item->ДатаОплаты = empty($payment->operation_date) ? date('YmdHis') : date('YmdHis', strtotime($payment->operation_date));
            $item->НомерОплаты = 'PM'.date('y').'-'.$payment->id;//обязательно в номере ТИРЕ. длина номера 11 символов!!!
            $item->НомерКарты = $payment->card_pan;

            $item->ID_Заказ = $payment->register_id;
            $item->ID_УспешнаяОперация = $payment->operation_id;

            $item->СуммаОплаты = (float)$payment->amount; 
            $item->ИсточникОплаты = $payment->create_from;
            
            $item->Пролонгация = empty($payment->prolongation) ? 0 : 1;  //1 - истина, 0 ложь
            $item->СрокПролонгации = empty($payment->prolongation_day) ? 0 : $payment->prolongation_day;
            $item->СБП = empty($payment->is_sbp) ? 0 : 1;  //1 - истина, 0 ложь
            $item->НачислитьПроцент = empty($payment->calc_percents) ? 0 : 1;  //1 - истина, 0 ложь
            $item->ЗакрытПоСкидке = empty($payment->grace_payment) ? 0 : 1;  //1 - истина, 0 ложь
            $item->НомерСектора = $payment->sector;

            $organization = $this->organizations->get_organization($payment->organization_id);

            $item->ИННОрагнизации = $organization->inn;
            
            if (!empty($payment->multipolis)) {
                $item->Мультиполис = (object)[
                    'СуммаСтраховки' => $payment->multipolis->amount,
                    'НомерСтраховки' => $payment->multipolis->number,
                    'Organization' => $payment->organization ? $payment->organization->onec_code : '000000005', 
                ];
                $item->СуммаОплаты -= $payment->multipolis->amount; // (тут без мультиполиса)
            }

            if (!empty($payment->tv_medical)) {
                $item->Телемедицина = (object)[
                    'ID_ВитаМед' => 'ID_'. $payment->tv_medical->tv_medical_id,
                    'Сумма' => $payment->tv_medical->amount,
                    'НомерПолиса' => $payment->tv_medical->id,
                    'insurer' => '',
                    'Organization' => $payment->organization ? $payment->organization->onec_code : '000000005',
                ];
                $item->СуммаОплаты -= $payment->tv_medical->amount; // (тут без телемедицины)
            }

            if (!empty($payment->star_oracle)) {
                $item->ЗвездныйОракул = (object)[
//                    'ID_ЗвездныйОракул' => 'ID_' . $payment->star_oracle->id,
                    'Сумма' => $payment->star_oracle->amount,
                    'НомерПолиса' => $payment->star_oracle->id,
                    'Organization' => $this->organizations->get_organization($payment->star_oracle->organization_id)->onec_code ?: '000000005',
                ];
                $item->СуммаОплаты -= $payment->star_oracle->amount; // (тут без телемедицины)
            }

            //$item->Страховка = new StdClass();
            //$item->Страховка->СуммаСтраховки = empty($payment->insure) ? 0 : $payment->insure;

            if ($payment->insure > 0) {
                $item->ШтрафнойКД = (object)[
                    'СуммаКД' => (float)$payment->insure,
                    'OrderID' => $payment->register_id,
                    'OperationID' => $payment->operation_id,
                    'КомплектID' => 99,
                    'КомплектНазвание' => 'ШтрафнойКД',
                    'Organization' => '000000005', //TODO: сделать код 
                ];
                $item->СуммаОплаты -= $payment->insure;
            }
            $item->ОплатаПоДоговору  = empty($payment->contract_payment) ? false : true;
            $item->Скидка = (int) $payment->discount_amount;
            $item->РеферальнаяСкидка = (int) $payment->referral_discount_amount;

            $items[] = $item;
        }

        $request = new StdClass();
        $request->ArrayOplata = json_encode($items, JSON_UNESCAPED_UNICODE);

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Oplata', (array)$request, 'b2p_payment.txt');

        try {
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $stat_z_client->__soapCall('Oplata', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Oplata', (array)$request, (array)$returned, 'b2p_payment.txt');

        return $returned;
    }

    /**
     * Soap1c::send_payment_recurring()
     * Отсылает данные по рекуррентной оплате
     *
     * @param $payment
     * @return
     */
    public function send_payment_recurring($payment)
    {
        $item = new StdClass();
        $item->НомерЗайма = $payment->contract_number;
        $item->ДатаОплаты = empty($payment->operation_date) ? date('YmdHis') : date('YmdHis', strtotime($payment->operation_date));
        $item->НомерОплаты = 'PM'.date('y').'-'.$payment->id;//обязательно в номере ТИРЕ. длина номера 11 символов!!!
        $item->НомерКарты = $payment->card_pan;

        $item->ID_Заказ = $payment->register_id;
        $item->ID_УспешнаяОперация = $payment->operation_id;
        $item->Рекурент = 1;

        $organization = $this->organizations->get_organization($payment->organization_id);

        $item->ИННОрагнизации = $organization->inn;
        $item->ЗвездныйОракул = (object)[
            'Сумма' => $payment->star_oracle->amount,
            'НомерПолиса' => $payment->star_oracle->id,
            'Organization' => $this->organizations->get_organization($payment->star_oracle->organization_id)->onec_code ?: '000000005',
        ];
        $item->СуммаОплаты = 0;
        $item->Пролонгация = 0;

        $request = new StdClass();
        $request->ArrayOplata = json_encode(array($item), JSON_UNESCAPED_UNICODE);

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Oplata', (array)$request, 'b2p_payment.txt');

        try {
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $stat_z_client->__soapCall('Oplata', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Oplata', (array)$request, (array)$returned, 'b2p_payment.txt');

        return $returned;
    }


    /**
     * Soap1c::send_contracts()
     * 
     * Метод отсылает в 1с данные по выданным кредитам
     * 
     * @param array $orders
     * обьект $order входящий в массив должен содержать обьекты p2pcredit, insure
     *  Пример получения:
     *     $order->p2pcredit = $core->best2pay->get_contract_p2pcredit($contract->id);
     *     $order->insure = $core->best2pay->get_insure($contract->insurance_id);
     * @return string - в случае успеха должно вернуться номер договора
     */
    public function send_contracts($orders)
    {
        $managers = array();
        foreach ($this->managers->get_crm_managers() as $m)
            $managers[$m->id] = $m;

        $items = array();
        foreach ($orders as $order)
        {
            $item = new StdClass();

            $item->Организация = 'Boostra';
            $item->Дата = date('YmdHis', strtotime($order->confirm_date)); //формат ггггММддччммсс
            $item->Сумма = $order->amount;  //в рублях
            $item->ПроцентСтавка = $order->percent;
            $item->Срок = (int)$order->period;
            $item->Менеджер = empty($order->manager_id) ? '' : $managers[$order->manager_id]->name_1c;
            $item->УИД_Займ = exec($this->config->root_dir . 'generic/uidgen');
            $item->УИД_Заявка = $order->order_uid;
            $item->agrid = ''; // агрид скористы
            $item->НомерЗаявки = $order->id_1c;
            
            $contract_number = 'Б'.date('y', strtotime($order->date)).'-';
            if ($order->order_id > 999999)
                $contract_number .= $order->order_id; 
            else
                $contract_number .= '0'.$order->order_id; 
            $item->Номер = $contract_number; // 12345-67890обязательно в номере ТИРЕ. длина номера 11 символов!!!

            $item->Клиент = new StdClass();
            $item->Клиент->Фамилия = $order->lastname;
            $item->Клиент->Имя = $order->firstname;
            $item->Клиент->Отчество = $order->patronymic;
            $item->Клиент->ДатаРожденияПоПаспорту = date('YmdHis', strtotime($order->birth));

            $item->Клиент->АдресРегистрацииГород = trim($order->Regcity.' '.$order->Regcity_shorttype);
            $item->Клиент->АдресРегистрацииДом = $order->Reghousing.(empty($order->Regbuilding) ? '' : ' стр. '.$order->Regbuilding);
            $item->Клиент->АдресРегистрацииИндекс = $order->Regindex;
            $item->Клиент->АдресРегистрацииКвартира = $order->Regroom;
            $item->Клиент->АдресРегистрацииРегион = trim($order->Regregion.' '.$order->Regregion_shorttype);
            $item->Клиент->АдресРегистрацииУлица = trim($order->Regstreet.' '.$order->Regstreet_shorttype);
            $item->Клиент->АдресРегистрацииРайон = empty($order->Regdistrict) ? '' : trim($order->Regdistrict);
            $item->Клиент->АдресРегистрацииНасПункт = empty($order->Reglocality) ? '' : trim($order->Reglocality);
            $item->Клиент->АдресРегистрацииТелефон = '';

            $item->Клиент->АдресФактическогоПроживанияИндекс = $order->Faktindex;
            $item->Клиент->АдресФактическогоПроживанияРегион = trim($order->Faktregion.' '.$order->Faktregion_shorttype);
            $item->Клиент->АдресФактическогоПроживанияРайон = empty($order->Faktdistrict) ? '' : trim($order->Faktdistrict);
            $item->Клиент->АдресФактическогоПроживанияГород = trim($order->Faktcity.' '.$order->Faktcity_shorttype);
            $item->Клиент->АдресФактическогоПроживанияНасПункт = empty($order->Faktlocality) ? '' : trim($order->Faktlocality);
            $item->Клиент->АдресФактическогоПроживанияУлица = trim($order->Faktstreet.' '.$order->Faktstreet_shorttype);
            $item->Клиент->АдресФактическогоПроживанияДом = $order->Fakthousing.(empty($order->Faktbuilding) ? '' : ' стр. '.$order->Faktbuilding);
            $item->Клиент->АдресФактическогоПроживанияКвартира = $order->Faktroom;
            $item->Клиент->АдресФактическогоПроживанияТелефон = '';

            $item->Клиент->АдресФактическогоПроживанияМобильныйТелефон = $this->format_phone($order->phone_mobile); 

            $item->Клиент->ИНН = $order->inn;
            $item->Клиент->КоличествоИждевенцев = '';
            $item->Клиент->МестоРожденияПоПаспорту = $order->birth_place;
            $item->Клиент->Образование = '';

            $item->Клиент->ОрганизацияАдрес = $order->work_address;
            $item->Клиент->ОрганизацияГрафикЗанятости = '';
            $item->Клиент->ОрганизацияДолжность = $order->profession;
            $item->Клиент->ОрганизацияЕжемесячныйДоход = $order->income_base;
            $item->Клиент->ОрганизацияНазвание = $order->workplace;
            $item->Клиент->ОрганизацияСтажРаботыЛет = '';
            $item->Клиент->ОрганизацияСфераДеятельности = '';
            $item->Клиент->ОрганизацияТелефон = $this->format_phone($order->work_phone);
            $item->Клиент->ОрганизацияФИОРуководителя = $order->workdirector_name;
            $item->Клиент->ОрганизацияТелефонРуководителя = '';

            $item->Клиент->ПаспортДатаВыдачи = date('YmdHis', strtotime($order->passport_date)); //формат ггггММддччммсс
            $item->Клиент->ПаспортКемВыдан = $order->passport_issued;
            $item->Клиент->ПаспортКодПодразделения = $order->subdivision_code;
            $item->Клиент->ПаспортНомер = (string)substr(str_replace(array(' ', '-'), '', $order->passport_serial), 4, 6);
            $item->Клиент->ПаспортСерия = (string)substr(str_replace(array(' ', '-'), '', $order->passport_serial), 0, 4);

            $item->Клиент->Пол = $order->gender == 'male' ? 'Мужской' : 'Женский';


            $item->Клиент->КонтактныеЛица = array();
            /* 
            //TODO: разобраться с конт лицами            
                        foreach ($order->contactpersons as $cp)
                        {
                            $contactperson = new StdClass();
                            $contact_person_array = explode(' ', $cp->name);
                            $contactperson->Фамилия = isset($contact_person_array[0]) ? $contact_person_array[0] : '';
                            $contactperson->Имя =  isset($contact_person_array[1]) ? $contact_person_array[1] : ''; 
                            $contactperson->Отчество =  isset($contact_person_array[2]) ? $contact_person_array[2] : '';
                            $contactperson->ТелефонМобильный = $this->format_phone($cp->phone);
                            $contactperson->СтепеньРодства = $cp->relation;
                            
                            $item->Клиент->КонтактныеЛица[] = $contactperson;
                        }
            */
            $item->Payment = new StdClass();
            $item->Payment->CardId = $order->card_id;
            $item->Payment->Дата = date('YmdHis', strtotime($order->p2pcredit->date));
            $item->Payment->PaymentId = $order->p2pcredit->operation_id;
            $item->Payment->OrderId = $order->p2pcredit->register_id;

            $item->ЗаймСоСтраховкой = empty($order->insure) ? 0 : 1;

            if (!empty($order->insure)) {
                $item->Страховка = new StdClass();
                $item->Страховка->СуммаСтраховки = empty($order->insure) ? 0 : $order->insure->amount; //сумма страховки
                $item->Страховка->OrderID = empty($order->insure->register_id) ? '' : $order->insure->register_id;
                $item->Страховка->OperationID = empty($order->insure->operation_id) ? '' : $order->insure->operation_id;
                $item->Страховка->Ставка = empty($order->insure->stavka) ? '' : $order->insure->stavka;
            }
            
            $items[] = $item;
        }

        $request = new StdClass();
        $request->ArrayContracts = json_encode($items, JSON_UNESCAPED_UNICODE);

        $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");

        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump(($items));echo '</pre><hr />';

        try {
		    $returned = $stat_z_client->__soapCall('Request', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($returned);echo '</pre><hr />';        
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Request', (array)$request, (array)$returned, 'contracts.txt');

        return $returned;    	
    }


    public function send_repeat_zayavka($params)
    {
        if (!($user = $this->users->get_user((int)$params['user_id']))) {
            return null;
        }

        $z = new StdClass();

        $z->Uid = $user->uid;
        $z->УИД = $params['order_uid'];
        
        if (empty($params['organization_id'])) {
            $params['organization_id'] = $this->organizations->get_base_organization_id(['user_id' => $user->id]);
        }
        $organization = $this->organizations->get_organization($params['organization_id']);
        $z->ИННОрганизации = $organization->inn;

        $z->site_id = $user->site_id;
        $z->partner_id = $user->partner_id;
        if(empty($user->partner_name))
            $z->partner_name = 'Boostra';
        else
            $z->partner_name = $user->partner_name;

        // Информация из базы по заявке
        $z->ДатаЗаявки = date('YmdHis');

        $z->amount = intval($params['amount']);
        $z->period = intval($params['period']);
        $z->utm_source = empty($params["utm_source"]) ? 'Boostra' : $params["utm_source"];
        $z->utm_medium = empty($params["utm_medium"]) ? 'Site' : $params["utm_medium"];
        $z->utm_campaign = empty($params["utm_campaign"]) ? 'C1_main' : $params["utm_campaign"];
        $z->utm_content = empty($params["utm_content"]) ? '' : $params["utm_content"];
        $z->utm_term = empty($params["utm_term"]) ? '' : $params["utm_term"];
        $z->webmaster_id = empty($params["webmaster_id"]) ? '' : $params["webmaster_id"];
        $z->click_hash = empty($params['click_hash']) ? '' : $params['click_hash'];

        $z->ServicesSMS = (int)$user->service_sms;
        $z->ServicesInsure = (int)$user->service_insurance;
        $z->ServicesReason = (int)$user->service_reason;

        $z->ОтказНаСайте = 0;
        $z->ПричинаОтказаНаСайте = '';

        if (empty($params['b2p']))
            $z->CardID = $params['card'];
        else
            $z->CardID = '';

        $z->ТекстЗапроса = json_encode($z, JSON_UNESCAPED_UNICODE);

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetZayavkiUid', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetZayavkiUid', [$z]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetZayavkiUid', (array)$z, (array)$returned, 'orders.txt');

        sleep(3);

        return $returned;
    }


    public function set_order_complete($order_id)
    {
        //Отключай @Aleksandr Kislyakov

        //сейчас он хочет пачкой забирать ночью @Артур
        // 05.12 включил, отчеты не работают без него
        $this->orders->update_order($order_id, ['complete' => 1]);

        $order = $this->orders->get_order((int)$order_id);
        
        $z = new stdClass(); 
		$z->НомерЗаявки = $order->id_1c;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl FullApplication', (array)$z, 'statuses.txt');

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('FullApplication',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl FullApplication', (array)$z, (array)$returned, 'statuses.txt');
        
        return $returned;
    }
    
    public function update_fields($uid, $fields)
    {
    	$z = new StdClass();
        $z->НомерЗаявки = '';
        $z->Uid = $uid;
        
        $update = new StdClass();
        
        
        // место регистрации
        if (isset($fields['Regcity']))
            $update->АдресРегистрацииГород = $fields['Regcity'];
        if (isset($fields['Reghousing']))
            $update->АдресРегистрацииДом = $fields['Reghousing'];
        if (isset($fields['Regindex']))
            $update->АдресРегистрацииИндекс = $fields['Regindex'];
        if (isset($fields['Regroom']))
            $update->АдресРегистрацииКвартира = $fields['Regroom'];
        if (isset($fields['Regregion']))
            $update->АдресРегистрацииРегион = $fields['Regregion'];
        if (isset($fields['Regstreet']))
            $update->АдресРегистрацииУлица = $fields['Regstreet'];
        
        
        // место фактического проживания
        if (isset($fields['Faktcity']))
            $update->АдресФактическогоПроживанияГород = $fields['Faktcity'];
        if (isset($fields['Fakthousing']))
            $update->АдресФактическогоПроживанияДом = $fields['Fakthousing'];
        if (isset($fields['Faktindex']))
            $update->АдресФактическогоПроживанияИндекс = $fields['Faktindex'];
        if (isset($fields['Faktroom']))
            $update->АдресФактическогоПроживанияКвартира = $fields['Faktroom'];
        if (isset($fields['Faktregion']))
            $update->АдресФактическогоПроживанияРегион = $fields['Faktregion'];
        if (isset($fields['Faktstreet']))
            $update->АдресФактическогоПроживанияУлица = $fields['Faktstreet'];
        if (isset($fields['phone_mobile']))
            $update->АдресФактическогоПроживанияМобильныйТелефон = $this->format_phone($fields['phone_mobile']);

        // персональная информация
        if (isset($fields['birth']))
            $update->ДатаРожденияПоПаспорту = date('Ymd000000', strtotime($fields['birth']));
        if (isset($fields['birth_place']))
            $update->МестоРожденияПоПаспорту = $fields['birth_place'];
        if (isset($fields['gender']))
            $update->Пол = ($fields['gender'] == 'male') ? 'Мужской' : 'Женский';
        if (isset($fields['lastname']))
            $update->Имя = $fields['lastname'];
        if (isset($fields['firstname']))
            $update->Фамилия = $fields['firstname'];
        if (isset($fields['patronymic']))
            $update->Отчество = $fields['patronymic'];
        
        // паспортные данные
        if (isset($fields['passport_serial']))
        {
            $cl = str_replace(array(' ', '-'), '', $fields['passport_serial']);
            $update->ПаспортСерия = substr($cl, 0, 4);
            $update->ПаспортНомер = substr($cl, 4, 6);
        }
        if (isset($fields['subdivision_code']))
            $update->ПаспортКодПодразделения = $fields['subdivision_code'];
        if (isset($fields['passport_issued']))
            $update->ПаспортКемВыдан = $fields['passport_issued'];
        if (isset($fields['passport_date']))
            $update->ПаспортДатаВыдачи = date('Ymd000000', strtotime($fields['passport_date']));
            
            
        //  Данные о работе
        if (isset($fields['work_scope']))
            $update->ОрганизацияСфераДеятельности = $fields['work_scope'];
        if (isset($fields['profession']))
            $update->ОрганизацияДолжность = $fields['profession'];
        if (isset($fields['work_phone']))
            $update->ОрганизацияТелефон = $this->format_phone($fields['work_phone']);
        if (isset($fields['workplace']))
            $update->ОрганизацияНазвание = $fields['workplace'];
        if (isset($fields['workdirector_name']))
            $update->ОрганизацияФИОРуководителя = $fields['workdirector_name'];
        if (isset($fields['income_base']))
            $update->ОрганизацияЕжемесячныйДоход = $fields['income_base'];
            
        if (isset($fields['workaddress']))
            $update->ОрганизацияАдрес = $fields['workaddress'];
            

        // Контактные лица
        if (isset($fields['contactpersons']))
        {
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($fields['contactpersons']);echo '</pre><hr />';
            $update->КонтактныеЛица = ($fields['contactpersons']);
        }
        
        $user = $this->users->get_user_by_uid($uid);
        
        $z->TextJson = json_encode($update, JSON_UNESCAPED_UNICODE);
        $z->ChangeServices = 0;
        $z->ServicesSMS = $user->service_sms;
        $z->ServicesInsure = $user->service_insurance;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetChangingFields', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetChangingFields', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetChangingFields', (array)$z, (array)$returned);
        
		return $returned->return;    	
    
    }

    /**
     * Get document by type
     * @param string $zaimNumber
     * @param string $type {
     *      'Договор',
     *      'ПП',
     *      'Заявление о предоставление микрозайма',
     *      'Прочие сведения',
     *      'Согласие на ОПД',
     *      'Согласие на РПД',
     *      'Анкета',
     *      'Согласие БКИ'
     *  };
     * @return mixed
     */
    public function getDocument(string $zaimNumber, string $type)
    {
        if (empty($zaimNumber) || empty($type)) {
            return [];
        }
        $params = [
            'Номер' => $zaimNumber,
            'Тип' => $type
        ];

        $object = $this->generateObject($params);
        $response = $this->requestSoap($object, 'Tinkoff', 'ContractRepositoryType');

        return $response['response'] ?? $response;
    }

    public function get_documents($number)
    {
		if(!empty($number))
		{
            $z = new stdClass(); 
			$z->Номер = $number;

            $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl ContractRepository', (array)$z);

            try {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
                $returned = $client->__soapCall('ContractRepository',array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                throw $fault;
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl ContractRepository', (array)$z, (array)$returned);
            
            if (!empty($returned->return))
    			return json_decode($returned->return);
            else
                return $returned;
		}
		else
			return false;
    	
    }
    
    
    
    public function change_order_services($order_id_1c, $uid)
    {
        $z = new StdClass();
        $z->Uid = $uid;
        $z->НомерЗаявки = $order_id_1c;
        $z->ChangeServices = 1;
        $z->ServicesSMS = 0;
        $z->ServicesInsure = 1;
        $z->TextJson = '{}';

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetChangingFields', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetChangingFields', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetChangingFields', (array)$z, (array)$returned);
        
		return $returned->return;    	
    }

    /**
     * @param $order_id_1c
     * @param $uid
     * @param $insure
     *
     * @return mixed
     */
    public function change_order_insure($order_id_1c, $uid, $insure) {
        $z = (object)[
            'Uid' => $uid,
            'НомерЗаявки' => $order_id_1c,
            'ChangeServices' => 1,
            'ServicesSMS' => 0,
            'ServicesInsure' => $insure,
            'TextJson' => '{}',
        ];

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetChangingFields', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetChangingFields', [$z]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetChangingFields', $z, (array)$returned);

        return $returned->return;
    }

    public function change_order_insure_new_flow($order_id_1c, $insure) {
        //$z = [
        //    'Uid' => (string) $uid,
        //    'НомерЗаявки' => (string) $order_id_1c,
        //    'ChangeServices' => (int)1,
        //    'ServicesSMS' => (int)0,
        //    'ServicesInsure' =>(int) $insure,
        //    'TextJson' => '{}',
        //];

        $z = new StdClass();
        $z->НомерЗаявки = (string) $order_id_1c;
        $z->Insure = (int) $insure;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetInsure', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetInsure',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetInsure', $z, (array)$returned);

        return $returned->return;
    }

    public function get_quantity_loans($uid)
    {
		if(!empty($uid))
		{
            $inn_arr =  $this->organizations->get_site_inns();
            if (count($inn_arr) < 1){
                return false;
            }

            $z = new stdClass(); 
			$z->UID = $uid;
            $z->Partner = 'Boostra';
            $z->ArrayINN = json_encode($inn_arr, false);

            $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl QuantityLoansINN', (array)$z);

            try {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                $returned = $client->__soapCall('QuantityLoansINN',array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                throw $fault;
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl QuantityLoans', (array)$z, (array)$returned);
            
            if (!empty($returned->return))
    			return json_decode($returned->return);
            else
                return $returned;
		}
		else
			return false;
    	
    }
    
    
    /**
     * Soap1c::get_client_details()
     * Возвращает данные клиента по уид
     * 
     * @param mixed $uid
     * @return
     */
    public function get_client_details($uid)
    {
		if(!empty($uid))
		{
            $inn_arr =  $this->organizations->get_inns_by_site_id($this->config->site_id);
            if (count($inn_arr) < 1){
                return false;
            }

            $z = new stdClass();
			$z->UID = $uid;
            $z->ArrayINN = json_encode($inn_arr, false);

            $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl Details', (array)$z);

            try {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
                $returned = $client->__soapCall('Details',array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                throw $fault;
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl Details', (array)$z, (array)$returned);
            
            if (!empty($returned->return))
    			return json_decode($returned->return);
            else
                return $returned;
		}
		else
			return false;
    	
    }
    
    
    
	/**
	 * Soap1c::get_uid_images()
	 * Возвращает уиды изображений из хранилища привязанных к клиенту
	 * @param mixed $uid
	 * @return
	 */
	public function get_uid_images($uid)
	{
		if(!empty($uid))
		{
            $z = new stdClass(); 
			$z->UID = $uid;

            if(strpos(strtolower(php_sapi_name()), 'cli') === false) {
                $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl GetUidFoto', (array)$z);

                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                $returned = $client->__soapCall('GetUidFoto',array($z));
            } else {
                try {
                    $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                    $returned = $client->__soapCall('GetUidFoto',array($z));

                    $this->automationFails->setSoapError(false);
                } catch (SoapFault $fault) {
                    $this->automationFails->setSoapError(true);

                    $returned = $fault;
                } catch (Exception $fault) {
                    $returned = $fault;
                }
            }
            
            $log = 1;
            if (!empty($log))
                $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl GetUidFoto', (array)$z, (array)$returned, 'uid_photo.txt');
            
            if (!empty($returned->return))
    			return json_decode($returned->return);
            else
                return $returned;
		}
		else
			return false;

	}

    /**
     * Soap1c::add_client_authorized()
     * Передаем авторизации клиентов в кабинете
     * 
     * @param string $uid
     * @return void
     */
    public function add_client_authorized($uid)
    {
        date_default_timezone_set('Europe/Samara');
        $datetime_format = date('YmdHis');

        $z = new stdClass(); 
		$z->UID = $uid;
		$z->Time = $datetime_format;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl TimeLK', (array)$z, 'cards.txt');

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
            $returned = $uid_client->__soapCall('TimeLK', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl TimeLK', (array)$z, (array)$returned, 'cards.txt');
        
		return $returned->return;    	
    }

    /**
     * Soap1c::get_client_state()
     * Проверяет сушествование клиента в базе 1с по ФИО
     * @param mixed $lastname
     * @param mixed $firstname
     * @param mixed $patronymic
     * @param mixed $birth
     * @return
     */
    public function get_client_state($lastname, $firstname, $patronymic, $birth)
    {
        date_default_timezone_set('UTC');
        $birth_format = date('Ymd', strtotime($birth));

        $inn_arr =  $this->organizations->get_site_inns();
        if (count($inn_arr) < 1){
            return NULL;
        }

        $z = new stdClass(); 
		$z->Фамилия = $lastname;
		$z->Имя = $firstname;
		$z->Отчество = $patronymic;
		$z->ДатаРождения = $birth_format;
		$z->Пароль = $this->settings->api_password;
        $z->ArrayINN = json_encode($inn_arr, false);

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl VozvratStateINN', (array)$z);

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $uid_client->__soapCall('VozvratStateINN',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl VozvratState', (array)$z, (array)$returned);

		return $returned->return;    	
    }
    
    
    /**
     * Soap1c::get_statement_prolongation()
     * 
     * получает список документов для ознакомления с пролонгацией
     * 
     * @param string $loan_number - 
     * @return
     */
    public function get_statement_prolongation($loan_number)
    {
        $z = new stdClass(); 
		$z->Number = $loan_number;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl StatementProlongation', (array)$z);

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $uid_client->__soapCall('StatementProlongation',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl StatementProlongation', (array)$z, (array)$returned);
		return json_decode($returned->return);
        
        
    }
    
    public function get_statement_prolongation_base64($loan_number)
    {
        $z = new stdClass(); 
		$z->Number = $loan_number;

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $uid_client->__soapCall('StatementProlongationBase64',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl StatementProlongationBase64', (array)$z, $fault->getMessage());

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl StatementProlongationBase64', (array)$z, (array)$returned);
		return json_decode($returned->return);
    }

    /**
     * Soap1c::get_user_credits()
     * Вовращает список займов клиента по уид
     * 
     * @param string $uid_1c
     * @return
     */
    public function get_user_credits($uid_1c)
    {
		if(!empty($uid_1c))
		{
            $inn_arr =  $this->organizations->get_site_inns();
            if (count($inn_arr) < 1){
                return false;
            }

            $z = new stdClass(); 
			$z->UID = $uid_1c;
            $z->Partner = 'Boostra';
            $z->ArrayINN = json_encode($inn_arr, false);

            if(strpos(strtolower(php_sapi_name()), 'cli') === false) {
                $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl HistoryZaimINN', (array)$z, 'loan_history.txt');

                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                $returned = $client->__soapCall('HistoryZaimINN',array($z));
            } else {
                try {
                    $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                    $returned = $uid_client->__soapCall('HistoryZaimINN',array($z));

                    $this->automationFails->setSoapError(false);
                } catch (SoapFault $fault) {
                    $this->automationFails->setSoapError(true);

                    $returned = $fault;
                } catch (Exception $fault) {
                    $returned = $fault;
                }
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl HistoryZaim', (array)$z, (array)$returned, 'loan_history.txt');
            
			return json_decode($returned->return);
		}
		else
			return false;
	}
    
    
    /**
     * Soap1c::get_user_history()
     * Возвращает заявки клиента по уид
     * 
     * @param string $uid_1c
     * @param bool $log
     * @return
     */
    public function get_user_history($uid_1c, $log = true)
	{
        $inn_arr =  $this->organizations->get_site_inns();
        if (count($inn_arr) < 1){
            return false;
        }

		if(!empty($uid_1c))
		{
            $z = new stdClass(); 
			$z->UID = $uid_1c;
            $z->Partner = 'Boostra';
            $z->ArrayINN = json_encode($inn_arr, false);

            $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl HistoryINN', (array)$z);

            try {
                $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                $returned = $uid_client->__soapCall('HistoryINN',array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                throw $fault;
            }

            if (!empty($log))
                $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl History', (array)$z, (array)$returned);
            
			return json_decode($returned->return);
		}
		else
			return false;

	}
    
    public function credit_registration($loan_number, $sms_code, $card_id, $insurer, $insure_amount = 0)
    {
        $z = new stdClass(); 
		$z->НомерЗаявки = $loan_number;
        $z->CardId = $card_id;
        $z->КодСМС = $sms_code;
        $z->insurer = $insurer;
        $z->СтавкаСтраховки = $insure_amount;
        $z->ПрименитьСтавкуСтраховки = 1;

        $order = $this->orders->get_order_by_1c($loan_number);
        $user = $this->users->get_user($order->user_id);
        $z->inn = empty($user->inn) ? '' : $user->inn;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOtvetZayavki.1cws?wsdl CreditRegistration', (array)$z, 'payment.txt');

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOtvetZayavki.1cws?wsdl");
            $returned = $uid_client->__soapCall('CreditRegistration',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOtvetZayavki.1cws?wsdl CreditRegistration', (array)$z, (array)$returned, 'payment.txt');
        
		if ($returned->return == 'OK')
        {
            return $returned;
        }
        else
        {
            if ($decode = json_decode($returned->return))
                return (object)array('return' => $decode->Message);
            else
                return $returned;
        }
        
        
        
    }
    	
    /**
     * Soap1c::delete_user()
     * удаление аккаунта
     * 
     * @param mixed $uid
     * @param integer $deleting
     * @return
     */
    public function delete_user($uid, $deleting = 1)
    {
        $inn_arr =  $this->organizations->get_site_inns();
        if (count($inn_arr) < 1){
            return NULL;
        }

		$z = new StdClass();
        
		$z->UID = $uid;
		$z->Deleting = $deleting;
        $z->ArrayINN = json_encode($inn_arr, false);

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl DeleteLKINN', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('DeleteLKINN', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl DeleteLK', (array)$z, (array)$returned);
        
		return $returned;
    	
    }
    
    
    //ServicesOnlain
    /**
     * Soap1c::get_service_clients()
     * Получает из 1С список для передач чеков по страховкам
     * @return
     */
    public function get_service_clients($partner = 'Boostra')
    {        
		$z = new StdClass();
        
        $z->Partner = $partner;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('ServicesOnlain', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl ServicesOnlain', (array)$z, (array)$returned, 'service.log');

        if (!empty($returned->return))
            return json_decode($returned->return);
        
    }
    
    /**
     * Soap1c::get_service_return_clients()
     * Получает из 1С список для передач чеков по возвратам
     * @return
     */
    public function get_service_return_clients($partner = 'Boostra')
    {
		$z = new StdClass();
        
        $z->Partner = $partner;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('ServicesOnlainVozvrat', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl ServicesOnlainVozvrat', (array)$z, (array)$returned, 'service.log');

        if (!empty($returned->return))
            return json_decode($returned->return);
        
    }
    
    
    /**
     * Soap1c::auto_debiting()
     * Включает/отключает автосписание с карты
     * 
     * @param mixed $uid
     * @param mixed $card_id
     * @param mixed $status
     * @return
     */
    public function auto_debiting($uid, $card_id, $status)
    {
        $z = new StdClass();
        
		$z->UID = $uid;
		$z->CardID = $card_id;
        //отправляем всегда ложь. Временно. По просьбе Александра
		$z->Status = 0;//$status;
        $z->Partner = 'Boostra';        

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl StatusAutoDebiting', (array)$z, 'cards.txt');

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('StatusAutoDebiting', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }
		
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl StatusAutoDebiting', (array)$z, (array)$returned, 'cards.txt');
        
		return $returned;
    	
    }
    
    /**
     * Soap1c::get_card_list()
     * Получает список доступных кард пользователя
     * 
     * @param mixed $uid
     * @return
     */
    public function get_card_list($uid)
    {
    	$z = new stdClass();		
    	$z->UID = $uid;
        $z->Partner = 'Boostra';        
		
        if (empty($z->UID) || $z->UID == 'error')
        {
            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetCardList', (array)$z, 'ERROR UID. NOT SEND', 'cards.txt');
            return false;
        }

        if(strpos(strtolower(php_sapi_name()), 'cli') === false) {
            $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetCardList', (array)$z, 'cards.txt');

            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('GetCardList',array($z));
        } else {
            try {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
                $returned = $client->__soapCall('GetCardList',array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                $returned = $fault;
            } catch (SoapFault $fault) {
                $returned = $fault;
            }
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetCardList', (array)$z, (array)$returned, 'cards.txt');

		return json_decode($returned->return);
	}
    
    /**
     * Soap1c::get_1c_statuses()
     * Получает список статусов по списку договоров
     * 
     * @param mixed $uid
     * @return
     */
    public function get_1c_statuses(array $orders)
    {
        $r = [];
        foreach($orders as $order) {
            $r[] = ['НомерЗаявки' => $order->id_1c, 'СтатусЗаявки' => $order->status_1c];
        }
		
    	$z = new stdClass;
    	$z->TextJson = json_encode($r, JSON_UNESCAPED_UNICODE);

        if(strpos(strtolower(php_sapi_name()), 'cli') === false) {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('MasRequestStatus', [$z]);
        } else {
            try {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
                $returned = $client->__soapCall('MasRequestStatus', [$z]);

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                $returned = $fault;
            }
        }

//        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl MasRequestStatus', (array)$z, (array)$returned, 'update_statuses.txt');

		return json_decode($returned->return);
	}
    
    
    /**
     * Soap1c::send_loan()
     * Функция отправки заявки в 1с
     * 
     * @param mixed $z
     * @return
     */
    public function send_loan($z)
    {
    	// Очистим телефон от лишних символов
		$replace = array('(',')',' ','-');
		$z->phone_mobile = str_replace('+7','8',str_replace($replace, '', $z->phone_mobile));
		$z->passport_serial = str_replace(' ','', $z->passport_serial);
//		$z->СогласиеНаУступку  = 1;
        $z->УИД = isset($z->УИД) ? $z->УИД : '';
        $z->ТекстЗапроса = json_encode($z, JSON_UNESCAPED_UNICODE);

        if (empty($z->ДатаЗаявки))
            $z->ДатаЗаявки = date('YmdHis');
        
        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetZayavkiFull', (array)$z);
                
		try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetZayavkiFull', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
			$returned = $fault;
		}
		
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetZayavkiFull', (array)$z, (array)$returned, 'orders.txt');

        sleep(3);

		return $returned;
    }

    
    /**
     * Soap1c::get_contract()
     * Получает названия pdf файлов без активного договора
     * 
     * @param string $number - номер договора клиента
     * @return
     */
    public function get_contract_bki($uid, $asp)
    {
    	$z = new StdClass();
        $z->UID = $uid;
        $z->ASP = $asp;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl ContractBKI', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('ContractBKI',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl ContractBKI', (array)$z, (array)$returned);

		return json_decode($returned->return);
    	
    }

    public function get_contract_bki_base64($uid, $asp)
    {
        $base_organization = $this->organizations->get_base_organization();
        
    	$z = new StdClass();
        $z->UID = $uid;
        $z->ASP = $asp;
        $z->OrgINN = $base_organization->inn;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('ContractBKIBase64',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

		return json_decode($returned->return);    	
    }

    /**
     * Soap1c::get_contract()
     * Получает названия pdf файлов договора и др документов по кредиту
     * 
     * @param string $number - номер договора клиента
     * @return
     */
     
    public function get_contract($number)
    {
    	$z = new StdClass();
        $z->Номер = $number;
    
        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl Contract', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('Contract',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl Contract', (array)$z, (array)$returned);

		return json_decode($returned->return);
    	
    }

    /**
     * Credit Doctor Penalty
     * Soap1c::return_SO()
     *
     * @param array $params
     * @return object
     */
    public function send_addon_operation(array $params)
    {
        $ArraySO = new StdClass();
        $ArraySO->LoanNumber = $params['loan_number'];
        $ArraySO->AdditionalService = $params['additional_service'];
        $ArraySO->Operation = $params['operation'];

        $z = new stdClass();
        $z->TextJson = json_encode($ArraySO, JSON_UNESCAPED_UNICODE);
        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $client->__soapCall('AddonOperation', array($z));
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl AddonOperation', (array) $z, (array) $returned, 'AddonOperation.txt');
        return empty($returned->return) ? $returned : ($returned->return);

    }
    
    
    /**
     * Soap1c::send_payment_from_attach_card()
     * Отправка платежа в 1с с привязанной карты
     * 
     * @param int $card_id - Идентификатор привязанной карты
     * @param int $summ - Сумма платежа в копейках
     * 
     * @return json_string
     */
    public function send_payment_from_attach_card($card_id, $summ, $uid)
    {
        $z = new StdClass();
        $z->CardID = $card_id;
        $z->Сумма = $summ;
        $z->UID = $uid;
        $z->Organization = 'Boostra';
    
        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOplata.1cws?wsdl GetOplataCard', (array)$z, 'payment.txt');

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOplata.1cws?wsdl");
            $returned = $client->__soapCall('GetOplataCard',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOplata.1cws?wsdl GetOplataCard', (array)$z, (array)$returned, 'payment.txt');
        $this->addLogMethod(__METHOD__, $z, $returned, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOplata.1cws?wsdl GetOplataCard', ['function_args' => func_get_args()]);

        return $returned;

    }
    
    /**
     * Soap1c::send_payment_result()
     * Отправка результата оплаты в 1с
     * 
     * @param string $uid - UID клиента из  1с
     * @param int $summ - Сумма платежа в копейках
     * @param int $payment_id - Идентификатор платежа
     * 
     * @return json string
     */
    public function send_payment_result($transaction, $authorized = 0)
    {
        $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOplata.1cws?wsdl");

        switch ($transaction->payment_type)
        {
            case 'credit_rating':
                return $this->send_credit_rating_payment_result($transaction, $client);
            default:
                return $this->send_debt_payment_result($transaction, $authorized, $client);
        }
    }
    
    /**
     * Soap1c::remove_card()
     * Удаление карты
     * 
     * @param string $card_id
     * @param string $user
     * @return object
     */
    public function remove_card($card_id, $user)
    {
    	if (empty($card_id))
            return NULL;
            
        $z = new StdClass();
    	$z->UID = $user->uid;
    	$z->CardID = $card_id;
        $z->Пароль = '7b2e1ed80f2956cd8e00fb3f0b22ad2224f80d8e';        
        $z->Partner = 'Boostra';        

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl RemoveCard', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('RemoveCard',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl RemoveCard', (array)$z, (array)$returned);
        
		return $returned->return;
    }

    /**
     * Soap1c::get_uid_by_phone()
     * Получение УИД по номеру телефона клиента
     *
     * @param string $phone
     * @return stdClass | null
     */
    public function get_uid_by_phone_old($phone)
    {
    	if (empty($phone))
            return NULL;

        $inn_arr =  $this->organizations->get_site_inns();
        if (count($inn_arr) < 1){
            return NULL;
        }
            
        $z = new StdClass();
        $z->Телефон = $phone;
    	$z->Пароль = $this->settings->api_password;
        $z->ArrayINN = json_encode($inn_arr, false);
    
  		$replace = array('(',')',' ','-');
        $phone_normalized = str_replace($replace, '', $phone);
        $phone_normalized = preg_replace('/^\+7/', '8', $phone_normalized);
        $z->Телефон = $phone_normalized;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl SearchTel', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('SearchTel',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl SearchTel', (array)$z, (array)$returned, 'search_tel.txt');

		return isset($returned->return) ? json_decode($returned->return) : null;
    }

    /**
     * Soap1c::get_uid_by_phone()
     * Получение УИД по номеру телефона клиента
     *
     * @param string $phone
     * @return stdClass | null
     */
    public function get_uid_by_phone($phone)
    {
        if (empty($phone)){
            return NULL;
        }

        $inn_arr =  $this->organizations->get_site_inns();
        if (count($inn_arr) < 1){
            return NULL;
        }

        $z = new StdClass();
        $z->Телефон = $phone;
        $z->Пароль = $this->settings->api_password;
        $z->ArrayINN = json_encode($inn_arr, false);

        $replace = array('(',')',' ','-');
        $z->Телефон = str_replace('+7','8',str_replace($replace,'',$z->Телефон));

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl SearchTelINN', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('SearchTelINN', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl SearchTelINN', (array)$z, (array)$returned, 'search_tel_by_inn.txt');

        return isset($returned->return) ? json_decode($returned->return) : null;
    }


    /**
     * Soap1c::send_doubling_phone()
     * Отправляет телефон в клиентский центр для решения проблемы с дублированием, 
     * 
     * @param string $phone
     * @return string
     */
    public function send_doubling_phone($phone)
    {
        $z = new StdClass();
        $z->Телефон = $phone;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl PhoneAccessory', (array)$z);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('PhoneAccessory',array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl PhoneAccessory', (array)$z, (array)$returned);

		return json_decode($returned->return);
   	
    }
    
    public function soap_send_files($user_id, $debug = false)
    {
        if ($user = $this->users->get_user((int)$user_id))
        {
            
            $z = new StdClass();
            
           	$z->UID = $user->uid;
        	$z->Пароль = $this->settings->api_password;

            $files = $this->users->get_files(array('user_id'=>$user_id, 'status'=>0));
            $files_dir = $this->config->root_url.'/'.$this->config->original_images_dir;
            foreach ($files as $kk => $file)
            {
                if (file_exists($this->config->root_dir.$this->config->original_images_dir.$file->name))
                {
                    $file->url = $files_dir.$file->name;
                    if ($file->type == 'passport1' || $file->type == 'passport2' || $file->type == 'passport3' || $file->type == 'passport4')
                        $file->type == 'passport';                                        
                    $file->url = str_replace('https://', 'http://', $file->url);

                    /*загружаем в хрвнилище
                    $storage_uid = $this->filestorage->upload_file($file->url);
                    if ($storage_uid) {
                        $file->storage_uid = $storage_uid;
                        //запоминаем юид
                        $this->users->update_file($file->id, ['storage_uid' => $storage_uid]);
                    }
                    */
                }
                else
                {
                    $this->users->update_file($file->id, array('status'=>3));
                    unset($files[$kk]);
                }
            }
                                            
            if (empty($files))
                return 'sent';
            
            $z->Files = json_encode($files, JSON_UNESCAPED_UNICODE);

            $method = __METHOD__;
            $soapUrl = $this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl";
            $soapCall = 'RequestFiles';

            try {
                $this->setLoggerState($method, $soapUrl . ' ' . $soapCall, (array)$z);
                $client = new SoapClient($soapUrl);
                $returned = $client->__soapCall($soapCall, array($z));
                $this->logging($method, $soapUrl . ' ' . $soapCall, (array)$z, (array)$returned);

                $this->automationFails->setSoapError(false);
            } catch (Exception $e) {
                $this->automationFails->setSoapError(true);
                $this->queue->add($method, $soapUrl, $soapCall, $z);
                $returned = new stdClass;
                $returned->return = 'OK';
            }

    		if($debug)
            {            
        		echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($z, $returned );echo '</pre><hr />';
            }
    		return $returned;
        }
    }

    public function send_credit_doctor_order($db_order, $sms_code)
    {
        $order = new StdClass();
        $order->НомерЗаявки = $db_order->id_1c;
        $order->Дата = DateTime::createFromFormat('Y-m-d H:i:s', $db_order->accept_date)->format('YmdHis');
        $order->Сумма = $db_order->amount;
        $order->ПроцентСтавка = $db_order->percent;
        $order->Менеджер = '';
        $order->УИД_Займ = '';
        $order->УИД_Заявка = '';
        $order->agrid = ''; // агрид скористы
        $order->КодСМС = $sms_code;

        $contract_number = 'Б'.date('y', strtotime($db_order->date)).'-';
        if ($db_order->id > 999999)
            $contract_number .= $db_order->id;
        else
            $contract_number .= '0'.$db_order->id;
        $order->Номер = $contract_number;

        $loan_first_payment = (new DateTime("+30 days {$db_order->date}"))->format("YmdHis");
        $loan_second_payment = (new DateTime("+60 days {$db_order->date}"))->format("YmdHis");
        $loan_last_day = (new DateTime("+90 days {$db_order->date}"))->format("YmdHis");
        $order->График = [
            [
                'Дата' => $loan_first_payment,
                'Сумма' => 3000,
                'СуммаПроцентов' => 266.30,
            ],
            [
                'Дата' => $loan_second_payment,
                'Сумма' => 3000,
                'СуммаПроцентов' => 177.53,
            ],
            [
                'Дата' => $loan_last_day,
                'Сумма' => 3000,
                'СуммаПроцентов' => 88.77,
            ],
        ];

        $url = $this->config->url_1c . $this->config->work_1c_db . "/ws/WebCRM.1cws?wsdl";
        $method = 'KD';

        $request_data = new StdClass();
        $request_data->ArrayContracts = json_encode([$order], JSON_UNESCAPED_UNICODE);

        $this->setLoggerState(__METHOD__, "$url $method", (array)$request_data);

        try {
            $stat_z_client = new SoapClient($url);
            $result = $stat_z_client->__soapCall($method, [$request_data]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, "$url $method", $request_data, (array)$result);

        return $result;
    }

    /**
     * Soap1c::format_phone()
     * Форматирует номер телефона в формат принимаемый 1с
     * формат 8(ххх)ххх-хх-хх
     * 
     * @param string $phone
     * @return string $format_phone
     */
    public function format_phone($phone)
    {
        if (empty($phone))
            return '';
        
        $replace_params = array('(', ')', ' ', '-', '+');
        $clear_phone = str_replace($replace_params, '', $phone);
        
        $substr_phone = substr($clear_phone, -10, 10);
        $format_phone = '8('.substr($substr_phone, 0, 3).')'.substr($substr_phone, 3, 3).'-'.substr($substr_phone, 6, 2).'-'.substr($substr_phone, 8, 2);

        return $format_phone;
    }

    public function send_credit_rating_payment_result($transaction, $soap_client)
    {
        $z = new StdClass();
        $z->УИД = $transaction->uid;
        $z->Сумма = $transaction->amount;
        $z->PaymentId = $transaction->payment_id;
        $z->Agrid = '';
        $z->mfoAgreement = $transaction->order_id;
        $z->ДатаОплаты = date('YmdHis', strtotime($transaction->created));

        $scorista_score = $this->scorings->get_last_scorista_for_user($transaction->user_id, true);
        if ($scorista_score && isset($scorista_score->scorista_ball) && $scorista_score->scorista_ball)
        {
            $z->Agrid = $scorista_score->scorista_ball;
        }

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOplata.1cws?wsdl GetOplataUID', (array)$z, 'payment.txt');

        try {
            $returned = $soap_client->__soapCall('GetOplataUID_KR', [$z]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOplata.1cws?wsdl GetOplataUID', (array)$z, (array)$returned, 'payment.txt');

        return $returned;
    }

    private function send_debt_payment_result($transaction, $authorized, $soap_client)
    {
        $z = new StdClass();
        $z->УИД = $transaction->uid;
        $z->Сумма = $transaction->amount;
        $z->PaymentId = $transaction->payment_id;
        $z->CodeSMS = $transaction->code_sms;
        $z->Prolongation = $transaction->prolongation;
        $z->mfoAgreement = $transaction->order_id;
        $z->СуммаСтраховка = floatval($transaction->insure_amount);
        $z->Partner = 'Boostra';
        $z->СтраховкаНаИП = 0;
        $z->StatusAUTHORIZED = (int)$authorized;
        $z->ПролонгацияБеспроцентногоЗайма = 0;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOplata.1cws?wsdl GetOplataUID', (array)$z, 'payment.txt');

        try {
            $returned = $soap_client->__soapCall('GetOplataUID', [$z]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOplata.1cws?wsdl GetOplataUID', (array)$z, (array)$returned, 'payment.txt');

        return $returned;
    }

    /**
     * Check json data
     * @param $string
     * @return bool
     */
    public function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Обобщенный метод для запросов к SOAP
     * @param $object
     * @param $service
     * @param $method
     * @param string $logfile
     * @return array
     */
    public function requestSoap($object, $service, $method, string $logfile = 'soap.txt')
    {
        $result = [];

        try {
            $client = new \SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/" . $service . ".1cws?wsdl");
            $returned = $client->__soapCall($method, [$object]);
            if ($this->isJson($returned->return)) {
                $result['response'] = json_decode($returned->return, true);
            } else {
                $result['response'] = $returned->return;
            }
            $this->automationFails->setSoapError(false);
        } catch (\SoapFault $fault) {
            $this->automationFails->setSoapError(true);
            $result['errors'] = $fault->getMessage();

            $filename = pathinfo($logfile, PATHINFO_FILENAME);
            $this->open_search_logger->create("Ошибка вызова метода 1с $method в сервисе $service", [
                'object_1c' => $object,
                'method_1c' => $method,
                'service_1c' => $service,
                'error' => $fault->getMessage(),
            ], $filename ?: 'soap', \OpenSearchLogger::LOG_LEVEL_ERROR, '1c');
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . "/ws/" . $service . ".1cws?wsdl " . $method, (array)$object, $result, $logfile);
        return $result;
    }

    /**
     * Генерируем объект
     * @param $data
     * @return \StdClass
     */
    public function generateObject($data = [])
    {
        $object = new \StdClass();
        foreach ($data as $label => $value) {
            $object->{$label} = $value;
        }
        return $object;
    }

    /**
     * Отправляет данные о кредитной регистрации ФД в 1C через SOAP
     * @param int $order_id Идентификатор заказа (OrderID)
     * @param int $agree_claim_value Флаг отказа от уступки права требования (ОтказОтУступкиПраваТребования)
     * @return mixed Ответ от SOAP-сервиса
     */
    public function sendCreditRegistrationFD(int $order_id, int $agree_claim_value)
    {
        $params = [
            'ФД' => [
                'OrderID' => $order_id,
                'ОтказОтУступкиПраваТребования' => $agree_claim_value
            ],
        ];

        $object = $this->generateObject($params);
        $response = $this->requestSoap($object, 'WebOtvetZayavki', 'CreditRegistrationKD', 'payment.txt');

        return $response['response'] ?? $response;
    }

    /**
     * Отправляет запрос на формирование заявления по мультиполису в 1С
     * @param $UID
     * @return array|mixed
     */
    public function sendMultipolisContract($UID)
    {
        $params = [
          'UID' => $UID,
          'ДатаСтраховки' => date('YmdHis'),
        ];

        $object = $this->generateObject($params);
        $response = $this->requestSoap($object, 'WebCRM', 'multipolis', 'multipolis_contract.txt');

        return $response['response'] ?? $response;
    }

    /**
     * Получает несколько балансов из 1С
     * необходимо когда открыто несколько займов одновременно
     * @param string $uid
     * @return array|mixed
     */
    public function get_user_balances_array_1c(string $uid)
    {
        $inn_arr =  $this->organizations->get_site_inns();
        if (count($inn_arr) < 1){
            return NULL;
        }

        $object = $this->generateObject(
            [
                'UID' => $uid,
                'Пароль' => $this->settings->api_password,
                'Partner' => 'Boostra',
                'ArrayINN' => json_encode($inn_arr, false),
            ]
        );

        $result = $this->requestSoap($object, 'WebLK', 'GetLKMass', 'get_lk_mass.txt');
        return $result['response'] ?? $result;
    }

    /**
     * Переводит заявку в Тех - отказ в 1С
     * @param $order_id_1c
     * @return Exception|mixed
     */
    public function set_tehokaz($order_id_1c)
    {
        $z = new StdClass();
        $z->НомерЗаявки = $order_id_1c;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('TehOtkaz', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl TehOtkaz', (array)$z, (array)$returned);
        return empty($returned->return) ? $returned : ($returned->return);
    }

    public function get_acado_uid($passport_series, $passport_number)
    {
        $z = new StdClass();
        $z->Серия = $passport_series;
        $z->Номер = $passport_number;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('ПоискПоПаспорту', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl ПоискПоПаспорту', (array)$z, (array)$returned, 'acado.txt');
        return empty($returned->return) ? $returned : ($returned->return);
        
    }

    /**
     * Цессии по номеру договора
     * @param $LoanNumber
     * @return array
     */
    public function NoticeOfAssignment($LoanNumber)
    {
        try {
            $request = [
                'LoanNumber' => $LoanNumber,
            ];
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('NoticeOfAssignment', array($request));
            $response = (array) $response;

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $response['error'] = $fault->getMessage();
        } catch (Exception $exception) {
            $response['error'] = $exception->getMessage();
        }

        $responseToLog = array();
        $responseToLog['return'] = substr($response['return'] ?? '',50);
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl NoticeOfAssignment', $request, $responseToLog);

        return $response;
    }

    /**
     * @param $payments
     * @return mixed
     * @throws SoapFault
     */
    public function send_refuser_payments($payments)
    {
        $items = array();
        foreach ($payments as $payment) {
            $item = new StdClass();

            $item->PaymentDate = empty($payment->operation_date) ? '' : date('YmdHis', strtotime($payment->operation_date));
            $item->PaymentNumber = $payment->id;
            $item->CardPan = $payment->card_pan;
            $item->RegisterID = $payment->register_id;
            $item->OperationID = $payment->operation_id;
            $item->Amount = (float)$payment->amount;
            $item->SBP = empty($payment->is_sbp) ? 0 : 1;
            $item->НомерСектора = $payment->sector;

            $organization = $this->organizations->get_organization($payment->organization_id);
            $order = $this->orders->get_order($payment->order_id);

            $item->INNOrganization = $organization->inn;
            $item->OrderNumber = $order->id_1c; //НомерЗаявки

            $user = $this->users->get_user((int)$payment->user_id);
            $item->UserUID = $user->uid;

            $items[] = $item;
        }

        $request = new StdClass();
        $request->TextJSON = json_encode($items);

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl PaymentRefuser', (array)$request, 'b2p_payment.txt');

        try {
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $stat_z_client->__soapCall('PaymentRefuser', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl PaymentRefuser', (array)$request, (array)$returned, 'b2p_payment.txt');

        return $returned;
    }

    public function send_order_manager($order_id_1c, $manager_name_1c)
    {
        $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");

        $z = new stdClass();
        $z->НомерЗаявки = $order_id_1c;
        $z->Сотрудник = $manager_name_1c;

        try {
            $returned = $stat_z_client->__soapCall('ManagerApplication', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);
            $this->logging(__METHOD__, 'SoapFault', $z, $fault, 'send_order_manager_1c_faults.txt');
            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl ManagerApplication', (array)$z, (array)$returned, 'order_managers.txt');

        return empty($returned->return) ? $returned : ($returned->return);
    }

    public function send_credit_doctor($order_id_1c)
    {
        $z = new stdClass();
        $z->id = $order_id_1c;
        $z->agreement = '';

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $uid_client->__soapCall('ServiceConnection', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl ServiceConnection', (array) $z, (array) $returned, 'ServiceConnection.txt');
    }

    /**
     * Отправляет дополнительный email(ы) в 1С.
     * @param string $userUid UID клиента в 1С
     * @param string|string[] $emails Email или массив email-адресов
     * @return array Ответ 1С
     * @throws InvalidArgumentException
     */
    public function sendAdditionalEmail(string $userUid, $emails): array
    {
        $payload = $this->prepareEmailsPayload($emails);

        $data = $this->soap->generateObject([
            'ContragentUID' => $userUid,
            'Email' => $payload,
        ]);

        return $this->soap->requestSoap($data, 'WebSignal', 'AddEmailFromKI', 'soapAdditionalEmail.txt');
    }

    /**
     * Подготавливает JSON-массив email-адресов для отправки в 1С.
     * @param string|string[] $emails Email или массив email-адресов
     * @return string JSON-массив email'ов
     * @throws InvalidArgumentException
     */
    private function prepareEmailsPayload($emails): string
    {
        if (is_string($emails)) {
            $emails = trim($emails);
            $emails = $emails === '' ? [] : [$emails];
        } else {
            $emails = array_values(array_filter(
                array_map('trim', $emails),
                static fn(string $email): bool => $email !== ''
            ));
        }

        if (empty($emails)) {
            throw new InvalidArgumentException('Attempted to send empty email list to 1C.');
        }

        return json_encode($emails, JSON_UNESCAPED_UNICODE);
    }

    public function sendAdditionalPhone(string $user_uid, string $phone): array
    {
        $data = $this->soap->generateObject([
            'ContragentUID' => $user_uid,
            'PhoneNumber' => $phone,
        ]);
        return $this->soap->requestSoap($data, 'WebSignal', 'AddPhoneNumberFromKI', 'soapAdditionalPhone.txt');
    }

    public function send_aksi($ball, $result, $limit, $order_id = '', $is_scorista = false, $version = '')
    {
        $request = new StdClass();

        $request->СкорБалл = empty($ball) ? '' : (float)$ball;
        $request->Решение = empty($result) ? '' : $result;
        $request->Лимит = empty($limit) ? '' : $limit;
        $request->НомерЗаявки = $order_id;
        $request->НомерЗайма = '';
        $request->ПроскореноАкси = !$is_scorista;
        $request->ПроскореноСкористой = $is_scorista;
        $request->ВерсияАкси = $version;

        $logUrl = $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Aksi';
        try {
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $stat_z_client->__soapCall('Aksi', array($request));

            $this->logging(__METHOD__, $logUrl, (array)$request, (array)$response, 'soapAksi.txt');
            $this->automationFails->setSoapError(false);

            return $response;
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $logUrl .= "\nERROR: " . $e->getMessage();
            $this->logging(__METHOD__, $logUrl, (array)$request, [], 'soapAksi.txt');
            echo "SOAP Error: " . $e->getMessage();
        } catch (Exception $e) {
            $logUrl .= "\nERROR: " . $e->getMessage();
            $this->logging(__METHOD__, $logUrl, (array)$request, [], 'soapAksi.txt');
            echo "SOAP Error: " . $e->getMessage();
        }
    }

    public function send_scorista_id($user_uid, $order_id_1c = '', $scorista_id = '')
    {
        $z = new StdClass();
        $z->UID = $user_uid;
        $z->НомерЗаявки = $order_id_1c;
        $z->Agrid = $scorista_id;
        $z->Organization = 'Boostra';

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
            $returned = $client->__soapCall('ScoristaAgrid', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl ScoristaAgrid', (array)$z, (array)$returned);

        return empty($returned->return) ? $returned : $returned->return;
    }

    public function getRefinancingDocuments($loan_number)
    {
        try {
            $request = [
                'LoanNumber' => $loan_number,
            ];
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('RefinancingDocuments', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $response['error'] = $fault->getMessage();
        } catch (Exception $exception) {
            $response['error'] = $exception->getMessage();
        }

        $responseToLog = array();
        $responseToLog['return'] = substr($response->return ?? '',50);
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl RefinancingDocuments', $request, $responseToLog);

        return $response->return ?? [];
    }

    /**
     * Проверяет, был ли произведен платеж через расчетный счет для указанного договора
     *
     * @param string $loanNumber Номер договора
     * @return bool  true - платеж через расчетный счет был, false - платежа нет или произошла ошибка
     */
    public function checkIfPaidViaSettlementAccount(string $loanNumber): bool
    {
        $data = $this->soap->generateObject([
            'LoanNumber' => $loanNumber
        ]);

        $responseData = $this->soap->requestSoap($data, 'WebSignal', 'checkPaymentByCurrentAccount');
        $response = $responseData['response'];

        if (isset($response['error'])) {
            return false;
        } elseif (isset($response['hasBankPayment'])) {
            return (bool)$response['hasBankPayment'];
        } else {
            return false;
        }
    }
}
