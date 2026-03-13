<?php

namespace App\Modules\Clients\Infrastructure\Repository;

use App\Modules\Clients\Domain\Entity\ActiveLoan;
use App\Modules\Clients\Domain\Repository\LoanRepositoryInterface;
use Database;

class LoanRepository implements LoanRepositoryInterface
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function findActiveByUserId(int $userId, ?array $organizationIds = null): ?ActiveLoan
    {
        $activeFilter = "(o.`1c_status` IN ('5.Выдан','Выдан') OR b.sale_info = 'Договор продан')";
        return $this->findByUserId($userId, $organizationIds, $activeFilter);
    }

    public function findAllByUserId(int $userId, ?array $organizationIds = null): ?ActiveLoan
    {
        return $this->findByUserId($userId, $organizationIds, null);
    }

    private function findByUserId(int $userId, ?array $organizationIds = null, ?string $extraWhere = null): ?ActiveLoan
    {
        $orgFilter = '';
        $hasOrgFilter = false;
        if (!empty($organizationIds)) {
            $placeholders = implode(',', array_fill(0, count($organizationIds), '?'));
            $orgFilter = " AND o.organization_id IN ({$placeholders}) ";
            $hasOrgFilter = true;
        }

        $sql = "SELECT
                    o.id as order_id,
                    o.amount as order_amount,
                    o.approve_amount,
                    o.loan_type as order_loan_type,
                    o.`1c_status`,
                    o.contract_id,
                    o.deleteKD,
                    o.additional_service_repayment,
                    o.date as order_date,
                    o.percent as order_percent,
                    o.organization_id,

                    c.id as contract_db_id,
                    c.number as contract_number,
                    c.amount as contract_amount,
                    c.period,
                    c.return_date,
                    c.responsible_person_id,
                    c.issuance_date,
                    c.close_date,
                    c.base_percent as contract_percent,
                    
                    org.contract_prefix,

                    b.zaim_summ,
                    b.ostatok_od,
                    b.zaim_date,
                    b.ostatok_percents,
                    b.ostatok_peni,
                    b.prolongation_count,
                    b.payment_date,
                    b.penalty,
                    b.loan_type,
                    CASE 
                        WHEN o.`1c_status` IN ('5.Выдан','Выдан') OR b.sale_info = 'Договор продан' THEN 1
                        ELSE 0 
                    END as is_active

                FROM s_orders o
                LEFT JOIN s_contracts c ON o.contract_id = c.id
                LEFT JOIN s_organizations org ON o.organization_id = org.id
                LEFT JOIN s_user_balance b ON 
                    (
                        c.number = b.zaim_number
                        OR CONCAT(org.contract_prefix, YEAR(o.date), '-', o.id) = b.zaim_number
                        OR b.zayavka = o.id
                    )
                    AND b.user_id = ?
                
                WHERE o.user_id = ?
                  " . $orgFilter . (
                        $extraWhere ? " AND (" . $extraWhere . ")" : ''
                  ) . "
                GROUP BY c.number
                ORDER BY o.date DESC";

        // Порядок плейсхолдеров в запросе:
        // 1) b.user_id = ?
        // 2) o.user_id = ?
        // 3) o.organization_id IN (?, ?, ...) (если включен фильтр по организациям)
        $queryParams = [];
        $queryParams[] = $userId; // для b.user_id
        $queryParams[] = $userId; // для o.user_id
        if ($hasOrgFilter) {
            foreach ($organizationIds as $orgId) {
                $queryParams[] = $orgId;
            }
        }

        $this->db->query($sql, ...$queryParams);
        $results = $this->db->results();

        if (!$results) {
            return null;
        }

        $rows = array_map(fn($r) => (array)$r, $results);

        return ActiveLoan::fromDatabaseRows($rows);
    }
}
