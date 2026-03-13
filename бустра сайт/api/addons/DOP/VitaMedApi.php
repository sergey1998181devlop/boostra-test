<?php

namespace ADDONS\DOP;

/**
 * Класс для работы с API Финансового доктора
 */
class VitaMedApi extends DOPApi
{
    protected function domains(): array
    {
        return [
            'https://api.cashmed.ru',
            'https://api.stageback.ru',
        ];
    }

    protected function apiKey(): string
    {
        return trim($this->settings->apikeys['vitamed']['license_key']);
    }
}
