<?php


error_reporting(0);
ini_set('display_errors', 'off');

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");


require '../api/Simpla.php';
chdir('..');

if (!$_POST){
    die();
}

if ($_POST["cc_ck"] !== '#fsKRv#04LzF') {
    die();
}

$simpla = new Simpla();

$phoneSearch = $_POST["phone"] ?: $_POST["call_phone_a"];

$query = $simpla->db->placehold("
            SELECT 
                 concat(lastname, ' ', firstname, ' ', patronymic) as fio
            FROM __users WHERE phone_mobile = ?",$phoneSearch);
$simpla->db->query($query);

$res = json_decode(json_encode($simpla->db->result()), true);

if ($res) {
    $simpla->response->json_output(["name" => $res["fio"]]);
}

