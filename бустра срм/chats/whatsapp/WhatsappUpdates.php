<?php

namespace chats\whatsapp;

use Simpla;
use chats\main\Main;
use chats\main\Users;
use chats\whatsapp\WhatsappSettings AS Settings;

class WhatsappUpdates {

    public static function getTextMessage($message) {
        $simplaObj = new Simpla;
        $phone = Users::preparePhone($message->chatId);
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message->author,
            'chat_id' => (string) $message->chatId,
            'update_id' => (int) $message->messageNumber,
            'message_id' => (string) $message->id,
            'message_status' => 0,
            'text' => (string) $message->body,
            'date' => date('Y-m-d H:i:s'),
            'status' => self::setStatusMessage($message),
            'user_id' => $simplaObj->users->get_phone_user($phone),
            'phone' => $phone
        ];
        Main::getMessage($data);
    }

    private static function setStatusMessage($message) {
        if ($message->fromMe) {
            return 1;
        }
        return 2;
    }

    public static function getImageMessage($message) {
        $simplaObj = new Simpla;
        if (!is_dir(Settings::whatsAppUploadImage)) {
            mkdir(Settings::whatsAppUploadImage);
        }
        $phone = Users::preparePhone($message->chatId);
        $fileUrl = \chats\main\Main::uploadFile($message->body, Settings::whatsAppUploadImage);
        if ($fileUrl) {
            $text = '<div style="text-align: center;">'
                    . '<img style="height: 100px; width: auto; cursor: pointer;" '
                    . 'title="Посмотреть полно-размерное фото" onclick="openFullImage(this);" '
                    . 'src="' . (string) $fileUrl . '"/>'
                    . '<div>' . $message->caption . '</div>'
                    . '</div>';
        } else {
            $text = 'Файл удален';
        }
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message->author,
            'chat_id' => (string) $message->chatId,
            'update_id' => (int) $message->messageNumber,
            'message_status' => 0,
            'message_id' => (string) $message->id,
            'text' => $text,
            'date' => date('Y-m-d H:i:s'),
            'status' => self::setStatusMessage($message),
            'user_id' => $simplaObj->users->get_phone_user($phone),
            'phone' => $phone
        ];
        Main::getMessage($data);
    }

    public static function getVoiceMessage($message) {
        $simplaObj = new Simpla;
        $phone = Users::preparePhone($message->chatId);
        if (!is_dir(Settings::whatsAppUploadVoice)) {
            mkdir(Settings::whatsAppUploadVoice);
        }
        $fileUrl = \chats\main\Main::uploadFile($message->body, Settings::whatsAppUploadVoice);
        if ($fileUrl) {
            $text = '<audio controls src="' . $fileUrl . '"></audio>';
        } else {
            $text = 'Сообщение удалено';
        }
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message->author,
            'chat_id' => (string) $message->chatId,
            'update_id' => (int) $message->messageNumber,
            'message_id' => (string) $message->id,
            'text' => $text,
            'message_status' => 0,
            'date' => date('Y-m-d H:i:s'),
            'status' => self::setStatusMessage($message),
            'user_id' => $simplaObj->users->get_phone_user($phone),
            'phone' => $phone
        ];
        Main::getMessage($data);
    }

    public static function getDocumentMessage($message) {
        $simplaObj = new Simpla;
        $phone = Users::preparePhone($message->chatId);
        if (!is_dir(Settings::whatsAppUploadDocument)) {
            mkdir(Settings::whatsAppUploadDocument);
        }
        $fileUrl = \chats\main\Main::uploadFile($message->body, Settings::whatsAppUploadDocument);
        if ($fileUrl) {
            $text = '<a target="_blank" href="' . $fileUrl . '">Скачать документ</a>';
        } else {
            $text = 'Файл удален';
        }
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message->author,
            'chat_id' => (string) $message->chatId,
            'update_id' => (int) $message->messageNumber,
            'message_id' => (string) $message->id,
            'text' => $text,
            'message_status' => 0,
            'date' => date('Y-m-d H:i:s'),
            'status' => self::setStatusMessage($message),
            'user_id' => $simplaObj->users->get_phone_user($phone),
            'phone' => $phone
        ];
        Main::getMessage($data);
    }

    public static function getAudioMessage($message) {
        $simplaObj = new Simpla;
        $phone = Users::preparePhone($message->chatId);
        if (!is_dir(Settings::whatsAppUploadAudio)) {
            mkdir(Settings::whatsAppUploadAudio);
        }
        $fileUrl = \chats\main\Main::uploadFile($message->body, Settings::whatsAppUploadAudio);
        if ($fileUrl) {
            $text = '<audio controls src="' . $fileUrl . '"></audio>';
        } else {
            $text = 'Сообщение удалено';
        }
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message->author,
            'chat_id' => (string) $message->chatId,
            'update_id' => (int) $message->messageNumber,
            'message_id' => (string) $message->id,
            'text' => $text,
            'message_status' => 0,
            'date' => date('Y-m-d H:i:s'),
            'status' => self::setStatusMessage($message),
            'user_id' => $simplaObj->users->get_phone_user($phone),
            'phone' => $phone
        ];
        Main::getMessage($data);
    }

    public static function getVideoMessage($message) {
        $simplaObj = new Simpla;
        $phone = Users::preparePhone($message->chatId);
        if (!is_dir(Settings::whatsAppUploadVideo)) {
            mkdir(Settings::whatsAppUploadVideo);
        }
        $fileUrl = \chats\main\Main::uploadFile($message->body, Settings::whatsAppUploadVideo);
        if ($fileUrl) {
            $text = '<video preload="metadata" height="120px" src="' . $fileUrl . '" controls></video>';
        } else {
            $text = 'Сообщение удалено';
        }
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message->author,
            'chat_id' => (string) $message->chatId,
            'update_id' => (int) $message->messageNumber,
            'message_id' => (string) $message->id,
            'text' => $text,
            'message_status' => 0,
            'date' => date('Y-m-d H:i:s'),
            'status' => self::setStatusMessage($message),
            'user_id' => $simplaObj->users->get_phone_user($phone),
            'phone' => $phone
        ];
        Main::getMessage($data);
    }

    public static function getLocationMessage($message) {
        $simplaObj = new Simpla;
        $phone = Users::preparePhone($message->chatId);
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message->author,
            'chat_id' => (string) $message->chatId,
            'update_id' => (int) $message->messageNumber,
            'message_id' => (string) $message->id,
            'text' => $message->body,
            'message_status' => 0,
            'date' => date('Y-m-d H:i:s'),
            'status' => self::setStatusMessage($message),
            'user_id' => $simplaObj->users->get_phone_user($phone),
            'phone' => $phone
        ];
        Main::getMessage($data);
    }

    public static function getCallLogMessage($message) {
        $simplaObj = new Simpla;
        $phone = Users::preparePhone($message->chatId);
        if (!is_dir(Settings::whatsAppUploadCallLog)) {
            mkdir(Settings::whatsAppUploadCallLog);
        }
        $fileUrl = \chats\main\Main::uploadFile($message->body, Settings::whatsAppUploadCallLog);
        $data = [
            'chat_type' => (string) 'whatsapp',
            'user_id_in_chat' => (string) $message->author,
            'chat_id' => (string) $message->chatId,
            'update_id' => (int) $message->messageNumber,
            'message_id' => (string) $message->id,
            'text' => '<a href="' . (string) $fileUrl . '">Скачать файл</a>',
            'date' => date('Y-m-d H:i:s'),
            'message_status' => 0,
            'status' => self::setStatusMessage($message),
            'user_id' => $simplaObj->users->get_phone_user($phone),
            'phone' => $phone
        ];
        Main::getMessage($data);
    }

}
