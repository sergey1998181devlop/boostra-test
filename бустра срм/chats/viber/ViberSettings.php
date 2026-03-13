<?php

namespace chats\viber;

use chats\main\Settings as Main;

class ViberSettings {

    const viberAvatar = Main::avatar;
    const viberWebhookUrl = protocol . baseUrl . '/chats.php?chat=viber&class=webhook&method=getWebhook';
    const viberBaseUrl = 'https://chatapi.viber.com/pa/';
    const viberBotToken = Main::viberBotToken;
    const viberBotId = Main::viberBotId;
    const viberBotName = Main::viberBotName;
    const viberUploadDir = uploadDir . 'viber' . DIRECTORY_SEPARATOR;                                          # Директория для загрузки файлов
    const viberUploadImage = self::viberUploadDir . 'image' . DIRECTORY_SEPARATOR;
    const viberUploadVoice = self::viberUploadDir . 'voice' . DIRECTORY_SEPARATOR;
    const viberUploadDocument = self::viberUploadDir . 'document' . DIRECTORY_SEPARATOR;
    const viberUploadAudio = self::viberUploadDir . 'audio' . DIRECTORY_SEPARATOR;
    const viberUploadVideo = self::viberUploadDir . 'video' . DIRECTORY_SEPARATOR;
    const viberUploadCallLog = self::viberUploadDir . 'callLog' . DIRECTORY_SEPARATOR;

}
