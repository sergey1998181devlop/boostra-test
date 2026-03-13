<?php

$dbConfig = parse_ini_file(APP_ROOT.'/config/config.php');

return [
    /**
     * Default Database Connection Name.
     */
    'default'     => env('DB_CONNECTION', 'mysql'),

    /**
     * Define multiple database Configurations.
     */
    'connections' => [
        'mysql' => [
            'driver'   => 'mysql',
            'host'     => $dbConfig['db_server'],
            'port'     => $dbConfig['db_port'],
            'database' => $dbConfig['db_name'],
            'username' => $dbConfig['db_user'],
            'password' => $dbConfig['db_password'],
            'error'    => env('DB_ERROR', 'PDO::ERRMODE_EXCEPTION'),
            'charset' => 'utf8mb4',
            'cert'     => env('DB_CERT', '/config/root.crt'),
        ],
        'archive' => [
            'driver'   => 'mysql',
            'host'     => $dbConfig['db_archive_server'] ?? '',
            'port'     => $dbConfig['db_archive_port'] ?? '3306',
            'database' => $dbConfig['db_archive_name'] ?? '',
            'username' => $dbConfig['db_archive_user'] ?? '',
            'password' => $dbConfig['db_archive_password'] ?? '',
            'error'    => env('DB_ERROR', 'PDO::ERRMODE_EXCEPTION'),
            'charset'  => 'utf8mb4',
            'cert'     => env('DB_CERT', '/config/root.crt'),
        ],
    ],
];
