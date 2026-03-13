<?php

require_once '../../api/Simpla.php';
require_once __DIR__ . '/MonitoringIntervalTrait.php';

class BoostraTakeCard extends Simpla
{
    use MonitoringIntervalTrait;

    public function run()
    {
        $this->checkApiKey();
        
        $interval = $this->getInterval();
        $count = $this->getBoostraTakeCardCount($interval);

        $this->response->json_output([
            'count' => $count
        ]);
    }

    /**
     * Получить количество привязок карты за интервал
     *
     * @param string $interval
     * @return int
     */
    private function getBoostraTakeCardCount(string $interval): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(id) as count
            FROM s_users
            WHERE card_added = 1
            AND card_added_date > (NOW() - INTERVAL $interval)
        ");

        $this->db->query($query);
        $result = $this->db->result();

        return (int)($result->count ?? 0);
    }
}

(new BoostraTakeCard())->run();

