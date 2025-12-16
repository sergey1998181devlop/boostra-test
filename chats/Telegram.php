<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Telegram
 *
 * @author alexey
 */
include_once ROOT . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'Simpla.php';

class Telegram extends Simpla
{

    private $botToken;
    private $botId;
    private $apiUrl;
    private $url;
    private $phone;
    private $message;
    public $useMessage = 'Получить код';

    public function __construct()
    {
        $this->botToken = $this->config->tlgBotToken;
        $this->apiUrl = $this->config->tlgBotApiUrl;
        $this->url = $this->apiUrl . $this->botToken;
        $_SESSION['chat_type'] = 'telegram';
        if (!isset($_SESSION['timeOut'])) {
            $_SESSION['timeOut'] = time() - 1;
        }
        if (isset($_GET['phone'])) {
            if ($_GET['phone']) {
                $this->phone = (int) $this->curl->preparePhone($_GET['phone']);
                $_SESSION['phone'] = $this->phone;
            }
        }
    }

    public function getUpdates()
    {

        if (isset($_SESSION['phone'])) {
            $url = (string) $this->url . '/getUpdates';
            $curl = $this->curl->curlInit($url);
            $res = (object) $this->curl->curlClose($curl);
            if ($res->Data->ok) {
                $updates = $res->Data->result;
                foreach ($updates as $message) {
                    if (property_exists($message, 'message')) {
                        $date = date('Y-m-d H:i:s', $message->message->date);
                        $data = [
                            'chat_type' => (string) 'telegram',
                            'user_id_in_chat' => (int) $message->message->from->id,
                            'chat_id' => (int) $message->message->chat->id,
                            'update_id' => (int) $message->update_id,
                            'message_status' => (int) 2,
                            'message_id' => (int) $message->message->message_id,
                            'text' => (string) $message->message->text,
                            'date' => (string) $date,
                            'status' => 0,
                        ];
                        $this->chats->setUpdates($data);
                    }
                }
            }
        }
    }

    private function sendMessage($text, $chatId)
    {
        $url = (string) $this->url . '/sendMessage?chat_id=' . $chatId . '&text=' . $text;
        $curl = $this->curl->curlInit($url);
        return (object) $this->curl->curlClose($curl);
    }
}
