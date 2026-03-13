<?php

namespace chats\whatsapp;

use chats\main\Settings as Main;

class WhatsappSettings {

    const whatsAppToken = Main::whatsAppToken;
    const whatsAppInstansNumber = Main::whatsAppInstansNumber;
    const whatsAppSendDelay = Main::whatsAppSendDelay;
    const whatsAppInstanceStatuses = Main::whatsAppInstanceStatuses;
    const whatsAppWebhookStatuses = Main::whatsAppWebhookStatuses;
    const whatsAppStatusNotificationsOn = Main::whatsAppStatusNotificationsOn;
    const whatsAppAckNotificationsOn = Main::whatsAppAckNotificationsOn;
    const whatsAppChatUpdateOn = Main::whatsAppChatUpdateOn;
    const whatsAppVideoUploadOn = Main::whatsAppVideoUploadOn;
    const whatsAppGuaranteedHooks = Main::whatsAppGuaranteedHooks;
    const whatsAppIgnoreOldMessages = Main::whatsAppIgnoreOldMessages;
    const whatsAppOldMessagesPeriod = Main::whatsAppOldMessagesPeriod;
    const whatsAppProcessArchive = Main::whatsAppProcessArchive;
    const whatsAppDisableDialogsArchive = Main::whatsAppDisableDialogsArchive;
    const whatsAppParallelHooks = Main::whatsAppParallelHooks;

    # !!! Не изменять !!!
    const url = 'https://api.chat-api.com/instance' . self::whatsAppInstansNumber . '/';
    const token = '?token=' . self::whatsAppToken;
    const whatsAppWebhookUrl = protocol . baseUrl . '/chats.php?chat=whatsapp&class=webhook&method=getWebhook';
    const whatsAppUploadDir = uploadDir . 'whatsApp' . DIRECTORY_SEPARATOR;                                          # Директория для загрузки файлов
    const whatsAppUploadImage = self::whatsAppUploadDir . 'image' . DIRECTORY_SEPARATOR;
    const whatsAppUploadVoice = self::whatsAppUploadDir . 'voice' . DIRECTORY_SEPARATOR;
    const whatsAppUploadDocument = self::whatsAppUploadDir . 'document' . DIRECTORY_SEPARATOR;
    const whatsAppUploadAudio = self::whatsAppUploadDir . 'audio' . DIRECTORY_SEPARATOR;
    const whatsAppUploadVideo = self::whatsAppUploadDir . 'video' . DIRECTORY_SEPARATOR;
    const whatsAppUploadCallLog = self::whatsAppUploadDir . 'callLog' . DIRECTORY_SEPARATOR;

    private static function setDataSettings() {
        $data = new \stdClass();
        $data->sendDelay = self::whatsAppSendDelay;
        $data->webhookUrl = self::whatsAppWebhookUrl;
        $data->instanceStatuses = self::whatsAppInstanceStatuses;
        $data->webhookStatuses = self::whatsAppWebhookStatuses;
        $data->statusNotificationsOn = self::whatsAppStatusNotificationsOn;
        $data->ackNotificationsOn = self::whatsAppAckNotificationsOn;
        $data->chatUpdateOn = self::whatsAppChatUpdateOn;
        $data->videoUploadOn = self::whatsAppVideoUploadOn;
        $data->guaranteedHooks = self::whatsAppGuaranteedHooks;
        $data->ignoreOldMessages = self::whatsAppIgnoreOldMessages;
        $data->oldMessagesPeriod = self::whatsAppOldMessagesPeriod;
        $data->processArchive = self::whatsAppProcessArchive;
        $data->disableDialogsArchive = self::whatsAppDisableDialogsArchive;
        $data->parallelHooks = self::whatsAppParallelHooks;
        return $data;
    }

    /**
     * Установка настроек 
     * 
     * @return type
     */
    public function setSettings() {
        $data = self::setDataSettings();
        return \chats\whatsapp\WhatsappCurl::sendPost($data, 'settings');
    }

    /**
     * Устанавливает URL для получения webhook
     * 
     * @param type $webhookUrl
     * @return type
     */
    public function setWebhook() {
        $data = new \stdClass();
        $data->webhookUrl = self::whatsAppWebhookUrl;
        return Curl::sendPost($data, 'webhook');
    }

}
