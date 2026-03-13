<?php
error_reporting(0);
ini_set('display_errors', 'Off');
date_default_timezone_set('Europe/Moscow');

header('Cache-Control: must-revalidate');
header('Pragma: no-cache');
header('Expires: -1');

session_start();
chdir('..');

require 'api/Simpla.php';

$simpla = new Simpla();
$ourToken = 'TRVUSgTGZ4ml1MwccfoZhKiauhUFghPbbV93qeDAIQI82ekXPjgwcgePvT42e5wE';
$phone = $simpla->request->get('phone');
$token = $simpla->request->get('token');
if ($token == $ourToken) {
    $utc = $simpla->users->getTimezoneByPhone($phone);
    if (empty($utc)) {
        $utc = '+03:00';
    }

    $simpla->response->json_output(['timezone' => $utc]);
}else{
    $simpla->response->json_output(['error' => 'incorrect token']);
}


?>
