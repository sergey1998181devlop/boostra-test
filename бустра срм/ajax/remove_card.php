<?php

use App\Modules\Card\Services\CardService;
use App\Modules\RecurrentsCenter\Services\RecurrentCenterService;

error_reporting(0);
ini_set('display_errors', 0);

session_start();
chdir('..');

require_once 'api/Simpla.php';
require_once 'app/Core/Helpers/BaseHelper.php';

try {
    $simpla = new Simpla();
    $cardId = $simpla->request->post('card_id');
    $userId = $simpla->request->post('user_id');
    $managerId = $simpla->request->post('manager_id');
    $rcService = (new RecurrentCenterService());

    if (!$cardId) {
        $simpla->request->json_output(['error' => 'card_not_found']);
    }

    $user = $simpla->users->get_user((int)$userId);
    if (!$user) {
        $simpla->request->json_output(['error' => 'first_card_blocked']);
    }

    $count_cards = $simpla->best2pay->count_cards([
        'user_id' => $userId,
    ]);

    if ($count_cards <= 1) {
        $simpla->request->json_output(['error' => 'card_blocked']);
    }

    $orders = $simpla->orders->get_orders(['user_id' => $user->id]);
    $balance = $simpla->users->get_user_balance($user->id);
    $busy_cards = [];
    foreach ($orders as $order) {
        if (!$order->status_1c
            || in_array($order->status_1c, $simpla->orders::IN_PROGRESS_STATUSES)
            || ($order->status_1c == Orders::ORDER_1C_STATUS_ISSUED
                && $order->id_1c == $balance->zayavka
                && $balance->ostatok_od + $balance->ostatok_percents + $balance->ostatok_peni > 0)) {
            $busy_cards[$order->card_id] = true;
        }
    }

    if (!empty($busy_cards[$cardId])) {
        $simpla->request->json_output(['error' => 'card_busy']);
    }

    $card = $simpla->best2pay->get_card($cardId);
    if (!$card) {
        $simpla->request->json_output(['error' => 'card_not_found']);
    }

    $simpla->best2pay->update_cards((string)$userId, (string)$card->pan, (string)$card->expdate, $card->organization_id, ['deleted' => 1, 'deleted_date' => date('Y-m-d H:i:s')]);
    $uid = $simpla->users->getUserUidById($user->id);

    $rcService->sendRequest([
        'pan' => $card->pan,
        'token' => $card->token,
        'autodebit' => 0,
        'client_uid' => $uid,
    ], $rcService->getUrl() . CardService::DELETE_API_URL);

    $comment = [
        'manager_id' => $managerId,
        'user_id' => $userId,
        'block' => 'collection',
        'text' => 'Удалил карту ' . $card->pan,
        'created' => date('Y-m-d H:i:s'),
    ];

    $simpla->comments->add_comment($comment);

    $simpla->best2pay->add_sbp_log([
        'card_id' => $cardId,
        'action' => $simpla->orders::CARD_ACTIONS['DELETE_CARD_MANAGER'],
        'date' => date('Y-m-d H:i:s')
    ]);

    $simpla->request->json_output(['result' => 'success']);
} catch (Throwable $e) {
    logger('delete_card')->error(
        __METHOD__ . PHP_EOL
        . $e->getFile() . PHP_EOL
        . $e->getLine() . PHP_EOL
        . $e->getMessage() . PHP_EOL
    );

    $simpla->request->json_output(['error' => 'server error']);
}