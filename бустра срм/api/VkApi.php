<?php

require_once 'Simpla.php';

class VkApi extends Simpla
{
    public function add(array $row)
    {
        $this->db->query($this->db->placehold('INSERT INTO __user_vk SET ?%', $row));
    }

    public function get(int $user_id)
    {
        return $this->getBy('user_id', $user_id);
    }

    public function getByVkUserId(int $vk_user_id)
    {
        return $this->getBy('vk_user_id', $vk_user_id);
    }

    public function getBy(string $column, $value)
    {
        $this->db->query($this->db->placehold("SELECT * FROM __user_vk WHERE `$column` = ?", $value));
        return $this->db->result();
    }
}