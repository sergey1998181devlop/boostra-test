<?php

require_once 'api/Simpla.php';

$simpla = new Simpla;

$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$query = parse_url($url, PHP_URL_QUERY);

$parsedData = parse_str($query);

$api_code = $simpla->settings->api_password;
if ($code == $api_code) {
    $resp = $simpla->soap->get_uid_by_phone($phone);

    echo json_encode($resp);
} else {
    var_dump($phone);
}
