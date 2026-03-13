<?php

/**
 * Конфигурация Phinx
 */

// Подключаем существующую систему конфигурации
require_once('./api/Simpla.php');
$simpla = new Simpla();
$config = $simpla->config;

// Путь к CA-сертификату (аналогично Database.php)
$cert_path = realpath(__DIR__ . '/config/root.crt');
$is_dev = $config->is_dev;

// Базовая конфигурация подключения
$connection_config = [
    'adapter' => 'mysql',
    'host' => $config->db_server,
    'name' => $config->db_name,
    'user' => $config->db_user,
    'pass' => $config->db_password,
    'port' => $config->db_port,
    'charset' => $config->db_charset,
    'collation' => 'utf8mb4_unicode_ci',
];

// Добавляем SSL только для production (когда is_dev не установлен)
if (empty($is_dev) && file_exists($cert_path)) {
    $connection_config['mysql_attr_ssl_ca'] = $cert_path;
    $connection_config['mysql_attr_ssl_verify_server_cert'] = true;
}

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_environment' => 'default',
        'default' => $connection_config,
    ],
    'version_order' => 'creation',
];