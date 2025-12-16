<?php

namespace Messengers\Telegram;

class Config
{

    public const BOT_TOKEN = '2019092920:AAGzSZRAlzSCA3795hamVoOEhYGijoMC0_Y';
    public const MAIN_URL = 'https://api.telegram.org/bot' . self::BOT_TOKEN;
    public const GET_FILE_URL = 'https://api.telegram.org/file/bot' . self::BOT_TOKEN;
    public const SET_WEBHOOK_URL = self::MAIN_URL . '/setWebhook';
}