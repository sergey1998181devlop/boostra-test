<?php

namespace chats\viber;

use chats\viber\ViberSettings AS Settings;
use chats\viber\ViberSetDataUser AS User;
use chats\viber\ViberCurl AS Curl;
use chats\main\Users;
use chats\main\Main;

class ViberMessages {

    /**
     * Генерация тела сообщения для отправки в мессенджер 
     */
    private static function dataTextMessageGenerate($data) {
        $obj = [
            'receiver' => User::getUserId($data),
            'min_api_version' => 1,
            'sender' => (object) [
                'name' => Settings::viberBotName,
                'avatar' => 'https://' . baseUrl . '/' . Settings::viberAvatar,
            ],
            'tracking_data' => 'tracking data'
        ];
        if (isset($data['text'])) {
            $obj['text'] = $data['text'];
        }
        return $obj;
    }

    /**
     * Получение данных о пользователе для оправки сообщения
     */
    private static function setUserInfo($data) {

        if (isset($data['phone'])) {
            $res = Users::getUserInfoByPhone($data['phone']);
            $user_id = $res->id;
        } else {
            if (isset($data['id'])) {
                $user_id = $data['id'];
            }
        }
        $userInfo = User::getUserInfoByIdInCRM(User::getUserId($data));
        if ($userInfo) {
            $phone = $userInfo->phone;
            $userIdInMessanger = $userInfo->userIdInMessanger;
        } else {
            $phone = null;
            $userIdInMessanger = null;
        }
        return ['phone' => $phone, 'user_id' => $user_id, 'userIdInMessanger' => $userIdInMessanger];
    }

    /**
     * Генерация массива с телом сообщения для записи в базу CRM
     */
    private static function generateMessageForCRM($text, $data) {
        $userInfo = self::setUserInfo($data);
        $message = [
            'chat_type' => (string) 'viber',
            'user_id_in_chat' => (string) Settings::viberBotName,
            'chat_id' => (string) $userInfo['userIdInMessanger'],
            'update_id' => null,
            'message_status' => (int) 2,
            'message_id' => rand(),
            'text' => (string) $text,
            'date' => date("Y-m-d H:i:s"),
            'status' => 1,
            'user_id' => $userInfo['user_id'],
            'phone' => $userInfo['phone'],
        ];
        return $message;
    }

    /**
     * Отправка текстового сообщения
     */
    public static function sendText($data) {
        $obj = self::dataTextMessageGenerate($data);
        $obj['type'] = 'text';
        $result = json_decode(Curl::sendPost('send_message', (object) $obj));
        $message = self::generateMessageForCRM($data['text'], $data);
        return self::result($result, $message);
    }

    private static function result($result, $message) {
        if ($result->Data->status == 0) {
            Main::insertMessage($message);
            return $result;
        } else {
            /**
             * записать в лог ошибку отправки
             */
            file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'viber_error.log', json_encode($result) . "\n", FILE_APPEND | LOCK_EX);
            return false;
        }
    }

    /**
     * Отправка сообщения с изображением
     */
    public static function sendImage($data) {
        $obj = self::dataTextMessageGenerate($data);
        $obj['type'] = 'picture';
        $obj['media'] = $data['url'];
        $result = json_decode(Curl::sendPost('send_message', (object) $obj));
        if (!isset($data['text'])) {
            $data['text'] = false;
        }
        $text = '<div><img style="height: 100px; width: auto; cursor: pointer;" '
                . 'title="Посмотреть полно-размерное фото" onclick="openFullImage(this);" '
                . 'src="' . (string) $data['url'] . '"/><div>' . $data['text'] . '</div></div>';
        $message = self::generateMessageForCRM($text, $data);
        return self::result($result, $message);
    }

    /**
     * Отправка сообщения с видео
     */
    public static function sendVideo($data) {
        $obj = self::dataTextMessageGenerate($data);
        $obj['type'] = 'video';
        $obj['media'] = $data['url'];
        $obj['size'] = strlen(file_get_contents($data['url']));
        if (!isset($data['text'])) {
            $data['text'] = false;
        }
        $text = '<div><video preload="metadata" height="120px" src="' . $data['url'] . '" controls></video><div>' . $data['text'] . '</div></div>';
        $result = json_decode(Curl::sendPost('send_message', (object) $obj));
        $message = self::generateMessageForCRM($text, $data);
        return self::result($result, $message);
    }

    /**
     * Отправка сообщения с файлом
     */
    public static function sendFile($data) {
        if (isset($data['text'])) {
            unset($data['text']);
        }
        $obj = self::dataTextMessageGenerate($data);
        $obj['type'] = 'file';
        $obj['media'] = $data['url'];
        $obj['size'] = strlen(file_get_contents($data['url']));
        $obj['file_name'] = $data['name'];
        $result = json_decode(Curl::sendPost('send_message', (object) $obj));
        $text = '<a target="_blank" href="' . $data['url'] . '">Скачать документ</a>';
        $message = self::generateMessageForCRM($text, $data);
        return self::result($result, $message);
    }

}
