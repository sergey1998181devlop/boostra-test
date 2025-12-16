<?php

require_once 'Simpla.php';

/**
 * Убирает или добавляет пользователей для скрытия блока с допами
 */
class HideService extends Simpla {

    /**
     * Добавляет запись
     * @param int $user_id
     * @return mixed
     */
    public function addItem(int $user_id)
    {
        $sql = $this->db->placehold("INSERT IGNORE INTO s_hide_service SET user_id = ?", $user_id);
        $this->db->query($sql);
        return $this->db->insert_id();
    }

    /**
     * Удаляет запись
     * @param int $user_id
     * @return mixed
     */
    public function deleteItem(int $user_id)
    {
        $sql = $this->db->placehold("DELETE FROM s_hide_service WHERE user_id = ?", $user_id);
        return $this->db->query($sql);
    }

    /**
     * Проверяет пользователя в списках
     * @param int $user_id
     * @return false|int
     */
    public function hasItem(int $user_id): bool
    {
        $query = $this->db->placehold("SELECT EXISTS(SELECT * FROM s_hide_service WHERE user_id = ?) as r", $user_id);
        $this->db->query($query);
        return (bool)$this->db->result('r');
    }
}
