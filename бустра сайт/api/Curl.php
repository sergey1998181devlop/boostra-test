<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Curl
 *
 * @author alexey
 */
class Curl {

    public $headers = [
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        103 => '103 Early Hints',
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        307 => '307 Temporary Redirect',
        308 => '308 Permanent Redirect',
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Timeout',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Payload Too Large',
        414 => '414 URI Too Long',
        415 => '415 Unsupported Media Type',
        416 => '416 Range Not Satisfiable',
        417 => '417 Expectation Failed',
        418 => "418 I'm a teapot",
        422 => '422 Unprocessable Entity',
        425 => '425 Too Early',
        426 => '426 Upgrade Required',
        428 => '428 Precondition Required',
        429 => '429 Too Many Requests',
        431 => '431 Request Header Fields Too Large',
        451 => '451 Unavailable For Legal Reasons',
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported',
        506 => '506 Variant Also Negotiates',
        507 => '507 Insufficient Storage',
        508 => '508 Loop Detected',
        510 => '510 Not Extended',
        511 => '511 Network Authentication Required',
        0 => 'Error request'
    ];
    
    public $header = '200 OK';

    /**
     * Открываем ресурс соединения и предаем заголовки
     * @param string $url
     * @param array $curlHeaders
     * @return resource
     */
    public function curlInit($url, $curlHeaders = ['application/json']) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        return $curl;
    }
    
    /**
     * Закрываем соединение и получаем результат
     * @param resource $curl
     * @return object
     */
    public function curlClose($curl) {
        $curlResponse = curl_exec($curl);
        $curlResponseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $res = json_decode($curlResponse);
        $header = $this->headers[$curlResponseCode];
        curl_close($curl);
        return json_encode((object)['ResponseCode' => $curlResponseCode, 'Data' => $res, 'Header' => $header]);
    }
    
    /**
     * Подготавливаем номер телефона для дальнейшей работы (удаляем все кроме цифр)
     * @param string $phone
     * @return integer
     */
    public function preparePhone($phone) {
        $phone = preg_replace('/\D+/iu', '', $phone);
        settype($phone, 'integer');
        return (int) $phone;
    }
    
}