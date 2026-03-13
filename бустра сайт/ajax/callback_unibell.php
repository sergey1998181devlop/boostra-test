<?php
session_start();
chdir('..');

require_once 'api/UniBell.php';

header('content-type: application/json');

$uniBell= new UniBell();
$uniBell->callBackStatus();
exit();
