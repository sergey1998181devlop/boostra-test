<?php

session_start();
chdir('..');

require_once 'api/Simpla.php';

use App\Modules\NotificationCenter\Services\NotificationCenterService;
use App\Modules\ShortLink\Services\ShortLinkService;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не поддерживается'], JSON_UNESCAPED_UNICODE);
    exit;
}

$simpla = new Simpla();

$contractNumber = $simpla->request->post('contract_number', 'string');
$userId = $simpla->request->post('user_id', 'integer');
$uid = $simpla->request->post('uid', 'string');

if (empty($contractNumber)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Не указан номер договора'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($userId) || empty($uid)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Не указан пользователь'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $shortLinkService = new ShortLinkService(
        new NotificationCenterService()
    );

    $link = $shortLinkService->getFriendPaymentLink($userId, $uid, $contractNumber);

    if ($link) {
        echo json_encode(['success' => true, 'short_link' => $link], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Не удалось сгенерировать ссылку',
        'error_code' => 'FRIEND_LINK_GENERATION_FAILED',
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    logger('friend_payment')->error(
        'Ошибка get_friend_payment_link.php:' . PHP_EOL .
        'File: ' . $e->getFile() . PHP_EOL .
        'Line: ' . $e->getLine() . PHP_EOL .
        'Message: ' . $e->getMessage()
    );
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера'], JSON_UNESCAPED_UNICODE);
}