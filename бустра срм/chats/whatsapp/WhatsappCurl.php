<?php

namespace chats\whatsapp;

use chats\whatsapp\WhatsappSettings AS Settings;
use chats\main\Curl;

class WhatsappCurl extends Curl {

    /**
     * Отправка и получение ответа post запроса
     *
     * @param object $data Тело сообщения
     * @return string(json_encode)  Строка json с результатом выполнения запроса
     */
    public static function sendPost(object $data, string $method) {
        $url = Settings::url . $method . Settings::token;
        $curl = self::curlInit($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        return self::curlClose($curl);
    }

    /**
     *  Отправка и получение ответа get запроса
     *
     * @param object $data Тело сообщения
     * @return string(json_encode)  Строка json с результатом выполнения запроса
     */
    public static function sendGet(array $data, string $method) {
        $addUrl = false;
        foreach ($data AS $key => $value) {
            $addUrl = '&' . $key . '=' . $value;
        }
        $url = Settings::url . $method . Settings::token . $addUrl;
        $curl = self::curlInit($url);
        return self::curlClose($curl);
    }

}
