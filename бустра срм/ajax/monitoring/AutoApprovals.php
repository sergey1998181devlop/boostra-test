<?php

require_once '../../api/Simpla.php';
require_once __DIR__ . '/MonitoringIntervalTrait.php';

class AutoApprovals extends Simpla
{
    use MonitoringIntervalTrait;

    public function run()
    {
        $this->checkApiKey();
        
        $interval = $this->getInterval();
        $count = $this->getAutoApprovalsCount($interval);

        $this->response->json_output([
            'count' => $count
        ]);
    }

    /**
     * Получить количество одобрений авто за интервал
     *
     * @param string $interval
     * @return int
     */
    private function getAutoApprovalsCount(string $interval): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(o.id) as count
            FROM s_orders AS o
            WHERE o.manager_id = 50
            AND o.status = 2
            AND o.approve_date > (NOW() - INTERVAL $interval)
        ");

        $this->db->query($query);
        $result = $this->db->result();

        return (int)($result->count ?? 0);
    }
}

(new AutoApprovals())->run();

