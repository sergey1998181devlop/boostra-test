<?php

require_once __DIR__ . '/../api/XMLSerializer.php';

class Infosphere extends Simpla
{
    private const API_URL = 'https://i-sphere.ru/2.00/';
    private $XMLSerializer;

    private const CURL_TIMEOUT = 30;

    private string $login;
    private string $password;

    public function __construct()
    {
    	parent::__construct();        
        $this->XMLSerializer = new XMLSerializer();

        $isphereApiKeys = $this->settings->getApiKeys('isphere');
        $this->login = $isphereApiKeys['login'];
        $this->password = $isphereApiKeys['password'];
    }
    
    private function build_request($data, $type)
    {
        $params = [
            'UserID' => $this->login,
            'Password' => $this->password,
            'sources' => $type,
            'PersonReq' => $data,
        ];
        return $this->XMLSerializer->serialize($params);
    }

    private function build_request_by_phone(string $phone, string $type): string
    {
        $params = [
            'UserID' => $this->login,
            'Password' => $this->password,
            'sources' => $type,
            'PhoneReq' => [
                'phone' => $phone
            ],
        ];

        return $this->XMLSerializer->serialize($params);
    }

    public function send($request, ?int $timeout = null)
    {
        $ch = curl_init(static::API_URL);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        if ($timeout !== null) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        }

        $html  = curl_exec($ch);

        $error = curl_error($ch);
        if (!empty($error)) {
            $this->logging(__METHOD__, static::API_URL, $request, ['curl_error' => $error], 'infosphere.txt');
        }

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

    public function check_fns($data)
    {
        $request  = $this->build_request($data, 'fns');

        return $this->send($request, 20);
    }

    public function check_efrsb($data)
    {
        $request = $this->build_request($data, 'bankrot');

        return $this->send($request, 15);
    }

    public function check_egrul($data)
    {
        $request = $this->build_request($data, 'egrul');

        return $this->send($request);
    }

    /**
     * Пример ответа
     *
     * array (
     *     '@attributes' => [...],
     *     'Request' => [...],
     *     'Source' => [
     *         0 => [
     *             '@attributes' => [...],
     *             'Name' => 'Viber',
     *             'Title' => 'Поиск в Viber',
     *             'CheckTitle' => 'Поиск в Viber',
     *             'Request' => 'viber_phone 79284182712',
     *             'ResultsCount' => '0',
     *         ],
     *         1 => [
     *             '@attributes' => [...],
     *             'Name' => 'WhatsApp',
     *             'Title' => 'Поиск телефона в WhatsApp',
     *             'CheckTitle' => 'Поиск телефона в WhatsApp',
     *             'Request' => 'whatsappweb_phone 79299931824',
     *             'ResultsCount' => '1',
     *             'Record' => [
     *                 'Field' => [
     *                     0 => [
     *                         'FieldType' => 'string',
     *                         'FieldName' => 'phone',
     *                         'FieldTitle' => 'Телефон',
     *                         'FieldDescription' => 'Телефон',
     *                         'FieldValue' => '79299931824',
     *                     ],
     * ...
     *                     5 => [
     *                         'FieldType' => 'string',
     *                         'FieldName' => 'ResultCode',
     *                         'FieldTitle' => 'Код результата',
     *                         'FieldDescription' => 'Код результата',
     *                         'FieldValue' => 'FOUND',
     *                     ],
     *                ],
     *           ],
     *      ],
     * )
     *
     * @param array $types
     * @param string $phone
     * @return mixed
     */
    public function checkUserMessengers(string $phone, array $types)
    {
        $types = implode(', ', $types);
        $request  = $this->build_request_by_phone($phone, $types);
        return $this->send($request, self::CURL_TIMEOUT);
    }
}