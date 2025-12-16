<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
chdir('..');

require_once 'api/Simpla.php';

$simpla = new Simpla();
$response = [];

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    $response['error'] = 'Пользователь не авторизован';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$action = $simpla->request->post('action');

if ($action === 'cancel_cooling_off') {
    // Get user's current order with status 8 (cooling-off period)
    $order = $simpla->orders->get_last_order_by_status($user_id, $simpla->orders::STATUS_COOLING);
    
    if (!empty($order) && $order->status == $simpla->orders::STATUS_COOLING) {
        // Update order status to STATUS_REJECTED (3)
        $update_result = $simpla->orders->update_order($order->id, [
            'status' => $simpla->orders::STATUS_REJECTED,
            'reason_id' => $simpla->reasons::REASON_COOLING_REJECT
        ]);
        
        $cool_date = $simpla->order_data->get($order->order_id, $simpla->order_data::HOURS_IN_COOLING);
        if (!empty($cool_date)) {
            $hours_in_cooling_period = time() - strtotime($cool_date->updated) / 3600;
            $simpla->order_data->set($order->order_id, $simpla->order_data::HOURS_IN_COOLING, $hours_in_cooling_period);
        }
        
        if ($update_result) {
            $response['success'] = true;
            $response['message'] = 'Заявка успешно отменена';
        } else {
            $response['error'] = 'Не удалось обновить статус заявки';
        }
    } else {
        $response['error'] = 'Заявка не найдена или не находится в периоде охлаждения';
    }
} else {
    $response['error'] = 'Неверное действие';
}

header('Content-Type: application/json');
echo json_encode($response);
?>