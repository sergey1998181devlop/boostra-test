<?php

use App\Core\Application\Application;
use App\Services\ReturnExtraService;

require_once dirname(__DIR__) . '/api/Simpla.php';

header('Content-Type: application/json');

$simpla = new Simpla();
$request = $simpla->request;

// Получаем параметры
$order_id = $request->get('order_id', 'integer');
$amount = $request->get('amount', 'integer');

if (empty($order_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'order_id is required']);
    exit;
}

if (empty($amount) || $amount <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'amount is required']);
    exit;
}

$order = $simpla->orders->get_order($order_id);
if (!$order) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

$user_id = (int)$order->user_id;
$is_new_client = empty($order->have_close_credits);

try {
    $app = Application::getInstance();
    /** @var ReturnExtraService $extraService */
    $extraService = $app->make(ReturnExtraService::class);

    $extraServiceSum = 0;

    if ($user_id) {
        $visibility = $extraService->checkVisibility($user_id, $order_id);

        $fdAvailable = ($visibility['financial_doctor']['show'] ?? false) || ($visibility['financial_doctor']['enable'] ?? false);
        $tvAvailable = ($visibility['tv_medical']['show'] ?? false) || ($visibility['tv_medical']['enable'] ?? false);

        if ($fdAvailable || $user_id === 4415384) {
            $creditDoctorPrice = $extraService->getServicePrice($amount, $is_new_client, $user_id, $order_id);
            $extraServiceSum += (int)($creditDoctorPrice->price ?? 0);
        }

        if ($tvAvailable) {
            $extraServiceSum += TVMedical::ISSUANCE_AMOUNT;
        }
    }

    $response = [
        'success' => true,
        'extra_service_sum' => $extraServiceSum,
    ];

    echo json_encode($response);

} catch (Exception $e) {
    log_error('crm_extra_service_price error', [
        'error' => $e->getMessage(),
        'user_id' => $user_id,
        'amount' => $amount,
    ]);

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
