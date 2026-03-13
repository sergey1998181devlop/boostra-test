<?php

require_once '../../api/Simpla.php';
require_once __DIR__ . '/MonitoringIntervalTrait.php';

class IssuancePK extends Simpla
{
    use MonitoringIntervalTrait;

    public function run()
    {
        $this->checkApiKey();
        
        $interval = $this->getInterval();
        $count = $this->getIssuancePKCount($interval);

        $this->response->json_output([
            'count' => $count
        ]);
    }

    /**
     * Получить количество выдач ПК за интервал
     *
     * @param string $interval
     * @return int
     */
    private function getIssuancePKCount(string $interval): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(p.id) as count
            FROM b2p_p2pcredits AS p
            LEFT JOIN s_orders AS o ON o.id = p.order_id
            WHERE o.have_close_credits = 1
            AND p.status = 'APPROVED'
            AND p.complete_date > (NOW() - INTERVAL $interval)
        ");

        $this->db->query($query);
        $result = $this->db->result();

        return (int)($result->count ?? 0);
    }
}

(new IssuancePK())->run();

