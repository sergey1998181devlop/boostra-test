<?php

require_once '../../api/Simpla.php';
require_once __DIR__ . '/MonitoringIntervalTrait.php';

class TiketPKkpi extends Simpla
{
    use MonitoringIntervalTrait;
    public function run()
    {
        $this->checkApiKey();
        
        $interval = $this->getInterval();
        $count = $this->getTiketPKkpiCount($interval);

        $this->response->json_output([
            'count' => $count
        ]);
    }

    /**
     * Получить количество заявок ПК kpi за интервал
     *
     * @param string $interval
     * @return int
     */
    private function getTiketPKkpiCount(string $interval): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(o.id) as count
            FROM s_orders AS o
            WHERE o.have_close_credits = 1
            AND o.complete = 1
            AND o.date > (NOW() - INTERVAL $interval)
        ");

        $this->db->query($query);
        $result = $this->db->result();

        return (int)($result->count ?? 0);
    }
}

(new TiketPKkpi())->run();

