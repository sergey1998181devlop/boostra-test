<?php

error_reporting(8);
ini_set('display_errors', 'On');
date_default_timezone_set('Europe/Moscow');

header('Cache-Control: must-revalidate');
header('Pragma: no-cache');
header('Expires: -1');
define('ROOT', dirname(__DIR__));

session_start();
chdir('..');

require '../api/Simpla.php';

$simpla = new Simpla();
$documentId = (int)$_GET['document_id'];

if (isset($_GET['client_visible'])) {
    $document = $simpla->documents->get_document($documentId);
    $newValue = $document->client_visible == 1 ? 0 : 1;
    $simpla->documents->update_document($documentId, [
        'client_visible' => $newValue
    ]);
    $simpla->response->json_output(['success' => 'success', 'client_visible' => $newValue]);
}
