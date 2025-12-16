<?php

chdir('..');
require_once 'api/Simpla.php';
require_once 'api/PromoEvents.php';

header('Content-Type: application/json; charset=utf-8');

$simpla = new Simpla();
$promo  = new PromoEvents();

$action = $simpla->request->post('action', 'string');

$allowedActions = [
    'page_open',
    'open_promo_modal',
    'friend_pay_click',
    'friend_pay_copy'
];

if (!$action || !in_array($action, $allowedActions, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'bad_action']);
    exit;
}

$userId = 0;
if (!empty($simpla->user) && !empty($simpla->user->id)) {
    $userId = (int)$simpla->user->id;
} else {
    $userId = (int)$simpla->request->post('user_id', 'integer');
}

if (in_array($action, ['friend_pay_click', 'friend_pay_copy'], true)) {
    $overdueDays = $simpla->request->post('overdue_days', 'string');
    $action = $action . ':' . $overdueDays;
}

try {
    $id = $promo->saveEvent($userId, $action);
    echo json_encode(['success' => true, 'id' => (int)$id]);
} catch (Exception $e) {
    http_response_code(500);
    throw new Exception(
        'Ошибка promo_logger.php:' . PHP_EOL .
        'File: ' . $e->getFile() . PHP_EOL .
        'Line: ' . $e->getLine() . PHP_EOL .
        'Message: ' . $e->getMessage()
    );
}