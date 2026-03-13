<?php

error_reporting(0);
ini_set('display_errors', 'off');

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

session_start();
chdir('..');

require 'api/Simpla.php';
$simpla = new Simpla();
$value = $simpla->request->post('value');
$template = $simpla->sms->get_templates(['type' => 'sms-prolongation']);
$simpla->sms->update_template($template[0]->id, [
    'status' => (boolean)$value
]);
$simpla->response->json_output('success');

exit();
