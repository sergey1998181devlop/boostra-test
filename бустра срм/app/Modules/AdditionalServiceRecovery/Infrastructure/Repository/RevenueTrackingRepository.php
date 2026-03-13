<?php

namespace App\Modules\AdditionalServiceRecovery\Infrastructure\Repository;

use App\Modules\AdditionalServiceRecovery\Domain\Model\ServiceCandidate;
use Database;

class RevenueTrackingRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Создает запись о том, что услуга была восстановлена.
     */
    public function createTrackingRecord(ServiceCandidate $candidate, int $processLogId, int $ruleId): void
    {
        $sql = "
            INSERT INTO s_service_recovery_revenue
                (process_log_id, rule_id, order_id, user_id, service_key, reenabled_at)
            VALUES
                (?, ?, ?, ?, ?, NOW())
        ";
        
        $this->db->query(
            $sql,
            $processLogId,
            $ruleId,
            $candidate->getOrderId(),
            $candidate->getUserId(),
            $candidate->getServiceKey()
        );
    }
}
