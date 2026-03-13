<?php

class ChatMessage
{
    protected $apiKey;
    protected $defaultChannelId;
    protected $url;

    function __construct()
    {
        $this->apiKey = '3739f14076ae43e986bc48a1fdc7762b'; // Ключ авторизации интеграции по API
        $this->defaultChannelId = '72c22b56-3689-4cb3-b1ab-533488023583';
        $this->url = 'https://api.wazzup24.com/v2/send_message';
    }

    public function messageSend($phone, $text, $chatType = 'whatsapp') {
        $channelId  = $this->defaultChannelId;
        $chatId = $this->preparePhone($phone);

        //var_dump($chatId);
        // Формируем тело запроса
        $post_data = json_encode(array(
            'channelId'=>$channelId,
            'chatId'=>$chatId,
            'chatType'=>$chatType,
            'text'=>$text
        ));

        $response = $this->makeRequest($post_data);

        if (! $response) {
            //добавить обработку ошибки
            $success = false;
        } else {
            //добавить логирование\обоработку успешной отправки
            $success = true;
        }

        return $success;
    }

    protected function makeRequest($post_data)
    {
        $curl = curl_init(); // Используем curl для запроса к Wazzup API

        // Отправляем запрос в Wazzup
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic '. $this->apiKey,
            'Content-Type:application/json'
        ));
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $server_response = curl_exec($curl);
        $http_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Парсим ответ
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($server_response, 0, $header_size);

        if ($http_response_code != 201) {
            error_log($header);
            $status = false;
        } else {
        // Если все ок, то вернется guid отправленного сообщения
            $res = json_decode($header);
            $msg_guid = $res->messageId;
            $status = $msg_guid;
        }

        curl_close ($curl);
        //var_dump($header);
        return $status;
    }

    protected function preparePhone($phone)
    {
        $phone_prepared = (string)$phone;
        $phone_prepared = trim($phone_prepared, '+');
        if ($phone_prepared[0] == 8) {
            $phone_prepared[0] = 7;
        }
        return (string)$phone_prepared;
    }

}
