<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Whatsapp
 *
 * @author alexey
 */
include_once ROOT . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'Simpla.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Telegram.php';

class Whatsapp extends Simpla
{

    private $token;
    private $instans;
    private $url;
    private $phone;

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        $_SESSION['chat_type'] = 'whatsapp';
        if (isset($_GET['phone'])) {
            if ($_GET['phone']) {
                $this->phone = (int) $this->curl->preparePhone($_GET['phone']);
                $_SESSION['phone'] = $this->phone;
            }
        }
        $this->token = '?token=' . $this->config->whatsAppToken;
        $this->instans = $this->config->whatsAppInstansNumber;
        $this->url = 'https://api.chat-api.com/instance' . $this->instans . '/';
    }

    /**
     * Отправка сообщения получателю по номеру телефона
     * @param string|int $phone
     * @param string $text
     * @return object
     */
    public function sendMessage($phone, $text)
    {
        $url = $this->url . 'sendMessage' . $this->token;
        $data = [
            'phone' => (int) $this->curl->preparePhone($phone),
            'body' => (string) $text,
        ];
        $curl = $this->curl->curlInit($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        return $this->curl->curlClose($curl);
    }

    /**
     * отправляем код верификации
     * @param string|int $phone
     */
    public function sendCode($phone = false)
    {
        if (!$phone) {
            $phone = $this->phone;
        }
        return $this->sendMessage($phone, $this->chats->newCodeGenerate($phone));
    }
}
