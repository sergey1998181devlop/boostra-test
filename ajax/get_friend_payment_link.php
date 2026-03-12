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

$userId      = $simpla->request->post('user_id', 'integer');
$uid         = $simpla->request->post('uid', 'string');
$overdueDays = $simpla->request->post('overdue_days', 'integer');
$phone       = $simpla->request->post('phone', 'string');
$order_id    = $simpla->request->post('order_id', 'integer');

if (empty($userId) || empty($uid)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Не указан пользователь'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($overdueDays === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Не указано количество дней просрочки'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Не указан номер телефона'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($order_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Не указан order_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $shortLinkService = new ShortLinkService(
        new NotificationCenterService()
    );

    $shortLink = $shortLinkService->getFriendPaymentLink(
        $userId,
        $uid,
        $overdueDays,
        $phone,
        $order_id
    );

    echo json_encode([
        'success' => true,
        'short_link' => $shortLink
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    logger('friend_payment')->error($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}