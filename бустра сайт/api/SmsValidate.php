<?php

require_once(__DIR__ . '/../api/Simpla.php');


/**
 * Класс для защиты от флуда СМС
 * Class SmsValidate
 */
class SmsValidate extends Simpla
{

    /**
     * Добавляет запись в БД
     * @param $data
     * @return void
     */
    public function addNewRow($data)
    {
        $query = $this->db->placehold("INSERT INTO s_sms_validate SET ?%", $data);
        $this->db->query($query);
    }

    /**
     * Обновляет запись в БД
     * @param $id
     * @param $data
     * @return void
     */
    public function updateRow($id, $data)
    {
        $query = $this->db->placehold("UPDATE s_sms_validate SET ?% WHERE id = ?", $data, $id);
        $this->db->query($query);
    }

    /**
     * Получает запись по телефону или IP
     * @param $ip
     * @param $phone
     * @return false|int
     */
    public function getRow($ip, $phone)
    {
        $sql = "SELECT * FROM s_sms_validate WHERE";

        if (!empty($ip) && !empty($phone)) {
            $sql .= $this->db->placehold(" ip = ? OR phone = ?", $ip, $phone);
        } elseif (!empty($ip)) {
            $sql .= $this->db->placehold(" ip = ?", $ip);
        } elseif (!empty($phone)) {
            $sql .= $this->db->placehold(" phone = ?", $phone);
        }

        $query = $this->db->placehold($sql);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Проверяет дату последней смс по IP и Телефону
     * @param $ip
     * @param $phone
     * @param $sms_delay
     * @return bool
     */
    public function getBadSmsByTime($ip, $phone, $sms_delay = 30): bool
    {
        $query = $this->db->placehold("SELECT EXISTS(SELECT * FROM s_sms_validate WHERE (ip = ? OR phone = ?) AND (sms_time + " . $sms_delay . ") > UNIX_TIMESTAMP()) as r", $ip, $phone);
        $this->db->query($query);
        return $this->db->result('r');
    }

    /**
     * Удаляет запись
     * @param $id
     * @return void
     */
    public function deleteRow($id)
    {
        $query = $this->db->placehold("DELETE FROM s_sms_validate WHERE id = ?", $id);
        $this->db->query($query);
    }

    /**
     * Обновляет или создает запись о смс и флуде
     * @param $sms_validate
     * @param $insert_data
     * @return void
     */
    public function updateFloodSMS($sms_validate, $insert_data)
    {
        if (!empty($sms_validate->id)) {
            $total_unique = $sms_validate->phone === $insert_data['phone'] && $sms_validate->ip === $_SERVER['REMOTE_ADDR'] ? 1 : 0;
            $insert_data['total_unique'] = $sms_validate->total_unique + $total_unique;
            $this->sms_validate->updateRow($sms_validate->id, $insert_data);
        } else {
            $this->sms_validate->addNewRow($insert_data);
        }
    }
}
