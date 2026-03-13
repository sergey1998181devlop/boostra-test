<?php
ini_set("soap.wsdl_cache_enabled", 0);
ini_set('default_socket_timeout', '60');

require_once 'Simpla.php';

class Dop1c extends Simpla
{
    private $url = 'http://141.0.180.209:8218/bp3_Alphavit/ru_RU/ws/';
    private $login = 'Админ';
    private $password = 'Админ2$';

    public function __construct()
    {
        parent::__construct();
    }
    
    
    /**
     * Dop1c::test()
     * $item->user
     * $item->transaction
     * 
     * @return
     */
    public function send_return_service($item)
    {
        $data = [
            'Клиент_id' => $item->user->UID,
            'Дата' => date('YmdHis', strtotime($item->data->operation_date)),
            'ДоговорЗайма' => $item->data->contract_number,
            'Сумма' => $item->data->amount,
            'Услуга_id' => $item->data->type,
            'КомплектНазвание' => $item->data->complect,
            'ПродажаOrderID' => $item->data->b2p_order,
            'ПродажаOperationID' => $item->data->b2p_operation,
            'Сектор' => $item->data->b2p_sector,
            'ВозвратOrderID' => $item->data->return_b2p_order,
            'ВозвратOperationID' => $item->data->return_b2p_operation,
            'НомерКарты' => $item->data->card_pan,
            'ИННПоставщикаУслуги' => $item->data->provider_inn,
            'ТипПродажи' => $item->data->agent ? 'Агентская' : 'Прямая',
            'Операция_id' => $item->data->number, 
            'ПродажаОперация_id' => $item->data->sale_number, 
            'НомерДоговора' => $item->data->service_number,
            'Организация' => 'Бустра',
        ];
        
        $request = new StdClass();
        $request->TextJSON = json_encode($data, JSON_UNESCAPED_UNICODE);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($data, $request);echo '</pre><hr />';        
        return $this->send_request('CRM_WebService', 'ReturnSaleService', $request, 1);
    }

    public function send_service($item)
    {
        list($passport_series, $passport_number) = $this->parse_passport($item->user->passport_serial);
        
        $data = [
            'Дата' => date('YmdHis', strtotime($item->data->operation_date)),
            'Клиент_id' => $item->user->UID,
            'ДоговорЗайма' => $item->data->contract_number,
            'Сумма' => $item->data->amount,
            'Услуга_id' => $item->data->type,
            'КомплектНазвание' => $item->data->complect,
            'НомерДоговора' => $item->data->service_number,
            'Операция_id' => $item->data->number,
            'ПродажаOrderID' => $item->data->b2p_order,
            'ПродажаOperationID' => $item->data->b2p_operation,
            'Сектор' => $item->data->b2p_sector,
            'НомерКарты' => $item->data->card_pan,
            'ИННПоставщикаУслуги' => $item->data->provider_inn,
            'ТипПродажи' => $item->data->agent ? 'Агентская' : 'Прямая', // Прямая, Агентская
            'Организация' => 'Бустра',
            'Клиент' => [
            	'id' => $item->user->UID,
            	'ИНН' => $item->user->inn ?? '',
            	'СНИЛС' => $item->user->Snils ?? '',
            	'ФИО' => $item->user->lastname.' '.$item->user->firstname.' '.$item->user->patronymic.' '.$item->user->birth,
            	'Фамилия' => $item->user->lastname,
            	'Имя' => $item->user->firstname,
            	'Отчество' => $item->user->patronymic,
            	'ДатаРождения' => date('YmdHis', strtotime($item->user->birth)),
            	'МестоРождения' => $item->user->birth_place,
            	'АдресРегистрации' => $this->get_full_regaddress($item->user),
            	'АдресПроживания' => $this->get_full_faktaddress($item->user),
            	'Телефон' => $item->user->phone_mobile,
            	'ОКАТО' => $item->user->okato ?? '',
            	'ОКТМО' => $item->user->oktmo ?? '',
            	'Паспорт' => [
            		'Серия' => $passport_series,
            		'Номер' => $passport_number,
            		'КемВыдан' => $item->user->passport_issued,
            		'КодПодразделения' => $item->user->subdivision_code,
            		'ДатаВыдачи' => date('YmdHis', strtotime($item->user->passport_date)),
                ],
           	],
        ];
        
        $request = new StdClass();
        $request->TextJSON = json_encode($data, JSON_UNESCAPED_UNICODE);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($data, $request);echo '</pre><hr />';        
        return $this->send_request('CRM_WebService', 'SaleService', $request, 1);
    }

    private function send_request($service, $method, $request, $log = 1, $logfile = 'dop.txt')
    {
        $params = array();
        if (!empty($this->login) || !empty($this->password)) {
            $params['login'] = $this->login;
            $params['password'] = $this->password;
        }
        
        try {
            $service_url = $this->url . $service . ".1cws?wsdl";
            $client = new SoapClient($service_url, $params);
            $response = $client->__soapCall($method, array($request));
        } catch (Exception $fault) {
            $response = $fault;
        }
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($response);echo '</pre><hr />';
        if (!empty($log)) {
            $this->logging(__METHOD__, $service_url . ' ' . $method, (array)$request, (array)$response, $logfile);
        }

        return $response;
    }
    
    public function parse_passport($passport_serial)
    {
        $passport_serial = str_replace(['-', ' '], '', $passport_serial);
        return [
            substr($passport_serial, 0, 4),
            substr($passport_serial, 4, 6),
        ];
    }
    
    /**
     * Dop1c::get_full_regaddress()
     * 
     * @param object $user
     * @return string
     */
    public function get_full_regaddress($user)
    {
        $regaddress = '';
        
        $regaddress .= empty($user->Regindex) ? '' : trim($user->Regindex).', ';
        $regaddress .= empty($user->Regregion) ? '' : trim(trim($user->Regregion).' '.trim($user->Regregion_shorttype));
        $regaddress .= empty($user->Regdistrict) ? '' : ', '.trim($user->Regdistrict);
        $regaddress .= empty($user->Regcity) ? '' : ', '.trim(trim($user->Regcity).' '.trim($user->Regcity_shorttype));
        $regaddress .= empty($user->Reglocality) ? '' : ', '.trim($user->Reglocality);
        $regaddress .= empty($user->Regstreet) ? '' : ', '.trim(trim($user->Regstreet).' '.trim($user->Regstreet_shorttype));
        $regaddress .= empty($user->Reghousing) ? '' : ', д.'.trim($user->Reghousing);
        $regaddress .= empty($user->Regbuilding) ? '' : ', стр. '.trim($user->Regbuilding);
        $regaddress .= empty($user->Regroom) ? '' : ', кв. '.trim($user->Regroom);
        
        return $regaddress;
    }

    public function get_full_faktaddress($user)
    {
        $faktaddress = '';
        
        $faktaddress .= empty($user->Faktindex) ? '' : trim($user->Faktindex).', ';
        $faktaddress .= empty($user->Faktregion) ? '' : trim(trim($user->Faktregion).' '.trim($user->Faktregion_shorttype));
        $faktaddress .= empty($user->Faktdistrict) ? '' : ', '.trim($user->Faktdistrict);
        $faktaddress .= empty($user->Faktcity) ? '' : ', '.trim(trim($user->Faktcity).' '.trim($user->Faktcity_shorttype));
        $faktaddress .= empty($user->Faktlocality) ? '' : ', '.trim($user->Faktlocality);
        $faktaddress .= empty($user->Faktstreet) ? '' : ', '.trim(trim($user->Faktstreet).' '.trim($user->Faktstreet_shorttype));
        $faktaddress .= empty($user->Fakthousing) ? '' : ', д.'.trim($user->Fakthousing);
        $faktaddress .= empty($user->Faktbuilding) ? '' : ', стр. '.trim($user->Faktbuilding);
        $faktaddress .= empty($user->Faktroom) ? '' : ', кв. '.trim($user->Faktroom);
        
        return $faktaddress;
    }
}