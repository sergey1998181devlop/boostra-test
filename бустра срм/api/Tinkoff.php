<?php

require_once 'Simpla.php';


class TinkoffMerchantAPI
{
    private $api_url;
    private $terminalKey;
    private $secretKey;
    private $paymentId;
    private $status;
    private $error;
    private $response;
    private $paymentUrl;

    function __construct($terminalKey, $secretKey, $api_url)
    {
        $this->api_url = $api_url;
        $this->terminalKey = $terminalKey;
        $this->secretKey = $secretKey;
    }

    function __get($name)
    {
        switch ($name) {
            case 'paymentId':
                return $this->paymentId;
            case 'status':
                return $this->status;
            case 'error':
                return $this->error;
            case 'paymentUrl':
                return $this->paymentUrl;
            case 'response':
                return htmlentities($this->response);
            default:
                if ($this->response) {
                    if ($json = json_decode($this->response, true)) {
                        foreach ($json as $key => $value) {
                            if (strtolower($name) == strtolower($key)) {
                                return $json[ $key ];
                            }
                        }
                    }
                }

                return false;
        }
    }

    public function payment($args)
    {
        return $this->buildQuery('Payment', $args);
    }

    public function init($args)
    {
        return $this->buildQuery('Init', $args);
    }

    public function getState($args)
    {
        return $this->buildQuery('GetState', $args);
    }

    public function confirm($args)
    {
        return $this->buildQuery('Confirm', $args);
    }

    public function charge($args)
    {
        return $this->buildQuery('Charge', $args);
    }

    public function cancel($args)
    {
        return $this->buildQuery('Cancel', $args);
    }


    public function addCustomer($args)
    {
        return $this->buildQuery('AddCustomer', $args);
    }

    public function getCustomer($args)
    {
        return $this->buildQuery('GetCustomer', $args);
    }

    public function removeCustomer($args)
    {
        return $this->buildQuery('RemoveCustomer', $args);
    }

    public function getCardList($args)
    {
        return $this->buildQuery('GetCardList', $args);
    }
	public function AddCard($args)
    {
        return $this->buildQuery('AddCard', $args);
    }

    public function removeCard($args)
    {
        return $this->buildQuery('RemoveCard', $args);
    }

    public function CheckOrder($args)
    {
        return $this->buildQuery('CheckOrder', $args);
    }

    public function resend()
    {
        return $this->buildQuery('Resend', array());
    }

    public function buildQuery($path, $args)
    {
        $url = $this->api_url;
        if (is_array($args) ) {
            if ( ! array_key_exists('TerminalKey', $args)) $args['TerminalKey'] = $this->terminalKey;
            if ( ! array_key_exists('Token', $args) ) $args['Token'] = $this->_genToken($args);
        }
        $url = $this->_combineUrl($url, $path);


        return $this->_sendRequest($url, $args);
    }

    private function _genToken($args)
    {
        $token = '';
        $args['Password'] = $this->secretKey;
        ksort($args);
        
        foreach ($args as $name => $arg) {
            if ($name != 'DATA')
            {
                if (!is_array($arg) and !preg_match('/DigestValue|SignatureValue|X509SerialNumber/',$name)) {
                    $token .= $arg;
                }
            }
        }
        $token = hash('sha256', $token);

        return $token;
    }

    private function _combineUrl()
    {
        $args = func_get_args();
        $url = '';
        foreach ($args as $arg) {
            if (is_string($arg)) {
                if ($arg[ strlen($arg) - 1 ] !== '/') $arg .= '/';
                $url .= $arg;
            } else {
                continue;
            }
        }

        return $url;
    }

    private function _sendRequest($api_url, $args)
    {
        $this->error = '';
        if (is_array($args)) {
            $args = json_encode($args);
        }

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $api_url);
//            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));

            $out = curl_exec($curl);
            $this->response = $out;
            $json = json_decode($out);

if ($_SERVER['REMOTE_ADDR'] == '193.176.87.139')
{
    $error = curl_error($curl);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($api_url, $args);echo '</pre><hr />';
    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($out, $error);echo '</pre><hr />';
}
            if ($json) {
                if (@$json->ErrorCode !== "0") {
                    $this->error = @$json->Details;
                } else {
                    $this->paymentUrl = @$json->PaymentURL;
                    $this->paymentId = @$json->PaymentId;
                    $this->status = @$json->Status;
                }
            }

            curl_close($curl);

            return $out;

        } else {
            throw new HttpException('Can not create connection to ' . $api_url . ' with args ' . $args, 404);
        }
    }
}
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

    /**
     * Номер сертификата Boostra для подписи X509_SERIAL_NUMBER
     */
    public const BOOSTRA_X509_SERIAL_NUMBER = '03a55bda0094ae01a946c964ae9fb57580';

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
    
    public function test()
    {
// нужен терминал Е2С

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
    
    public function hold($user_id, $card_id, $rebill_id)
    {
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

    public function get_cardlist($customer_id)
    {
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
    
    public function add_card($customer_id)
    {
        $this->merchant = new TinkoffMerchantAPI(self::TINKOFF_TERMINAL_KEY_E2C, self::TINKOFF_SECRET_KEY_E2C, 'https://securepay.tinkoff.ru/v2');

        $this->merchant->addCard(array(
            'CustomerKey' => $customer_id,        
            'CheckType' => '3DSHOLD'
        ));
                
        if ($this->merchant->error)
            $response['error'] = $this->merchant->error;
        else
            $response = (array)json_decode(htmlspecialchars_decode($this->merchant->response));
        
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
    
    public function init_payment_atop($user_id, $amount)
    {
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

    public function get_order_info($order_id)
    {
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
        return $response;
    }

    public function get_state_atop($payment_id)
    {
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

    /**
     * Генерация данных для подписи
     * $args['TerminalKey'] - обязательный элемент массива
     * @param $args
     * @param $boostra_crt_path
     * @return array
     */
    public function generateSignData($args, $boostra_crt_path): array
    {
        $token = '';
        krsort($args);

        foreach ($args as $name => $arg) {
            if ($name != 'DATA')
            {
                if (!is_array($arg) and !preg_match('/DigestValue|SignatureValue|X509SerialNumber/',$name)) {
                    $token .= $arg;
                }
            }
        }

        $token = hash('sha256', $token);

        $private_key = file_get_contents($boostra_crt_path);
        $signature_value = "";

        openssl_sign($token, $signature_value, $private_key, 'RSA-SHA256');

        return [
            'DigestValue' => base64_encode($token),
            'SignatureValue' => base64_encode($signature_value),
        ];
    }

    /**
     * Отправка CURL
     * @param $api_url
     * @param $args
     * @return mixed
     */
    private function sendRequest($api_url, $args)
    {
        $json_fields = json_encode($args);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_url,
            CURLOPT_POST => TRUE,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => $json_fields,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ]
        ));

        $out = curl_exec($curl);

        curl_close($curl);

        return json_decode($out, true);
    }

    /**
     * Получает баланс счёта
     * @param string $terminal_key
     * @param string $x509_serial_number
     * @return bool|string
     * @deprecated
     */
    public function get_balance(string $terminal_key, string $x509_serial_number)
    {
        $args = $this->generateSignData(['TerminalKey' => $terminal_key], $this->config->boostra_crt_path);
        $args['TerminalKey'] = $terminal_key;
        $args['X509SerialNumber'] = $x509_serial_number;

        return $this->sendRequest('https://securepay.tinkoff.ru/e2c/v2/GetAccountInfo/', $args);
    }

    /**
     * Добавляет карту в БД
     * @param array $data
     * @return mixed
     */
    public function addCardToDb(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_tinkoff_cards SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Получает карты Тинькофф из БД
     * @param array $filter_data
     * @return array|false
     */
    public function getCardsByFilter(array $filter_data)
    {
        $where = [];

        $sql = "SELECT * FROM s_tinkoff_cards WHERE 1=1
                -- {{where}}";

        if (!empty($filter_data['user_id'])) {
            $where[] = $this->db->placehold('user_id = ?', intval($filter_data['user_id']));
        }

        if (!empty($filter_data['card_id'])) {
            $where[] = $this->db->placehold('card_id = ?', intval($filter_data['card_id']));
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);

        return $this->db->results();
    }
}