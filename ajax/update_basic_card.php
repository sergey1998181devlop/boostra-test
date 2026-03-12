<?php

error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
chdir('..');

require_once 'api/Simpla.php';

$simpla = new Simpla();

$card_id = $simpla->request->post('card_id');
$user_id = $simpla->request->post('user_id');

$simpla->users->updateBasicCard($user_id,$card_id);

echo "success";