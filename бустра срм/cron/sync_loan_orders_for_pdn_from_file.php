<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL);

ini_set('max_execution_time', '10800');
ini_set('memory_limit', '256M'); // некоторые отчеты имеют большой объем, из-за чего 500 ошибка по памяти

chdir('..');
define('ROOT', dirname(__DIR__));
date_default_timezone_set('Europe/Moscow');

require_once __DIR__ . '/pdn/SyncLoadOrdersPDN.php';

const LOG_FILENAME = 'sync_loan_orders_for_pdn_fron_file.txt';

const ORDERS_PATH = ROOT . '/files/pdn_calculation/additional_orders.txt';

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$syncLoadOrdersPDN = new SyncLoadOrdersPDN(
    LOG_FILENAME,
    microtime(true),
    10500
);

$syncLoadOrdersPDN->runFromFile(ORDERS_PATH);
