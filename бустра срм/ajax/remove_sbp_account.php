<?php

use App\Modules\RecurrentsCenter\Services\RecurrentCenterService;

session_start();
chdir('..');

require_once 'api/Simpla.php';
require_once 'app/Core/Helpers/BaseHelper.php';
require_once 'app/Core/Application/Application.php';

try {
    $simpla = new Simpla();
    $rcService = new RecurrentCenterService();

    if (!isset($_SESSION['manager_id'])) {
        $simpla->request->json_output(['error' => 'auth_error']);
    }

    if (!$managerId = $simpla->managers->get_manager(intval($_SESSION['manager_id']))) {
        $simpla->request->json_output(['error' => 'auth_error']);
    }

    if (!$sbpAccountToken = $simpla->request->post('sbp_account_token')) {
        $simpla->request->json_output(['error' => 'sbp_token_not_found']);
    }

    if (!$userId = $simpla->request->post('user_id')) {
        $simpla->request->json_output(['error' => 'sbp_user_id_not_found']);
    }

    if (!$user = $simpla->users->get_user((int)$userId)) {
        $simpla->request->json_output(['error' => 'sbp_user_not_found']);
    }

    $orders = $simpla->orders->get_orders(['user_id' => $user->id]);
    $balance = $simpla->users->get_user_balance($user->id);
    $params = [
        ['token', '=', $sbpAccountToken],
        ['user_id', '=', $userId]
    ];

    if (!$sbpAccount = $simpla->sbpAccount->first($params)) {
        $simpla->request->json_output(['error' => 'sbp_not_found']);
    }

    $isSbpAccountBusy = false;
    foreach ($orders as $order) {
        if (
            !$order->status_1c
            || in_array($order->status_1c, $simpla->orders::IN_PROGRESS_STATUSES)
            || (
                $order->status_1c == Orders::ORDER_1C_STATUS_ISSUED
                && $order->id_1c == $balance->zayavka
                && $balance->ostatok_od + $balance->ostatok_percents + $balance->ostatok_peni > 0
            )
        ) {
            $isSbpAccountBusy = true;
        }
    }

    if ($isSbpAccountBusy) {
        $simpla->request->json_output(['error' => 'sbp_blocked']);
    }

    $simpla->sbpAccount->updateSbpAccount($sbpAccount->id, ['deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')]);
    $uid = $simpla->users->getUserUidById($user->id);

    $data = [
        'token' => $sbpAccount->token,
        'deleted' => 1,
        'client_uid' => $uid
    ];

    $rcService->deleteSbpToken((string)$sbpAccount->token, (string)$uid);

    $comment = [
        'manager_id' => $managerId,
        'user_id' => $userId,
        'block' => 'collection',
        'text' => 'Удалил счёт СБП ' . $sbpAccount->token,
        'created' => date('Y-m-d H:i:s'),
    ];

    $simpla->comments->add_comment($comment);

    $simpla->best2pay->add_sbp_log([
        'card_id' => $sbpAccount->id,
        'action' =>  $simpla->orders::CARD_ACTIONS['DELETE_SBP_MANAGER'],
        'date' => date('Y-m-d H:i:s')
    ]);

    $simpla->request->json_output(['result' => 'success']);
} catch (Throwable $e) {
    logger('delete_sbp')->error(
        __METHOD__ . PHP_EOL
        . $e->getFile() . PHP_EOL
        . $e->getLine() . PHP_EOL
        . $e->getMessage() . PHP_EOL
    );

    $simpla->request->json_output(['error' => 'server error']);
}
