<?php

namespace ADDONS\DOP;

/**
 * Класс для работы с API Финансового доктора
 */
class StarOracleApi extends DOPApi
{
    protected function domains(): array
    {
        return [
            'https://api.staroracle.ru',
            'https://api.stageback.ru',
        ];
    }

    protected function apiKey(): string
    {
        return trim($this->settings->apikeys['star_oracle']['license_key']);
    }
}

