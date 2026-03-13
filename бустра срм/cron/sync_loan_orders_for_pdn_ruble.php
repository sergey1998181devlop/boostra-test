<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL);

ini_set('max_execution_time', '600');

chdir('..');
define('ROOT', dirname(__DIR__));
date_default_timezone_set('Europe/Moscow');

require_once __DIR__ . '/pdn/SyncLoadOrdersPDN.php';

const TAXPAYER = '7724435898';
const LOG_FILENAME = 'sync_loan_orders_for_pdn_ruble.log';

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$syncLoadOrdersPDN = new SyncLoadOrdersPDN(
    LOG_FILENAME,
    microtime(true),
    500
);

$syncLoadOrdersPDN->runFromWork(TAXPAYER);
