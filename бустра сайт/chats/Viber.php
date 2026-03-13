<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Viber
 *
 * @author alexey
 */
include_once ROOT . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'Simpla.php';

class Viber extends Simpla {

    const uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'viber' . DIRECTORY_SEPARATOR;

    private $headers;
    private $phone;

    public function __construct() {
        if (!is_dir(__DIR__ . DIRECTORY_SEPARATOR . 'files')) {
            mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'files');
        }
        if (!is_dir(self::uploadDir)) {
            mkdir(self::uploadDir);
        }
        $_SESSION['chat_type'] = 'viber';
        if (isset($_GET['phone'])) {
            if ($_GET['phone']) {
                $this->phone = (int) $this->curl->preparePhone($_GET['phone']);
                $_SESSION['phone'] = $this->phone;
            }
        }
        $this->headers = ['X-Viber-Auth-Token: ' . $this->config->viberBotToken, 'Content-Type:application/json'];
    }

    public function setWebHook() {
        $data = (object) [
                    'url' => 'https://' . baseUrl . '/chats.php?api=viber&method=webHook',
                    'event_types' => [
                        'delivered',
                        'seen',
                        'failed',
                        'subscribed',
                        'unsubscribed',
                        'conversation_started',
                        'message',
                    ],
                    'send_name' => true,
                    'send_photo' => true,
        ];
        $url = $this->config->viberSetWebHookUri;
        $curl = $this->curl->curlInit($url, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        return $this->curl->curlClose($curl);
    }

    public function removeWebHook() {
        $data = (object) [
                    'uri' => '',
        ];
        $url = $this->config->viberSetWebHookUri;
        $curl = $this->curl->curlInit($url, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        return $this->curl->curlClose($curl);
    }

    public function sendMessage($text, $userId) {
        $url = 'https://chatapi.viber.com/pa/send_message';
        $data = (object) [
                    'receiver' => $userId,
                    'min_api_version' => 1,
                    'sender' => (object) [
                        'name' => $this->config->viberBotName,
                        'avatar' => 'https://' . baseUrl . '/design/boostra_mini_norm/img/favicon192x192.png',
                    ],
                    'tracking_data' => 'tracking data',
                    'type' => 'text',
                    'text' => $text,
        ];
        $curl = $this->curl->curlInit($url, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $result = json_decode($this->curl->curlClose($curl));
        $userInfo = $this->chats->getUserInfoByMessangerId($userId);
        if ($userInfo) {
            $phone = $userInfo->phone;
            $user_id = $userInfo->user_id;
            $userIdInMessanger = $userInfo->userIdInMessanger;
        } else {
            $phone = null;
            $user_id = null;
            $userIdInMessanger = null;
        }
        $message = [
            'chat_type' => (string) 'viber',
            'user_id_in_chat' => (string) $this->config->viberBotName,
            'chat_id' => (string) $userIdInMessanger,
            'update_id' => null,
            'message_status' => (int) 2,
            'message_id' => rand(),
            'text' => (string) $text,
            'date' => date("Y-m-d H:i:s"),
            'status' => 1,
            'user_id' => $user_id,
            'phone' => $phone,
        ];
        if ($result->Data->status == 0) {
            $this->chats->insertMessage($message);
            return $result;
        } else {
            /**
             * записать в лог ошибку отправки
             */
            file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'viber_error.log', json_encode($result) . "\n", FILE_APPEND | LOCK_EX);
            return false;
        }
    }

    public function getBotInfo() {
        $data = (object) [];
        $url = 'https://chatapi.viber.com/pa/get_account_info';
        $curl = $this->curl->curlInit($url, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        return $this->curl->curlClose($curl);
    }

    public function getUserInfo($userId) {
        $url = 'https://chatapi.viber.com/pa/get_user_details';
        $data = (object) [
                    'id' => $userId,
        ];
        $curl = $this->curl->curlInit($url, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        return $this->curl->curlClose($curl);
    }

    public function getUserOnline(array $usersId) {
        $url = 'https://chatapi.viber.com/pa/get_online';
        $data = (object) [
                    'ids' => $usersId,
        ];
        $curl = $this->curl->curlInit($url, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        return $this->curl->curlClose($curl);
    }

    public function webHook() {
        $hook = json_decode(file_get_contents('php://input'));
        if ($hook->message->type === 'text') {
            $this->textMessage($hook);
        } elseif ($hook->message->type === 'text') {
            $this->textMessage($hook);
        } elseif ($hook->message->type === 'picture') {
            $this->pictureMessage($hook);
        } elseif ($hook->message->type === 'video') {
            $this->videoMessage($hook);
        } elseif ($hook->message->type === 'file') {
            $this->fileMessage($hook);
        }
    }

    private function textMessage($hook) {
        $phone = null;
        $userId = null;
        $userInfo = $this->chats->getUserInfoByMessangerId($hook->sender->id);
        if ($userInfo) {
            $phone = $userInfo->phone;
            $userId = $userInfo->user_id;
        }
        $data = [
            'chat_type' => (string) 'viber',
            'user_id_in_chat' => (string) $hook->sender->id,
            'chat_id' => (string) $hook->sender->id,
            'update_id' => (int) $hook->message_token,
            'message_status' => (int) 0,
            'message_id' => (int) $hook->message_token,
            'text' => (string) $hook->message->text,
            'date' => date("Y-m-d H:i:s"),
            'status' => 2,
            'user_id' => $userId,
            'phone' => $phone,
        ];
        $match = false;
        if (strripos($hook->message->text, '/start') !== false) {
            preg_match('/\/start (?<phone>\d{11})/ui', $hook->message->text, $match);
            if ($match) {
                $phone = $match['phone'];
                if ($this->chats->verifyPhone($phone, 'viber')) {
                    $result = $this->sendMessage($this->chats->newCodeGenerate($phone), $hook->sender->id);
                    if ($result) {
                        $this->chats->insertMessage($data);
                    }
                } else {
                    $this->sendMessage('Ваш мессенджер не привязан к аккаунту. Перешлите сюда сообщение (полностью!!!) отправленое Вам по смс', $hook->sender->id);
                    $this->chats->sendSms($phone, '/code ' . rand(10000, 99999) . ' ' . $phone);
                }
            }
        } elseif (strripos($hook->message->text, '/code') !== false) {
            preg_match('/\/code (?<code>\d{5}) (?<phone>\d{11})/ui', $hook->message->text, $match);
            if ($match) {
                $code = $match['code'];
                $phone = $match['phone'];
                if ($this->chats->checkSmsCode($phone, $code)) {
                    $userInfo = [
                        'phone' => $phone,
                        'typeMessanger' => 'viber',
                        'userIdInMessanger' => $hook->sender->id,
                        'chatId' => $hook->sender->id,
                        'user_id' => $this->users->get_phone_user($phone),
                    ];
                    $this->chats->goodVerifyMessanger($userInfo);
                    $this->sendMessage('Ваш месенджер успешно подтвержден', $hook->sender->id);
                    $this->sendMessage($this->chats->newCodeGenerate($phone), $hook->sender->id);
                } else {
                    $this->sendMessage('Вы отправили не верное сообщение, либо данное сообщение устарело.
                    Или же Вы уже привязали Ваш месенджер к аккаунту', $hook->sender->id);
                }
            }
        } else {
            $this->chats->insertMessage($data);
        }
    }

    private function pictureMessage($hook) {
        $phone = null;
        $userId = null;
        $userInfo = $this->chats->getUserInfoByMessangerId($hook->sender->id);
        if ($userInfo) {
            $phone = $userInfo->phone;
            $userId = $userInfo->user_id;
        }
        $fileUrl = $this->uploadFile($hook->message, self::uploadDir . 'picture' . DIRECTORY_SEPARATOR);
        $data = [
            'chat_type' => (string) 'viber',
            'user_id_in_chat' => (string) $hook->sender->id,
            'chat_id' => (string) $hook->sender->id,
            'update_id' => (int) $hook->message_token,
            'message_status' => (int) 0,
            'message_id' => (int) $hook->message_token,
            'text' => '<img style="height: 100px; width: auto; cursor: pointer;" title="Посмотреть полно-размерное фото" onclick="openFullImage(this);" src="' . (string) $fileUrl . '"/>',
            'date' => date("Y-m-d H:i:s"),
            'status' => 2,
            'user_id' => $userId,
            'phone' => $phone,
        ];
        $this->chats->insertMessage($data);
    }

    private function videoMessage($hook) {
        $phone = null;
        $userId = null;
        $userInfo = $this->chats->getUserInfoByMessangerId($hook->sender->id);
        if ($userInfo) {
            $phone = $userInfo->phone;
            $userId = $userInfo->user_id;
        }
        $fileUrl = $this->uploadFile($hook->message, self::uploadDir . 'video' . DIRECTORY_SEPARATOR);
        $data = [
            'chat_type' => (string) 'viber',
            'user_id_in_chat' => (string) $hook->sender->id,
            'chat_id' => (string) $hook->sender->id,
            'update_id' => (int) $hook->message_token,
            'message_status' => (int) 0,
            'message_id' => (int) $hook->message_token,
            'text' => '<video preload="metadata" height="120px" src="' . $fileUrl . '" controls></video>',
            'date' => date("Y-m-d H:i:s"),
            'status' => 2,
            'user_id' => $userId,
            'phone' => $phone,
        ];
        $this->chats->insertMessage($data);
    }

    private function fileMessage($hook) {
        $phone = null;
        $userId = null;
        $userInfo = $this->chats->getUserInfoByMessangerId($hook->sender->id);
        if ($userInfo) {
            $phone = $userInfo->phone;
            $userId = $userInfo->user_id;
        }
        $fileUrl = $this->uploadFile($hook->message, self::uploadDir . 'file' . DIRECTORY_SEPARATOR);
        $data = [
            'chat_type' => (string) 'viber',
            'user_id_in_chat' => (string) $hook->sender->id,
            'chat_id' => (string) $hook->sender->id,
            'update_id' => (int) $hook->message_token,
            'message_status' => (int) 0,
            'message_id' => (int) $hook->message_token,
            'text' => '<a target="_blank" href="' . $fileUrl . '">Скачать документ</a>',
            'date' => date("Y-m-d H:i:s"),
            'status' => 2,
            'user_id' => $userId,
            'phone' => $phone,
        ];
        $this->chats->insertMessage($data);
    }

    private function uploadFile($file, $dir) {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $stringContent = file_get_contents($file->media);
        $extension = $this->getExtensionFile($file->file_name);
        $fileName = md5($stringContent) . $extension;
        $file = $dir . $fileName;
        file_put_contents($file, $stringContent);
        return protocol . '://' . baseUrl . str_replace(ROOT, '', $dir) . $fileName;
    }

    private function getExtensionFile($name) {
        $match = false;
        preg_match('/\.(.{3,5})$/ui', $name, $match);
        if (isset($match[1])) {
            return $match[1];
        }
        return false;
    }

}
