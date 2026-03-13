<?php

require_once dirname(__DIR__) . '/api/Simpla.php';

error_reporting(E_ERROR);

/**
 * Класс отправляющий чеки
 * class CronSendReceipts
 */
class CronSendReceipts extends Simpla
{
    public function run()
    {
        $filter_data = [
            'filter_is_sent' => 0,
            'filter_not_empty_user' => 1,
            'limit' => 500,
            'order_by' => 'id ASC',
        ];

        $receipts = $this->receipts->selectAll($filter_data);
        foreach ($receipts as $receipt) {
            $receipt->Services = [$receipt];

            if (!empty($receipt->payment_id)) {
                $payment = $this->best2pay->get_payment($receipt->payment_id);
                $receipt->contract_number = $payment->contract_number;
            } elseif (!empty($receipt->transaction_id)) {
                $transaction = $this->best2pay->get_transaction($receipt->transaction_id);
                $receipt->contract_number = $transaction->contract_number;
            }

            if ($receipt->payment_type == 'credit_rating') {
                $receipt->contract_number = $receipt->user_id.'-'.date('YmdHis', strtotime($receipt->date_added));
            } elseif (empty($receipt->contract_number)) {
                $contract = $this->contracts->get_contract_by_params(['order_id' => $receipt->order_id]);
                $receipt->contract_number = $contract->number ?: $receipt->order_id;
            }

            $result = $this->cloudkassir->send_receipt($receipt);

            if (!empty($result)) {
                $update_data = [
                    'is_sent' => 1,
                    'receipt_id' => $result['Model']['Id'],
                    'receipt_url' => $result['Model']['ReceiptLocalUrl'],
                    'success' => (int)!empty($result['Success']),
                ];
                $this->receipts->updateItem($receipt->id, $update_data);
            }
        }
    }
}

$start = microtime(true);
(new CronSendReceipts())->run();
$end = microtime(true);

$time_worked = microtime(true) - $start;
exit(date('c', $start) . ' - ' . date('c', $end) . ' :: script ' . __FILE__ . ' work ' . $time_worked . '  s.');
