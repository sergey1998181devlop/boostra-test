<?php

return [
    /**
     * Настройки Redis для кэширования
     */
    'redis' => [
        'host' => env('REDIS_HOST', 'redis'),
        'port' => (int)env('REDIS_PORT', 6379),
        'database' => (int)env('REDIS_CACHE_DB', 0),
        'timeout' => (float)env('REDIS_TIMEOUT', 2.0),
    ],

    /**
     * TTL (время жизни) для различных типов кэша в секундах
     */
    'ttl' => [
        'default' => 600, // 10 минут
    ],
];
