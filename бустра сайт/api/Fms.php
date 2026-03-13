<?php

require_once 'Simpla.php';

class Fms extends Simpla
{
    private $api_url = 'http://services.fms.gov.ru/info-service.htm?sid=2000';
    private $cookie_dir = 'files/scorings/cookies/';
    private $captcha_dir = 'files/scorings/captcha/';
    
    private $session_id = null;
    
    private $page = null;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->cookie_dir = $this->config->root_dir.$this->cookie_dir;
        $this->captcha_dir = $this->config->root_dir.$this->captcha_dir;

        $this->session_id = md5(rand().microtime());
        
    }
    
    
    public function run($audit_id, $user_id, $order_id)
    {
        $this->user_id = $user_id;
        $this->audit_id = $audit_id;
        $this->order_id = $order_id;
        
        $this->type = $this->scorings->get_type($this->scorings::TYPE_FMS);
    	
        $user = $this->users->get_user((int)$user_id);
        
        return $this->scoring($user->passport_serial);
    }


    private function scoring($passport)
    {
        $passport_serial = str_replace(array(' ', '-'), '', $passport);
        $serial = substr($passport_serial, 0, 4);
        $number = substr($passport_serial, 4, 6);
        $resp   = $this->check_passport($serial, $number);
        
        if (isset($resp['string_result'])) {
            $add_scoring = array(
                'user_id' => $this->user_id,
                'audit_id' => $this->audit_id,
                'type' => $this->scorings::TYPE_FMS,
                'body' => $resp['string_result'],
                'string_result' => $resp['string_result'],
                'success' => (int)$resp['success'],
            );
            $this->scorings->add_scoring($add_scoring);
        }
        
        return (int)$resp['success'];
    }

    public function check_passport($serial, $number)
    {
        $data = [
            'passport_series' => $serial,
            'passport_number' => $number
        ];
        $update = [
            'success' => 0,
            'string_result' => null,
        ];
        $result = $this->infosphere->check_fms($data);

        if (isset($result['Source']) && $result['Source']['ResultsCount'] > 0) {
            foreach ($result['Source']['Record'] as $source) {
                foreach ($source as $field) {
                    if ($field['FieldName'] == 'ResultCode') {
                        if ($field['FieldValue'] == 'VALID') {
                            $update = [
                                'success' => 1,
                                'string_result' => 'Паспорт корректный',
                            ];
                        } else {
                            $update = [
                                'success' => 0,
                                'string_result' => 'Паспорт некорректный',
                            ];
                        }
                    }
                }
            }
        }
        return $update;
    }
    
    private function load_form()
    {

        $headers = array(
            'Host: services.fms.gov.ru',
            'User-Agent: Mozilla/5.0 (Windows NT 6.2; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
//            'Accept-Encoding: gzip, deflate',
            'Referer: http://services.fms.gov.ru/info-service-result.htm?sid=2000',
            'DNT: 1',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Pragma: no-cache',
            'Cache-Control: no-cache',
        );


        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_COOKIE, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_dir.$this->session_id.'.txt');        
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_dir.$this->session_id.'.txt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        curl_close($ch);
        
        $this->page = $result;
    }
    
    private function load_captcha()
    {
        $headers = array(
            'Host: services.fms.gov.ru',
            'User-Agent: Mozilla/5.0 (Windows NT 6.2; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0',
            'Accept: image/webp,*/*',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate',
            'DNT: 1',
            'Connection: keep-alive',
            'Referer: http://services.fms.gov.ru/info-service-result.htm?sid=2000',
            'Pragma: no-cache',
            'Cache-Control: no-cache',
        );
        
        $captcha_url = 'http://services.fms.gov.ru/services/captcha.jpg';
        $ch = curl_init($captcha_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIE, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_dir.$this->session_id.'.txt');        
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_dir.$this->session_id.'.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        curl_close($ch);

        $captcha_src = $result;

        file_put_contents($this->captcha_dir.$this->session_id.'.jpg', $captcha_src);
    }    
    
    private function send_form($data)
    {
        $headers = array(
            'Host: services.fms.gov.ru',
            'User-Agent: Mozilla/5.0 (Windows NT 6.2; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
//            'Accept-Encoding: gzip, deflate',
            'Content-Type: application/x-www-form-urlencoded',
            'Origin: http://services.fms.gov.ru',
            'DNT: 1',
            'Connection: keep-alive',
            'Referer: http://services.fms.gov.ru/info-service.htm?sid=2000',
            'Upgrade-Insecure-Requests: 1',
            'Pragma: no-cache',
            'Cache-Control: no-cache',
        );

        $url = 'http://services.fms.gov.ru/info-service.htm';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_COOKIE, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_dir.$this->session_id.'.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_dir.$this->session_id.'.txt');
        
//        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_REFERER, 'http://services.fms.gov.ru/info-service.htm?sid=2000');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0');
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

//echo __FILE__.' '.__LINE__.'<br /><pre><code>';var_dump($result);echo '</pre><hr />';
//    $this->send('http://services.fms.gov.ru/info-service-result.htm?sid=2000');



        return $result;
    }
    
    public function send($url, $data = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_COOKIE, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_dir.$this->session_id.'.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_dir.$this->session_id.'.txt');
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (!is_null($data))
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $result = curl_exec($ch);
        curl_close($ch);
echo $result;                
        return $result;
    }
}