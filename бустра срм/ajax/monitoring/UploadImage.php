<?php

require_once '../../api/Simpla.php';
require_once __DIR__ . '/MonitoringIntervalTrait.php';

class UploadImage extends Simpla
{
    use MonitoringIntervalTrait;
    public function run()
    {
        $this->checkApiKey();
        
        $interval = $this->getInterval();
        $count = $this->getUploadImageCount($interval);

        $this->response->json_output([
            'count' => $count
        ]);
    }

    /**
     * Получить количество загрузок фото за интервал
     *
     * @param string $interval
     * @return int
     */
    private function getUploadImageCount(string $interval): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(id) as count
            FROM s_users
            WHERE files_added = 1
            AND files_added_date > (NOW() - INTERVAL $interval)
        ");

        $this->db->query($query);
        $result = $this->db->result();

        return (int)($result->count ?? 0);
    }
}

(new UploadImage())->run();

