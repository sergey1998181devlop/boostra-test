<?php

require_once 'Simpla.php';
require_once('addons/TinkoffMerchantAPI.php');	


class Tinkoff extends Simpla
{
    private $merchant;
    
    // включение тестового терминала
    private $test_mode = 0;
    
    const TINKOFF_TERMINAL_KEY_TEST = '1556097708543DEMO';
    const TINKOFF_SECRET_KEY_TEST = 'kotkrcylhgihbwpw';
    
    const TINKOFF_TERMINAL_KEY = '1556097708543';
    const TINKOFF_SECRET_KEY = 'a56zc57338umq6f1';
    
    const TINKOFF_TERMINAL_KEY_AFT = '1556097708543AFT';
    const TINKOFF_SECRET_KEY_AFT = 'a56zc57338umq6f1';
    
    const TINKOFF_TERMINAL_KEY_E2C = '1556097708543E2C';
    const TINKOFF_SECRET_KEY_E2C = '04MhNhetP413YhNl';
    
    const TINKOFF_TERMINAL_KEY_ATOP = '1614347646151ATOP';
    const TINKOFF_SECRET_KEY_ATOP = 'q0xgg8y6vnkzj751';

    const TRANSACTION_STATUS_CONFIRMED = 'CONFIRMED';
    const TRANSACTION_STATUS_AUTHORIZED = 'AUTHORIZED';
    
    // ключи для оплаты индивидуального рассмотрения
/*
тестовый
1628329339131DEMO 
2mqcgq7ud2tl4x84

рабочий
1628329339131
bnle94r0zcuaayv7
*/
    const TINKOFF_TERMINAL_KEY_IP = '1628329339131DEMO';
    const TINKOFF_SECRET_KEY_IP = '2mqcgq7ud2tl4x84';

    /*
        //Если Organization = "PO" Тогда

        TerminalKey ="1635245936571";// "1556097708543AFT";

        Password = "j6mijieksvqhc1z7";//"a56zc57338umq6f1";

        //ИначеЕсли Organization = "ST" Тогда

        TerminalKey ="1635427834376";// "1556097708543AFT";

        Password = "8ut3aj39iw56x58e";//"a56zc57338umq6f1";

        //ИначеЕсли Organization = "IP" Тогда

        TerminalKey ="1627553499438";// "1556097708543AFT";

        Password = "h6nq1i81ki9fwjy1";//"a56zc57338umq6f1";

        //ИначеЕсли Organization = "FR" Тогда

        TerminalKey ="1630329068653ATOP";// "1556097708543AFT";

        Password = "cr8cz6jadcoo5ut6";//"a56zc57338umq6f1";

        //ИначеЕсли Organization = "BoostraECOM" Тогда

        TerminalKey ="1556097708543";// "1556097708543AFT";

        Password = "a56zc57338umq6f1";//"a56zc57338umq6f1";

        //ИначеЕсли Organization = "T" Тогда

        TerminalKey ="1643378547748";// "1556097708543AFT";

        Password = "ipktb7vbdj0gj2v8";//"a56zc57338umq6f1";
    */
    
    public function __construct()
    {
    	parent::__construct();
        
        if ($this->is_developer)
            $this->test_mode = 0;
/*        
        if ($this->test_mode)
            $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_E2C, self::TINKOFF_SECRET_KEY_E2C, 'https://securepay.tinkoff.ru/v2');
        else
            $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY, self::TINKOFF_SECRET_KEY, 'https://securepay.tinkoff.ru/v2');
*/
    }
    
    public function hold($user_id, $card_id, $rebill_id)
    {
        // CRM mode
        return (array)json_decode(file_get_contents('https://manager.boostra.ru/ajax/tinkoff.php?action=hold&user_id='.$user_id.'&card_id='.$card_id.'&rebill_id='.$rebill_id));

        
        $order_id = $this->create_order_id($card_id);
        $format_sum = 100;

        // тестовый адрес терминала https://rest-api-test.tinkoff.ru/v2
        $this->merchant = new TinkoffMerchantAPI(
            self::TINKOFF_TERMINAL_KEY, 
            self::TINKOFF_SECRET_KEY, 
            'https://securepay.tinkoff.ru/v2'
//            'https://rest-api-test.tinkoff.ru/v2'
        );

        $data = new StdClass();
        $data->mfoAgreement = $order_id;
//        $data->email = 'alpex-s@rambler.ru';
        
        
        $this->merchant->init(array(
            'OrderId' => $order_id,
            'Amount' => $format_sum,
            'PayType' => 'T',
            'DATA' => $data,
        ));
        if ($this->merchant->error)
        {
            $response['error'] = $this->merchant->error;
        }
        else
        {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
            
            $charge_response = $this->merchant->charge(array(
                'PaymentId'=>$response['PaymentId'],
                'RebillId' => $rebill_id
            ));
            
            $cancel_response = $this->merchant->cancel(array('PaymentId'=>$response['PaymentId']));
        }
                
        return (array)json_decode(htmlspecialchars_decode($charge_response));
        
    }

    public function test_refund($payment_id) {
        // тестовый адрес терминала https://rest-api-test.tinkoff.ru/v2
        $this->merchant = new TinkoffMerchantAPI(
            self::TINKOFF_TERMINAL_KEY, 
            self::TINKOFF_SECRET_KEY, 
            'https://securepay.tinkoff.ru/v2'
            //'https://rest-api-test.tinkoff.ru/v2'
        );

        $cancel_response = $this->merchant->cancel(array('PaymentId'=>$payment_id));

        return (array)json_decode(htmlspecialchars_decode($payment_id));
    }


    public function take_insurance($order, $card_id, $rebill_id, $amount, $zaim_number)
    {
        $order_id = $this->create_order_id($card_id);
        $format_sum = $amount;//$this->format_summ($amount);

        // тестовый адрес терминала https://rest-api-test.tinkoff.ru/v2
        $insurer = $this->orders->get_insure_ip();

        //TODO: выбираем терминал
        $this->merchant = new TinkoffMerchantAPI(
            self::TINKOFF_TERMINAL_KEY,
            self::TINKOFF_SECRET_KEY,
            'https://securepay.tinkoff.ru/v2'
            //'https://rest-api-test.tinkoff.ru/v2'
        );

        $data = new StdClass();
        $data->mfoAgreement = $order_id;
        //        $data->email = 'alpex-s@rambler.ru';

        $this->merchant->init(array(
            'OrderId' => $order_id,
            'Amount' => $format_sum,
            'PayType' => 'T',
            'DATA' => $data,
        ));
        if ($this->merchant->error) {
            $response['error'] = $this->merchant->error;
        } else {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
            
            if (!empty($response['Success'])) {
                $this->transactions->add_transaction(array(
                    'user_id' => $order->user_id,
                    'uid' => $order->user_uid,
                    'order_id' => $response['OrderId'],
                    'card_id' => $card_id,
                    'amount' => $response['Amount'],
                    'payment_id' => $response['PaymentId'],
                    //'terminal_type' => 'ATOP',
                    'payment_link' => $response['PaymentURL'],
                    'sended' => 1,//???
                    'status' => $response['Status'],
                    //'prolongation' => '',
                    //'code_sms' => $code_sms,
                    //'insure_amount' => $insure_amount,
                    'loan_id' => $order->id_1c,
                    'contract_number' => $zaim_number,
                    'payment_type' => 'insurance'

                ));
            }
            

            $this->logging(__METHOD__, 'tinkoff_order', (array)[], (array)$response, 'tinkoff_history.txt');

            $charge_response = $this->merchant->charge(array(
                'PaymentId' => $response['PaymentId'],
                'RebillId' => $rebill_id
            ));
        }

        return (array)json_decode(htmlspecialchars_decode($charge_response));
    }


    public function remove_customer($uid)
    {
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_AFT, self::TINKOFF_SECRET_KEY_AFT, 'https://securepay.tinkoff.ru/v2');

        $r = $this->merchant->removeCustomer(array(
            'CustomerKey' => $uid,        
        ));

        if ($this->merchant->error)
            $response['error'] = $this->merchant->error;
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
        
        return $response;
    }
    
    
    public function test()
    {
// нужен терминал Е2С
$customer_id = 'TEST-CUSTOMER-007';
        $order_id = $this->create_order_id('007');
        $format_sum = $this->format_summ(1);

        // тестовый адрес терминала https://rest-api-test.tinkoff.ru/v2
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_ATOP, self::TINKOFF_SECRET_KEY_ATOP, 'https://securepay.tinkoff.ru/v2');

        $data = new StdClass();
        $data->mfoAgreement = $order_id;
        
        $this->merchant->init(array(
            'OrderId' => $order_id,
            'Amount' => $format_sum,
            'PayForm' => 'mfo',
            'DATA' => $data,
        ));
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($r);echo '</pre><hr />';
exit;

/*
    	$this->merchant->Init(array(
            'CustomerKey' => 'TEST-CUSTOMER-001',
            'Recurrent' => 'Y',
            'OrderId' => 'TEST-ORDER-001',
            'Amount' => 100
        ));
*/
        $r = $this->merchant->getCardList(array(
            'CustomerKey' => 'TEST-CUSTOMER-007',        
        ));
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($r);echo '</pre><hr />';
        $this->merchant->addCard(array(
            'CustomerKey' => 'TEST-CUSTOMER-007',        
            'CheckType' => '3DSHOLD'
        ));
                
        if ($this->merchant->error)
            $response['error'] = $this->merchant->error;
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
        
        return $response;
    }
    
    public function test_charge($user_id, $amount)
    {


/**

        // {"CardId":"55150753","Pan":"559900******9036","Status":"A","RebillId":"726079108","CardType":1,"ExpDate":"1221"}
        $init = $this->merchant->init(array(
            'Amount' => 100,
            'OrderId' => 'TEST-CONTRACT-007'
        ));
        $init = json_decode($init);
        
        // {"Success":true,"ErrorCode":"0","TerminalKey":"1556097708543E2C","Status":"NEW","PaymentId":"385598357","OrderId":"TEST-CONTRACT-007","Amount":100,"PaymentURL":"https://securepay.tinkoff.ru/new/f62ej5qV"}
        $charge = $this->merchant->charge(array(
            'PaymentId' => $init->PaymentId,
            "RebillId" => "726079108"
        ));
        
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($init, $charge);echo '</pre><hr />';
**/
    }
    
    public function get_cardlist($customer_id)
    {
        // CRM mode
        return (array)json_decode(file_get_contents('https://manager.boostra.ru/ajax/tinkoff.php?action=get_cardlist&customer_id='.$customer_id));

        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_E2C, self::TINKOFF_SECRET_KEY_E2C, 'https://securepay.tinkoff.ru/v2');

        $r = $this->merchant->getCardList(array(
            'CustomerKey' => $customer_id,        
        ));

        if ($this->merchant->error)
            $response['error'] = $this->merchant->error;
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
        
        return $response;
    	
    }

    public function get_cardlist_by_one_s($customer_id)
    {
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_E2C, self::TINKOFF_SECRET_KEY_E2C, 'https://securepay.tinkoff.ru/v2');

        $z = new stdClass();
        $z->UID = $customer_id;
        $z->TerminalKey = '1556097708543E2C';
        $z->OrderId = '';
        $z->CardId = '';
        $z->Amount = '';
        $z->DATA = '';
        $z->PaymentId = '';
        $z->CheckType = '';
        $z->Partner = 'Boostra';

        $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
        $returned = $uid_client->__soapCall('GetRequestText',array($z));

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetRequestText', (array)$z, (array)$returned, 'requests_by_one_s_logs.txt');

        parse_str($returned->return, $parsed);

        $r = $this->merchant->getCardList($parsed);

        if ($this->merchant->error) {
            $response['error'] = $this->merchant->error;
        }else {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
        }
        
        return $response;
    }

    public function add_customer_by_one_s($customer_id)
    {
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_E2C, self::TINKOFF_SECRET_KEY_E2C, 'https://securepay.tinkoff.ru/v2');

        $z = new stdClass();
        $z->UID = $customer_id;
        $z->TerminalKey = '1556097708543E2C';
        $z->OrderId = '';
        $z->CardId = '';
        $z->Amount = '';
        $z->DATA = '';
        $z->PaymentId = '';
        $z->CheckType = '';
        $z->Partner = 'Boostra';

        $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
        $returned = $uid_client->__soapCall('GetRequestText',array($z));

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetRequestText', (array)$z, (array)$returned, 'requests_by_one_s_logs.txt');

        parse_str($returned->return, $parsed);

        $r = $this->merchant->addCustomer($parsed);

        if ($this->merchant->error) {
            $response['error'] = $this->merchant->error;
        }else {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
        }
        
        return $response;
    }

    public function add_card_by_one_s($customer_id)
    {
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_E2C, self::TINKOFF_SECRET_KEY_E2C, 'https://securepay.tinkoff.ru/v2');

        $z = new stdClass();
        $z->UID = $customer_id;
        $z->TerminalKey = '1556097708543E2C';
        $z->OrderId = '';
        $z->CardId = '';
        $z->Amount = '';
        $z->DATA = '';
        $z->PaymentId = '';
        $z->CheckType = '3DSHOLD';
        $z->Partner = 'Boostra';

        $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
        $returned = $uid_client->__soapCall('GetRequestText',array($z));

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetRequestText', (array)$z, (array)$returned, 'requests_by_one_s_logs.txt');

        parse_str($returned->return, $parsed);

        $r = $this->merchant->addCard($parsed);

        if ($this->merchant->error) {
            $response['error'] = $this->merchant->error;
        }else {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
        }
        
        return $response;
    }

    public function payment_by_one_s($payment_id)
    {
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_E2C, self::TINKOFF_SECRET_KEY_E2C, 'https://securepay.tinkoff.ru/e2c/v2');

        $z = new stdClass();
        $z->UID = '';
        $z->TerminalKey = '1556097708543E2C';
        $z->OrderId = '';
        $z->CardId = '';
        $z->Amount = '';
        $z->DATA = '';
        $z->PaymentId = $payment_id;
        $z->CheckType = '';
        $z->Partner = 'Boostra';

        $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
        $returned = $uid_client->__soapCall('GetRequestText',array($z));

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetRequestText', (array)$z, (array)$returned, 'requests_by_one_s_logs.txt');

        //exit;
        parse_str($returned->return, $parsed);

        $r = $this->merchant->payment($parsed);

        if ($this->merchant->error) {
            $response['error'] = $this->merchant->error;
        }else {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
        }
        
        return $response;
    }

    public function init_by_one_s($user_id, $card_id, $amount, $data = '')
    {
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_E2C, self::TINKOFF_SECRET_KEY_E2C, 'https://securepay.tinkoff.ru/e2c/v2');

        $order_id = $this->create_order_id($user_id);
        $format_sum = $this->format_summ($amount);

        $z = new stdClass();
        $z->UID = '';
        $z->TerminalKey = '1556097708543E2C';
        $z->OrderId =  $order_id;
        $z->CardId = $card_id;
        $z->Amount = $format_sum;
        $z->DATA = $data;//Должны обязательно заполнять любую инфу. Можно например Телефон=89270123456
        $z->PaymentId = '';
        $z->CheckType = '';
        $z->Partner = 'Boostra';

        $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
        //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($z);echo '</pre><hr />';
        //exit;
        $returned = $uid_client->__soapCall('GetRequestText',array($z));

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetRequestText', (array)$z, (array)$returned, 'requests_by_one_s_logs.txt');

        //exit;

        parse_str($returned->return, $parsed);

        
        $parsed['Amount'] = (string)
            $format_sum;
        $parsed['CardId'] = (string) 
            $card_id;
        

        //$parsed['X509SerialNumber'] = '6B0000087E8B60657B7AF2DCD200000000087E';
        ksort($parsed, SORT_STRING);

        //$number = $parsed['X509SerialNumber'];
        //unset($parsed['X509SerialNumber']);
        //unset($parsed['DigestValue']);
        //unset($parsed['SignatureValue']);
        //$parsed['DATA'] = $data;

        /*
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($this->generateToken($parsed));echo '</pre><hr />';

        $parsed['DigestValue'] = base64_encode($this->generateToken($parsed));

        $data = $parsed['DigestValue'];

        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($parsed['DigestValue']);echo '</pre><hr />';
        $private_key = file_get_contents('/home/boostra/crt/private.key');

        //var_dump($private_key);
        //exit;
        $binary_signature = "";

        $algo = "RSA-SHA256";
        openssl_sign($data, $binary_signature, $private_key, $algo);
        
        $parsed['SignatureValue'] = base64_encode($binary_signature);

        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($binary_signature);echo '</pre><hr />';

        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($parsed);echo '</pre><hr />';
        //exit;

        $public_key_res = file_get_contents('/home/boostra/crt/open-api-cert.pem');

        $ok = openssl_verify($data, base64_decode($parsed['SignatureValue']), $public_key_res, OPENSSL_ALGO_SHA256);
        if ($ok == 1) {
            echo "корректна";
        } elseif ($ok == 0) {
            echo "некорректна";
        } else {
            echo "ошибка: ".openssl_error_string();
        }

        */
        //exit;
        
        $r = $this->merchant->init($parsed);

        if ($this->merchant->error) {
            $response['error'] = $this->merchant->error;
        }else {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
        }
        
        return $response;
    }

    public function get_state_by_one_s($payment_id)
    {
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_E2C, self::TINKOFF_SECRET_KEY_E2C, 'https://securepay.tinkoff.ru/e2c/v2');

        $z = new stdClass();
        $z->UID = '';
        $z->TerminalKey = '1556097708543E2C';
        $z->OrderId = '';
        $z->CardId = '';
        $z->Amount = '';
        $z->DATA = '';//Должны обязательно заполнять любую инфу. Можно например Телефон=89270123456
        $z->PaymentId = $payment_id;
        $z->CheckType = '';
        $z->Partner = 'Boostra';

        $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
        $returned = $uid_client->__soapCall('GetRequestText',array($z));

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetRequestText', (array)$z, (array)$returned, 'requests_by_one_s_logs.txt');

        parse_str($returned->return, $parsed);

        $r = $this->merchant->getState($parsed);

        if ($this->merchant->error) {
            $response['error'] = $this->merchant->error;
        } else {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
        }
        
        return $response;
    }
    
    public function add_card($customer_id)
    {
        // CRM mode
        return (array)json_decode(file_get_contents('https://manager.boostra.ru/ajax/tinkoff.php?action=add_card&customer_id='.$customer_id));
        
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_E2C, self::TINKOFF_SECRET_KEY_E2C, 'https://securepay.tinkoff.ru/v2');

        $merchant_data = [
            'CustomerKey' => $customer_id,
            'CheckType' => '3DSHOLD'
        ];

        $this->merchant->addCard($merchant_data);
                
        if ($this->merchant->error)
            $response['error'] = $this->merchant->error;
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));

        $this->addLogMethod(__METHOD__, $merchant_data, $response, 'https://securepay.tinkoff.ru/v2', ['function_args' => func_get_args()]);

        return $response;
        
    }
        
    /**
     * Tinkoff::init_payment()
     * 
     * @param integer $order_id - Номер  в базе сайта
     * @param float $amount - сумма в рублях
     * 
     * @return array
     */
    public function init_payment($user_id, $amount)
    {
        $order_id = $this->create_order_id($user_id);
        $format_sum = $this->format_summ($amount);

        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_AFT, self::TINKOFF_SECRET_KEY_AFT, 'https://securepay.tinkoff.ru/v2');

        $merchant_data = [
            'OrderId' => $order_id,
            'Amount' => $format_sum
        ];

        $this->merchant->init($merchant_data);
        
        if ($this->merchant->error)
            $response['error'] = $this->merchant->error;
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));

        $this->addLogMethod(__METHOD__, $merchant_data, $response, 'https://securepay.tinkoff.ru/v2', ['function_args' => func_get_args()]);

        return $response;
    }
    
    public function init_payment_atop($user_id, $amount)
    {
        // CRM mode
        return (array)json_decode(file_get_contents('https://manager.boostra.ru/ajax/tinkoff.php?action=init_payment_atop&amount='.$amount.'&user_id='.$user_id));

        $order_id = $this->create_order_id($user_id);
        $format_sum = $this->format_summ($amount);

        // тестовый адрес терминала https://rest-api-test.tinkoff.ru/v2
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_ATOP, self::TINKOFF_SECRET_KEY_ATOP, 'https://securepay.tinkoff.ru/v2');

        $data = new StdClass();
        $data->mfoAgreement = $order_id;
//        $data->email = 'alpex-s@rambler.ru';

        if (!empty($_COOKIE['card_pay_id'])) {
            $data->DefaultCard = $_COOKIE['card_pay_id'];
        }

        $merchant_data = [
            'OrderId' => $order_id,
            'Amount' => $format_sum,
            'PayForm' => 'mfo',
            'DATA' => $data,
        ];

        $this->merchant->init($merchant_data);
        
        if ($this->merchant->error)
            $response['error'] = $this->merchant->error;
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));

        $this->addLogMethod(__METHOD__, $merchant_data, $response, 'https://securepay.tinkoff.ru/v2', ['function_args' => func_get_args()]);

        return $response;
        
    }

    public function init_payment_ip($individual_order_id, $amount)
    {
        $order_id = $this->create_order_id($individual_order_id);
        $format_sum = $this->format_summ($amount);
        
        $terminal_key = $this->settings->individual_settings['tinkoff_terminal_key'];
        $secret_key = $this->settings->individual_settings['tinkoff_secret_key'];
        
        $this->merchant = new TinkoffMerchantAPI($terminal_key, $secret_key, 'https://securepay.tinkoff.ru/v2');

        $this->merchant->init(array(
            'OrderId' => $order_id,
            'Amount' => $format_sum
        ));
        
        if ($this->merchant->error)
            $response['error'] = $this->merchant->error;
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
        
        return $response;
    }
    
    public function get_state_test($payment_id)
    {
        $this->merchant = new TinkoffMerchantAPI(
            self::TINKOFF_TERMINAL_KEY, 
            self::TINKOFF_SECRET_KEY,
            'https://securepay.tinkoff.ru/v2'
        );

        $this->merchant->getState(array(
            'PaymentId' => $payment_id
        ));
        
        if ($this->merchant->error)
        {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
            $response['error'] = $this->merchant->error;
        }
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));

        return $response;
    }

    public function get_state_atop($payment_id)
    {
        // CRM mode
        return (array)json_decode(file_get_contents('https://manager.boostra.ru/ajax/tinkoff.php?action=get_state_atop&payment_id='.$payment_id));

        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_ATOP, self::TINKOFF_SECRET_KEY_ATOP, 'https://securepay.tinkoff.ru/v2');

        $this->merchant->getState(array(
            'PaymentId' => $payment_id
        ));
        
        if ($this->merchant->error)
        {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
            $response['error'] = $this->merchant->error;
        }
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));

        return $response;
    }

    public function get_state($payment_id)
    {
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_AFT, self::TINKOFF_SECRET_KEY_AFT, 'https://securepay.tinkoff.ru/v2');

        $this->merchant->getState(array(
            'PaymentId' => $payment_id
        ));
        
        if ($this->merchant->error)
        {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
            $response['error'] = $this->merchant->error;
        }
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));

        return $response;
    }
    
    public function get_state_ip($payment_id)
    {
        $terminal_key = $this->settings->individual_settings['tinkoff_terminal_key'];
        $secret_key = $this->settings->individual_settings['tinkoff_secret_key'];

        $this->merchant = new TinkoffMerchantAPI($terminal_key, $secret_key, 'https://securepay.tinkoff.ru/v2');

        $this->merchant->getState(array(
            'PaymentId' => $payment_id
        ));
        
        if ($this->merchant->error)
        {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
            $response['error'] = $this->merchant->error;
        }
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));

        return $response;
    }
    
    public function get_order_info($order_id)
    {
        // CRM mode
        return (array)json_decode(file_get_contents('https://manager.boostra.ru/ajax/tinkoff.php?action=get_order_info&order_id='.$order_id));

        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_ATOP, self::TINKOFF_SECRET_KEY_ATOP, 'https://securepay.tinkoff.ru/v2');

        $this->merchant->CheckOrder(array(
            'OrderId' => $order_id
        ));
        
        if ($this->merchant->error)
        {
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
            $response['error'] = $this->merchant->error;
        }
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($this->merchant->error, $this->merchant->response);echo '</pre><hr />';
        return $response;
    }
    
    /**
     * Tinkoff::format_order_id()
     * Форматирует номер ордера в такой формат:
     * 2 цифры - год
     * 2 цифры - месяц
     * 2 цифры - день
     * 2 цифры - час
     * 2 цифры - минута
     * 2 цифры - секунда
     * 6 Знаков- номер ордера дополненый в начале нулями до 8 значков
     * 2 фифры - случайное число
     * 
     * @param integer $order_id
     * 
     * @return string
     */
    public function create_order_id($user_id)
    {
        $response = date('ymdHis');
        

        $length_user_id = strlen($user_id);
        $zero_addeds = 6 - $length_user_id;
        while ($zero_addeds > 0)
        {
            $response .= '0';
            $zero_addeds--; 
        }
        $response .= $user_id;
        $response .= rand(10, 99);
        
        return $response;
    }
    
    /**
     * Tinkoff::format_summ()
     * форматирует сумму в копейки
     * 
     * @param string $summ
     * 
     * @return integer
     */
    public function format_summ($summ)
    {
        return str_replace(',', '.', $summ) * 100;
    }

    private function generateToken(array $args) {
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($args);echo '</pre><hr />';   
        $token = '';
        ksort($args, SORT_STRING);
        
        foreach ($args as $key => $arg) {
            if (!is_array($arg)) {
                if ($key != 'X509SerialNumber') {
                    $token .= $arg;
                }
            }
        }

        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($token);echo '</pre><hr />';        
        return hash('sha256', $token);
    }

    private function generateTokenRaw(array $args) {
        $token = '';
        ksort($args, SORT_STRING);
        
        foreach ($args as $arg) {
            if (!is_array($arg)) {
                $token .= $arg;
            }
        }

        return $token;
    }


    public function pay_contract($order)
    {
        //$order = $this->orders->get_order($order_id);
        if (!$order) {
            return false;
        }
        if ($order->status != 8) {
            return false;
        }

        //TODO: проверяем,, если уже есть по этому ордеру выданный займ то отмена

        $this->orders->update_order($order->order_id, ['status' => 9]);

        //TODO/ получаем айди карты

        $tinkoff_order = $this->tinkoff->init_by_one_s($order->user_id, $order->card_id, $order->amount);

        $this->logging(__METHOD__, 'tinkoff_order', (array)[], (array)$tinkoff_order, 'tinkoff_history.txt');
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($tinkoff_order);echo '</pre><hr />';  
        /*
            Пример ответа:
            {
                "Success": true,
                "ErrorCode": "0",
                "TerminalKey": "1523606238702E2C",
                "Status": "CHECKED",
                "PaymentId": "2353039",
                "OrderId": "PaymentTestN",
                "Amount": 100
            }
            Статус платежа:
            при успешном сценарии: CHECKED
            при неуспешном: REJECTED
        */
        if (empty($tinkoff_order)) {
            return 'ORDER UNREGISTERED';
        }

        $status = isset($tinkoff_order['Status']) ? $tinkoff_order['Status'] : '';

        if ($status == 'REJECTED') {
            return 'ORDER UNREGISTERED';
        } elseif ($status == 'CHECKED') {
            $tinkoff_payment_id = (string)$tinkoff_order['PaymentId'];

            $data = '';

            $p2pcredit = [
                'order_id' => $order->order_id,
                'user_id' => $order->user_id,
                'date' => date('Y-m-d H:i:s'),
                'body' => $data,
                'register_id' => $tinkoff_payment_id//$b2p_order_id,
            ];

            $p2pcredit_id = $this->add_p2pcredit($p2pcredit);

            $p2pcredit = $this->get_p2pcredit($p2pcredit_id);

            $response = $this->tinkoff->payment_by_one_s($p2pcredit->register_id);

            echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($response);echo '</pre><hr />';  
            $this->logging(__METHOD__, 'tinkoff_order', (array)[], (array)$response, 'tinkoff_history.txt');
            /*
                array(6) {
                    ["Success"]=>
                    bool(true)
                    ["ErrorCode"]=>
                    string(1) "0"
                    ["TerminalKey"]=>
                    string(16) "1556097708543E2C"
                    ["Status"]=>
                    string(9) "COMPLETED"
                    ["PaymentId"]=>
                    string(10) "1092537042"
                    ["OrderId"]=>
                    string(20) "22030115404000602452"
                }
            */

            //$status = (string)$response['Status'];
            $status = isset($response['Status']) ? $response['Status'] : '';

            $this->update_p2pcredit($p2pcredit_id, array(
                'response' => json_encode($response),
                'status' => $status,
                'operation_id' => $response['OrderId'],//(string)$xml->id,
                'complete_date' => date('Y-m-d H:i:s'),
            ));

            return $status;
        }

        if (!empty($tinkoff_order['error'])) {
            return $tinkoff_order['error'];
        }

        /*
            $xml = simplexml_load_string($b2p_order);
            $b2p_order_id = (string)$xml->id;

            if (empty($b2p_order))
                return 'ORDER UNREGISTERED';

            $data = array(
                'sector' => $sector,
                'amount' => $order->amount * 100,
                'currency' => $this->currency_code,
                'reference' => $order->order_id,
                'token' => $card->token,
                'pan' => $card->pan,
                'id' => $b2p_order_id,
            );
            $data['signature'] = $this->get_signature(array(
                $data['sector'],
                $data['id'],
                $data['amount'],
                $data['currency'],
                $data['pan'],
                $data['token'],
                $password
            ));

            $p2pcredit = array(
                'order_id' => $order->order_id,
                'user_id' => $order->user_id,
                'date' => date('Y-m-d H:i:s'),
                'body' => $data,
                'register_id' => $b2p_order_id,
            );
            if ($p2pcredit_id = $this->add_p2pcredit($p2pcredit)) {
                $response = $this->send('P2PCredit', $data, 'gateweb');

                $xml = simplexml_load_string($response);
                $status = (string)$xml->state;

                $this->update_p2pcredit($p2pcredit_id, array(
                    'response' => $response,
                    'status' => $status,
                    'operation_id' => (string)$xml->id,
                    'complete_date' => date('Y-m-d H:i:s'),
                ));

                return $status;
            }
        */
    }

    public function get_payment_id_p2pcredit($contract_id)
    {
        $query = $this->db->placehold("
            SELECT *
            FROM tinkoff_p2pcredits
            WHERE payment_id = ?
            ORDER BY id DESC
            LIMIT 1
        ", (int)$contract_id);
        $this->db->query($query);
          
        return $this->db->result();
    }

    public function get_contract_p2pcredit($contract_id)
    {
        $query = $this->db->placehold("
            SELECT *
            FROM tinkoff_p2pcredits
            WHERE contract_id = ?
            ORDER BY id DESC
            LIMIT 1
        ", (int)$contract_id);
        $this->db->query($query);
          
        return $this->db->result();
    }

    public function get_p2pcredit($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM tinkoff_p2pcredits
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        if ($result = $this->db->result()) {
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

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM tinkoff_p2pcredits
            WHERE 1
                $id_filter
                $order_id_filter
                $status_filter
                $sent_filter
                $keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        if ($results = $this->db->results()) {
            foreach ($results as $result) {
                $result->body = unserialize($result->body);
                $result->response = unserialize($result->response);
            }
        }
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($query);
        echo '</pre><hr />';
        return $results;
    }

    public function count_p2pcredits($filter = array())
    {
        $id_filter = '';
        $order_id_filter = '';
        $status_filter = '';
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

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM tinkoff_p2pcredits
            WHERE 1
                $id_filter
                $order_id_filter
                $status_filter
                $sent_filter
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
            INSERT INTO tinkoff_p2pcredits SET ?%
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
            UPDATE tinkoff_p2pcredits SET ?% WHERE id = ?
        ", $p2pcredit, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function delete_p2pcredit($id)
    {
        $query = $this->db->placehold("
            DELETE FROM tinkoff_p2pcredits WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    public function get_order_insure($order_id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM tinkoff_insures
            WHERE order_id = ?
        ", (int)$order_id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }

    public function add_insure($insure)
    {
		$query = $this->db->placehold("
            INSERT INTO tinkoff_insures SET ?%
        ", (array)$insure);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }

    /**
     * Формируем ссылку для пользователя который авторизован по паспорту
     * @param $amount
     * @return array
     */
    public function init_payment_passport($amount)
    {
        $order_id = uniqid();
        $format_sum = $this->format_summ($amount);

        $this->merchant = new TinkoffMerchantAPI(
            self::TINKOFF_TERMINAL_KEY_ATOP,
            self::TINKOFF_SECRET_KEY_ATOP,
            'https://securepay.tinkoff.ru/v2'
        );

        $data = new StdClass();
        $data->mfoAgreement = $order_id;

        $merchant_data = [
            'OrderId' => $order_id,
            'Amount' => $format_sum,
            'PayForm' => 'mfo',
            'DATA' => $data,
        ];

        $this->merchant->init($merchant_data);

        if ($this->merchant->error) {
            $response['error'] = $this->merchant->error;
        } else {
            $response = json_decode(htmlspecialchars_decode($this->merchant->response), true);
        }

        $this->addLogMethod(__METHOD__, $merchant_data, $response, 'https://securepay.tinkoff.ru/v2', ['function_args' => func_get_args()]);

        return $response;
    }

    /**
     * Проверяем проведена ли транзакция
     * @param $status
     * @return bool
     */
    public function transactionIsSuccess($status)
    {
        return in_array($status, [self::TRANSACTION_STATUS_AUTHORIZED, self::TRANSACTION_STATUS_CONFIRMED]);
    }
}