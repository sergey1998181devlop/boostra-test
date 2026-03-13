<?php
session_start();
chdir('..');

require_once 'vendor/autoload.php';
require_once 'api/Simpla.php';

$simpla = new Simpla();
$simpla->yookassa_api->notificationPayments();
exit();
