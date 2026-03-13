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
$phone_mobile = $simpla->request->post('phone');
$task_id  = $simpla->tasks->update_task_mark($phone_mobile);
$simpla->response->json_output(['id' => $task_id]);
?>
