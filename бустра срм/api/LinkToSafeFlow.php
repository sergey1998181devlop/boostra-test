<?php

require_once 'Simpla.php';
class LinkToSafeFlow extends Simpla
{

    /**
     * Получение статистики ссылок
     * @return array
     */
    public function getLinkStats()
    {
        $query = $this->db->placehold("
        SELECT * FROM __link_stats
    ");
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * Генерация безопасной ссылки
     * @return object
     */
    public function generateSafeLink(): object
    {
        $uniquePart = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 11);
        $expirationDate = date('Y-m-d H:i:s', strtotime('+96 hours'));

        $link = $this->config->front_url . '/safe-flow?code=' . $uniquePart . '&utm_source=dop_o';

        $query = $this->db->placehold("INSERT INTO __link_stats (link, created_at, expiration_date) VALUES (?, CURRENT_TIMESTAMP, ?)", $link, $expirationDate);
        $this->db->query($query);

        $query = $this->db->placehold("SELECT * FROM __link_stats WHERE link = ?", $link);
        $this->db->query($query);
        $linkStats = $this->db->result();

        return $linkStats;
    }



}