<?php

require_once 'Simpla.php';

/**
 * Класс для работы с доп.телефонами пользователя.
 */
class UserPhones extends Simpla
{
    const SOURCE_NBKI_PHONE = 'NBKI_PHONE';
    const SOURCE_NBKI_CONTACT = 'NBKI_CONTACT';

    /**
     * Обрабатывает XML строку из АКСИ НБКИ и возвращает найденные телефоны
     * @param $xml_string
     * @return array
     */
    public function parse_xml($xml_string)
    {
        $xml_string = mb_convert_encoding($xml_string, 'windows-1251', 'utf-8');
        $xml = simplexml_load_string($xml_string);
        $phones = $xml->preply->report->PhoneReply;
        $contacts = $xml->preply2->report->ContactReply;
        return [
            'phones' => $phones,
            'contacts' => $contacts,
        ];
    }

    /**
     * Загружает XML строку и синхронизирует найденные телефоны
     * @param int $order_id
     * @param string $xml_string
     */
    public function load_xml($order_id, $xml_string)
    {
        $parse_result = $this->parse_xml($xml_string);
        $phones = $parse_result['phones'];
        $contacts = $parse_result['contacts'];

        $order = $this->orders->get_order($order_id);
        $user_id = $order->user_id;
        $user = $this->users->get_user($user_id);
        $user_1c_uid = $user->UID;

        foreach ($phones as $phone) {
            if (!empty($phone->number)) {
                $this->sync_phone($user_id, (string)$phone->number, self::SOURCE_NBKI_PHONE, $user_1c_uid);
            }
        }
        foreach ($contacts as $contact) {
            if (!empty($contact->phone)) {
                $this->sync_phone($user_id, (string)$contact->phone, self::SOURCE_NBKI_CONTACT, $user_1c_uid);
            }
        }
    }

    /**
     * Синхронизирует телефон с CRM и 1C
     * @param string $user_id
     * @param string $phone
     */
    public function sync_phone($user_id, $phone, $source, $user_1c_uid = '')
    {
        $user = $this->users->get_user($user_id);
        if (empty($user_1c_uid)) {
            $user_1c_uid = $user->UID;
        }
        $phone = $this->users->clear_phone($phone, '7');

        $this->sync_crm($user, $phone, $source);
        $this->sync_1c($user_1c_uid, $phone);
    }

    /**
     * Отправляет телефон в бд CRM
     * @param $user
     * @param $phone
     * @param $source
     * @return bool false - телефон уже добавлен к этому пользователю, иначе true
     */
    private function sync_crm($user, $phone, $source)
    {
        if ($user->phone_mobile == $phone)
            return false; // Это основной телефон

        if ($user->work_phone == $phone)
            return false; // Это рабочий телефон

        $user_with_phone = $this->get_users($phone);
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
    private function sync_1c($user_uid, $phone)
    {
        $this->soap->send_additional_phone($user_uid, $phone);
    }

    /**
     * Поиск пользователей с указанным доп.телефоном
     * @param $phone
     * @return array|false
     */
    public function get_users($phone)
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
    public function get_phones($user_id)
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