<?php

namespace ADDONS\DOP;

/**
 * Класс для работы с API Финансового доктора
 */
class ConciergeApi extends DOPApi
{
    protected function domains(): array
    {
        return [
            'https://api.b-concierge.online',
            'https://api.stageback.ru',
        ];
    }

    protected function apiKey(): string
    {
        return trim($this->settings->apikeys['multipolis']['license_key']);
    }
}

