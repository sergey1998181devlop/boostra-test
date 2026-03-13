<?php

return [
    'extra_service' => [
        'return_tables' => [
            'financial_doctor' => '__credit_doctor_to_user',
            'star_oracle'      => '__star_oracle',
        ],
        'return_status'         => 'SUCCESS',
        'return_threshold_days' => [
            'both'             => 10,
            'financial_doctor' => 30,
            'star_oracle'      => 30,
        ],
    ],

    'recurrent_center' => [
        'url_prod' => env('RECURRENT_CENTER_URL_PROD', ''),
        'url_dev' => env('RECURRENT_CENTER_URL_DEV', ''),
        'api_token' => env('RECURRENT_CENTER_TOKEN', ''),
    ],

    'notification_center' => [
        'url_prod' => env('NOTIFICATION_CENTER_URL_PROD', ''),
        'url_dev' => env('NOTIFICATION_CENTER_URL_DEV', ''),
        'api_token' => env('NOTIFICATION_CENTER_TOKEN', ''),
    ],

    'srkv' => [
        'cache_ttl'               => 2592000, // 30 дней (временно, пока без крона)
        'min_conversion_issuance' => 23.5,
        'min_conversion_payment'  => 20.3,
        'min_return_pct'          => 4.7,
        'coefficient_thresholds'  => [
            'no_sale'  => 0.4,
            'discount' => 0.1,
        ],
    ],
];
