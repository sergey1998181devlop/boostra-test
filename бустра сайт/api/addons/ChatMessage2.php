<?php
/* Версия для работы с Green-API */
class ChatMessage2
{
    protected $urlSend; //url отправки сообщения
    protected $urlCheck; //url проверки наличия вотсап
    protected $idInstance;
    protected $apiTokenInstance;

    function __construct()
    {
        $this->apiTokenInstance = 'a43f3cbf44dd98e447c8ea829a0adf5dc64bad3ee31d79789b'; //Токен Api
        $this->idInstance = '8730'; //ID Instance
        $this->urlSend = 'https://api.green-api.com/waInstance' . $this->idInstance . '/SendMessage/' . $this->apiTokenInstance;
        $this->urlCheck = 'https://api.green-api.com/waInstance' . $this->idInstance . '/checkWhatsapp/' . $this->apiTokenInstance;
    }

    public function messageSend($phone, $text) {

        //Проверяем наличие вотсап
        $check = $this->checkWhatsapp($phone);

        $chatId = $phone . '@c.us';

        // Формируем тело запроса
        $post_data = json_encode(array(
            'chatId'=>$chatId,
            'message'=>$text
        ));

        $response = $this->makeRequest($post_data, $this->urlSend);

        if (! $response) {
            //добавить обработку ошибки
            $success = false;
        } else {
            //добавить логирование\обоработку успешной отправки
            $success = true;
        }

        return $success;
    }

    protected function makeRequest($post_data, $url)
    {
        $curl = curl_init(); // Используем curl для запроса к Wazzup API

        // Отправляем запрос в Wazzup
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json'
        ));
        curl_setopt($curl, CURLOPT_URL, $url);
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

    public function checkWhatsapp($phone) {
        // Формируем тело запроса
        $post_data = json_encode(array(
            'phoneNumber'=>$phone,
        ));

        $response = $this->makeRequest($post_data, $this->urlCheck);
    }

}
