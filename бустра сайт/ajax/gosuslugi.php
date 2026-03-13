<?php

sleep(1);

$response['error'] = '<div class="error">Функция временно недоступна. <br />Пожалуйста авторизуйтесь по номеру телефона.</div>';

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		

echo json_encode($response);