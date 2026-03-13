<?php

session_start();
chdir('..');

require_once dirname(__FILE__).'/../api/Simpla.php';
$simpla = new Simpla();
date_default_timezone_set('Europe/Moscow');


$simpla->users->deleteTgHash(date('Y-m-d H:i:s', strtotime('-1 hour')));