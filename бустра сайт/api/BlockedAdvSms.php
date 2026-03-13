<?php

/**
 * Класс удаляющий / добавляющий пользователь которым необходимо заблокировать отправку смс
 */
class BlockedAdvSms extends Simpla
{
    /**
     * @param int $user_id
     * @return mixed
     */
    public function deleteItemByUserId(int $user_id)
    {
        $query = $this->db->placehold("DELETE FROM s_block_sms_adv WHERE user_id = ?", $user_id);
        return $this->db->query($query);
    }

    /**
     * @param int $user_id
     * @return false|int
     */
    public function getItemByUserId(int $user_id)
    {
        $query = $this->db->placehold("SELECT * FROM s_block_sms_adv WHERE user_id = ?", $user_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function addItem(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_block_sms_adv SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }
}
