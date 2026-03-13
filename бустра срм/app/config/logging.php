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
            'path' => APP_ROOT . '/logs/app.log',
            'level' => env('LOG_LEVEL', 'info'),
            'days' => 30,
            'permission' => 0664,
        ],

        'error' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/error.log',
            'level' => 'error',
            'days' => 60,
            'permission' => 0664,
        ],

        'sql' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/sql.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'api' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/api.log',
            'level' => 'info',
            'days' => 14,
            'permission' => 0664,
        ],

        'single' => [
            'driver' => 'single',
            'path' => APP_ROOT . '/logs/application.log',
            'level' => 'debug',
            'permission' => 0664,
        ],

        'emergency' => [
            'path' => APP_ROOT . '/logs/emergency.log',
        ],

        'mango' => [
            'driver' => 'single',
            'path' => APP_ROOT . '/logs/mango.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'rc' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/rc.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'autodebit_change' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/autodebit_change.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'delete_card' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/delete_card.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'delete_sbp' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/delete_sbp.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'cache' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/cache.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        's3' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/s3.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'obuchat' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/obuchat.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'tqm' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/tqm.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'mango_cron' => [
            'driver' => 'daily',
            'path' => APP_ROOT . '/logs/mango_cron.log',
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'comment' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/comment.log',
            'level'      => 'debug',
            'days'       => 7,
            'permission' => 0664,
        ],

        'comment_1c' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/comment_1c.log',
            'level'      => 'debug',
            'days'       => 7,
            'permission' => 0664,
        ],

        'voximplant' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/voximplant.log',
            'level'      => 'debug',
            'days'       => 7,
            'permission' => 0664,
        ],

        'cc_task' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/cc_task.log',
            'level'      => 'debug',
            'days'       => 30,
            'permission' => 0664,
        ],

        'cc_task_schedule' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/cc_task_schedule.log',
            'level'      => 'debug',
            'days'       => 30,
            'permission' => 0664,
        ],

        'user_balance_import' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/user_balance_import.log',
            'level'      => 'debug',
            'days'       => 30,
            'permission' => 0664,
        ],

        'voximplant_campaign' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/voximplant_campaign.log',
            'level'      => 'debug',
            'days'       => 30,
            'permission' => 0664,
        ],

        'voximplant_dnc' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/voximplant_dnc.log',
            'level'      => 'debug',
            'days'       => 30,
            'permission' => 0664,
        ],

        'voximplant_api' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/voximplant_api.log',
            'level'      => 'debug',
            'days'       => 30,
            'permission' => 0664,
        ],

        'voximplant_manager' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/voximplant_manager.log',
            'level'      => 'debug',
            'days'       => 30,
            'permission' => 0664,
        ],

        'voximplant_missed_calls' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/voximplant_missed_calls.log',
            'level'      => 'debug',
            'days'       => 30,
            'permission' => 0664,
        ],

        'voximplant_send_mkk' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/voximplant_send_mkk.log',
            'level'      => 'debug',
            'days'       => 30,
            'permission' => 0664,
        ],

        'manager_schedule' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/manager_schedule.log',
            'level'      => 'debug',
            'days'       => 30,
            'permission' => 0664,
        ],

        'call_list' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/call_list.log',
            'level'      => 'debug',
            'days'       => 30,
            'permission' => 0664,
        ],

        'sms' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/sms.log',
            'level'      => 'warning',
            'days'       => 7,
            'permission' => 0664,
        ],

        'mindbox' => [
            'driver'     => 'daily',
            'path'       => APP_ROOT . '/logs/mindbox.log',
            'level'      => 'info',
            'days'       => 14,
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
