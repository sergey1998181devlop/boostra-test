<?php

session_start();
chdir('..');

require 'api/Simpla.php';


$simpla = new Simpla();
$startTime = date("H:i:s");
file_put_contents('voximplant/timezone.txt', "start : $startTime \n", FILE_APPEND);

$user = new Users();

$simpla->users->addTimezone();
$endTime = date("H:i:s");
file_put_contents('voximplant/timezone.txt', "end : $endTime \n", FILE_APPEND);
exit();