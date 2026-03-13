<?php

$serviceConfig = parse_ini_file(APP_ROOT . '/config/config.php');

return [
    'app' => [
        'back_url' => $serviceConfig['back_url'] ?? '',
    ],
    'record_storage' => [
        'url' => env('RECORD_STORAGE_URL', 'https://storage.yandexcloud.net'),
        'region' => env('RECORD_STORAGE_REGION', 'ru-central1'),
        'access_key' => env('RECORD_STORAGE_ACCESS_KEY', ''),
        'secret_key' => env('RECORD_STORAGE_SECRET_KEY', ''),
        'call_bucket' => env('RECORD_STORAGE_CALL_BUCKET', ''),
    ],
    'user_ticket_storage' => [
        'url' => env('USER_TICKET_STORAGE_URL', 'https://storage.yandexcloud.net'),
        'region' => env('USER_TICKET_STORAGE_REGION', 'ru-central1'),
        'access_key' => env('USER_TICKET_STORAGE_ACCESS_KEY', ''),
        'secret_key' => env('USER_TICKET_STORAGE_SECRET_KEY', ''),
        'bucket' => env('USER_TICKET_STORAGE_BUCKET', ''),
    ],
    '1c' => [
        'db' => $serviceConfig['work_1c_db'] ?? '',
        'url' => $serviceConfig['url_1c'] ?? '',
    ],
    'suvvy' => [
        'manager_id' => $serviceConfig['suvvy_manager_id'] ?? null
    ],
    'telegram' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'notifications_chat_id' => env('TELEGRAM_NOTIFICATIONS_CHAT_ID', '-1002459695515'),
        'complaints_calls_thread_id' => env('TELEGRAM_COMPLAINTS_CALLS_THREAD_ID', 236),
        'complaints_tickets_thread_id' => env('TELEGRAM_COMPLAINTS_TICKETS_THREAD_ID', 1074),
        'status_site_work_thread_id' => env('TELEGRAM_STATUS_SITE_WORK_THREAD_ID', 1260),
        'negative_feedback_thread_id' => env('TELEGRAM_NEGATIVE_FEEDBACK_THREAD_ID', 4372),
        'refund_stats_thread_id' => env('TELEGRAM_REFUND_STATS_THREAD_ID', 44562),
        'ai_bot_thread_id' => env('TELEGRAM_AI_BOT_THREAD_ID', 47821),
        'highlighted_tickets_thread_id' => env('TELEGRAM_HIGHLIGHTED_TICKETS_THREAD_ID'),
    ],
    'obuchat' => [
        'api_url' => env('OBUCHAT_API_URL'),
        'webhook_incoming_record_rating' => env('OBUCHAT_INCOMING_RECORD_RATING_WEBHOOK_URI'),
        'webhook_outgoing_record_rating' => env('OBUCHAT_OUTGOING_RECORD_RATING_WEBHOOK_URI'),
    ],
    'usedesk' => [
        'api_url' => env('USEDESK_API_URL'),
        'api_secret_key' => env('USEDESK_API_SECRET_KEY'),
        'complaint_ticket_app_id' => env('USEDESK_COMPLAINT_TICKET_APP_ID'),
        'complaint_ticket_secret_key' => env('USEDESK_COMPLAINT_TICKET_SECRET_KEY'),
        'negative_feedback_secret_key' => env('USEDESK_NEGATIVE_FEEDBACK_SECRET_KEY'),
        'user_ticket_app_id' => env('USEDESK_USER_TICKET_APP_ID'),
        'user_ticket_secret_key' => env('USEDESK_USER_TICKET_SECRET_KEY'),
    ],
    's3' => [
        'endpoint' => env('S3_DOCS_ENDPOINT', 'https://storage.yandexcloud.net'),
        'region'   => env('S3_DOCS_REGION', 'ru-central1'),
        'key'      => env('S3_DOCS_KEY', ''),
        'secret'   => env('S3_DOCS_SECRET', ''),
        'bucket'   => env('S3_DOCS_BUCKET', ''),
    ],
    'voximplant' => [
        'api_url_v3' => env('VOXIMPLANT_API_URL_V3', 'https://kitapi-ru.voximplant.com/api/v3'),
        'api_url_v4' => env('VOXIMPLANT_API_URL_V4', 'https://kitapi-ru.voximplant.com/api/v4'),
        'domain' => env('VOXIMPLANT_DOMAIN', 'boostra2023'),
        'token' => env('VOXIMPLANT_TOKEN', ''),

        // legacy keys, kept for backward compatibility
        'domain_rzs' => env('VOXIMPLANT_DOMAIN_RZS'),
        'token_rzs' => env('VOXIMPLANT_TOKEN_RZS'),
        'domain_lord' => env('VOXIMPLANT_DOMAIN_LORD'),
        'token_lord' => env('VOXIMPLANT_TOKEN_LORD'),
        'domain_moredeneg' => env('VOXIMPLANT_DOMAIN_MOREDENEG'),
        'token_moredeneg' => env('VOXIMPLANT_TOKEN_MOREDENEG'),
        'domain_rubl' => env('VOXIMPLANT_DOMAIN_RUBL'),
        'token_rubl' => env('VOXIMPLANT_TOKEN_RUBL'),

        'organizations' => [
            13 => [
                'code' => 'RZS',
                'label' => 'РЗС',
                'vox' => [
                    'domain' => env('VOXIMPLANT_DOMAIN_RZS', env('VOXIMPLANT_DOMAIN', 'boostra2023')),
                    'token' => env('VOXIMPLANT_TOKEN_RZS', env('VOXIMPLANT_TOKEN', '')),
                    'campaigns' => [
                        'default' => env('VOXIMPLANT_CAMPAIGN_RZS'),
                        'callback' => env('VOXIMPLANT_CAMPAIGN_RZS_CALLBACK', '68293'),
                    ],
                ],
            ],
            15 => [
                'code' => 'LORD',
                'label' => 'Лорд',
                'vox' => [
                    'domain' => env('VOXIMPLANT_DOMAIN_LORD', env('VOXIMPLANT_DOMAIN', 'boostra2023')),
                    'token' => env('VOXIMPLANT_TOKEN_LORD', env('VOXIMPLANT_TOKEN', '')),
                    'campaigns' => [
                        'default' => env('VOXIMPLANT_CAMPAIGN_LORD'),
                        'callback' => env('VOXIMPLANT_CAMPAIGN_LORD_CALLBACK', '68136'),
                    ],
                ],
            ],
            17 => [
                'code' => 'MOREDENEG',
                'label' => 'МореДенег',
                'vox' => [
                    'domain' => env('VOXIMPLANT_DOMAIN_MOREDENEG'),
                    'token' => env('VOXIMPLANT_TOKEN_MOREDENEG'),
                    'campaigns' => [
                        'default' => env('VOXIMPLANT_CAMPAIGN_MOREDENEG'),
                        'callback' => env('VOXIMPLANT_CAMPAIGN_MOREDENEG_CALLBACK'),
                    ],
                ]
            ],
            21 => [
                'code' => 'RUBL',
                'label' => 'Рубль.Ру',
                'vox' => [
                    'domain' => env('VOXIMPLANT_DOMAIN_RUBL'),
                    'token' => env('VOXIMPLANT_TOKEN_RUBL'),
                    'campaigns' => [
                        'default' => env('VOXIMPLANT_CAMPAIGN_RUBL'),
                        'callback' => env('VOXIMPLANT_CAMPAIGN_RUBL_CALLBACK'),
                    ],
                ]
            ],
        ],
        'default_organization_id' => env('VOXIMPLANT_DEFAULT_ORGANIZATION_ID', 13),
    ],
    'push' => [
        'api_url' => env('PUSH_API_URL'),
        'api_key' => env('PUSH_API_KEY'),
    ],
    'smsc' => [
        'api_url' => env('SMSC_API_URL', 'https://smsc.ru/sys/send.php'),
        'login' => env('SMSC_LOGIN', ''),
        'password' => env('SMSC_PASSWORD', ''),
        'sender' => env('SMSC_SENDER', '')
    ],
    'neuro' => [
        'ext_user' => env('NEURO_EXT_USER', ''),
        'ext_pass' => env('NEURO_EXT_PASS', ''),
        'api' => [
            'auth_url' => env('NEURO_AUTH_URL', 'https://api-v3.neuro.net/api/v2/ext/auth'),
            'cms_stream_base' => env('NEURO_CMS_STREAM_BASE', 'https://cms-v3.neuro.net/api/v2/log/call/stream'),
        ]
    ],
    'recurrent_center' => [
        'url_prod' => env('RECURRENT_CENTER_URL_PROD', ''),
        'url_dev' => env('RECURRENT_CENTER_URL_DEV', ''),
        'api_token' => env('RECURRENT_CENTER_TOKEN', ''),
    ],
    'tinkoff_tqm' => [
        'api_url' => env('TINKOFF_TQM_API_URL', 'https://tqm-cloud.tbank.ru'),
        'api_key' => env('TINKOFF_TQM_API_KEY', ''),
        'api_version' => env('TINKOFF_TQM_API_VERSION', '1.0'),
    ],
];
