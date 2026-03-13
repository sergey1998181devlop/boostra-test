<?php

require_once '../../api/Simpla.php';
require_once __DIR__ . '/MonitoringIntervalTrait.php';

class Partial extends Simpla
{
    use MonitoringIntervalTrait;
    public function run()
    {
        $this->checkApiKey();
        
        $interval = $this->getInterval();
        $count = $this->getPartialCount($interval);

        $this->response->json_output([
            'count' => $count
        ]);
    }

    /**
     * Получить количество частичных оплат за интервал
     *
     * @param string $interval
     * @return int
     */
    private function getPartialCount(string $interval): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(p.id) as count
            FROM b2p_payments AS p
            WHERE p.prolongation = 0
            AND p.reason_code = 1
            AND p.operation_date > (NOW() - INTERVAL $interval)
        ");

        $this->db->query($query);
        $result = $this->db->result();

        return (int)($result->count ?? 0);
    }
}

(new Partial())->run();

