<?php

namespace App\Modules\AdditionalServiceRecovery\Application\Service;

use App\Modules\AdditionalServiceRecovery\Application\DTO\RecoveryFilterRequest;
use App\Modules\AdditionalServiceRecovery\Application\DTO\RecoveryReport;
use Database;

class RecoveryReporter
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getReport(RecoveryFilterRequest $filters): RecoveryReport
    {
        $params = [];
        $sql = "
            SELECT
                COUNT(rsr.id) AS reenabled_count,
                COUNT(rsr.payment_id) AS paid_count,
                SUM(rsr.payment_amount) AS total_revenue,
                SUM(rsr.refund_amount) AS total_refunds,
                (IFNULL(SUM(rsr.payment_amount), 0) - IFNULL(SUM(rsr.refund_amount), 0)) AS net_revenue,
                rsr.rule_id,
                r.name as rule_name
            FROM 
                s_service_recovery_revenue rsr
            LEFT JOIN 
                s_service_recovery_rules r ON r.id = rsr.rule_id
            WHERE 1
        ";

        if ($filters->getDateFrom()) {
            $sql .= " AND rsr.reenabled_at >= ?";
            $params[] = $filters->getDateFrom()->format('Y-m-d 00:00:00');
        }

        if ($filters->getDateTo()) {
            $sql .= " AND rsr.reenabled_at <= ?";
            $params[] = $filters->getDateTo()->format('Y-m-d 23:59:59');
        }

        $ruleIds = $filters->getRuleIds();
        if (!empty($ruleIds)) {
            $inPlaceholders = implode(',', array_fill(0, count($ruleIds), '?'));
            $sql .= " AND rsr.rule_id IN ({$inPlaceholders})";
            $params = array_merge($params, $ruleIds);
        }

        $sql .= " GROUP BY rsr.rule_id, r.name ORDER BY net_revenue DESC";

        $this->db->query($sql, ...$params);
        $details = $this->db->results();

        $totalRevenue = (float)array_sum(array_column($details, 'total_revenue'));
        $totalRefunds = (float)array_sum(array_column($details, 'total_refunds'));
        $totalNetRevenue = $totalRevenue - $totalRefunds;
        $totalReenabled = (int)array_sum(array_column($details, 'reenabled_count'));
        $totalPaid = (int)array_sum(array_column($details, 'paid_count'));

        return new RecoveryReport(
            $filters->getDateFrom(),
            $filters->getDateTo(),
            $totalRevenue,
            $totalRefunds,
            $totalNetRevenue,
            $totalReenabled,
            $totalPaid,
            $details
        );
    }
}
