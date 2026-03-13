<?php

namespace Messengers\WhatsApp;

final class Config
{
    public const TOKEN = '6564d86de86af85070a6fbfca695a2ac9de6d131';
    public const PROFILE_ID = 'a3f9a3b5-1b9e';
    public const MAIN_URL = 'https://wappi.pro/api';
    public const SET_WEBHOOK_URL = self::MAIN_URL . '/webhook/url/set';
    public const SET_WEBHOOK_TYPES_URL = self::MAIN_URL . '/webhook/types/set';
    public const WEBHOOK_TYPES = [
        "authorization_status",
        "incoming_message",
        "delivery_status",
        "outgoing_message_api",
        "outgoing_message_phone"
    ];

}