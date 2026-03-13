<?php

require_once('../api/Simpla.php');
require_once '../api/Documents.php';

$simpla = new Simpla();
$response = array();

if ($simpla->request->get('action') == 'download_zip') {
    $user_id = (int)$simpla->request->get('user_id');

    if (!$user_id) {
        echo json_encode([
            'error' => 'User ID is missing',
            'code' => 400
        ]);
        return;
    }

    $download = new Documents();
    $download->download_zip($user_id);
}
