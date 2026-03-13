<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/Simpla.php';

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$best2payActions = ['add_card', 'cards'];

if (in_array($action, $best2payActions)) {
    require_once 'best2pay.php';
    exit();
}

$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!$csrfHeader || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfHeader)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$orderId = $_SESSION['order_id'] ?? 0;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$simpla = new Simpla();
$result = $simpla->virtualCard->handleAjax((int)$userId, (int)$orderId);

header('Content-Type: application/json');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
