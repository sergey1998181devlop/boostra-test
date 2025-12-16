<?php

require_once __DIR__ . '/../api/XMLSerializer.php';

class Infosphere extends Simpla
{
    private const API_URL = 'https://i-sphere.ru/2.00/';
    private const USERNAME = 'boostra_api';
    private const PASSWORD = 'I%8PXcEN';
    private $XMLSerializer;

    public function __construct()
    {
    	parent::__construct();        
        $this->XMLSerializer = new XMLSerializer();
    }
    
    private function build_request($data, $type)
    {
        $params = [
            'UserID' => static::USERNAME,
            'Password' => static::PASSWORD,
            'sources' => $type,
            'PersonReq' => $data,
        ];
        return $this->XMLSerializer->serialize($params);
    }
    
    public function send($request)
    {
        $ch = curl_init(static::API_URL);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $html  = curl_exec($ch);
        $html  = simplexml_load_string($html);
        $array = json_decode(json_encode($html), true);
        curl_close($ch);

        return $array;
    }

    public function check_fssp($data)
    {
        $request  = $this->build_request($data, 'fssp');
        
        return $this->send($request);
    }

    public function check_fms($data)
    {
        $request  = $this->build_request($data, 'fms');
        
        return $this->send($request);
    }
}