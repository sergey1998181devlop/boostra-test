<?php

require_once 'Simpla.php';

class SspNbkiRequestLog extends Simpla
{
    private const S3_DIRECTORY = 's3/ssp_requests';

    /**
     * @param array $where
     * @return stdClass|null|bool
     */
    public function getLog(array $where)
    {
        $conditions = [];
        foreach ($where as $condition => $value) {
            $conditions[] = $this->db->placehold("`$condition` = ?", $value);
        }

        $conditions = implode(' AND ', $conditions);
        $this->db->query("SELECT * FROM ssp_nbki_request_log WHERE $conditions ORDER BY id DESC LIMIT 1");

        return $this->db->result();
    }

    /**
     * @param array $data
     * @return int
     */
    public function saveNewLog(array $data): int
    {
        $query = $this->db->placehold("INSERT INTO ssp_nbki_request_log SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateLog(int $id, array $data): bool
    {
        $query = $this->db->placehold("UPDATE ssp_nbki_request_log SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    public function saveInS3($content, string $fileName, string $type): string
    {
        $path = self::S3_DIRECTORY . '/' . strtolower($type) . '/' . $fileName . '.xml';

        $this->s3_api_client->putFileBody($content, $path);

        return $path;
    }
}