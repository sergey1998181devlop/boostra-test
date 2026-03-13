<?php

namespace chats\main;

class Settings {

    const avatar = 'design/boostra_mini_norm/img/favicon192x192.png';

    # Главные настройки WhatsApp
    const whatsAppToken = '2hq8t1yzdc3eufvz';       # Токен 
    const whatsAppInstansNumber = '343779';         # Инстанс номер
    const whatsAppPhoneNumber = '79024288041';
    # Дополнительные настройки 
    const whatsAppSendDelay = 0;                    #
    const whatsAppInstanceStatuses = true;          #
    const whatsAppWebhookStatuses = true;           #
    const whatsAppStatusNotificationsOn = true;     #
    const whatsAppAckNotificationsOn = true;        #
    const whatsAppChatUpdateOn = true;              #
    const whatsAppVideoUploadOn = true;             #
    const whatsAppGuaranteedHooks = true;           #
    const whatsAppIgnoreOldMessages = false;        #
    const whatsAppOldMessagesPeriod = 600;          #
    const whatsAppProcessArchive = true;            #
    const whatsAppDisableDialogsArchive = false;    #
    const whatsAppParallelHooks = true;             #
    const whatsAppUploadDir = '';

    # Настройки Манго Офис
    const mangoApiKey = 'wggj3754fqg68vdw46pehcf7lmz5es41';     # Уникальный код вашей АТС
    const mangoApiSalt = 'ybas86mdmtaled79andiqig1u5xznrqc';    # Ключ для создания подписи
    
    # Настройки Viber
    const viberBotToken = '4e1eb04a01a7d84d-4d3f162158138628-2a90dac758cbe3ec';
    const viberBotId = 'aEKuXV4S2klTBKtN4x3DhQ==';
    const viberBotName = 'boostrarubot';

}
