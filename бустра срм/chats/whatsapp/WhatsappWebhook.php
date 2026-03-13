<?php

namespace chats\whatsapp;

use chats\whatsapp\WhatsappCurl AS Curl;
use chats\whatsapp\WhatsappSetDataInstance AS Data;
use chats\whatsapp\WhatsappMain AS Main;
use chats\whatsapp\WhatsappSettings AS Settings;

class WhatsappWebhook {

    public function __construct() {
        if (!is_dir(Settings::whatsAppUploadDir))
            mkdir(Settings::whatsAppUploadDir);
        if (!is_dir(Settings::whatsAppUploadAudio))
            mkdir(Settings::whatsAppUploadAudio);
        if (!is_dir(Settings::whatsAppUploadVideo))
            mkdir(Settings::whatsAppUploadVideo);
        if (!is_dir(Settings::whatsAppUploadVoice))
            mkdir(Settings::whatsAppUploadVoice);
        if (!is_dir(Settings::whatsAppUploadDocument))
            mkdir(Settings::whatsAppUploadDocument);
        if (!is_dir(Settings::whatsAppUploadImage))
            mkdir(Settings::whatsAppUploadImage);
        if (!is_dir(Settings::whatsAppUploadCallLog))
            mkdir(Settings::whatsAppUploadCallLog);
    }

    /**
     * Повторяет отправку вебхука для сообщения
     * @param string $messageId
     * @return type
     */
    public function repeatHook(string $messageId) {
        $data = Data::setDataInstance('repeatHook', $messageId);
        return Curl::sendPost($data, 'repeatHook');
    }

    /**
     * Получить статус сформированного для сообщения вебхука.
     * 
     * @param string $msgId
     * @return type
     */
    public function webhookStatus(array $data) {
        if (isset($data['msgId'])) {
            return Curl::sendGet(['msgId' => $data['msgId']], 'webhookStatus');
        } else {
            return (object) ['ResponseCode' => '200', 'Data' => (object) [
                            'error' => 'Не передан обязательный параметр msgId'
                        ], 'Header' => Curl::$headers[200]];
        }
    }

    /**
     * Получает обновления 
     */
    public function getWebhook() {
        $hook = json_decode(file_get_contents('php://input'));
        if (isset($hook->messages)) {
            Main::getMessage($hook->messages);
        }
        if (isset($hook->ack)) {
            Main::getUpdatesMessages($hook->ack);
        }
    }

}
