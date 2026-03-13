<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

chdir('..');
require_once 'api/Simpla.php';
require_once 'api/Helpers.php';

set_error_handler(static function (
    int $severity,
    string $message,
    string $file,
    int $line
) {
    $fatal = [E_ERROR, E_PARSE];

    if (in_array($severity, $fatal, true)) {
        // НЕ трогаем фатальные — вернём FALSE,
        // чтобы PHP сам их показал согласно display_errors=1
        return false;
    }

    // Исключаем ошибки из Settings.php
    if (basename($file) === 'Settings.php') {
        return true;
    }

    $map = [
        E_WARNING => 'WARNING',
        E_NOTICE => 'NOTICE',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED',
    ];
    $level = $map[$severity] ?? 'UNKNOWN';

    (new Simpla())->logging(
        __METHOD__,
        '',
        'ERROR',
        [
            'severity' => $severity,
            'level' => $level,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ],
        'dop_api.txt'
    );

    return true;
});

session_start();

class GenerateDOPTransactions extends Simpla
{
    /**
     * @throws JsonException
     */
    public function processRequest(): void
    {
        $orderId = $this->request->get('order_id', 'int');
        $date = $this->request->get('date', 'string') ?: '2025-09-01 00:00:00';

        $sql = "
                SELECT c.id,
                       c.order_id,
                       c.complete_date,
                       c.register_id,
                       c.operation_id,
                       c.body,
                       cn.number,
                       c.date,
                       coalesce(cd.amount, 0) + coalesce(tvmed.amount, 0)        AS dop_sum,
                       coalesce(cd.amount, 0)                                    AS cd,
                       coalesce(tvmed.amount, 0)                                 AS tv_medical
                FROM b2p_p2pcredits AS c
                         join s_orders o on o.id = c.order_id
                         join s_contracts cn on cn.order_id = c.order_id
                         LEFT JOIN s_credit_doctor_to_user cd ON cd.order_id = c.order_id AND cd.status = 'SUCCESS'
                         LEFT JOIN s_tv_medical_payments tvmed ON tvmed.order_id = c.order_id AND tvmed.action_type = 'issuance' AND tvmed.status = 'SUCCESS'
                WHERE
                c.complete_date >= ? and c.status = 'APPROVED'
                and coalesce(cd.amount, 0) + coalesce(tvmed.amount, 0) > 0
                AND (
                   (COALESCE(cd.amount,0) > 0 AND NOT EXISTS (
                       SELECT 1
                       FROM b2p_transactions b
                       WHERE b.order_id = c.order_id
                         AND b.type IS NULL
                         AND b.description LIKE 'Кредитный доктор%'
                   ))
                   OR
            
                   (COALESCE(tvmed.amount,0) > 0 AND NOT EXISTS (
                       SELECT 1
                       FROM b2p_transactions b
                       WHERE b.order_id = c.order_id
                         AND b.type IS NULL
                         AND b.description LIKE 'Телемедицина%'
                   ))
              )
                
            ";
        if ($orderId !== null) {
            $sql .= $this->db->placehold(" AND c.order_id = ? ", $orderId);
        }

        $sql .= " ORDER BY c.order_id DESC";

        $query = $this->db->placehold(
            $sql,$date
        );

        $this->db->query($query);
        $p2pcredits = $this->db->results();

        if (!$p2pcredits) {
            $this->jsonResponse(true, 'Нет контрактов для отправки');
            return;
        }

        $stats = [
            'no_orders' => [],
            'orders' => [],
            'tv_medical' => [],
            'cd' => [],
        ];
        foreach ($p2pcredits as $p2pcredit) {
            $order = $this->orders->get_order($p2pcredit->order_id);
            if (!$order) {
                $stats['no_orders'][] = $p2pcredit->order_id;
                continue;
            }
            $stats['orders'][] = $p2pcredit->order_id;

            $fio = Helpers::getFIO($order);

            $body = unserialize($p2pcredit->body);

            $tv_medical = $this->tv_medical->getTVMedical($order->order_id, $order->user_id, null, null, null, 'issuance');
            if ($tv_medical && !$tv_medical->payment_id) {
                $description = "Телемедицина - к заявке $order->order_id $fio";

                $transaction_id = $this->best2pay->add_transaction(array(
                    'user_id' => $tv_medical->user_id,
                    'order_id' => $tv_medical->order_id,
                    'contract_number' => $p2pcredit->number,
                    'amount' => $tv_medical->amount * 100,
                    'sector' => $body['sector'],
                    'register_id' => $p2pcredit->register_id,
                    'operation' => $p2pcredit->operation_id,
                    'reason_code' => 1,
                    'reference' => '',
                    'description' => $description,
                    'created' => date('Y-m-d H:i:s'),
                    'card_pan' => '',
                    'operation_date' => $p2pcredit->date,
                ));
                $this->tv_medical->updatePayment($tv_medical->id, ['payment_id' => $transaction_id]);
                $stats['tv_medical'][] = $p2pcredit->order_id;
            }

            $credit_doctor = $this->credit_doctor->getUserCreditDoctor((int)$order->order_id, (int)$order->user_id, $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS);
            if ($credit_doctor && !$credit_doctor->transaction_id) {
                $cd_description = "Кредитный доктор - к заявке $order->order_id $fio";

                $cd_transaction_id = $this->best2pay->add_transaction(array(
                    'user_id' => $credit_doctor->user_id,
                    'order_id' => $credit_doctor->order_id,
                    'contract_number' => $p2pcredit->number,
                    'amount' => $credit_doctor->amount * 100,
                    'sector' => $body['sector'],
                    'register_id' => $p2pcredit->register_id,
                    'operation' => $p2pcredit->operation_id,
                    'reason_code' => 1,
                    'reference' => '',
                    'description' => $cd_description,
                    'created' => date('Y-m-d H:i:s'),
                    'card_pan' => '',
                    'operation_date' => $p2pcredit->date,
                ));
                $this->credit_doctor->updateUserCreditDoctorData($credit_doctor->id, ['transaction_id' => $cd_transaction_id]);
                $stats['cd'][] = $p2pcredit->order_id;
            }

            $this->best2pay->update_p2pcredit($p2pcredit->id, ['sent' => 0]);
        }

        $this->jsonResponse(true, 'Успешно', $stats);
    }

    /**
     * @param bool $success
     * @param string $message
     * @param array $data
     * @return void
     * @throws JsonException
     */
    private function jsonResponse(bool $success, string $message, array $data = []): void
    {
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $data), JSON_THROW_ON_ERROR);
    }
}

(new GenerateDOPTransactions())->processRequest();
