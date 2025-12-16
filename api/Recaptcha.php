<?php

require_once 'Simpla.php';

class Recaptcha extends Simpla
{
    private $url = 'https://www.google.com/recaptcha/api/siteverify';
    
    public function check($recaptcha)
    {
    	if (empty($recaptcha))
            return false;
        
        $data = array(
            'secret' => $this->settings->apikeys['recaptcha']['secret'],
            'response' => $recaptcha,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        );
        $dataStr = http_build_query($data);
        
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: '.strlen($dataStr)
        );
        $ch = curl_init($this->url);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
        
        $json = curl_exec($ch);
        curl_close($ch);
        
        $response = json_decode($json);
        
        return $response->success;
    }
    
}