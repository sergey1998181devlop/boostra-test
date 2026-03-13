<?php

namespace chats\whatsapp;

use chats\whatsapp\WhatsappUpdates AS Update;
use chats\main\Main;
use Simpla;

class WhatsappMain extends Simpla {

    public static function getMessage($data) {
        foreach ($data as $message) {
            if ($message->type === 'chat') {
                Update::getTextMessage($message);
            } elseif ($message->type === 'image') {
                Update::getImageMessage($message);
            } elseif ($message->type === 'ptt') {
                Update::getVoiceMessage($message);
            } elseif ($message->type === 'document') {
                Update::getDocumentMessage($message);
            } elseif ($message->type === 'audio') {
                Update::getAudioMessage($message);
            } elseif ($message->type === 'video') {
                Update::getVideoMessage($message);
            } elseif ($message->type === 'location') {
                Update::getLocationMessage($message);
            } elseif ($message->type === 'call_log') {
                Update::getCallLogMessage($message);
            }
        }
    }

    public static function getUpdatesMessages($data) {
        foreach ($data as $updateMessage) {
            $status = 1;
            if (in_array($updateMessage->status, Main::$statuses)) {
                $status = Main::$statuses[$updateMessage->status];
            }
            $obj = [
                'chat_type' => 'whatsapp',
                'message_id' => (string) $updateMessage->id,
                'message_status' => $status,
                'status' => 1
            ];
            Main::getUpdatesStatusMessage($obj);
        }
    }

}
