<?php

header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");
define('ROOT', dirname(__DIR__));

session_start();
chdir('..');

require 'api/Simpla.php';

$simpla = new Simpla();

$zaimNumber  = $simpla->request->get('data');

$simpla->users->deleteAspZaim($zaimNumber);
unlink(ROOT."/files/asp/asp_zaim_".$zaimNumber.'pdf');

$simpla->response->json_output(['result' => 'success']);