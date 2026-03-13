<?php
require_once('../api/Simpla.php');
$simpla = new Simpla();

$token = $_GET['token'];

$result = array();
if ($token != 'Bstr_163_get_ip') {
    $result['error'] = 1;
} else {
    $result['insure'] = $simpla->orders->get_insure_ip();
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		
echo json_encode($result);