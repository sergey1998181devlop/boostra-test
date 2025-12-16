<?php

require_once 'Simpla.php';

/**
 * Класс для работы с доп.телефонами пользователя.
 */
class UserPhones extends Simpla
{
    const SOURCE_TICKET_PHONE = 'TICKET_PHONE';

    /**
     * Синхронизирует телефон с CRM и 1C
     * @param int $userId
     * @param string $phone
     * @param string $source
     * @param string $user1cUid
     */
    public function syncPhone(int $userId, string $phone, string $source, string $user1cUid = '')
    {
        $user = $this->users->get_user($userId);
        if (empty($user1cUid)) {
            $user1cUid = $user->uid;
        }
        $phone = $this->users->clear_phone($phone, '7');

        $this->syncСrm($user, $phone, $source);
        $this->sync1c($user1cUid, $phone);
    }

    /**
     * Отправляет телефон в бд CRM
     * @param $user
     * @param $phone
     * @param $source
     * @return bool false - телефон уже добавлен к этому пользователю, иначе true
     */
    private function syncСrm($user, $phone, $source)
    {
        if ($user->phone_mobile == $phone)
            return false; // Это основной телефон

        if ($user->work_phone == $phone)
            return false; // Это рабочий телефон

        $user_with_phone = $this->getUsers($phone);
        foreach ($user_with_phone as $other_user_id) {
            if ($other_user_id == $user->id)
                return false; // Этот доп.телефон уже добавлен
        }

        $this->add([
            'user_id' => $user->id,
            'phone' => $phone,
            'source' => $source
        ]);

        return true;
    }

    /**
     * Отправляет телефон в 1С
     * @param $user_uid
     * @param $phone
     */
    private function sync1c($user_uid, $phone)
    {
        $this->soap->sendAdditionalPhone($user_uid, $phone);
    }

    /**
     * Поиск пользователей с указанным доп.телефоном
     * @param $phone
     * @return array|false
     */
    public function getUsers($phone)
    {
        $query = $this->db->placehold('SELECT user_id FROM __user_phones WHERE phone = ?', $phone);
        $this->db->query($query);
        return $this->db->results('user_id');
    }

    /**
     * Добавление доп.телефона
     * @param array $row
     * @return int
     */
    public function add($row)
    {
        $query = $this->db->placehold('INSERT INTO __user_phones SET ?%', (array)$row);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Получение конкретного телефона по его Id
     * @param $phone_id
     * @return false|ArrayObject
     */
    public function get($phone_id)
    {
        $query = $this->db->placehold('SELECT * FROM __user_phones WHERE id = ?', $phone_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Все доп.телефоны пользователя
     * @param $user_id
     * @return false|array
     */
    public function getPhones($user_id)
    {
        $query = $this->db->placehold('SELECT * FROM __user_phones WHERE user_id = ? AND is_active = 1', $user_id);
        $this->db->query($query);
        return $this->db->results();
    }

    public function update($id, $data)
    {
        $data['modified_date'] = date('Y-m-d H:i:s');
        $query = $this->db->placehold("UPDATE __user_phones SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }
}