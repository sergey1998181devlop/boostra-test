<?php

return [
    'profiles' => [
        'call_records' => [
            'url' => env('NEURO_RECORD_STORAGE_URL', 'https://storage.yandexcloud.net'),
            'region' => env('NEURO_RECORD_STORAGE_REGION', 'ru-central1'),
            'access_key' => env('NEURO_RECORD_STORAGE_ACCESS_KEY', ''),
            'secret_key' => env('NEURO_RECORD_STORAGE_SECRET_KEY', ''),
            'bucket' => env('NEURO_RECORD_STORAGE_BUCKET', ''),
        ],
    ],
];