<?php

require_once('Simpla.php');

/**
 * Класс для работы с сайтами (лендингами), хранящимися в таблице s_sites.
 * Предоставляет методы для получения информации о сайтах.
 * Идентификаторы сайтов (site_id) хранятся в @App\Enums\Site.
 */
class Sites extends Simpla
{
    public const SITE_BOOSTRA = 'boostra';
    public const SITE_SOYAPLACE = 'soyaplace';
    public const SITE_RUBL = 'rubl';
    public const SITE_NEOMANI = 'neomani';

    /**
     *  Получим список активных сайтов
     *
     * @return array|false
     */
    public function getActiveSites()
    {
        $query = $this->db->placehold("SELECT * FROM __sites WHERE is_active=?", 1);
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * @return array|false
     */
    public function getAllSites()
    {
        $query = $this->db->placehold("SELECT * FROM __sites");
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * @return array|false
     */
    public function getDomainBySiteId($site_id): string
    {
        $query = $this->db->placehold("SELECT domain FROM __sites WHERE is_active=1 AND site_id=? LIMIT 1", $site_id);
        $this->db->query($query);
        return $this->db->result('domain') ?? '';
    }
}
