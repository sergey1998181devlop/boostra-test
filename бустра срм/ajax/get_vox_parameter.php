<?php

error_reporting(0);
ini_set('display_errors', 'Off');

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

session_start();
chdir('..');

require 'api/Simpla.php';
$simpla = new Simpla();

$response = $simpla->voximplant->getVoxParameter();

$bool = false;
if (!empty(json_decode($response)->result[0])) {
    $bool = json_decode($response)->result[0]->can_change_caller_id;
}
$simpla->response->json_output(['parameter' => $bool]);
