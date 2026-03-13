<?php

/**
 * Данный скрипт служит для проверки новых транзакций
 * на будущее если понадобиться можно дописывать свою логику для определенных платежей со статусами
 * в кроне передаем аргумент, который является типом платежа
 */

date_default_timezone_set('Europe/Samara');
require_once(dirname(dirname(__DIR__)) . '/api/Simpla.php');

const TRANSACTION_STATUS_NEW = 'NEW';

$simpla = new Simpla();

$payment_type = $argv[1] ?? 'credit_rating';

if ($payment_type == 'credit_rating') {
    $transactions = $simpla->transactions->getTransactionByTypeAndStatus($payment_type, TRANSACTION_STATUS_NEW);
    // пробежимся по новым транзакциям
    foreach ($transactions as $transaction) {
        // проверяем ответ от Тинькова по транзакции
        $response = $simpla->tinkoff->get_state_atop($transaction->payment_id);

        // если статус оплачена, то пишем признак в БД
        if ($simpla->tinkoff->transactionIsSuccess($response['Status'])) {
            $result = $simpla->soap->send_payment_result($transaction, $response['Status'] == 'AUTHORIZED');

            $simpla->transactions->update_transaction($transaction->id, [
                'sended' => 1,
                'send_result' => $result->return ?? serialize($result),
                'status' => $response['Status'],
            ]);

            // тут обновляем пользователя
            $user_id = (int)$transaction->user_id;
            $user = $simpla->users->get_user($user_id);

            // ставим флаг покупки КР для новых юзеров
            if (empty($user->skip_credit_rating)) {
                $simpla->users->addSkipCreditRating($user_id, 'PAY');
            }

            // добавим КР пользователю
            $rating_document_id = $simpla->credit_rating->handle_credit_rating_paid(
                $user,
                $transaction->id,
                $transaction->code_sms
            );
        } elseif (!empty($response['Status'])) {
            $simpla->transactions->update_transaction($transaction->id, ['status' => $response['Status']]);
        }
    }
}

exit();
