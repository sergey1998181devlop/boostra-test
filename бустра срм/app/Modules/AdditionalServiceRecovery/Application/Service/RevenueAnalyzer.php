<?php

namespace App\Modules\AdditionalServiceRecovery\Application\Service;

use Database;

/**
 * Class RevenueAnalyzer
 * Анализирует оплаты и возвраты для ранее восстановленных услуг.
 * Предназначен для запуска по CRON.
 */
class RevenueAnalyzer
{
    private Database $db;

    private array $paymentTablesConfig = [
        's_tv_medical_payments' => [
            'amount_field' => 'amount',
            'date_field' => 'date_added',
            'refund_amount_field' => 'return_amount',
            'refund_date_field' => 'return_date'
        ],
        's_multipolis' => [
            'amount_field' => 'amount',
            'date_field' => 'date_added',
            'refund_amount_field' => 'return_amount',
            'refund_date_field' => 'return_date'
        ],
    ];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function run(): void
    {
        $unprocessedRecords = $this->getUnprocessedTrackingRecords();

        foreach ($unprocessedRecords as $record) {
            $paymentInfo = null;
            foreach ($this->paymentTablesConfig as $tableName => $config) {
                $paymentInfo = $this->findPayment($record, $tableName, $config);
                if ($paymentInfo) {
                    break;
                }
            }

            if ($paymentInfo) {
                $this->updateTrackingRecord($record->id, $paymentInfo);
            }
        }
    }

    private function getUnprocessedTrackingRecords(): array
    {
        $sql = "SELECT * FROM s_service_recovery_revenue WHERE payment_id IS NULL";
        $this->db->query($sql);
        return $this->db->results();
    }

    private function findPayment(object $record, string $tableName, array $config): ?array
    {
        $sql = "
            SELECT 
                id, 
                {$config['amount_field']} AS amount, 
                {$config['date_field']} AS payment_date,
                {$config['refund_amount_field']} AS refund_amount,
                {$config['refund_date_field']} AS refund_date
            FROM {$tableName}
            WHERE order_id = ? 
              AND {$config['date_field']} > ?
            ORDER BY {$config['date_field']} ASC 
            LIMIT 1
        ";

        $this->db->query($sql, $record->order_id, $record->reenabled_at);
        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return [
            'payment_id' => $result->id,
            'payment_table' => $tableName,
            'payment_amount' => $result->amount,
            'payment_at' => $result->payment_date,
            'is_refunded' => !empty($result->refund_amount) && $result->refund_amount > 0,
            'refund_amount' => $result->refund_amount,
            'refund_at' => $result->refund_date,
        ];
    }

    private function updateTrackingRecord(int $recordId, array $paymentInfo): void
    {
        $sql = "UPDATE s_service_recovery_revenue SET ?% WHERE id = ?";
        $this->db->query($sql, $paymentInfo, $recordId);
    }
}

