<?php

namespace chats\whatsapp;

use chats\main\Main;
use chats\main\Settings;
use chats\main\Users;
use Simpla;

class WhatsappAddMessageInDb {

    public static function addMessage(string $typeMessage, array $message, string $response) {
        switch ($typeMessage):
            case 'text':
                $data = self::getDataText($message, $response);
                Main::getMessage($data);
                break;
            case 'file':
                $data = self::getDataFile($message, $response);
                Main::getMessage($data);
                break;
            case 'ptt':
                $data = self::getDataPtt($message, $response);
                Main::getMessage($data);
                break;
            case 'link':
                $data = self::getDataLink($message, $response);
                Main::getMessage($data);
                break;
            case 'contact':
                $data = self::getDataContact($message, $response);
                Main::getMessage($data);
                break;
            case 'location':
                $data = self::getDataLocation($message, $response);
                Main::getMessage($data);
            case 'vcard':
                $data = self::getDataVcard($message, $response);
                Main::getMessage($data);
                break;
        endswitch;
    }

    private static function getDataText($message, $response) {
        $result = json_decode($response);
        $simplaObj = new Simpla;
        $phone = Users::preparePhone($result->Data->message);
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) Settings::whatsAppPhoneNumber . '@c.us',
            'chat_id' => (string) $phone . '@c.us',
            'update_id' => $result->Data->queueNumber,
            'message_id' => (string) $result->Data->id,
            'message_status' => 0,
            'text' => (string) $message['text'],
            'date' => date('Y-m-d H:i:s'),
            'status' => 1,
            'user_id' => $simplaObj->users->get_phone_user($phone),
            'phone' => $phone
        ];
        return $data;
    }

    public static function getDataFile($message, $response) {
        $result = json_decode($response);
        $simplaObj = new Simpla;
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message['managerId'],
            'chat_id' => (string) $message['chatId'],
            'update_id' => 0,
            'message_id' => (string) $result->id,
            'message_status' => 0,
            'text' => (string) $message['text'],
            'date' => date('Y-m-d H:i:s'),
            'status' => 1,
            'user_id' => $simplaObj->users->get_phone_user($message['phone']),
            'phone' => $message['phone']
        ];
        return $data;
    }

    public static function getDataPtt($message, $response) {
        $result = json_decode($response);
        $simplaObj = new Simpla;
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message['managerId'],
            'chat_id' => (string) $message['chatId'],
            'update_id' => 0,
            'message_id' => (string) $result->id,
            'message_status' => 0,
            'text' => (string) $message['text'],
            'date' => date('Y-m-d H:i:s'),
            'status' => 1,
            'user_id' => $simplaObj->users->get_phone_user($message['phone']),
            'phone' => $message['phone']
        ];
        return $data;
    }

    public static function getDataLink($message, $response) {
        $result = json_decode($response);
        $simplaObj = new Simpla;
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message['managerId'],
            'chat_id' => (string) $message['chatId'],
            'update_id' => 0,
            'message_id' => (string) $result->id,
            'message_status' => 0,
            'text' => (string) $message['text'],
            'date' => date('Y-m-d H:i:s'),
            'status' => 1,
            'user_id' => $simplaObj->users->get_phone_user($message['phone']),
            'phone' => $message['phone']
        ];
        return $data;
    }

    public static function getDataContact($message, $response) {
        $result = json_decode($response);
        $simplaObj = new Simpla;
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message['managerId'],
            'chat_id' => (string) $message['chatId'],
            'update_id' => 0,
            'message_id' => (string) $result->id,
            'message_status' => 0,
            'text' => (string) $message['text'],
            'date' => date('Y-m-d H:i:s'),
            'status' => 1,
            'user_id' => $simplaObj->users->get_phone_user($message['phone']),
            'phone' => $message['phone']
        ];
        return $data;
    }

    public static function getDataLocation($message, $response) {
        $result = json_decode($response);
        $simplaObj = new Simpla;
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message['managerId'],
            'chat_id' => (string) $message['chatId'],
            'update_id' => 0,
            'message_id' => (string) $result->id,
            'message_status' => 0,
            'text' => (string) $message['text'],
            'date' => date('Y-m-d H:i:s'),
            'status' => 1,
            'user_id' => $simplaObj->users->get_phone_user($message['phone']),
            'phone' => $message['phone']
        ];
        return $data;
    }

    public static function getDataVcard($message, $response) {
        $result = json_decode($response);
        $simplaObj = new Simpla;
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message['managerId'],
            'chat_id' => (string) $message['chatId'],
            'update_id' => 0,
            'message_id' => (string) $result->id,
            'message_status' => 0,
            'text' => (string) $message['text'],
            'date' => date('Y-m-d H:i:s'),
            'status' => 1,
            'user_id' => $simplaObj->users->get_phone_user($message['phone']),
            'phone' => $message['phone']
        ];
        return $data;
    }

}
