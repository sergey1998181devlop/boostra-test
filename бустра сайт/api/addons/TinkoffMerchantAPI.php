<?php

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
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
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

            if ($json) {
                if (@$json->ErrorCode !== "0") {
                    $this->error = @$json->Details;
                } else {
                    $this->paymentUrl = @$json->PaymentURL;
                    $this->paymentId = @$json->PaymentId;
                    $this->status = @$json->Status;
                }
            }
            $error = curl_error($curl);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($error);echo '</pre><hr />';
            curl_close($curl);

            return $out;

        } else {
            throw new HttpException('Can not create connection to ' . $api_url . ' with args ' . $args, 404);
        }
    }
}