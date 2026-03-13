<?php

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

chdir('..');

require_once 'api/Simpla.php';

$simpla = new Simpla();
$result = [];

switch ($simpla->request->get('action', 'string')):
    case 'update':
        $key = $simpla->request->post('key');
        $value = $simpla->request->post('value');

        $simpla->settings->{$key} = $value;
        $result['success'] = true;
        break;
    default:
        $result['error'] = 'undefined_action';
endswitch;

$simpla->response->json_output($result);
