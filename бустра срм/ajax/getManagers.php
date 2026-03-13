<?php

session_start();
chdir('..');

require 'api/Simpla.php';

$response = array();
$simpla = new Simpla();

$managers = new Managers();
$date = date("Y-m-d");

$result = $managers->getSelectedManagers($date);

$simpla->response->json_output($result);

echo 'success';
