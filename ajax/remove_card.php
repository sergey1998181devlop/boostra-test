<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
chdir('..');

require_once 'api/Simpla.php';

$simpla = new Simpla();
$card_id = $simpla->request->post('card_id');


if($card_id) {
    if ($user = $simpla->users->get_user((int)$_SESSION['user_id'])) {
        $count_cards = $simpla->best2pay->count_cards([
            'deleted' => 0,
            'deleted_by_client' => 0,
            'user_id' => $user->id,
        ]);
        if($count_cards > 1) {
            $orders = $simpla->orders->get_orders(['user_id' => $user->id]);
            $balance = $simpla->users->get_user_balance($user->id);
            $busy_cards = [];
            foreach ($orders as $order) {
                if (!$order->status_1c
                    || in_array($order->status_1c, $simpla->orders::IN_PROGRESS_STATUSES)
                    || ($order->status_1c == '5.Выдан'
                        && $order->id_1c == $balance->zayavka
                        && $balance->ostatok_od + $balance->ostatok_percents + $balance->ostatok_peni > 0)) {
                    $busy_cards[$order->card_id] = true;
                }
            }
            if (empty($busy_cards[$card_id])) {
                if ($card = $simpla->best2pay->get_card($card_id)) {
                        $simpla->best2pay->update_card($card_id, ['deleted_by_client' => 1, 'deleted_by_client_date' => date('Y-m-d H:i:s')]);
                        $comment = [
                            'manager_id' => 50,
                            'user_id' => $card->user_id,
                            'block' => 'collection',
                            'text' => 'Клиент удалил карту ' . $card->pan,
                            'created' => date('Y-m-d H:i:s'),
                        ];
                        $simpla->comments->add_comment($comment);
                        $simpla->best2pay->add_sbp_log([
                            'card_id' => $card_id,
                            'action' => $simpla->best2pay::CARD_ACTIONS['DELETE_CARD_CLIENT'],
                            'date' => date('Y-m-d H:i:s')
                        ]);
                        $simpla->request->json_output(['result' => 'success']);
                } else {
                    $all_cards = $simpla->soap->get_card_list($user->uid);
                    if (count($all_cards) > 1) {
                        $orders = $simpla->orders->get_orders(['user_id' => $user->id]);
                        $balance = $simpla->users->get_user_balance($user->id);
                        if (empty($busy_cards[$card_id])
                            && $simpla->soap->remove_card($card_id, $user)) {
                            $simpla->request->json_output(['result' => 'success']);
                        }
                    }

                }
            } else {
                $simpla->request->json_output(['error' => 'card_blocked']);
            }
        } else {
            $simpla->request->json_output(['error' => 'first_card_blocked']);
        }
    }
}