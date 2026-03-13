<?php

namespace chats\viber;

use chats\viber\ViberSettings AS Settings;
use chats\viber\ViberCurl AS Curl;
use chats\viber\ViberUpdates AS Update;

class ViberWebhook {

    /**
     * получение новых сообщений
     */
    public function getWebhook() {
        $hook = json_decode(file_get_contents('php://input'));
        if ($hook->message->type === 'text') {
            Update::textMessage($hook);
        } elseif ($hook->message->type === 'picture') {
            Update::pictureMessage($hook);
        } elseif ($hook->message->type === 'video') {
            Update::videoMessage($hook);
        } elseif ($hook->message->type === 'file') {
            Update::fileMessage($hook);
        }
    }

    /**
     * Удаление веб-хука
     */
    public function removeWebhook() {
        $data = (object) [
                    'uri' => '',
        ];
        return Curl::sendPost('set_webhook', $data);
    }

    /**
     * Установка адреса для получения веб-хуков
     */
    public function setWebhook() {
        $data = (object) [
                    'url' => Settings::viberWebhookUrl,
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
        return Curl::sendPost('set_webhook', $data);
    }

}
