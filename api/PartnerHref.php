<?php

/**
 * Class PartnerHref
 * Класс для работы с партнерскими ссылками
 */

require_once 'Simpla.php';

class PartnerHref extends Simpla
{
    /**
     * Получает актуальную ссылку партнера для отказника
     * @param int $is_pk
     * @return object
     */
    public function getActualItem($is_pk = 0, $link_type = 'bonon-shop-window')
    {
        $id = $_SESSION['partner_item_id'] ?? 0;
        $ctype = $is_pk ? 'pk' : 'nk';
        $this->db->query("SELECT id, href
                          FROM s_partner_href
                          WHERE
                            client_type = ?
                            AND link_type = ?
                            AND id > IF(EXISTS(SELECT * FROM s_partner_href WHERE id > ? AND link_type = ? AND client_type = ?), ?, 0)
                          LIMIT 1", $ctype, $link_type, $id, $link_type, $ctype, $id);
        $result = $this->db->result();

        if (!empty($result)) {
            $_SESSION['partner_item_id'] = (int)$result->id;
        }

        return $result;
    }

    /**
     * Добавляет статистику по ссылкам
     * @param int $user_id
     * @param int $href_id
     * @param string $type
     * @return void
     */
    public function addStatistic(int $user_id, int $href_id, string $type_action = 'view')
    {
        $query = $this->db->placehold("INSERT INTO __partner_href_statistics SET ?%", compact('user_id', 'href_id', 'type_action'));
        $this->db->query($query);
    }
}
