<?php

require_once 'Simpla.php';

/**
 * Класс для работы с Апи партнеров
 */
class Ping3Data extends Simpla
{
    /** @var string Пользователь был найден в 1с при проверке checkDouble */
    public const USER_FIND_1C = 'user_find_1c';

    /**
     * @var string Тип пользователя при проверке checkDouble
     * 0 - Это обрабатываем как нового
     * 1 - Обработка как повторного клиента, что был в базе
     */
    public const USER_TYPE = 'user_type';

    /**
     * Новый клиент, не было в базе
     */
    public const USER_TYPE_NEW = 0;

    /**
     * Повторный клиент, был в базе
     */
    public const USER_TYPE_REPEAT = 1;

    /**
     * У повторного пользователя была найдена автозаявка
     */
    public const REPEAT_HAS_CRM_AUTO_APPROVE = 'repeat_has_crm_auto_approve';

    /**
     * У повторного пользователя был найден крос ордер
     */
    public const REPEAT_HAS_CRM_CROSS_ORDER = 'repeat_has_cross_order';

    /**
     * Заявка, которую поменяли с автозаявки в партнерскую
     */
    public const PING3_CRM_AUTO_APPROVE = 'ping3_crm_auto_approve';

    /**
     * Заявка, которую поменяли с cross_order в партнерскую
     */
    public const PING3_CRM_CROSS_ORDER= 'ping3_crm_cross_order';

    /**
     * Устанавливает в базу служебную информацию с ключами
     *
     * @param string $key_name Имя ключа для выборки
     * @param string $key_value Значение ключа
     * @param int $value Само значение
     * @return bool
     */
    public function addPing3Data(string $key_name, string $key_value, int $value): bool
    {
        $data = compact('key_name', 'key_value', 'value');
        $sql = $this->db->placehold("INSERT INTO s_partner_api_data SET ?% ON DUPLICATE KEY UPDATE value = VALUES(value)", $data);
        $this->db->query($sql);
        return $this->db->insert_id();
    }

    /**
     * Получает значение из базы
     * @param string $key_name
     * @param string $key_value
     * @return false|int
     */
    public function getPing3Data(string $key_name, string $key_value)
    {
        $sql = $this->db->placehold("SELECT `value` FROM s_partner_api_data WHERE key_name = ? AND key_value = ? ORDER BY id DESC LIMIT 1", $key_name, $key_value);
        $this->db->query($sql);
        return $this->db->result('value');
    }
}
