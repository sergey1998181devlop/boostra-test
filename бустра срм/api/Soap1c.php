<?php

class Soap1c extends Simpla
{
    private const SYSTEM_NAME = 'boostra.ru';

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

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl MaxOverdueByClient', (array)$request, (array)$returned, 'MaxOverdueByClient.txt');
        return !isset($returned->return) ? $returned : $returned->return;
    }

    /**
     * Soap1c::send_payments_il()
     * Отсылает данные по оплатам клиентов
     *
     * WebSignal , метод OplataInstalment - принимает оплату по инстолментам
     * параметр ArrayOplata- массив из структур, содержит:
     * Дата - дата оплаты в формате ггггММддЧЧммсс
     * НомерЗайма
     * OperationID
     * OrderID
     * НомерКарты
     * Сумма
     * АСП
     * СуммаЧДП
     * СуммаПДП
     * @param array $payments
     * @return
     */
    public function send_payments_il($payments)
    {
        $items = array();
        foreach ($payments as $payment) {
            $item = new StdClass();

            $item->Дата = empty($payment->operation_date) ? '' : date('YmdHis', strtotime($payment->operation_date));
            $item->НомерЗайма = $payment->contract_number;
            $item->OperationID = $payment->operation_id;
            $item->OrderID = $payment->register_id;
            $item->НомерКарты = $payment->card_pan;
            $item->Сумма = (float)$payment->amount;
            $item->СБП = empty($payment->is_sbp) ? 0 : 1;
            $item->АСП = $payment->asp;
            $item->ИсточникОплаты = $payment->create_from;
            $item->НомерСектора = $payment->sector;
            $item->Скидка = $payment->discount_amount;
            
            $item->НомерОплаты = 'PM' . date('y') . '-' . $payment->id;//обязательно в номере ТИРЕ. длина номера 11 символов!!!

            $boostra_sectors = $this->best2pay->get_boostra_sectors();
            if (in_array($payment->sector, $boostra_sectors)) {
                $organization = $this->organizations->get_organization($this->organizations::BOOSTRA_ID);
            } else {
                $organization = $this->organizations->get_organization($this->organizations::AKVARIUS_ID);
            }
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
                    'ID_ВитаМед' => 'ID_' . $payment->tv_medical->tv_medical_id,
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

            $items[] = $item;
        }

        $request = new StdClass();
        $request->ArrayOplata = json_encode($items, JSON_UNESCAPED_UNICODE);

        try {
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $stat_z_client->__soapCall('OplataInstalment', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl OplataInstalment', (array)$request, (array)$returned, 'b2p_payment_il.txt');
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($items, $returned);
        echo '</pre><hr />';
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

    /**
     * Soap1c::recompense()
     * Возврат допуслуги взаимозачетом
     * WebSignal метод ReturnOfServicesToLoan
     * Параметры ВидУслуги, OperationID, Сумма
     *
     * ВидУслуги - возможные варианты КредитныйДоктор, Мультиполис, Телемедицина
     * OperationID - отправленной услуги
     * Сумма - сумма которая идет в зачет.
     * @param array $params
     * @return object
     */
    public function recompense($params)
    {
        $request = [
            'ВидУслуги' => $params['type'],
            'OperationID' => $params['operation'],
            'Сумма' => $params['amount'],
            'id' => $params['return_transaction_id'],
            'ДатаОперации' => date('YmdHis', strtotime($params['operation_date'])),
        ];

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $uid_client->__soapCall('ReturnOfServicesToLoan', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $response = $fault;
        }
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($request, $response);
        echo '</pre><hr />';
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl ReturnOfServicesToLoan', (array)$request, (array)$response, 'ReturnInsurance.txt');

        return $response;

    }

    /**
     * Soap1c::DataForRecurrentLoansResult()
     *
     * Aleksandr 1С Kislyakov boostra, [19.10.2023 19:20]
     * МассивПортфелей модержит Структуры каждого портфеля, т.е.
     * СтруктураПортфеля, состоит из UID (уид портфеля), и массив займов "Займы"
     *
     * Массив займов содержит Структуры займов из полей "Займ" (номер займа), Результат - массив результатов
     *
     * Результат содержит структуры из "Статус", НомерОплаты (обязателен если оплата), СуммаОплаты - (обязателен если оплата)
     *
     * [
     * {
     * "UID":"345678";
     * "Займы":[
     * {
     * "Займ":"Б23-123544";
     * Результат:[
     * {
     * Статус:Оплата;
     * НомерОплаты:РМ23-00000;
     * СуммаОплаты:1000
     * };
     * {
     * Статус:Оплата;
     * НомерОплаты:РМ23-00001;
     * СуммаОплаты:2000
     * }
     * ]
     * }
     * ]
     * }
     * ]
     * @return
     */
    public function DataForRecurrentLoansResult($list)
    {
        $data = [];
        $data['UID'] = $list->list_uid;
        $data['Займы'] = [];

        foreach ($list->recurrents as $recurrent) {
            $cell = [];
            $item = [
                'Займ' => $recurrent->number,
                'Результат' => []
            ];
            if (empty($recurrent->payments)) {
                if ($recurrent->status == 7) {
                    $cell = ['Статус' => 'Нет карт для списания'];
                } elseif ($recurrent->status == 8) {
                    $cell = ['Статус' => 'Договор не найден'];
                } else {
                    $cell = ['Статус' => 'Не удачно'];
                }

                $item['Результат'][] = $cell;

            } else {
                foreach ($recurrent->payments as $payment) {
                    $cell = [
                        'Статус' => $payment->reason_code == 1 ? 'Оплата' : 'Не удалось списать',
                        'НомерОплаты' => $this->best2pay->get_payment_number($payment),
                        'СуммаОплаты' => $payment->amount,
                        'Card' => $payment->card_pan,
                    ];
                    $item['Результат'][] = $cell;
                }
            }

            $data['Займы'][] = $item;
        }

        $z = new stdClass();
        $z->TextJSON = json_encode([$data], JSON_UNESCAPED_UNICODE);

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('DataForRecurrentLoansResult', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($list->list_uid, $returned);
        echo '</pre><hr />';
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl DataForRecurrentLoansResult', (array)$z, (array)$returned, 'DetailPay.txt');

        return $returned;
    }

    /**
     * Soap1c::DataForRecurrent()
     * Получает Список портфелей для списания
     * @return
     */
    public function DataForRecurrent()
    {
        $z = new stdClass();
        $z->INN = json_encode($this->organizations->get_inn_for_recurrents());

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('DataForRecurrent', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($returned);
        echo '</pre><hr />';
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl DataForRecurrent', (array)$z, (array)$returned, 'DetailPay.txt');

        return $returned;
    }

    /**
     * Soap1c::DataForRecurrentLoans()
     * Получает Список догооворв в портфеле для списания
     * @param mixed $uid
     * @return
     */
    public function DataForRecurrentLoans($uid)
    {
        $z = new stdClass();
        $z->UID = $uid;
        $z->INN = json_encode($this->organizations->get_inn_for_recurrents());

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('DataForRecurrentLoans', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($returned);
        echo '</pre><hr />';
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl DataForRecurrentLoans', (array)$z, (array)$returned, 'DetailPay.txt');

        return $returned;
    }

    public function GetDetailPay($payNumber)
    {
        $z = new stdClass();
        $z->НомерОплаты = $payNumber;

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('DetailPay', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($returned);
        echo '</pre><hr />';
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl DetailPay', (array)$z, (array)$returned, 'DetailPay.txt');

        return $returned;
    }


    /** New convenient call */
    private $method = '';
    private $endpoint_prefix = '/ws/';
    private $endpoin_suffix = '.1cws?wsdl';

    /**
     * Карта конвертации параметров CRM в параметры 1C
     *
     * 'имя_параметра_в_CRM' => 'ИМЯ_ПАРАМЕТРА_В_1С'
     *
     * @var array
     */
    private $convert_map = [
        'lastname' => 'Фамилия',
        'firstname' => 'Имя',
        'patronymic' => 'Отчество',
        'phone_mobile' => 'Телефон',
        'birth' => 'ДатаРождения',
        'register_id' => 'OrderID',
        'operation_id' => 'OperationID',
        'amount' => 'Сумма',
        'uid' => 'УИД',
        'created' => 'ДатаОплаты',
        'agrid' => 'Agrid',
    ];

    /**
     * Функции конвертации для различных параметров
     *
     * @return array
     */
    private function getConvertActions(): array
    {
        return [
            'birth' => static function ($item) {
                return date('YmdHis', strtotime($item));
            },
            'created' => static function ($item) {
                return date('YmdHis', strtotime($item));
            },
        ];
    }

    public function request($endpoint, $method, $params, $data_format = 'object'): string
    {
        $endpoint_host = $this->config->url_1c . $this->config->work_1c_db . $this->endpoint_prefix;
        $endpoint_uri = $endpoint_host . $endpoint . $this->endpoin_suffix;
        $request_data = $params ? $this->compileParams($params, $data_format) : [];

        try {
            $client = new SoapClient($endpoint_uri);
            $response = $client->__soapCall(
                $method,
                [$request_data]
            );

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(
            $method,
            $endpoint_uri,
            (array)$request_data,
            (array)$response,
            $this->camelCaseToSnakeCase($method) . ".txt"
        );

        return $response->return;
    }

    private function compileParams($params, $data_format, $convert_map = null, $convert_actions = null)
    {
        // Assign convert map and actions
        $convert_map = array_merge($this->convert_map, (array)$convert_map);
        $convert_actions = array_merge($this->getConvertActions(), (array)$convert_actions);

        // Convert to 1C params
        $converted_data = [];
        foreach ($params as $key => $value) {
            if (isset($convert_map[$key])) {
                $converted_data[$convert_map[$key]] = isset($convert_actions[$key])
                    ? $convert_actions[$key]($value)
                    : $value;
            } else {
                $converted_data[$key] = $value;
            }
        }

        // Compile a request object
        switch ($data_format) {
            case 'object':
                $request_object = (object)$converted_data;
                break;
            case 'json':
                $request_object = new StdClass();
                $request_object->TextJson = json_encode($converted_data, JSON_UNESCAPED_UNICODE);
                break;
        }

        return $request_object;
    }

    /**
     * Получает активные займы из 1С и возвращает их в виде массива
     *
     * @throws Exception
     */
    public function getActiveLoans(): array
    {
        $response_1c = $this->request(
            'WebLK',
            'GetLoans',
            [
                'Partner' => 'Boostra',
            ]
        );

        return (new \boostra\helpers\Converter($response_1c))
            ->to('array');
    }

    /**
     * Функция конвертации camelCase в snake_case
     */
    private function camelCaseToSnakeCase($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /** END OF New convenient call */

    /**
     * Получает несколько балансов из 1С
     * необходимо когда открыто несколько займов одновременно
     * @param string $uid
     * @param string $site_id Идентификатор сайта
     * @return array|mixed
     */
    public function get_user_balances_array_1c(string $uid, string $site_id = 'boostra')
    {
        $inn_arr = $this->organizations->get_inns_by_site_id($site_id);
        if (empty($inn_arr)){ 
            return false;
        }

        $object = $this->generateObject(
            [
                'UID' => $uid,
                'ArrayINN' => json_encode($inn_arr, false),
                'Пароль' => $this->settings->api_password,
                'Partner' => 'Boostra',
            ]
        );

        $result = $this->requestSoap($object, 'WebLK', 'GetLKMassINN', 'get_lk_mass.txt');
        return $result['response'] ?? $result;
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

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl ServiceConnection', (array)$z, (array)$returned, 'ServiceConnection.txt');
    }


    public function send_credit_rating($params)
    {
        $item = [
            'OrderID' => $params['register_id'],
            'OperationID' => $params['operation_id'],
            'Сумма' => $params['amount'],
            'УИД' => $params['uid'],
            'ДатаОплаты' => date('YmdHis', strtotime($params['created'])),
            'Agrid' => $params['agrid'],
            'НомерКарты' => empty($params['card_pan']) ? '' : $params['card_pan'],
        ];
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($item);
        echo '</pre><hr />';
        $request = new StdClass();
        $request->TextJson = json_encode($item, JSON_UNESCAPED_UNICODE);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $client->__soapCall('KR', [$request]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl KR', (array)$item, (array)$returned, 'payment.txt');
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($returned);
        echo '</pre><hr />';
        return $returned->return;
    }

    /**
     * Пример ответа:
     *
     * $issued_loans = array (
     *   0 =>
     *     (object) array(
     *       'НомерЗайма' => 'RZS25-8706807',
     *       'ОстатокОД' => 1900,
     *       'ОрганизацияИНН' => '9717088848',
     *   ),
     * );
     */
    public function DebtForFIO($params)
    {
        $site_id = $this->users->get_site_id_by_user_id($params["user_id"]);
        $inn_arr = $this->organizations->get_inns_by_site_id($site_id);

        $item = [
            'Фамилия' => $params['lastname'],
            'Имя' => $params['firstname'],
            'Отчество' => $params['patronymic'],
            'ДатаРождения' => date('YmdHis', strtotime($params['birth'])),
            'Телефон' => $params['phone_mobile'],
            'INN' => $inn_arr,
            'ArrayINN' => $inn_arr,
        ];

        $request = new StdClass();
        $request->TextJson = json_encode($item, JSON_UNESCAPED_UNICODE);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $client->__soapCall('DebtForFIO', [$request]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl DebtForFIO', (array)$item, (array)$fault, 'debt_fio.txt');
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl DebtForFIO', (array)$item, (array)$returned, 'debt_fio.txt');

        return json_decode($returned->return);
    }


    /**
     * Soap1c::return_KD()
     *
     * @param array $params
     * @param string $params ['register_id']
     * @param string $params ['insure_operation']
     * @param string $params ['return_operation']
     * @param string $params ['date']
     * @param string $params ['amount']
     * @return object
     */
    public function return_credit_doctor($params)
    {
        $ArrayKD = new StdClass();
        $ArrayKD->OrderID = $params['register_id'];
        $ArrayKD->ПродажаOperationID = $params['insure_operation'];
        $ArrayKD->ВозвратOrderID = $params['return_register_id'];
        $ArrayKD->ВозвратOperationID = $params['return_operation'];
        $ArrayKD->Дата = date('YmdHis', strtotime($params['date']));
        $ArrayKD->Сумма = $params['amount'];
        $ArrayKD->НомерКарты = empty($params['card_pan']) ? '' : $params['card_pan'];
        $ArrayKD->НомерСектора = $params['sector'] ?? '';

        $z = new stdClass();
        $z->ArrayKD = json_encode([$ArrayKD], JSON_UNESCAPED_UNICODE);
        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('VozvratKD', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl VozvratKD', (array)$z, (array)$returned, 'ReturnInsurance.txt');
        return $returned;

    }

    /**
     * Star Oracle
     * Soap1c::return_SO()
     *
     * @param array $params
     * @param string $params['register_id']
     * @param string $params['insure_operation']
     * @param string $params['return_operation']
     * @param string $params['date']
     * @param string $params['amount']
     * @return object
     */
    public function return_star_oracle($params)
    {
        $ArraySO = new StdClass();
        $ArraySO->OrderID = $params['register_id'];
        $ArraySO->ПродажаOperationID = $params['insure_operation'];
        $ArraySO->ВозвратOrderID = $params['return_register_id'];
        $ArraySO->ВозвратOperationID = $params['return_operation'];
        $ArraySO->Дата = date('YmdHis', strtotime($params['date']));
        $ArraySO->Сумма = $params['amount'];
        $ArraySO->НомерКарты = empty($params['card_pan']) ? '' : $params['card_pan'];
        $ArraySO->НомерСектора = $params['sector'] ?? '';


        $z = new stdClass();
        $z->ArraySO = json_encode([$ArraySO], JSON_UNESCAPED_UNICODE);
        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('VozvratSO', array($z));
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl VozvratSO', (array) $z, (array) $returned, 'ReturnInsurance.txt');
        return $returned;

    }

    /**
     * Safe deal
     * Soap1c::return_safe_deal()
     *
     * @param array $params
     * @param string $params['register_id']
     * @param string $params['insure_operation']
     * @param string $params['return_operation']
     * @param string $params['date']
     * @param string $params['amount']
     * @return object
     */
    public function return_safe_deal($params)
    {
        $ArraySafeDeal = new StdClass();
        $ArraySafeDeal->OrderID = $params['register_id'];
        $ArraySafeDeal->ПродажаOperationID = $params['insure_operation'];
        $ArraySafeDeal->ВозвратOrderID = $params['return_register_id'];
        $ArraySafeDeal->ВозвратOperationID = $params['return_operation'];
        $ArraySafeDeal->Дата = date('YmdHis', strtotime($params['date']));
        $ArraySafeDeal->Сумма = $params['amount'];
        $ArraySafeDeal->НомерКарты = empty($params['card_pan']) ? '' : $params['card_pan'];
        $ArraySafeDeal->НомерСектора = $params['sector'] ?? '';

        $z = new stdClass();
        $z->ArraySD = json_encode([$ArraySafeDeal], JSON_UNESCAPED_UNICODE);
        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('VozvratSafeDeal', array($z));
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl VozvratSafeDeal', (array) $z, (array) $returned, 'ReturnInsurance.txt');
        return $returned;

    }

    /**
     * Soap1c::get_orders_for_date_payment()
     * Вовращает список займов по дате оплаты
     *
     * @param null $date_payment
     * @return array
     */
    public function get_orders_by_date_payment($date_payment = null, string $partner = 'Boostra', $site_inns = []): array
    {
        if (!$date_payment) {
            return [];
        }

        $z = new stdClass();
        $z->payment_date = $date_payment;
        $z->ArrayINN = json_encode($site_inns, false);
        $z->Пароль = $this->settings->api_password;
        $z->Partner = $partner;

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
            $returned = $uid_client->__soapCall('GetArrayUsersBalanceINN', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(
            __METHOD__,
            $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl GetArrayUsersBalanceINN',
            (array)$z,
            (array)$returned,
            'update_loan_history.txt'
        );

        return json_decode($returned->return) ?? [];
    }

    /**
     * Soap1c::get_orders_by_date_payment_Il()
     * Вовращает список займов по дате оплаты IL
     *
     * @param null $date_payment
     * @return array
     */
    public function get_orders_by_date_payment_Il($date_payment = null, string $partner = 'Boostra', array $site_inns = []): array
    {
        if (!$date_payment) {
            return [];
        }

        $z = new stdClass();
        $z->payment_date = $date_payment;
        $z->ArrayINN = json_encode($site_inns, false);
        $z->Пароль = $this->settings->api_password;
        $z->Partner = $partner;
        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
            $returned = $uid_client->__soapCall('GetArrayUsersBalanceILINN', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }
        $this->logging(
            __METHOD__,
            $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl GetArrayUsersBalanceILINN',
            (array)$z,
            (array)$returned,
            'update_loan_history.txt'
        );

        return json_decode($returned->return) ?? [];
    }

    /**
     * Soap1c::return_multipolis()
     *
     * @param array $params
     * @param string $params ['register_id']
     * @param string $params ['insure_operation']
     * @param string $params ['return_operation']
     * @param string $params ['date']
     * @param string $params ['amount']
     * @return object
     */
    public function return_multipolis($params)
    {
        $ArrayMult = new StdClass();
        $ArrayMult->OrderID = $params['register_id'];
        $ArrayMult->ПродажаOperationID = $params['insure_operation'];
        $ArrayMult->ВозвратOrderID = $params['return_register_id'];
        $ArrayMult->ВозвратOperationID = $params['return_operation'];
        $ArrayMult->Дата = date('YmdHis', strtotime($params['date']));
        $ArrayMult->Сумма = $params['amount'];
        $ArrayMult->НомерКарты = empty($params['card_pan']) ? '' : $params['card_pan'];
        $ArrayMult->НомерСектора = $params['sector'] ?? '';

        $z = new stdClass();
        $z->ArrayMult = json_encode([$ArrayMult], JSON_UNESCAPED_UNICODE);
        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('VozvratMult', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl VozvratMult', (array)$z, (array)$returned, 'ReturnInsurance.txt');

        return $returned;
    }

    /**
     * Soap1c::return_tv_medical()
     *
     * @param array $params
     * @param string $params ['register_id']
     * @param string $params ['insure_operation']
     * @param string $params ['return_operation']
     * @param string $params ['date']
     * @param string $params ['amount']
     * @return object
     */
    public function return_tv_medical($params)
    {
        $ArrayTM = new StdClass();
        $ArrayTM->OrderID = $params['register_id'];
        $ArrayTM->ПродажаOperationID = $params['insure_operation'];
        $ArrayTM->ВозвратOrderID = $params['return_register_id'];
        $ArrayTM->ВозвратOperationID = $params['return_operation'];
        $ArrayTM->Дата = date('YmdHis', strtotime($params['date']));
        $ArrayTM->Сумма = $params['amount'];
        $ArrayTM->НомерКарты = empty($params['card_pan']) ? '' : $params['card_pan'];
        $ArrayTM->НомерСектора = $params['sector'] ?? '';

        $z = new stdClass();
        $z->ArrayTM = json_encode([$ArrayTM], JSON_UNESCAPED_UNICODE);
        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('VozvratTM', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl VozvratTM', (array)$z, (array)$returned, 'ReturnInsurance.txt');

        return $returned;
    }


    /**
     * Soap1c::return_insurance()
     *
     * @param array $params
     * @param string $params ['number']
     * @param string $params ['application_date']
     * @param integer $params ['card_id']
     * @return string
     */
    public function return_insurance($params)
    {
        $z = new stdClass();
        $z->НомерСтраховки = $params['number'];
        $z->ДатаЗаявления = date('YmdHis', strtotime($params['application_date']));
        $z->CardID = $params['card_id'];

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $uid_client->__soapCall('ReturnInsurance', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl ReturnInsurance', (array)$z, (array)$returned, 'ReturnInsurance.txt');
        return isset($returned->return) ? $returned->return : NULL;

    }

    public function set_order_complete($order_id)
    {
        $this->orders->update_order($order_id, ['complete' => 1]);

        $order = $this->orders->get_order($order_id);

        $z = new stdClass();
        $z->НомерЗаявки = $order->id_1c;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('FullApplication', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl FullApplication', (array)$z, (array)$returned, 'statuses.txt');

        return $returned;
    }

    /**
     * Soap1c::get_user_credits()
     * Вовращает список займов клиента по уид
     *
     * @param string $uid_1c
     * @param string $site_id Идентификатор сайта
     * @return
     */
    public function get_user_credits($uid_1c, string $site_id='boostra')
    {
        $inn_arr =  $this->organizations->get_inns_by_site_id($site_id);
        if (empty($inn_arr)){ 
            return false;
        }

        if (!empty($uid_1c)) {
            $z = new stdClass();
            $z->UID = $uid_1c;
            $z->ArrayINN = json_encode($inn_arr, false);
            $z->Partner = 'Boostra';

            try {
                $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                $returned = $uid_client->__soapCall('HistoryZaimINN', array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                $returned = $fault;
            } catch (Exception $fault) {
                $returned = $fault;
            }

            $this->logging(
                __METHOD__,
                $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl HistoryZaimINN',
                (array)$z,
                (array)$returned,
                'loan_history.txt'
            );

            return json_decode($returned->return);
        } else {
            return false;
        }
    }

    //  Если ответ 1 - можно отправлять
    public function limit_sms($number)
    {
        $z = new stdClass();
        $z->Number = $number;

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('LimitSMS', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl LimitSMS', (array)$z, (array)$returned, 'LimitSMS.txt');
        return isset($returned->return) ? $returned->return : NULL;
    }

    public function get_number_of_sms($number, $days)
    {
        $z = new stdClass();
        $z->Number = $number;
        $z->Day = $days;

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('SendingSMSin1C', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl SendingSMSin1C', (array)$z, (array)$returned, 'LimitSMS.txt');
        return $returned;
    }

    public function send_number_of_sms($number, $phone, $message)
    {
        $z = new stdClass();
        $z->Number = $number;
        $z->Date = date('YmdHis');
        $z->Phone = $phone;
        $z->TextSMS = $message;

        try {
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $uid_client->__soapCall('SendingSMSCRM', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl SendingSMSCRM', (array)$z, (array)$returned, 'LimitSMS.txt');
        return isset($returned->return) ? $returned->return : NULL;
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

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl ManagerApplication', (array)$z, (array)$returned, 'order_managers.txt');

        return empty($returned->return) ? $returned : ($returned->return);
    }


    public function get_close_loans($period)
    {
        if (!empty($period)) {
            $z = new stdClass();
            $z->NDay = $period;

            try {
                $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                $returned = $uid_client->__soapCall('CloseNDayBack', array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                $returned = $fault;
            } catch (Exception $fault) {
                $returned = $fault;
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl CloseNDayBack', (array)$z, (array)$returned);

            if (!empty($returned->return))
                return json_decode($returned->return);
        }

        return false;
    }

    /**
     * Soap1c::send_payments()
     * Отсылает данные по оплатам клиентов
     * Должен содержать объекты contract, insurance
     * @param $payments
     * @return mixed
     */
    public function send_payments($payments)
    {
        $items = array();
        foreach ($payments as $payment) {
            $item = new StdClass();
            $item->АСП = $payment->asp;

            $item->НомерЗайма = $payment->contract_number;
            $item->ДатаОплаты = date('YmdHis', strtotime($payment->operation_date));
            $item->НомерОплаты = $this->best2pay->get_payment_number($payment);//обязательно в номере ТИРЕ. длина номера 11 символов!!!
            $item->НомерКарты = empty($payment->card_pan) ? '' : $payment->card_pan;

            $item->ID_Заказ = $payment->register_id;
            $item->ID_УспешнаяОперация = $payment->operation_id;

            $item->СуммаОплаты = (float)$payment->amount;
            $item->ИсточникОплаты = $payment->create_from;

            $item->Пролонгация = empty($payment->prolongation) ? 0 : 1;  //1 - истина, 0 ложь
            $item->СрокПролонгации = empty($payment->prolongation_day) ? 0 : $payment->prolongation_day;
            $item->НачислитьПроцент = empty($payment->calc_percents) ? 0 : 1;  //1 - истина, 0 ложь
            $item->ЗакрытПоСкидке = empty($payment->grace_payment) ? 0 : 1;  //1 - истина, 0 ложь
            $item->Рекурент = ($payment->create_from ?? '') === 'recurrent' ? 1 : 0; //1 - истина, 0 ложь
            $item->СБП = empty($payment->is_sbp) ? 0 : 1;
            $item->НомерСектора = $payment->sector;

            $item->Скидка = $payment->discount_amount;

            $item->ИННОрагнизации = $payment->organization->inn;

            if (!empty($payment->multipolis)) {
                $organizationMP = $this->organizations->get_organization($payment->multipolis->organization_id);
                $item->Мультиполис = (object)[
                    'СуммаСтраховки' => $payment->multipolis->amount,
                    'НомерСтраховки' => $payment->multipolis->number,
                    'Organization' => $payment->organization ? $payment->organization->onec_code : '000000005',
                    'ИННОрганизацииВладельцаУслуги' => $organizationMP->inn,
                ];
                $item->СуммаОплаты -= $payment->multipolis->amount; // (тут без мультиполиса)
            }

            if (!empty($payment->tv_medical)) {
                $organizationTM = $this->organizations->get_organization($payment->tv_medical->organization_id);
                $item->Телемедицина = (object)[
                    'ID_ВитаМед' => 'ID_' . $payment->tv_medical->tv_medical_id,
                    'Сумма' => $payment->tv_medical->amount,
                    'НомерПолиса' => $payment->tv_medical->id,
                    'insurer' => '',
                    'Organization' => $organizationTM ?: '000000005',
                    'ИННОрганизацииВладельцаУслуги' => $organizationTM->inn,
                ];
                $item->СуммаОплаты -= $payment->tv_medical->amount; // (тут без телемедицины)
            }
            if (!empty($payment->star_oracle)) {
                $organizationSO = $this->organizations->get_organization($payment->star_oracle->organization_id);
                $item->ЗвездныйОракул = (object)[
                    'Сумма' => $payment->star_oracle->amount,
                    'НомерПолиса' => $payment->star_oracle->id,
                    'Organization' => $organizationSO->onec_code ?: '000000005',
                    'ИННОрганизацииВладельцаУслуги' => $organizationSO->inn,
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
                    'Organization' => '000000005',
                ];
                $item->СуммаОплаты -= $payment->insure;
            }

            $items[] = $item;
        }

        $request = new StdClass();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($items);echo '</pre><hr />';
        $request->ArrayOplata = json_encode($items, JSON_UNESCAPED_UNICODE);
        $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");

        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump(($items));
        echo '</pre><hr />';

        try {
            $returned = $stat_z_client->__soapCall('Oplata', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump('$returned', $returned);
        echo '</pre><hr />';
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Oplata', (array)$request, (array)$returned, 'b2p_payment.txt');

        return $returned;
    }

    /**
     * Soap1c::send_payment_recurring()
     * Отсылает данные по рекуррентной оплате
     *
     * @param $payments
     * @return mixed
     * @throws \SoapFault
     */
    public function send_payments_recurring($payments)
    {
        $items = array();
        foreach ($payments as $payment) {
            $item = new StdClass();
            $item->НомерЗайма = $payment->contract_number;
            $item->ДатаОплаты = empty($payment->operation_date) ? date('YmdHis') : date('YmdHis', strtotime($payment->operation_date));
            $item->НомерОплаты = 'PM' . date('y') . '-' . $payment->id; //обязательно в номере ТИРЕ. длина номера 11 символов!!!
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

            $items[] = $item;
        }

        $request = new StdClass();
        $request->ArrayOplata = json_encode($items, JSON_UNESCAPED_UNICODE);

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
     * Soap1c::send_refuser_payments()
     * Отсылает данные по оплатам отказников
     * @param $payments
     * @return mixed
     * @throws JsonException|SoapFault
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
            $item->UserUID = $user->UID;

            $items[] = $item;
        }

        $request = new StdClass();
        $request->TextJSON = json_encode($items, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($items);
        echo '</pre><hr />';

        try {
            $returned = $stat_z_client->__soapCall('PaymentRefuser', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump('$returned', $returned);
        echo '</pre><hr />';


        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl PaymentRefuser', (array)$request, (array)$returned, 'b2p_payment.txt');

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
        foreach ($this->managers->get_managers() as $m)
            $managers[$m->id] = $m;

        $items = array();
        foreach ($orders as $order) {
            $card = $this->best2pay->get_card($order->card_id);

            $item = new StdClass();

            $item->Организация = 'Boostra';
            $item->Дата = date('YmdHis', strtotime($order->p2pcredit->complete_date)); //формат ггггММддччммсс
            $item->Сумма = $order->amount;  //в рублях
            $item->ПроцентСтавка = $order->percent;
            $item->Срок = (int)$order->period;
            $item->Менеджер = empty($order->manager_id) ? '' : $managers[$order->manager_id]->name_1c;
            $item->УИД_Займ = '';
            $item->УИД_Заявка = '';
            $item->agrid = empty($order->scorista) ? '' : $order->scorista->scorista_id; // агрид скористы
            $item->НомерЗаявки = $order->id_1c;
            $item->НомерКарты = $card->pan;
            $item->КодСМС = $order->accept_sms;

            $contract_number = 'Б' . date('y', strtotime($order->date)) . '-';
            if ($order->order_id > 999999)
                $contract_number .= $order->order_id;
            else
                $contract_number .= '0' . $order->order_id;
            $item->Номер = $contract_number; // 12345-67890обязательно в номере ТИРЕ. длина номера 11 символов!!!

            $item->Клиент = new StdClass();
            $item->Клиент->Фамилия = $order->lastname;
            $item->Клиент->Имя = $order->firstname;
            $item->Клиент->Отчество = $order->patronymic;
            $item->Клиент->ДатаРожденияПоПаспорту = date('YmdHis', strtotime($order->birth));

            $item->Клиент->АдресРегистрацииГород = trim($order->Regcity . ' ' . $order->Regcity_shorttype);
            $item->Клиент->АдресРегистрацииДом = $order->Reghousing . (empty($order->Regbuilding) ? '' : ' стр. ' . $order->Regbuilding);
            $item->Клиент->АдресРегистрацииИндекс = $order->Regindex;
            $item->Клиент->АдресРегистрацииКвартира = $order->Regroom;
            $item->Клиент->АдресРегистрацииРегион = trim($order->Regregion . ' ' . $order->Regregion_shorttype);
            $item->Клиент->АдресРегистрацииУлица = trim($order->Regstreet . ' ' . $order->Regstreet_shorttype);
            $item->Клиент->АдресРегистрацииРайон = empty($order->Regdistrict) ? '' : trim($order->Regdistrict);
            $item->Клиент->АдресРегистрацииНасПункт = empty($order->Reglocality) ? '' : trim($order->Reglocality);
            $item->Клиент->АдресРегистрацииТелефон = '';

            $item->Клиент->АдресФактическогоПроживанияИндекс = $order->Faktindex;
            $item->Клиент->АдресФактическогоПроживанияРегион = trim($order->Faktregion . ' ' . $order->Faktregion_shorttype);
            $item->Клиент->АдресФактическогоПроживанияРайон = empty($order->Faktdistrict) ? '' : trim($order->Faktdistrict);
            $item->Клиент->АдресФактическогоПроживанияГород = trim($order->Faktcity . ' ' . $order->Faktcity_shorttype);
            $item->Клиент->АдресФактическогоПроживанияНасПункт = empty($order->Faktlocality) ? '' : trim($order->Faktlocality);
            $item->Клиент->АдресФактическогоПроживанияУлица = trim($order->Faktstreet . ' ' . $order->Faktstreet_shorttype);
            $item->Клиент->АдресФактическогоПроживанияДом = $order->Fakthousing . (empty($order->Faktbuilding) ? '' : ' стр. ' . $order->Faktbuilding);
            $item->Клиент->АдресФактическогоПроживанияКвартира = $order->Faktroom;
            $item->Клиент->АдресФактическогоПроживанияТелефон = '';

            $item->Клиент->АдресФактическогоПроживанияМобильныйТелефон = $this->format_phone($order->phone_mobile);

            $agreeClaimValue = $this->order_data->read($order->order_id, $this->order_data::AGREE_CLAIM_VALUE);
            $item->Клиент->ОтказОтУступкиПраваТребования = !empty($agreeClaimValue) ? $agreeClaimValue : 0;

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
                $item->Страховка->Ставка = empty($order->insure->stavka) ? round($order->insure->amount / $order->amount * 100) : $order->insure->stavka;
                $item->Страховка->insurer = $order->insurer;
            }
            // Добавим КД
            $credit_doctor = $this->credit_doctor->getUserCreditDoctor((int)$order->order_id, (int)$order->user_id, $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS);
            if (!empty($credit_doctor->transaction_id)) {
                $credit_doctor_transaction = $this->best2pay->get_transaction($credit_doctor->transaction_id);

                if (!empty($credit_doctor) && !empty($credit_doctor_transaction)) {
                    $credit_doctor_data = (object)[
                        'insurer' => $order->insurer,
                        'OperationID' => $credit_doctor_transaction->operation ?? '',
                        'OrderID' => $credit_doctor_transaction->register_id ?? '',
                        'СуммаКД' => $credit_doctor->amount,
                        'КомплектНазвание' => 'Комплект ' . $credit_doctor->credit_doctor_condition_id,
                        'КомплектID' => $credit_doctor->credit_doctor_condition_id,
                    ];

                    $item->КД = $credit_doctor_data;
                }
            }

            $items[] = $item;
        }
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($items);
        echo '</pre><hr />';

        $request = new StdClass();
        $request->ArrayContracts = json_encode($items, JSON_UNESCAPED_UNICODE);

        try {
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $stat_z_client->__soapCall('Request', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Request', (array)$request, (array)$returned, 'contracts.txt');
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($returned);
        echo '</pre><hr />';

        return $returned;
    }


    public function get_open_zaims()
    {
        $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");

        $z = new stdClass();

        try {
            $returned = $stat_z_client->__soapCall('OpenZaims', array());

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl OpenZaims', (array)$z, (array)$returned);

        return empty($returned->return) ? $returned : json_decode($returned->return);
    }


    public function soap_repeat_zayavka($amount, $period, $user_id, $card, $insure = NULL, $params = [])
    {
        if ($user = $this->users->get_user((int)$user_id)) {
            $z = new StdClass();

            $z->Uid = $user->UID;
            $z->УИД = $params['order_uid'];

            if (empty($params['organization_id'])) {
                $params['organization_id'] = $this->organizations::AKVARIUS_ID;
            }
            $organization = $this->organizations->get_organization($params['organization_id']);
            $z->ИННОрганизации = $organization->inn;

            $z->site_id = $user->site_id;
            $z->partner_id = $user->partner_id;
            if (empty($user->partner_name))
                $z->partner_name = 'Boostra';
            else
                $z->partner_name = $user->partner_name;

            if (empty($z->ДатаЗаявки))
                $z->ДатаЗаявки = date('YmdHis');

            // Информация из базы по заявке
            $z->amount = intval($amount);
            $z->period = intval($period);
            $z->utm_source = !empty($params['utm_source']) ? $params['utm_source'] : (empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"]);
            $z->utm_medium = !empty($params['utm_medium']) ? $params['utm_medium'] : (empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"]);
            $z->utm_campaign = empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"];
            $z->utm_content = empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"];
            $z->utm_term = empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"];
            $z->webmaster_id = empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"];
            $z->click_hash = empty($_COOKIE['click_hash']) ? '' : $_COOKIE['click_hash'];

            $z->ServicesSMS = 0;
            $z->ServicesInsure = is_null($insure) ? (int)$user->service_insurance : $insure;
            $z->ServicesReason = 0;

            $z->ОтказНаСайте = 0;
            $z->ПричинаОтказаНаСайте = '';

            $z->CardID = $card;

            $z->ТекстЗапроса = json_encode($z, JSON_UNESCAPED_UNICODE);

            if (!isset($client)) {
                try {
                    $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
                    $returned = $client->__soapCall('GetZayavkiUid', array($z));

                    $this->automationFails->setSoapError(false);
                } catch (SoapFault $fault) {
                    $this->automationFails->setSoapError(true);

                    $returned = $fault;
                }
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetZayavkiUid', (array)$z, (array)$returned);

            return $returned;


        }
    }


    /**
     * Soap1c::set_bankrupt()
     *
     *  СРО - строка , наименование органа , можно просто пустую строку
     * НомерДела  - строка
     * Арбитражный управляющий строка
     * Стадия банкротства
     * ДатаСтадии  строка формата ггггММддЧЧммсс
     * Ответственный - строка , менкджер
     * Описание - строка , комментарий
     * @param mixed $uid
     * @param mixed $cpo
     * @param mixed $nomer
     * @param mixed $arbitr
     * @param mixed $stady
     * @param mixed $stady_date
     * @param mixed $manager
     * @param mixed $desc
     * @return
     */
    public function set_bankrupt($uid, $cpo, $nomer, $arbitr, $stady, $stady_date, $manager, $desc)
    {
        if (!empty($uid_1c)) {
            $z = new stdClass();
            $z->UID = $uid;
            $z->СРО = $cpo;
            $z->НомерДела = $nomer;
            $z->АрбитражныйУправляющий = $arbitr;
            $z->СтадияБанкротства = $stady;
            $z->ДатаСтадии = $stady_date;
            $z->Ответственный = $manager;
            $z->Описание = $desc;

            try {
                $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                $returned = $uid_client->__soapCall('Bankruptcy', array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                $returned = $fault;
            } catch (Exception $fault) {
                $returned = $fault;
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl Bankruptcy', (array)$z, (array)$returned);

            return $returned;
        } else
            return false;
    }


    public function check_order_1c($id_1c)
    {
        if (!isset($stat_z_client))
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOtvetZayavki.1cws?wsdl");

        $inn_arr =  $this->organizations->get_inns_by_order_1c_id($id_1c);

        if (empty($inn_arr)){ 
            return false;
        }

        $z = new stdClass();
        $z->НомерЗаявки = $id_1c;
        $z->ArrayINN = json_encode($inn_arr, false);
        $z->Partner = 'Boostra';

        try {
            $returned = $stat_z_client->__soapCall('GetOtvetZayavkiINN', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        return empty($returned->return) ? $returned : ($returned->return);
    }

    /**
     * Возвращаемое значение
     * Если передана 1 заявка, то данные содержатся в return->Результат, а не return->Результат[0]!
     * {"return":{"Результат":{"НомерЗаявки":"026118271","Статус":"5.Выдан","Комментарий":"","ОфициальныйОтвет":"","Сумма":"","ПредложениеДействуетДо":"","Файл":"","Скориста":"Одобрено","ФайлBase64":""}}}
     *
     * Если передано 2 заявки, то данные содержатся в return->Результат[0] и return->Результат[1]
     * {"return":{"Результат":[{"НомерЗаявки":"026118271","Статус":"5.Выдан","Комментарий":"","ОфициальныйОтвет":"","Сумма":"","ПредложениеДействуетДо":"","Файл":"","Скориста":"Одобрено"},{"НомерЗаявки":"026118292","Статус":"5.Выдан","Комментарий":"","ОфициальныйОтвет":"","Сумма":"","ПредложениеДействуетДо":"","Файл":"","Скориста":"Нет данных","ФайлBase64":""}]}}
     *
     * @param array $orders_1c_id Массив s_orders.`1c_id`
     * @return false|object
     * @throws SoapFault
     */
    public function check_order_1c_array(array $orders_1c_id)
    {
        $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOtvetZayavki.1cws?wsdl");

        $request = [];
        foreach ($orders_1c_id as $id_1c) {
            $inn_arr =  $this->organizations->get_inns_by_order_1c_id($id_1c);

            if (empty($inn_arr)){
                return false;
            }

            $request[] = [
                'AppNumber' => $id_1c,
                'ArrayINN' => $inn_arr,
            ];
        }

        $request = [
            'TextJSON' => json_encode($request, JSON_UNESCAPED_UNICODE)
        ];

        try {
            // Можно вызывать так: $returned = $stat_z_client->getApplicationsInfo($request);
            // Если вызывать через __soapCall, то нужно обязательно передавать массив массивов (массив $request)
            // Для сброса кеша wsdl нужно ini_set("soap.wsdl_cache_enabled", 0);
            $returned = $stat_z_client->__soapCall('getApplicationsInfo', [$request]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        return empty($returned->return) ? $returned : $returned->return;
    }

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
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($returned);echo '</pre><hr />';        
        return empty($returned->return) ? $returned : ($returned->return);

    }


    /**
     * Soap1c::SetTestClient()
     * Устанавливает статус "тестовый клиент" для пользователя в 1С
     *
     * @param string $uid_1c Уникальный строковый идентификатор клиента в 1С
     * @return mixed Результат ответа от 1С или исключение при ошибке
     * @throws SoapFault
     */
    public function SetTestClient(string $uid_1c)
    {
        if (!empty($uid_1c)) {
            $z = new stdClass();
            $z->UID = $uid_1c;

            try {
                $soapClient = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOtvetZayavki.1cws?wsdl");
                $result = $soapClient->__soapCall('SetTestClient', [$z]);

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);
                $this->logging(__METHOD__, 'SOAP Fault', (array)$z, (array)$fault);
                throw $fault;
            } catch (Exception $fault) {
                $this->logging(__METHOD__, 'Exception', (array)$z, (array)$fault);
                return $fault;
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . "/ws/WebOtvetZayavki.1cws?wsdl SetTestClient", (array)$z, (array)$result);

            return $result;
        }

        return false;
    }


    /**
     * Soap1c::get_user_balance_1c()
     * Получает баланс по УИД клиента
     *
     * @param string $uid_1c
     * @param string $site_id Идентификатор сайта
     * @return
     */
    public function get_user_balance_1c(string $uid_1c, string $site_id = 'boostra')
    {
        $inn_arr =  $this->organizations->get_inns_by_site_id($site_id);
        if (empty($inn_arr)){ 
            return false;
        }

        if (!empty($uid_1c)) {
            $z = new stdClass();
            $z->UID = $uid_1c;
            $z->ArrayINN = json_encode($inn_arr, false);
            $z->Пароль = $this->settings->api_password;
            $z->Partner = 'Boostra';

            try {
                $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                $returned = $uid_client->__soapCall('GetLKINN', array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                $returned = $fault;
            } catch (Exception $fault) {
                $returned = $fault;
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl GetLKINN', (array)$z, (array)$returned);

            return $returned;
        } else
            return false;
    }


    /**
     * Сервис по страховкам
     *
     */
    public function getInsurance($number)
    {
        if (!empty($number)) {
            $z = new stdClass();
            $z->Номер = $number;
            $z->Пароль = $this->settings->api_password;
            $z->Partner = 'Boostra';

            try {
                $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                $returned = $uid_client->__soapCall('Strah', array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                $returned = $fault;
            } catch (Exception $fault) {
                $returned = $fault;
            }
            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl Strah', (array)$z, (array)$returned);
            return json_decode($returned->return);
        } else
            return false;
    }

    public function get_1c_statuses(array $orders)
    {
        $r = [];
        foreach ($orders as $order) {
            $r[] = ['НомерЗаявки' => $order->id_1c, 'СтатусЗаявки' => $order->status_1c];
        }

        $z = new stdClass;
        $z->TextJson = json_encode($r, JSON_UNESCAPED_UNICODE);

        if (strpos(strtolower(php_sapi_name()), 'cli') === false) {
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

        return json_decode($returned->return);
    }

    /**
     * Soap1c::send_scorista_id()
     * Отправляетв 1с айди скоринга скористы
     * Если передать только уид - вернутся все айди проведенных скорингов скористой по клиенту
     *
     * @param string $user_uid
     * @param string $order_id_1c
     * @param string $scorista_id
     * @return
     */
    public function send_scorista_id($user_uid, $order_id_1c = '', $scorista_id = '', $organization_id = null)
    {
        $z = new StdClass();
        $z->UID = $user_uid;
        $z->НомерЗаявки = $order_id_1c;
        $z->Agrid = $scorista_id;
        if (!empty($organization_id)) {
            $organization = $this->organizations->get_organization($organization_id);
            $organization_name = $organization->inn;
        } else {
            $organization_name = 'Boostra'; 
        }
        $z->Organization = $organization_name;

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


    /**
     * Soap1c::get_movements()
     * Возвращает движения по кредиту по номеру договора
     *
     * @param string $number
     * @return void
     */
    public function get_movements($number)
    {
        $z = new StdClass();
        $z->Number = $number;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
            $returned = $client->__soapCall('Movements', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl Movements', (array)$z, (array)$returned);

        return empty($returned->return) ? $returned : json_decode($returned->return);
    }

    /**
     * Soap1c::get_comments()
     * Получает из 1с список с комментариями по клиенту
     * @param mixed $user_uid
     * @param string $site_id Идентификатор сайта
     * @return
     */
    public function get_comments($user_uid, string $site_id = 'boostra')
    {
        $inn_arr =  $this->organizations->get_inns_by_site_id($site_id);
        if (empty($inn_arr)){ 
            $this->automationFails->setSoapError(true);
            return null;
        }

        $z = new StdClass();
        $z->UID = $user_uid;
        $z->Partner = 'Boostra';
        $z->ArrayINN = json_encode($inn_arr, false);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
            $returned = $client->__soapCall('Comments1СINN', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl Comments1СINN', (array)$z, (array)$returned);

        return empty($returned->return) ? $returned : json_decode($returned->return);
    }


    /**
     * Soap1c::send_comments()
     * отправляет комментарии в 1с, только по выданным кредитам
     *
     * @param array $comments
     * @return
     */
    public function send_comment($comments)
    {
        $items = array();

        if (!is_array(reset($comments))) {
            $comments = [$comments];
        }
        foreach ($comments as $comment) {
            $item = new StdClass();

            $item->НомерЗайма = $comment['number'] ?? '';
            $item->ИдентификаторКлиента = $comment['user_uid'] ?? '';
            $item->Дата = date('YmdHis', strtotime($comment['created']));
            $item->Комментарий = 'Boostra: ' . $comment['text'];
            $item->Ответственный = $comment['manager'];

            $items[] = $item;
        }
        $request = new StdClass();
        $request->TextJson = json_encode($items, JSON_UNESCAPED_UNICODE);
        $request->Partner = 'Boostra';
        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
            $returned = $client->__soapCall('Comments', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl Comments', (array)$request, (array)$returned);
        return empty($returned->return) ? $returned : $returned->return;
    }

    /**
     * Soap1c::update_fields()
     * Обновляет в 1с данные по клиенту
     *
     * @param mixed $order_id_1c
     * @param mixed $fields
     * @param string $uid
     * @return
     */
    public function update_fields($order_id_1c, $fields, $uid = '')
    {
        $z = new StdClass();
        $z->НомерЗаявки = $order_id_1c;
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

// АдресФактическогоПроживанияМобильныйТелефон

        // персональная информация
        if (isset($fields['birth']))
            $update->ДатаРожденияПоПаспорту = date('Ymd000000', strtotime($fields['birth']));
        if (isset($fields['birth_place']))
            $update->МестоРожденияПоПаспорту = $fields['birth_place'];
        if (isset($fields['gender']))
            $update->Пол = ($fields['gender'] == 'male') ? 'Мужской' : 'Женский';
        if (isset($fields['lastname']))
            $update->Фамилия = $fields['lastname'];
        if (isset($fields['firstname']))
            $update->Имя = $fields['firstname'];
        if (isset($fields['patronymic']))
            $update->Отчество = $fields['patronymic'];


        // паспортные данные
        if (isset($fields['passport_serial'])) {
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
        if (isset($fields['contactpersons'])) {
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($fields['contactpersons']);echo '</pre><hr />';
            $update->КонтактныеЛица = ($fields['contactpersons']);
        }

        $order_id = $this->orders->get_order_1cid($order_id_1c);
        $order = $this->orders->get_order((int)$order_id);

        $z->TextJson = json_encode($update, JSON_UNESCAPED_UNICODE);
        $z->ChangeServices = 0;
        $z->ServicesSMS = $order->service_sms;
        $z->ServicesInsure = $order->service_insurance;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetChangingFields', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetChangingFields', (array)$z, (array)$returned);

        return $returned->return;

    }


    /**
     * Soap1c::update_status_1c()
     *
     * @param mixed $order_id_1c
     * @param mixed $status
     * @param integer $amount
     * @return
     */
    public function update_status_1c($order_id_1c, $status, $manager, $amount = 0, $percent = 0.8, $reason = '', $cdoctor = 0, $period = 7)
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

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetStateApplication', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetStateApplication', (array)$z, (array)$fault, 'state.txt');
            return $fault;
        } catch (Exception $fault) {
            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetStateApplication', (array)$z, (array)$fault, 'state.txt');
            return $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetStateApplication', (array)$z, (array)$returned, 'state.txt');

        return $returned->return;
    }


    /**
     * Soap1c::GetCheckBlockZayavka()
     * Проверяет заблокирована ли заявка
     * @param mixed $order_id_1c
     * @return
     */
    public function check_block_order_1c($order_id_1c)
    {
        $z = new StdClass();
        $z->НомерЗаявки = $order_id_1c;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOtvetZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetCheckBlockZayavka', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOtvetZayavki.1cws?wsdl GetCheckBlockZayavka', (array)$z, (array)$returned);

        return $returned->return;
    }

    /**
     * Soap1c::block_order_1c()
     * Блокирует-разблокирует для изменений завку в 1с
     *
     * @param string $order_id_1c
     * @param integer $status : 0 - разблокировать, 1 - заблокировать
     * @return
     */
    public function block_order_1c($order_id_1c, $status)
    {
        $z = new StdClass();
        $z->НомерЗаявки = $order_id_1c;
        $z->Block = $status;

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOtvetZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetBlockZayavka', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOtvetZayavki.1cws?wsdl GetBlockZayavka', (array)$z, (array)$returned);

        return $returned->return;
    }


    /**
     * Soap1c::get_uid_images()
     * Возвращает уиды изображений из хранилища привязанных к клиенту
     * @param mixed $uid
     * @return
     */
    public function get_uid_images($uid)
    {
        if (!empty($uid)) {
            $z = new stdClass();
            $z->UID = $uid;

            try {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
                $returned = $client->__soapCall('GetUidFoto', array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                $returned = $fault;
            } catch (Exception $fault) {
                $returned = $fault;
            }

            if (!empty($log))
                $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl GetUidFoto', (array)$z, (array)$returned);

            if (!empty($returned->return))
                return json_decode($returned->return);
            else
                return $returned;
        } else
            return false;

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
        $replace = array('(', ')', ' ', '-');
        $z->phone_mobile = str_replace('+7', '8', str_replace($replace, '', $z->phone_mobile));
        $z->passport_serial = str_replace(' ', '', $z->passport_serial);

        if (empty($z->ДатаЗаявки))
            $z->ДатаЗаявки = date('YmdHis');

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetZayavkiFullCRM', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (Exception $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetZayavkiFullCRM', (array)$z, (array)$returned);

        return $returned;
    }

    /**
     * Soap1c::get_uid_by_phone()
     * Получение УИД по номеру телефона клиента
     *
     * @param string $phone
     * @return object
     *
     *  Примеры work_test_4:
     *  $res = $this->soap->get_uid_by_phone('79992011619');
     *  (object) array(
     *    'result' => false,
     *    'error' => 'ЛК удален',
     *    'client' => 'ЛК удален',
     *    'uid' => '',
     *    'NeedFull' => true,
     * )
     *
     * $res = $this->soap->get_uid_by_phone('71234567890');
     * (object) array(
     *    'result' => false,
     *    'error' => 'Не найден телефон',
     *    'client' => 'Не найден телефон',
     *    'uid' => '',
     *    'NeedFull' => true,
     * )
     *
     * $res = $this->soap->get_uid_by_phone($phone);
     * (object) array(
     *    'result' => true,
     *    'error' => '',
     *    'client' => 'Иванов Иван Иванович',
     *    'uid' => 'a1zdd607-b647-1130-89d7-2c577b22ac6c',
     *    'NeedFull' => true,
     * )
     *
     * Есть еще множественное совпадение
     */
    public function get_uid_by_phone($phone)
    {
        $z = new StdClass();
        $z->Телефон = $phone;
        $z->Пароль = $this->settings->api_password;

        $replace = array('(', ')', ' ', '-');
        $z->Телефон = str_replace('+7', '8', str_replace($replace, '', $z->Телефон));

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
            $returned = $client->__soapCall('SearchTel', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        } catch (SoapFault $fault) {
            $returned = $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl SearchTel', (array)$z, (array)$returned);

        return json_decode($returned->return);
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

        if (empty($z->UID) || $z->UID == 'error') {

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetCardList', (array)$z, 'ERROR UID. NOT SEND');
            return false;
        }

        if (!isset($client)) {
            try {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
                $returned = $client->__soapCall('GetCardList', array($z));

                $this->automationFails->setSoapError(false);
            } catch (SoapFault $fault) {
                $this->automationFails->setSoapError(true);

                $returned = $fault;
            } catch (SoapFault $fault) {
                $returned = $fault;
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetCardList', (array)$z, (array)$returned);

        }


        return empty($returned->return) ? json_decode($returned) : json_decode($returned->return);
    }

    /**
     * Soap1c::format_phone()
     * Форматирует номер телефона в формат принимаемый 1с
     * формат 8(ххх)ххх-хх-хх
     *
     * @param string $phone
     * @return string $format_phone
     */
    public function format_phone($phone): string
    {
        if (empty($phone))
            return '';

        $clear_phone = preg_replace('/\D/', '', $phone);

        if (strlen($clear_phone) < 10) {
            return '';
        }

        $substr_phone = substr($clear_phone, -10, 10);
        $format_phone = '8(' . substr($substr_phone, 0, 3) . ')' . substr($substr_phone, 3, 3) . '-' . substr($substr_phone, 6, 2) . '-' . substr($substr_phone, 8, 2);

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
        if ($scorista_score && isset($scorista_score->scorista_ball) && $scorista_score->scorista_ball) {
            $z->Agrid = $scorista_score->scorista_ball;
        }

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

    public function send_debt_payment_result($transaction, $authorized, $soap_client)
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
     * Получает кол-во заявок [НЕ ИСПОЛЬЗУЕМ]
     * @param $uid
     * @return array|false
     */
    public function get_quantity_loans($uid, $partner = 'Boostra')
    {
        $result = false;
        $site_id = $this->users->get_site_id_by_user_1c_id($uid);
        $inn_arr =  $this->organizations->get_inns_by_site_id($site_id);
        if (empty($inn_arr)){ 
            return false;
        }

        if (!empty($uid)) {
            $object = $this->generateObject(['UID' => $uid, 'Partner' => $partner]);
            $object->ArrayINN = json_encode($inn_arr, false);
            $result = $this->requestSoap($object, 'WebLK', 'QuantityLoansINN');
        }
        return $result;
    }

    /**
     * Проверка на JSON формат
     * @param $string
     * @return bool
     */
    public function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Обобщенный метод для запросов к SOAP
     * @param $object
     * @param $service
     * @param $method
     * @param string $log_filename
     * @return array
     */
    public function requestSoap($object, $service, $method, string $log_filename = 'soap.txt'): array
    {
        $result = [];

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/" . $service . ".1cws?wsdl");
            $returned = $client->__soapCall($method, [$object]);
            if ($this->isJson($returned->return)) {
                $result['response'] = json_decode($returned->return, true);
            } else {
                $result['response'] = $returned->return ?? $returned;
            }

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $result['errors'] = $fault->getMessage();
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . "/ws/" . $service . ".1cws?wsdl " . $method, (array)$object, $result, $log_filename);
        return $result;
    }

    /**
     * Генерируем объект
     * @param array $data
     * @return StdClass
     */
    public function generateObject(array $data = []): StdClass
    {
        $object = new StdClass();
        foreach ($data as $label => $value) {
            $object->{$label} = $value;
        }
        return $object;
    }

    /**
     * Получает состояние счёта Тинькоф для выдал
     * @return array|false
     */
    public function getTinkoffBalance()
    {
        $object = $this->generateObject(['Partner' => 'Boostra']);
        $response = $this->requestSoap($object, 'Tinkoff', 'GetAccountInfo');
        return $response['response'] ?? false;
    }

    public function get_documents($number)
    {
        if (!empty($number)) {
            $z = new stdClass();
            $z->Номер = $number;

//            $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl ContractRepository', (array)$z);

            try {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
                $returned = $client->__soapCall('ContractRepository', array($z));

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
        } else
            return false;

    }

    /**
     * Soap1c::send_contracts()
     * Метод отсылает в 1с данные по выданным кредитам
     * @wiki https://wiki.yandex.ru/homepage/1c/integracii/soap1c-boostra-crm/soap1csend-contracts-neworders/
     * @param array $orders
     * $order->contract
     * $order->card
     * $order->p2pcredit
     * $order->scorista
     * $order->insure
     * $order->credit_doctor
     * $order->credit_doctor->transaction
     * $order->tv_medical
     * $order->safe_deal
     * $order->user
     *
     *  Пример получения:
     *     $order->p2pcredit = $core->best2pay->get_contract_p2pcredit($contract->id);
     *     $order->insure = $core->best2pay->get_insure($contract->insurance_id);
     * @return string - в случае успеха должно вернуться номер договора
     */
    public function send_contracts_new($orders)
    {
        $managers = array();
        foreach ($this->managers->get_managers() as $m)
            $managers[$m->id] = $m;

        $items = array();
        foreach ($orders as $order) {
            $rcl_contract = $this->rcl->get_contract([
                'user_id' => $order->user_id,
                'organization_id' => $order->organization_id
            ]);
            $rcl_first_tranche = (int)$this->order_data->read($order->order_id, $this->order_data::RCL_FIRST_TRANCHE);

            $item = new StdClass();

            $item->ID = $order->contract->number;
            $item->Дата = !empty($order->contract->issuance_date) ? date('YmdHis', strtotime($order->contract->issuance_date)) : date('YmdHis', strtotime($order->p2pcredit->date));
            $item->Заявка_id = $order->id_1c;
            $item->Срок = $order->period;
            $item->Периодичность = $order->loan_type == 'IL' ? '2 недели' : 'День';;
            $item->ПроцентнаяСтавка = $order->percent;
            $item->ПСК = $order->contract->psk;
            $item->ПДН = $order->pdn_nkbi_loan;
            $item->УИДСделки = $order->contract->uid;
            $item->УИДЗаявки = $order->order_uid;
            $item->ИдентификаторФормыВыдачи = 'СНГБ';
            $item->ИдентификаторФормыОплаты = 'СНГБ';
            $item->Сумма = (float)$order->contract->amount;
            $item->СуммаНаРуки = (float)$order->contract->amount;
            $item->Порог = $order->contract->profit_border;
            $item->ИННОрганизации = $order->organization ? $order->organization->inn : '6317102210';
            $item->СпособПодачиЗаявления = 'Дистанционный';
            $item->КомиссияБанка = 0;
            $item->Менеджер = $managers[$order->manager_id]->name_1c ?? '';
            $item->НомерКарты = $order->card->pan;
            // Получатель платежа при выдаче: телефон/карта получателя фактические из s_b2p_p2pcredits.response
            if (!empty($order->recipient_phone)) {
                $item->ТелефонСБП = $this->format_phone($order->recipient_phone);
                // Используем только transaction_external_id (external_id из XML Best2Pay)
                $item->IDОперацииВСистемеСБП = isset($order->transaction_external_id) && $order->transaction_external_id !== ''
                    ? $order->transaction_external_id
                    : '';
            }
            if (!empty($order->recipient_card_pan)) {
                $item->ПолучательПлатежаКарта = $order->recipient_card_pan;
            }

            $item->КодСМС = $order->contract->asp;
            $item->agrid = $order->scorista->scorista_id;
            $item->СогласиеНаУслуги = (int)$order->is_user_credit_doctor;
            $item->Тест = (int)$order->test_user;
            //Для кросс ордеров нужно передать признак Кросс 1|0
            $item->Кросс = $order->utm_source == 'cross_order' ? 1 : 0;
            //Для кросс ордеров нужно передать номер договора родительской заявки ОрдерЗайм '' | 'Номер договора'
            $item->ОрдерЗайм = $this->cross_orders->getMainOrderContractNumber($order);
            $item->НомерВКЛ = $rcl_contract->number ?? '';
            $item->ЭтоОсновнойЗаймВКЛ = $rcl_first_tranche;

            if ($order->utm_source == 'refinance') {
                $item->РефинансированныйНомерЗайма = $order->utm_medium;
            }

            if (!empty($order->referer_id)) {
                $item->РеферерID = $order->referer_id;
            }

            if (!empty($order->insure)) {
                $item->Страховка = new StdClass();
                $item->Страховка->insurer = $order->insurer;
                $item->Страховка->Ставка = $order->insure_percent;
                $item->Страховка->OrderID = $order->insure->register_id ?? '';
                $item->Страховка->OperationID = $order->insure->operation_id ?? '';
            }

            if (!empty($order->credit_doctor)) {
                $item->КД = new StdClass();
                $item->КД->СуммаКД = $order->credit_doctor->amount;
                $item->КД->insurer = $order->insurer;
                $item->КД->OrderID = $order->credit_doctor->transaction->register_id ?? '';
                $item->КД->OperationID = $order->credit_doctor->transaction->operation ?? '';
                $item->КД->КомплектID = $order->credit_doctor->credit_doctor_condition_id;
                $item->КД->КомплектНазвание = 'Комплект ' . $order->credit_doctor->credit_doctor_condition_id;
                $item->КД->НомерКарты = $order->credit_doctor->transaction->card_pan;

                $item->СуммаНаРуки -= (float)$order->credit_doctor->amount;
            }

            if (!empty($order->star_oracle))
            {
                $item->ЗвездныйОракул = new StdClass();
                $item->ЗвездныйОракул->СуммаSO = $order->star_oracle->amount;
                $item->ЗвездныйОракул->OrderID = $order->star_oracle->transaction->register_id ?? '';
                $item->ЗвездныйОракул->OperationID = $order->star_oracle->transaction->operation ?? '';
                $item->ЗвездныйОракул->НомерКарты = $order->star_oracle->transaction->card_pan;

                $item->СуммаНаРуки -= (float)$order->star_oracle->amount;
            }


            if (!empty($order->safe_deal))
            {
                $item->БезопаснаяСделка = new StdClass();
                $item->БезопаснаяСделка->СуммаБС = $order->safe_deal->amount;
                $item->БезопаснаяСделка->OrderID = $order->safe_deal->transaction->register_id ?? '';
                $item->БезопаснаяСделка->OperationID = $order->safe_deal->transaction->operation ?? '';
                $item->БезопаснаяСделка->НомерКарты = $order->safe_deal->transaction->card_pan;

                $item->СуммаНаРуки -= (float)$order->safe_deal->amount;
            }

            if (!empty($order->tv_medical)) {
                $item->Телемедицина = new StdClass();
                $item->Телемедицина->ID_ВитаМед = 'ID_' . $order->tv_medical->tv_medical_id;
                $item->Телемедицина->Сумма = $order->tv_medical->amount;
                $item->Телемедицина->insurer = '';
                $item->Телемедицина->OrderID = $order->tv_medical->transaction->register_id ?? '';
                $item->Телемедицина->OperationID = $order->tv_medical->transaction->operation ?? '';
                $item->Телемедицина->НомерПолиса = $order->tv_medical->id;

                $item->СуммаНаРуки -= (float)$order->tv_medical->amount;
            }

            $item->Клиент = new StdClass();
            $item->Клиент->id = $order->user->UID;
            $item->Клиент->ФИО = trim($order->user->lastname . ' ' . $order->user->firstname . ' ' . $order->user->patronymic . ' ' . $order->user->birth);
            $item->Клиент->Фамилия = trim($order->user->lastname);
            $item->Клиент->Имя = trim($order->user->firstname);
            $item->Клиент->Отчество = trim($order->user->patronymic);
            $item->Клиент->ДатаРождения = date('YmdHis', strtotime($order->user->birth));
            $item->Клиент->МестоРождения = trim($order->user->birth_place);
            $item->Клиент->АдресПроживания = ''; //TODO
            $item->Клиент->АдресРегистрации = ''; //TODO
            $item->Клиент->Телефон = $this->format_phone($order->user->phone_mobile);
            $item->Клиент->ОКАТО = $this->user_data->read($order->user->id, 'okato') ?? '';
            $item->Клиент->ОКТМО = $this->user_data->read($order->user->id, 'oktmo') ?? '';
            $item->Клиент->ИНН = $order->user->inn;
            $item->Клиент->Пол = $order->user->gender == 'male' ? 'Мужской' : 'Женский';
            $item->Клиент->Самозанятый = (int)($this->order_data->read($order->order_id, $this->order_data::SELF_EMPLOYEE_ORDER) ?? 0);

            $item->Клиент->АдресРегистрацииГород = trim($order->user->Regcity . ' ' . $order->user->Regcity_shorttype);
            $item->Клиент->АдресРегистрацииДом = trim($order->user->Reghousing . (empty($order->user->Regbuilding) ? '' : ' стр. ' . $order->user->Regbuilding));
            $item->Клиент->АдресРегистрацииИндекс = trim($order->user->Regindex);
            $item->Клиент->АдресРегистрацииКвартира = trim($order->user->Regroom);
            $item->Клиент->АдресРегистрацииРегион = trim($order->user->Regregion . ' ' . $order->user->Regregion_shorttype);
            $item->Клиент->АдресРегистрацииУлица = trim($order->user->Regstreet . ' ' . $order->user->Regstreet_shorttype);
            $item->Клиент->АдресРегистрацииРайон = empty($order->user->Regdistrict) ? '' : trim($order->user->Regdistrict);
            $item->Клиент->АдресРегистрацииНасПункт = empty($order->user->Reglocality) ? '' : trim($order->user->Reglocality);

            $item->Клиент->АдресФактическогоПроживанияГород = trim($order->user->Faktcity . ' ' . $order->user->Faktcity_shorttype);
            $item->Клиент->АдресФактическогоПроживанияДом = trim($order->Fakthousing . (empty($order->user->Faktbuilding) ? '' : ' стр. ' . $order->user->Faktbuilding));
            $item->Клиент->АдресФактическогоПроживанияИндекс = trim($order->user->Faktindex);
            $item->Клиент->АдресФактическогоПроживанияКвартира = trim($order->user->Faktroom);
            $item->Клиент->АдресФактическогоПроживанияРегион = trim($order->user->Faktregion . ' ' . $order->user->Faktregion_shorttype);
            $item->Клиент->АдресФактическогоПроживанияУлица = trim($order->user->Faktstreet . ' ' . $order->user->Faktstreet_shorttype);
            $item->Клиент->АдресФактическогоПроживанияРайон = empty($order->user->Faktdistrict) ? '' : trim($order->user->Faktdistrict);
            $item->Клиент->АдресФактическогоПроживанияНасПункт = empty($order->user->Faktlocality) ? '' : trim($order->user->Faktlocality);

            $item->Клиент->ОрганизацияАдрес = !empty($order->user->work_address) ? trim($order->user->work_address) : '';
            $item->Клиент->ОрганизацияДолжность = trim($order->user->profession);
            $item->Клиент->ОрганизацияНазвание = trim($order->user->workplace);
            $item->Клиент->ОрганизацияЕжемесячныйДоход = trim($order->user->income_base);
            $item->Клиент->ОрганизацияТелефон = $this->format_phone($order->user->work_phone);
            $item->Клиент->ОрганизацияТелефонРуководителя = '';
            $item->Клиент->ОрганизацияФИОРуководителя = trim($order->user->workdirector_name);

            $item->Клиент->Паспорт = new StdClass();
            $item->Клиент->Паспорт->Серия = (string)substr(str_replace(array(' ', '-'), '', $order->passport_serial), 0, 4);
            $item->Клиент->Паспорт->Номер = (string)substr(str_replace(array(' ', '-'), '', $order->passport_serial), 4, 6);
            $item->Клиент->Паспорт->КемВыдан = trim($order->user->passport_issued);
            $item->Клиент->Паспорт->КодПодразделения = trim($order->subdivision_code);
            $item->Клиент->Паспорт->ДатаВыдачи = date('YmdHis', strtotime($order->user->passport_date));

            $agreeClaimValue = $this->order_data->read($order->order_id, $this->order_data::AGREE_CLAIM_VALUE);
            $item->Клиент->ОтказОтУступкиПраваТребования = !empty($agreeClaimValue) ? $agreeClaimValue : 0;

            $item->Клиент->КонтактныеЛица = [];

            $item->Payment = new StdClass();
            $item->Payment->CardId = $order->card->id;
            $item->Payment->Дата = date('YmdHis', strtotime($order->p2pcredit->date));
            $item->Payment->PaymentId = $order->p2pcredit->operation_id;
            $item->Payment->OrderId = $order->p2pcredit->register_id;
            $item->НомерСектора = $order->p2pcredit->body['sector'];


            $items[] = $item;
        }

        $request = new StdClass();
        $request->ArrayContracts = json_encode($items, JSON_UNESCAPED_UNICODE);

        try {
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $stat_z_client->__soapCall('RequestCRM', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $returned = $fault;
        }
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($items, $returned);
        echo '</pre><hr />';
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl RequestCRM', (array)$request, (array)$returned, 'RequestCRM.txt');

        return $returned;
    }

    /**
     * Soap1c::send_payments_new()
     * Отсылает данные по оплатам клиентов
     * Должен содержать обьекты contract, insurance
     * Метод    OplataCRM
     * Параметр    ArrayOplata
     * @param mixed $transactions
     * @return
     */
    public function send_payments_new($payments)
    {
        $items = array();
        foreach ($payments as $payment) {
            $item = new StdClass();

            $item->ID = $this->best2pay->get_payment_number($payment);
            $item->Дата = date('YmdHis', strtotime($payment->operation_date));
            $item->Пролонгация = empty($payment->prolongation) ? 0 : 1;
            $item->Закрытие = 0; //TODO: Доделать
            $item->ЗаймID = $payment->contract_number;
            $item->СрокПролонгации = 16;
            $item->Сумма = (float)$payment->amount; //Сумма од + %
            $item->СуммаОД = $payment->body_summ;
            $item->СуммаПроцентов = $payment->percents_summ;
            $item->OperationID = $payment->register_id;
            $item->OrderID = $payment->operation_id;
            $item->Оплаты = [];

            if (!empty($payment->multipolis)) {
                $item->Оплаты[] = [
                    'ВидОплаты' => 'Мультиполис',
                    'Сумма' => $payment->multipolis->amount,
                    'НомерДоговора' => $payment->multipolis->number,
                ];
                $item->Сумма -= $payment->multipolis->amount; // (тут без мультиполиса)
            }

            if (!empty($payment->tv_medical)) {
                $item->Оплаты[] = [
                    'ВидОплаты' => 'Телемедицина',
                    'Сумма' => $payment->tv_medical->amount,
                    'НомерДоговора' => $payment->tv_medical->id,
                ];
                $item->Сумма -= $payment->tv_medical->amount; // (тут без телемедицины)
            }

            if ($payment->insure > 0) {
                $item->Оплаты[] = [
                    'ВидОплаты' => 'ШтрафнойКД',
                    'Сумма' => (float)$payment->insure,
                    'КомплектНазвание' => 'ШтрафнойКД',
                    'КомплектID' => 99,
                ];
                $item->Сумма -= $payment->insure;
            }

            $items[] = $item;
        }

        $request = new StdClass();

        $request->ArrayOplata = json_encode($items, JSON_UNESCAPED_UNICODE);
        $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");


//		$returned = $stat_z_client->__soapCall('OplataCRM', array($request));
        if (!isset($returned)) {
            $returned = null;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl OplataCRM', (array)$request, (array)$returned, 'OplataCRM.txt');

        return $returned;
    }

    //LoanBalances в сервисе WebSignal, параметры Date1 и Date2 в формате ггггММддЧЧммсс
    public function get_loan_balances($date_from, $date_to)
    {
        $request = new StdClass();

        $request->Date1 = date('YmdHis', strtotime($date_from));
        $request->Date2 = date('YmdHis', strtotime($date_to));
        $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($request);
        echo '</pre><hr />';

        try {
            $returned = $stat_z_client->__soapCall('LoanBalances', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        return $returned;
    }

    public function DocumentEditing($zaim, $type, $action, $uid)
    {
        $request = new StdClass();

        $request->НомерЗайма = $zaim;
        $request->ТипДокумента = $type;
        $request->Событие = $action;
        $request->УИД = $uid;


        try {
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $stat_z_client->__soapCall('DocumentEditing', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            echo "SOAP Error: " . $fault->getMessage();
        }

//        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($request);echo '</pre><hr />';
        return $returned;
    }

    /**
     * @param $zaimNumber
     * @return mixed
     */
    public function deleteKd($zaimNumber)
    {
        $request = new StdClass();

        $request->НомерЗайма = $zaimNumber;

        try {
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            return $stat_z_client->__soapCall('DeleteKD', array($request));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            echo "SOAP Error: " . $fault->getMessage();
        }
    }

    /**
     * Отправка AxiNBKI в 1С
     * @param array $params
     * @return mixed
     */
    public function send_aksi(array $params)
    {
        $request = new StdClass();

        $request->СкорБалл = $params['ball'];
        $request->Решение = $params['result'];
        $request->Лимит = $params['limit'];
        $request->НомерЗаявки = $params['order_id'];
        $request->НомерЗайма = '';
        $request->ПроскореноАкси = true;
        $request->ПроскореноСкористой = false;
        $request->ВерсияАкси = $params['version'];
        $request->final_limit = $params['final_limit'];
        $request->sc_new01 = $params['sc_new01'];
        $request->sc_new02 = $params['sc_new02'];
        $request->sc_new03 = $params['sc_new03'];
        $request->sc_rpt01 = $params['sc_rpt01'];
        $request->sc_rpt02 = $params['sc_rpt02'];
        $request->sc_rpt03 = $params['sc_rpt03'];

        $logUrl = $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl Aksi';
        try {
            $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $stat_z_client->__soapCall('Aksi', array($request));

            $this->logging(__METHOD__, $logUrl, (array)$request, (array)$response, 'soapAksi.txt');
            $this->automationFails->setSoapError(false);

            return $response;
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $logUrl .= "\nERROR: " . $fault->getMessage();
            $this->logging(__METHOD__, $logUrl, (array)$request, [], 'soapAksi.txt');
            echo "SOAP Error: " . $fault->getMessage();
        } catch (Exception $e) {
            $logUrl .= "\nERROR: " . $e->getMessage();
            $this->logging(__METHOD__, $logUrl, (array)$request, [], 'soapAksi.txt');
            echo "SOAP Error: " . $e->getMessage();
        }
    }

    public function send_additional_phone($user_uid, $phone)
    {
        $data = $this->soap->generateObject([
            'ContragentUID' => $user_uid,
            'PhoneNumber' => $phone,
        ]);
        return $this->soap->requestSoap($data, 'WebSignal', 'AddPhoneNumberFromKI', 'soapAdditionalPhone.txt');
    }

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

    public function sendAddUserToCallBlacklist1c(string $uid)
    {
        $data = $this->soap->generateObject([
            'Date' => date('Y-m-d\TH:i:s'),
            'ContragentUID' => $uid,
            'SystemName' => static::SYSTEM_NAME,
        ]);
        $res = $this->soap->requestSoap($data, 'WebSignal', 'AddToCallBlacklist');
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl', (array)$data, (array)$res);
    }

    public function deleteUserFromCallBlacklist1c(string $uid)
    {
        $data = $this->soap->generateObject([
            'Date' => date('Y-m-d\TH:i:s'),
            'ContragentUID' => $uid,
            'SystemName' => static::SYSTEM_NAME,
        ]);
        $res = $this->soap->requestSoap($data, 'WebSignal', 'DeleteFromCallBlacklist');
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl', (array)$data, (array)$res);
    }

    public function getResponsibleForContracts(array $contractsNumber): array
    {
        $data = $this->soap->generateObject([
            'JSONArrayContractNumbers' => json_encode($contractsNumber, JSON_UNESCAPED_UNICODE),
        ]);

        return $this->soap->requestSoap($data, 'WebSignal', 'ResponsibleForContracts');
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
            $returned = $client->__soapCall('RemoveCard', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl RemoveCard', (array)$z, (array)$returned);

        return $returned->return;
    }

    /**
     * Отправляет расчет ПДН и данные для "Лист оценки платежеспособности заемщика" в 1С
     *
     * @param stdClass $pdnOrder
     * @param stdClass $pdnCalculationResult
     * @param string $efrsb
     * @param string $fssp
     * @return array
     */
    public function send_pdn(stdClass $pdnOrder, stdClass $pdnCalculationResult, string $efrsb, string $fssp): array
    {
        $request = new StdClass();
        $request->НомерЗайма = $pdnOrder->contract_number;
        $request->ПДН = $pdnCalculationResult->pti_percent;
        $request->СреднемесячныеПлатежи = $pdnCalculationResult->average_monthly_payment;
        $request->Доход = $pdnCalculationResult->average_monthly_income;
        $request->Источник = $pdnCalculationResult->calculation_type;

        $document = new StdClass();

        $manager = $pdnOrder->manager;
        if (empty($manager)) {
            $manager = 'Не указано';
        } else if ($manager === 'System') {
            $manager = 'Система';
        }

        $document->Банкротство = $efrsb;
        $document->КоличествоИсполнительныхПроизводств = $fssp;
        $document->Должность = $pdnOrder->profession ?? 'Не указано';
        $document->ЕжемесячныйДоход = max($pdnOrder->income_base, $pdnCalculationResult->form_income_salary_rounded);
        $document->Верификатор = $manager;

        $request->ДанныеДляЛистаОценки = json_encode($document, JSON_UNESCAPED_UNICODE);

        return $this->soap->requestSoap($request, 'WebSignal', 'FullPDN', 'sendPdn.txt');
    }


    /**
     * Получить просроченные заявки (получаем номер договора и день просрочки)
     *
     * Пример ответа:
     * 0 =>
     *     array (
     *     'LoanNumber' => '00216-18-001',
     *     'DayOfDelay' => 1,
     * ),
     * @param int $dayOfDelayStart День просрочки, с которого ищем заявки. Например, 0
     * @param int $dayOfDelayEnd День просрочки, до которого ищем заявки. Например, 5
     * @return array
     */
    public function getOverdueContracts(int $dayOfDelayStart, int $dayOfDelayEnd): array
    {
        $request = new StdClass();
        $request->DayOfDelayStart = $dayOfDelayStart;
        $request->DayOfDelayEnd = $dayOfDelayEnd;

        $result = $this->soap->requestSoap($request, 'WebSignal', 'LoanNumbersByDaysOfDelay');

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl LoanNumbersByDaysOfDelay',
            (array)$request, $result, 'overdue_orders.txt');

        return $result;
    }

    /**
     * Получить часовой пояс (+GMT часов) заявок
     *
     * Пример ответа:
     * 0 =>
     *     array (
     *     'LoanNumber' => '00216-18-001',
     *     'Timezone' => 3,
     * )
     *
     * @param array $contractsNumber Номера договора, по заявкам которым нужно получить часовой пояс
     * @return array
     */
    public function getContractsTimezone(array $contractsNumber): array
    {
        $request = new StdClass();
        $request->Loans = json_encode($contractsNumber);

        $result = $this->soap->requestSoap($request, 'WebSignal', 'LoanTimezones');

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl LoanTimezones',
            (array)$request, $result, 'overdue_contracts.txt');

        return $result;
    }

    public function sendComplaint(array $complaintData)
    {
        $result = $this->soap->requestSoap((object)$complaintData, 'WebSignal', 'AddComplaint');
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl AddComplaint',
            (array)$complaintData, $result, 'AddComplaint.txt');

        return $result;
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
            $response = (array)$response;

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $response['error'] = $fault->getMessage();
        } catch (Exception $exception) {
            $response['error'] = $exception->getMessage();
        }

        $responseToLog = array();
        $responseToLog['return'] = substr($response['return'] ?? '', 50);
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl NoticeOfAssignment', $request, $responseToLog);

        return $response;
    }


    /**
     * Получить номера договоров определенного ответственного (папки)
     * @param string $responsibleCode Код ответственного
     * @return array
     */
    public function getContractsByResponsibleCode(string $responsibleCode): array
    {
        $response = $this->requestSoap(['Сотрудник' => $responsibleCode], 'WebSignal', 'DogovorsOn0', 'responsible_contracts.txt');

        return $response['response'] ?? [];
    }

    public function setPermissionsProlongation(string $orderUID, bool $allowed, bool $prohibited, int $timeToBanChanges = 0): array
    {
        $request = [
            'LoanApplicationUID'        => $orderUID,
            'ProlongationAllowed'      => $allowed,
            'ProlongationIsProhibited' => $prohibited,
            'TimeToBanChanges'         => $timeToBanChanges,
        ];
        
        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('SetPermissionsProlongation', array($request));
            $response = (array)$response;

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $response['error'] = $fault->getMessage();
        } catch (Exception $exception) {
            $response['error'] = $exception->getMessage();
        }

        $responseToLog = array();
        $responseToLog['return'] = $response;
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl SetPermissionsProlongation', $request, $responseToLog);

        return $response;
    }

    public function permissionsProlongation(string $orderUID): array
    {
        try {
            $request = [
                'LoanApplicationUID' => $orderUID,
            ];
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('PermissionsProlongation', array($request));
            $response = (array)$response;

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $response['error'] = $fault->getMessage();
        } catch (Exception $exception) {
            $response['error'] = $exception->getMessage();
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl PermissionsProlongation', $request, $response);

        return $response;
    }

    public function contractForms(string $contractNumber): array
    {
        try {
            $request = [
                'ContractNumber' => $contractNumber,
            ];
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('ContractForms', array($request));
            $response = (array)$response;

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $response['error'] = $fault->getMessage();
        } catch (Exception $exception) {
            $response['error'] = $exception->getMessage();
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl ContractForms', $request, $response);

        return $response;
    }

    /**
     * Получить ответственных для исключения из нагрузки
     *
     * @param string $organizationUID UID организации
     * @return array Массив ответственных в формате [['Name' => 'ФИО', 'UID' => 'uid']]
     */
    public function getResponsiblesFor1CWorkloadExclusion(string $organizationUID): array
    {
        $request = [
            'OrganizationUID' => $organizationUID
        ];

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('GetResponsibleForWorkloadExclusion', [$request]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        if (isset($response->return)) {
            $result = json_decode($response->return, true);

            if (isset($result['Result'], $result['Responsibles']) && $result['Result'] === true) {
                $responsibles = json_decode($result['Responsibles'], true);

                if (is_array($responsibles)) {

                    $this->logging(
                        __METHOD__,
                        $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl GetResponsibleForWorkloadExclusion',
                        $request,
                        $response
                    );

                    return $responsibles;
                }
            }
        }

        $this->logging(
            __METHOD__,
            $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl GetResponsibleForWorkloadExclusion',
            $request,
            $response
        );

        return [];
    }

    /**
     * Исключить заем из нагрузки и назначить ответственного
     *
     * @param string $loanUID UID займа
     * @param string $responsibleUID UID ответственного
     * @return bool Результат операции
     */
    public function removeFromTheLoad(string $loanUID, string $responsibleUID): bool
    {
        $request = [
            'LoanUID' => $loanUID,
            'ResponsibleUID' => $responsibleUID
        ];

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('RemoveFromeTheLoad', [$request]);

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            throw $fault;
        }

        if (isset($response->return)) {
            $result = json_decode($response->return, true);

            if (isset($result['Result']) && $result['Result'] === true) {
                $this->logging(
                    __METHOD__,
                    $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl RemoveFromeTheLoad',
                    $request,
                    $response
                );

                return true;
            }
        }

        $this->logging(
            __METHOD__,
            $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl RemoveFromeTheLoad',
            $request,
            $response
        );

        return false;
    }

    public function paymentDeferment($zaimNumber): array
    {
        $data = $this->soap->generateObject([
            'ZaimNumber' => $zaimNumber,
        ]);

        return $this->soap->requestSoap($data, 'WebSignal', 'RegisterLoanDeferral', 'soapPaymentDeferment.txt');
    }

    /**
     * Отправка заявки на возврат услуги по реквизитам
     * WebSignal, метод AddRequestReturnService
     * 
     * Параметры:
     * - ВидУслуги: КредитныйДоктор, Мультиполис, Телемедицина, ЗвездныйОракул
     * - Сумма: сумма возврата
     * - OperationID: ID операции покупки услуги
     * - НомерСчета: номер счета клиента (р/с)
     * - БИКБанка: БИК банка
     * - ФИОПолучателя: ФИО получателя перевода (может отличаться от ФИО клиента)
     *
     * Возможные ответы:
     * - ОК: заявка успешно создана
     * - Не найдена услуга: услуга не найдена по OperationID
     * 
     * @param array $params
     * @return object
     */
    public function addRequestReturnService(array $params): object
    {
        $serviceTypeMap = [
            'credit_doctor' => 'КредитныйДоктор',
            'multipolis'    => 'Мультиполис',
            'tv_medical'    => 'Телемедицина',
            'star_oracle'   => 'ЗвездныйОракул',
            'safe_deal'   => 'БезопаснаяСделка',
        ];
        
        $request = new StdClass();
        $request->ВидУслуги = $serviceTypeMap[$params['service_type']] ?? '';
        $request->Сумма = $params['amount'];
        $request->OperationID = $params['operation_id'];
        $request->НомерСчета = $params['account_number'];
        $request->БИКБанка = $params['bik'];
        $request->ФИОПолучателя = isset($params['recipient_fio']) ? (string)$params['recipient_fio'] : '';

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('AddRequestReturnService', array($request));
            
            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);
            $response = $fault;
        } catch (Exception $e) {
            $response = $e;
        }
        
        $this->logging(
            __METHOD__, 
            $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl AddRequestReturnService',
            (array)$request,
            (array)$response,
            'ReturnByRequisites.txt'
        );
        
        return $response;
    }

    /**
     * Получение статусов заявок на возврат услуг по реквизитам
     * WebSignal, метод StatusRequestReturnService
     *
     * @param array $params
     * @return array
     */
    public function getStatusRequestReturnService(array $params): array
    {
        $serviceTypeMap = [
            'credit_doctor' => 'КредитныйДоктор',
            'multipolis'    => 'Мультиполис',
            'tv_medical'    => 'Телемедицина',
            'star_oracle'   => 'ЗвездныйОракул',
            'safe_deal'   => 'БезопаснаяСделка',
        ];

        $payload = [];

        if (!empty($params['contract_number'])) {
            $payload['НомерЗайма'] = $params['contract_number'];
        } else {
            $payload['OperationID'] = $params['operation_id'] ?? '';
            $payload['ВидУслуги'] = $serviceTypeMap[$params['service_type']] ?? '';
        }

        $request = new StdClass();
        $request->TextJson = json_encode($payload, JSON_UNESCAPED_UNICODE);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('StatusRequestReturnService', [$request]);
            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);
            $response = $fault;
        } catch (Exception $e) {
            $response = $e;
        }

        $this->logging(
            __METHOD__,
            $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl StatusRequestReturnService',
            (array)$request,
            (array)$response,
            'ReturnByRequisites.txt'
        );

        if (isset($response->return)) {
            $decoded = json_decode($response->return, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        if ($response instanceof Exception) {
            return ['error' => $response->getMessage()];
        }

        return [];
    }

    /**
     * Получить сумму переплаты по номеру займа
     * @param string $contractNumber Номер займа
     * @return float|array Возвращает сумму (float) или массив с ошибкой
     */
    public function getOverpaymentAmount(string $contractNumber)
    {
        $request = ['НомерЗайма' => $contractNumber];
        $url = $this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl";

        try {
            $client = new SoapClient($url);
            $returned = $client->__soapCall('OverpaymentAmount', array($request));
            $this->automationFails->setSoapError(false);

            if (isset($returned->return)) {
                return (float)$returned->return;
            }

            $error = 'Не получен ответ от 1С';
        } catch (Exception $e) {
            $this->automationFails->setSoapError($e instanceof SoapFault);
            $error = $e->getMessage();
        }

        $this->logging(__METHOD__, $url . 'OverpaymentAmount', $request, ['error' => $error], 'ReturnByRequisites.txt');
        return ['error' => $error];
    }

    public function getPartnerParam(string $companyName): string
    {
        $partner = $companyName;
        if (in_array($companyName, ['Akvarius', 'Boostra'])) {
            $partner = 'Boostra';
        } elseif ($partner !== 'RZS') {
            $partner = ucfirst(strtolower($partner));
        }

        return $partner;
    }

    /**
     * Отправить заявку на возврат переплаты в 1С
     * @param array $params ['loan_number', 'amount', 'account_number', 'bik', 'recipient_fio']
     * @return object
     */
    public function addRequestReturnOverpayment(array $params): object
    {
        $request = new StdClass();
        $request->НомерЗайма = $params['loan_number'];
        $request->Сумма = $params['amount'];
        $request->НомерСчета = $params['account_number'];
        $request->БИКБанка = $params['bik'];
        $request->ФИОПолучателя = isset($params['recipient_fio']) ? (string)$params['recipient_fio'] : '';

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $response = $client->__soapCall('AddRequestReturnOverpayment', array($request));
            
            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);
            $response = $fault;
        }
        
        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl AddRequestReturnOverpayment',
            (array)$request, (array)$response, 'ReturnByRequisites.txt');

        return $response;
    }

    /**
     * Обновить данные по сущности заявки из 1С (для получения полей см. getHyperCData)
     *
     * @param string $order_1c_id Номер заявки в 1С
     * @param array $params Параметры ['Хайпер' => value, 'ВерсияМодели' => value]
     * @return mixed
     */
    public function updateApplicationField($order_1c_id, array $params)
    {
        $z = new StdClass();
        $z->ApplicationNumber = $order_1c_id;

        $fields = new StdClass();

        if (array_key_exists('Хайпер', $params)) {
            $fields->Хайпер = $params['Хайпер'];
        }

        if (array_key_exists('ВерсияМодели', $params)) {
            $fields->ВерсияМодели = $params['ВерсияМодели'];
        }

        $z->Fields = json_encode($fields, JSON_UNESCAPED_UNICODE);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('UpdateApplicationField', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl UpdateApplicationField', (array)$z, (array)$fault, 'application_field.txt');
            return $fault;
        } catch (Throwable $fault) {
            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl UpdateApplicationField', (array)$z, (array)$fault, 'application_field.txt');
            return $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl UpdateApplicationField', (array)$z, (array)$returned, 'application_field.txt');

        return $returned->return ?? $returned;
    }

    /**
     * Получить данные по сущности заявки из 1С
     *
     * @param string $order_1c_id Номер заявки в 1С
     * @param array $fields Поля для получения из 1C ['Хайпер', 'ВерсияМодели']
     * @return mixed
     */
    public function getApplicationField($order_1c_id, array $fields)
    {
        $z = new StdClass();
        $z->ApplicationNumber = $order_1c_id;
        $z->Fields = json_encode($fields, JSON_UNESCAPED_UNICODE);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
            $returned = $client->__soapCall('GetApplicationField', array($z));

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetApplicationField', (array)$z, (array)$fault, 'application_field.txt');
            return $fault;
        } catch (Throwable $fault) {
            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetApplicationField', (array)$z, (array)$fault, 'application_field.txt');
            return $fault;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetApplicationField', (array)$z, (array)$returned, 'application_field.txt');

        return $returned->return ?? $returned;
    }

    /**
     * Soap1c::SendRcl()
     * Отправка кредитной линии в 1С
     *
     * WebSignal, метод CreditLine
     * Параметр ArrayContracts - массив из структур
     *
     * @param object $rcl_contract Контракт с вложенными объектами user и organization
     * @return mixed
     */
    public function SendRcl($rcl_contract)
    {
        $item = new StdClass();
        $item->Номер = $rcl_contract->number;
        $item->Дата = date('YmdHis', strtotime($rcl_contract->date_create));
        $item->ДатаНачала = date('YmdHis', strtotime($rcl_contract->date_start));
        $item->ДатаОкончания = date('YmdHis', strtotime($rcl_contract->date_end));
        $item->УИДДляБКИ = $rcl_contract->uid;
        $item->КодАСП = $rcl_contract->asp_code;
        $item->Сумма = (float)$rcl_contract->max_amount;
        $item->ИННОрганизации = $rcl_contract->organization->inn;
        $item->УИДКлиента = $rcl_contract->user->UID;
        $item->ПСК = $rcl_contract->psk;
        $item->ПСКРуб = $rcl_contract->psk_rub;
        $item->ПДН = $rcl_contract->pdn ?? '';
        $item->Источник = $rcl_contract->calculation_type ?? 'ПДН при доходе по Росстату'; // TODO пока не получаем из сервиса ПДН для ВКЛ
        $item->НомерЗаявки = $rcl_contract->order->id_1c;

        $request = new StdClass();
        $request->ArrayContracts = json_encode([$item], JSON_UNESCAPED_UNICODE);

        try {
            $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
            $returned = $client->__soapCall('CreditLine', array($request));
        } catch (Exception $e) {
            $returned = $e;
        }

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl CreditLine', (array)$request, (array)$returned, 'rcl_contracts.txt');

        return $returned;
    }
    /**
     * GetOverdue9Clients в сервисе WebSignal
     * Возвращает клиентов с просрочкой 9+ дней и начисления ШКД за период
     *
     * @param string $dateFrom Дата начала (yyyy-MM-dd)
     * @param string $dateTo Дата окончания (yyyy-MM-dd)
     * @return array
     */
    public function getOverdue9Clients(string $dateFrom, string $dateTo): array
    {
        $request = new StdClass();
        $request->Date1 = $dateFrom;
        $request->Date2 = $dateTo;

        $result = [];
        $wsdlUrl = $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl';

        $context = stream_context_create(['http' => ['timeout' => 30]]);

        try {
            $client = new SoapClient($wsdlUrl, [
                'stream_context' => $context,
                'connection_timeout' => 10,
            ]);
            $returned = $client->__soapCall('GetOverdue9Clients', [$request]);

            if ($this->isJson($returned->return)) {
                $result['response'] = json_decode($returned->return, true);
            } else {
                $result['response'] = $returned->return ?? $returned;
            }

            $this->automationFails->setSoapError(false);
        } catch (SoapFault $fault) {
            $this->automationFails->setSoapError(true);
            $result['errors'] = $fault->getMessage();
        }

        $this->logging(__METHOD__, $wsdlUrl . ' GetOverdue9Clients', (array)$request, $result, 'overdue9clients.txt');
        return $result;
    }

}
