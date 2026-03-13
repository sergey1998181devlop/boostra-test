<?php

namespace App\Repositories;

use App\Core\Database\SimplaDatabase;
use Orders;

class RefundStatsRepository
{
    private $db;

    /**
     * Маппинг сервисов и их payment_type в s_receipts
     */
    private const SERVICES_MAP = [
        'ФД' => [
            'RECOMPENSE_CREDIT_DOCTOR',
            'return_credit_doctor'
        ],
        'КС' => [
            'RECOMPENSE_MULTIPOLIS',
            'return_multipolis'
        ],
        'ВМ' => [
            'RECOMPENSE_TV_MEDICAL',
            'return_tv_medical'
        ],
        'ЗО' => [
            'RECOMPENSE_STAR_ORACLE',
            'return_star_oracle'
        ]
    ];
    private Orders $orders;

    public function __construct()
    {
        $this->db = SimplaDatabase::getInstance()->db();
        $this->orders = new Orders;
    }

    /**
     * @param string $fromDate
     * @param string $toDate
     * @return array
     */
    public function getRefundStats(string $fromDate, string $toDate): array
    {
        // Получаем статистику по сервисам
        $serviceStats = $this->getServiceStats($fromDate, $toDate);

        // Получаем статистику по процентам возврата
        $percentStats = $this->getPercentStats($fromDate, $toDate);

        // Получаем статистику  по допам.
        $additionsData = $this->currentAdditionsData($fromDate, $toDate);

        $addReturn = [
            // получаем статистику по заявкам
            'order_stat' => $this->getOrdersStats($fromDate, $toDate),
            'current_additions_data' => $additionsData,
            'return_percentage' =>  $this->returnPercentage($fromDate, $toDate, $additionsData),
        ];

        return $this->formatResults($serviceStats, $percentStats) + $addReturn;
    }

    /**
     * Получает статистику по сервисам
     */
    private function getServiceStats(string $fromDate, string $toDate): array
    {
        $caseConditions = [];
        $allowedTypes = [];

        foreach (self::SERVICES_MAP as $serviceKey => $paymentTypes) {
            $types = implode("','", $paymentTypes);
            $caseConditions[] = "WHEN r.payment_type IN ('$types') THEN '$serviceKey'";
            $allowedTypes = array_merge($allowedTypes, $paymentTypes);
        }

        $caseStatement = implode(' ', $caseConditions);
        $allowedTypesStr = "'" . implode("','", $allowedTypes) . "'";

        $query = $this->db->placehold("
            SELECT 
                CASE
                    {$caseStatement}
                END AS service_type,
                COUNT(*) as count,
                SUM(r.amount) as total_amount
            FROM s_receipts r
            WHERE r.payment_method = 'B2P'
                AND r.description LIKE 'Возврат%'
                AND r.date_added >= ?
                AND r.date_added <= ?
                AND r.success = 1
                AND r.payment_type IN ({$allowedTypesStr})
            GROUP BY service_type
            ORDER BY total_amount DESC
        ", $fromDate, $toDate);

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получает статистику по процентам возврата
     */
    private function getPercentStats(string $fromDate, string $toDate): array
    {
        $allowedTypes = [];
        foreach (self::SERVICES_MAP as $paymentTypes) {
            $allowedTypes = array_merge($allowedTypes, $paymentTypes);
        }
        $allowedTypesStr = "'" . implode("','", $allowedTypes) . "'";

        $query = $this->db->placehold("
            SELECT 
                CASE
                    WHEN ABS(r.amount - COALESCE(sm.amount, st.amount, sc.amount, so.amount)) < 1 THEN '100'
                    WHEN ABS(r.amount - (COALESCE(sm.amount, st.amount, sc.amount, so.amount) * 0.75)) < 1 THEN '75'
                    WHEN ABS(r.amount - (COALESCE(sm.amount, st.amount, sc.amount, so.amount) * 0.5)) < 1 THEN '50'
                    WHEN ABS(r.amount - (COALESCE(sm.amount, st.amount, sc.amount, so.amount) * 0.25)) < 1 THEN '25'
                    ELSE 'other'
                END as refund_percent,
                COUNT(*) as count
            FROM s_receipts r
            LEFT JOIN b2p_transactions bt ON r.transaction_id = bt.id
            LEFT JOIN s_multipolis sm ON bt.reference = sm.payment_id 
                AND r.payment_type IN ('RECOMPENSE_MULTIPOLIS', 'return_multipolis') 
                AND sm.return_status = 2
            LEFT JOIN s_tv_medical_payments st ON bt.reference = st.payment_id 
                AND r.payment_type IN ('RECOMPENSE_TV_MEDICAL', 'return_tv_medical') 
                AND st.return_status = 2
            LEFT JOIN s_credit_doctor_to_user sc ON bt.reference = sc.transaction_id 
                AND r.payment_type IN ('RECOMPENSE_CREDIT_DOCTOR', 'return_credit_doctor') 
                AND sc.return_status = 2
            LEFT JOIN s_star_oracle so ON bt.reference = so.transaction_id 
                AND r.payment_type IN ('RECOMPENSE_STAR_ORACLE', 'return_star_oracle') 
                AND so.return_status = 2
            WHERE r.payment_method = 'B2P'
                AND r.description LIKE 'Возврат%'
                AND r.date_added >= ?
                AND r.date_added <= ?
                AND r.success = 1
                AND r.payment_type IN ({$allowedTypesStr})
            GROUP BY refund_percent
            HAVING refund_percent != 'other'
        ", $fromDate, $toDate);

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Форматирует результаты запроса в нужную структуру
     */
    private function formatResults(array $serviceResults, array $percentResults): array
    {
        $stats = [
            'total' => [
                'amount' => 0,
                'count' => 0,
                'by_percent' => [
                    '25' => 0,
                    '50' => 0,
                    '75' => 0,
                    '100' => 0
                ]
            ],
            'by_service' => [],
        ];

        foreach (array_keys(self::SERVICES_MAP) as $serviceKey) {
            $stats['by_service'][$serviceKey] = [
                'amount' => 0,
                'count' => 0
            ];
        }

        foreach ($serviceResults as $row) {
            if (!$row->service_type) {
                continue;
            }

            $stats['total']['amount'] += $row->total_amount;
            $stats['total']['count'] += $row->count;

            $stats['by_service'][$row->service_type] = [
                'amount' => $row->total_amount,
                'count' => $row->count
            ];
        }

        foreach ($percentResults as $row) {
            if (isset($stats['total']['by_percent'][$row->refund_percent])) {
                $stats['total']['by_percent'][$row->refund_percent] = $row->count;
            }
        }

        return $stats;
    }

    private function getOrdersStats(string $fromDate, string $toDate): array
    {
        $query = "SELECT
            COUNT(*) AS total_orders,
            COALESCE(SUM(c.amount), 0) AS total_sum,
    
             -- Постоянные клиенты  
            SUM(CASE 
                WHEN COALESCE(o.have_close_credits, 0) = 1 
                OR COALESCE(o.utm_source, '') = 'cross_order'
             THEN 1 ELSE 0 END) AS regular_clients_count,
    
            COALESCE(SUM(CASE 
                 WHEN COALESCE(o.have_close_credits, 0) = 1 
                 OR COALESCE(o.utm_source, '') = 'cross_order'
                 THEN c.amount ELSE 0 END), 0) AS regular_clients_sum,
    
            -- Новые клиенты
            SUM(CASE 
                WHEN COALESCE(o.have_close_credits, 0) = 0 
                AND COALESCE(o.utm_source, '') != 'cross_order'
                THEN 1 ELSE 0 END) AS new_clients_count,
    
             COALESCE(SUM(CASE 
                WHEN COALESCE(o.have_close_credits, 0) = 0 
                AND COALESCE(o.utm_source, '') != 'cross_order'  
                THEN c.amount ELSE 0  END), 0) AS new_clients_sum

            FROM b2p_p2pcredits AS p
            LEFT JOIN s_orders AS o ON o.id = p.order_id
            LEFT JOIN s_contracts AS c ON c.order_id = p.order_id  
            WHERE p.status = 'APPROVED' AND p.complete_date BETWEEN ? AND ?";

        $query = $this->db->placehold($query, $fromDate, $toDate);
        $this->db->query($query);

        return (array)$this->db->result();
    }

    private function currentAdditionsData(string $fromDate, string $toDate): array
    {
        $query = "SELECT source,COUNT(*) AS count,COALESCE(SUM(amount), 0) AS sum
        FROM (
            SELECT 'credit_doctor_to_user' AS source, p.amount, p.date_edit AS dt
             FROM s_credit_doctor_to_user p WHERE p.status = 'SUCCESS'
    
            UNION ALL
    
            SELECT 'tv_medical_payments' AS source, p.amount, p.date_added AS dt
            FROM s_tv_medical_payments p WHERE p.status = 'SUCCESS'
    
            UNION ALL
    
            SELECT 'multipolis' AS source, p.amount, p.date_added AS dt
            FROM s_multipolis p WHERE p.status = 'SUCCESS'
    
            UNION ALL
    
            SELECT 'star_oracle' AS source, p.amount, p.date_edit AS dt
            FROM s_star_oracle p WHERE p.status = 'SUCCESS'
        ) t
        WHERE t.dt BETWEEN ? AND ? GROUP BY source;";

        $this->db->query($this->db->placehold($query, $fromDate, $toDate));
        $result = [];
        foreach ($this->db->results() as $r) {
            $result += [
                $r->source . '_sum' => $r->sum,
                $r->source . '_count' => $r->count
            ];
            $result['total_sum'] += $r->sum;
            $result['total_count'] += $r->count;

        }

        return $result;
    }

    /*
     * Процент возврата
     */
    private function returnPercentage(string $fromDate, string $toDate, array $additionsData = []): array
    {
        $query = "SELECT source,COUNT(*) AS count,COALESCE(SUM(amount), 0) AS sum
        FROM (
            SELECT 'credit_doctor_to_user' AS source, p.amount, p.date_edit AS dt
             FROM s_credit_doctor_to_user p WHERE  p.return_status = 2
    
            UNION ALL
    
            SELECT 'tv_medical_payments' AS source, p.amount, p.date_added AS dt 
            FROM s_tv_medical_payments p WHERE  p.return_status = 2
    
            UNION ALL
    
            SELECT 'multipolis' AS source, p.amount, p.date_added AS dt
            FROM s_multipolis p  WHERE  p.return_status = 2
    
            UNION ALL
    
            SELECT 'star_oracle' AS source, p.amount, p.date_edit AS dt
            FROM s_star_oracle p WHERE  p.return_status = 2
        ) t
        WHERE  t.dt BETWEEN ? AND ? GROUP BY source;";

        $this->db->query($this->db->placehold($query, $fromDate, $toDate));
        $items = $this->db->results();
        $result = [];

        $sumReturnTotal = 0;
        $sumIncomingTotal = 0;
        foreach ($items as $item) {
            $sumReturn = $item->sum;
            $sumIncoming = $additionsData[$item->source . '_sum'];
            // Сумма возврата/сумма поступления * 100
            $result[$item->source] = round($sumReturn / $sumIncoming * 100, 2);
            $sumReturnTotal += $sumReturn;
            $sumIncomingTotal += $sumIncoming;
        }

        $result['total'] = round($sumReturnTotal / $sumIncomingTotal * 100, 2);
        return $result;
    }
}