<?php

namespace Messengers\Viber;

class Config
{
    public const EVENT_TYPES = [
        'delivered',
        'seen',
        'failed',
        'subscribed',
        'unsubscribed',
        'conversation_started',
        'message'
    ];

    public const BOT_TOKEN = '4e1eb04a01a7d84d-4d3f162158138628-2a90dac758cbe3ec';
    public const MAIN_URL = 'https://chatapi.viber.com/pa';
    public const SET_WEBHOOK_URL = self::MAIN_URL . '/set_webhook';
}