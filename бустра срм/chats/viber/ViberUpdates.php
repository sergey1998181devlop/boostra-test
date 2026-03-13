<?php

namespace chats\viber;

use chats\main\Main;
use chats\viber\ViberMessages AS Message;

class ViberUpdates {

    private static function dataGenerate($hook) {
        $phone = null;
        $userId = null;
        $userInfo = Main::getUserInfoByMessangerId($hook->sender->id);
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
        return $data;
    }

    public static function textMessage($hook) {
        $data = self::dataGenerate($hook);
        $match = false;
        if (strripos($hook->message->text, '/start') !== false) {
            preg_match('/\/start (?<phone>\d{11})/ui', $hook->message->text, $match);
            if ($match) {
                $phone = $match['phone'];
                self::veryfi($phone, $hook, $data);
            }
        } elseif (strripos($hook->message->text, '/code') !== false) {
            preg_match('/\/code (?<code>\d{5}) (?<phone>\d{11})/ui', $hook->message->text, $match);
            if ($match) {
                self::sendCodeAfterVeryfi($hook, $match);
            }
        } else {
            Main::insertMessage($data);
        }
    }

    private static function veryfi($phone, $hook, $data) {
        if (Main::verifyPhone($phone, 'viber')) {
            $result = Message::sendTextMessage(Main::loginCodeGenerate($phone), $hook->sender->id);
            if ($result) {
                Main::insertMessage($data);
            }
        } else {
            Message::sendTextMessage('Ваш мессенджер не привязан к аккаунту. Перешлите сюда сообщение (полностью!!!) отправленое Вам по смс', $hook->sender->id);
            Main::sendSms($phone, '/code ' . rand(10000, 99999) . ' ' . $phone);
        }
    }

    private static function sendCodeAfterVeryfi($hook, $match) {
        $simplaObj = new Simpla();
        $code = $match['code'];
        $phone = $match['phone'];
        if (Main::checkSmsCode($phone, $code)) {
            $userInfo = [
                'phone' => $phone,
                'typeMessanger' => 'viber',
                'userIdInMessanger' => $hook->sender->id,
                'chatId' => $hook->sender->id,
                'user_id' => $simplaObj->users->get_phone_user($phone),
            ];
            Main::goodVerifyMessanger($userInfo);
            Message::sendTextMessage('Ваш месенджер успешно подтвержден', $hook->sender->id);
            Message::sendTextMessage(Main::loginCodeGenerate($phone), $hook->sender->id);
        } else {
            Message::sendTextMessage('Вы отправили не верное сообщение, либо данное сообщение устарело.
                    Или же Вы уже привязали Ваш месенджер к аккаунту', $hook->sender->id);
        }
    }

    public static function pictureMessage($hook) {
        $fileUrl = self::uploadFile($hook->message, uploadDir . 'picture' . DIRECTORY_SEPARATOR);
        $data = self::dataGenerate($hook);
        $data['text'] = '<img style="height: 100px; width: auto; cursor: pointer;"'
                . ' title="Посмотреть полно-размерное фото" '
                . 'onclick="openFullImage(this);" src="' . (string) $fileUrl . '"/>';
        Main::insertMessage($data);
    }

    public static function videoMessage($hook) {
        $fileUrl = self::uploadFile($hook->message, uploadDir . 'video' . DIRECTORY_SEPARATOR);
        $data = self::dataGenerate($hook);
        $data['text'] = '<video preload="metadata" height="120px" src="' . $fileUrl . '" controls></video>';
        Main::insertMessage($data);
    }

    public static function fileMessage($hook) {
        $fileUrl = self::uploadFile($hook->message, uploadDir . 'file' . DIRECTORY_SEPARATOR);
        $data = self::dataGenerate($hook);
        $data['text'] = '<a target="_blank" href="' . $fileUrl . '">Скачать документ</a>';
        Main::insertMessage($data);
    }

    public static function uploadFile($file, $dir) {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $stringContent = file_get_contents($file->media);
        $extension = static::getExtensionFile($file->file_name);
        $fileName = md5($stringContent) . $extension;
        $file = $dir . $fileName;
        file_put_contents($file, $stringContent);
        return protocol . '://' . baseUrl . str_replace(ROOT, '', $dir) . $fileName;
    }

    public static function getExtensionFile($name) {
        $match = false;
        preg_match('/\.(.{3,5})$/ui', $name, $match);
        if (isset($match[1])) {
            return $match[1];
        }
        return false;
    }

}
