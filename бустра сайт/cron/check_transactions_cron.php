<?PHP

/**
 * Данный скрипт необходим для проверки транзакций Тинькофф, от пользователей которые зашли по паспорту
 */

date_default_timezone_set('Europe/Samara');
require_once dirname(__DIR__) . '/api/Simpla.php';

const TRANSACTION_STATUS_NEW = 'NEW';

$simpla = new Simpla();

// получим список транзакций со статусом NEW
$transactions = $simpla->transactions->getPassportTransactionByStatus(TRANSACTION_STATUS_NEW);

// пробежимся по новым транзакциям
foreach ($transactions as $transaction) {

    // проверяем ответ от Тинькова по транзакции
    $response = $simpla->tinkoff->get_state_atop($transaction->payment_id);

    if ($simpla->tinkoff->transactionIsSuccess($response['Status'])) {
        $object_soap = $simpla->soap->generateObject(
            [
                'УИД' => $transaction->user_uid,
                'Сумма' => $transaction->amount,
                'PaymentId' => $transaction->payment_id,
                'УИД_Договор' => $transaction->loan_uid,
                'mfoAgreement' => $transaction->mfo_agreement,
            ]
        );

        $response_soap = $simpla->soap->requestSoap($object_soap, 'WebOplata', 'GetOplataUID_AD');

        //обновим статус транзакции если получили ответ от 1С
        if (!empty($response_soap['response'])) {
            $simpla->transactions->updatePassportTransaction($transaction->id, ['status' => $response['Status']]);
        }
    } elseif (!empty($response['Status'])) {
        $simpla->transactions->updatePassportTransaction($transaction->id, ['status' => $response['Status']]);
    }
}
