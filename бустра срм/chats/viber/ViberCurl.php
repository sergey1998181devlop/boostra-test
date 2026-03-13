<?php

namespace chats\viber;

use chats\main\Curl;
use chats\viber\ViberSettings AS Settings;

class ViberCurl extends Curl {

    public static function sendPost($method, $data) {
        Curl::$curlHeaders = ['X-Viber-Auth-Token: ' . Settings::viberBotToken, 'Content-Type:application/json'];
        $url = Settings::viberBaseUrl . $method;
        $curl = Curl::curlInit($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode((object) $data));
        $res = Curl::curlClose($curl);
        return $res;
    }

}
