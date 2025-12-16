<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | Этот параметр определяет канал логирования по умолчанию, который будет
    | использоваться для записи сообщений.
    |
    */

    'default' => env('LOG_CHANNEL', 'app'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Здесь вы можете настроить каналы логирования для вашего приложения.
    | Наше приложение поддерживает драйверы: single (один файл) и daily (ротация по дням).
    |
    */

    'channels' => [

        'app' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/app/app.log',
            'level' => env('LOG_LEVEL', 'info'),
            'days' => 30,
            'permission' => 0664,
        ],

        'error' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/app/error.log',
            'level' => 'error',
            'days' => 60,
            'permission' => 0664,
        ],

        'sql' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/app/sql.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'api' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/app/api.log',
            'level' => 'info',
            'days' => 14,
            'permission' => 0664,
        ],

        'single' => [
            'driver' => 'single',
            'path' => APP_ROOT . '/logs/app/application.log',
            'level' => 'debug',
            'permission' => 0664,
        ],

        'emergency' => [
            'path' => APP_ROOT . '/logs/app/emergency.log',
        ],

        'mango' => [
            'driver' => 'single',
            'path' => APP_ROOT . '/logs/app/mango.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'referral_link' => [
            'driver' => 'single',
            'path' => APP_ROOT . '/logs/app/referral_link.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        /** Notification Center */
        'nc' => [
            'driver' => 'single',
            'path' => APP_ROOT . '/logs/app/nc.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        /** Recurrent Center */
        'rc' => [
            'driver' => 'single',
            'path' => APP_ROOT . '/logs/app/rc.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Format
    |--------------------------------------------------------------------------
    |
    | Формат записи логов.
    |
    */

    'format' => env('LOG_FORMAT', '[%datetime%] %channel%.%level_name%: %message% %context% %extra%'),
    'date_format' => 'Y-m-d H:i:s',

    /*
    |--------------------------------------------------------------------------
    | Log Deprecations
    |--------------------------------------------------------------------------
    |
    | Этот параметр контролирует логирование предупреждений об устаревших функциях.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'app'),
        'trace' => false,
    ],
];
