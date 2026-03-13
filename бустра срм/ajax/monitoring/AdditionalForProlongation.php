<?php

require_once '../../api/Simpla.php';
require_once __DIR__ . '/MonitoringIntervalTrait.php';

class AdditionalForProlongation extends Simpla
{
    use MonitoringIntervalTrait;

    public function run()
    {
        $this->checkApiKey();
        
        $interval = $this->getInterval();
        $count = $this->getAdditionalForProlongationCount($interval);

        $this->response->json_output([
            'count' => $count
        ]);
    }

    /**
     * Получить количество доп при пролонгации за интервал
     *
     * @param string $interval
     * @return int
     */
    private function getAdditionalForProlongationCount(string $interval): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(p.id) as count
            FROM b2p_payments as p
            LEFT JOIN s_multipolis AS m ON m.payment_id = p.id
            LEFT JOIN s_tv_medical_payments AS t ON t.payment_id = p.id
            WHERE p.reason_code = 1
            AND p.prolongation = 1
            AND p.operation_date > (NOW() - INTERVAL $interval)
            AND (m.id IS NOT NULL OR t.id IS NOT NULL)
        ");

        $this->db->query($query);
        $result = $this->db->result();

        return (int)($result->count ?? 0);
    }
}

(new AdditionalForProlongation())->run();

