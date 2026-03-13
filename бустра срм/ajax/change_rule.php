<?php

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

chdir('..');

require_once 'api/Simpla.php';
require_once 'api/Managers.php';
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);
$managers = new Managers();
$manager = $managers->get_manager($data->id);

if (in_array($data->role, array_keys($managers->get_roles()))) {
    $managers->update_manager($data->id, ['role' => $data->role]);
}

$manager = $managers->get_manager($data->id);
$json = json_encode(['role' => $manager->role]);
$simpla = new Simpla();
$simpla->response->json_output($json);
