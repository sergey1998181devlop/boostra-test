<?php

require_once '../../api/Simpla.php';
require_once __DIR__ . '/MonitoringIntervalTrait.php';

class ExtraOnIssue extends Simpla
{
    use MonitoringIntervalTrait;

    public function run()
    {
        $this->checkApiKey();
        
        $interval = $this->getInterval();
        $count = $this->getExtraOnIssueCount($interval);

        $this->response->json_output([
            'count' => $count
        ]);
    }

    /**
     * Получить количество доп на выдаче за интервал
     *
     * @param string $interval
     * @return int
     */
    private function getExtraOnIssueCount(string $interval): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(c.id) as count
            FROM s_credit_doctor_to_user AS c
            LEFT JOIN b2p_p2pcredits as p ON p.id = c.transaction_id
            WHERE c.status = 'SUCCESS'
            AND p.complete_date > (NOW() - INTERVAL $interval)
        ");

        $this->db->query($query);
        $result = $this->db->result();

        return (int)($result->count ?? 0);
    }
}

(new ExtraOnIssue())->run();

