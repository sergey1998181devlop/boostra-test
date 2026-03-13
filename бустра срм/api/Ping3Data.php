<?php

require_once 'Simpla.php';

/**
 * Класс для работы с Апи партнеров Ping3
 */
class Ping3Data extends Simpla
{
    /** @var string Пользователь новый, который отдавали при проверке checkDouble */
    public const CHECK_USER_RESPONSE_NEW = 'new';

    /** @var string Повторный пользователь, который отдавали при проверке checkDouble */
    public const CHECK_USER_RESPONSE_REPEAT = 'repeat';

    /** @var string Пользователь отклонен, который отдавали при проверке checkDouble */
    public const CHECK_USER_RESPONSE_CANCEL = 'cancel';

    /**
     * Статус пользователя повторный, который отдавали для заявки PING3
     */
    public const PING3_USER_STATUS = 'ping3_user_status';

    /**
     * Utm метка партнерского апи
     */
    public const UTM_TERM = 'partner-api';

    /**
     * @var string Заявка создана от партнера (например, bankiru):
     */
    public const ORDER_FROM_PARTNER = 'order_from_partner';

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
     * Новый клиент, не было в базе
     */
    public const USER_TYPE_NEW = 0;

    /**
     * Повторный клиент, был в базе
     */
    public const USER_TYPE_REPEAT = 1;

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

    /**
     * Возвращает utm партнера, если заявка cross_order или crm_auto_approve от ping3
     *
     * @param int $order_id
     * @return false|string
     */
    public function getPing3AutoOrderUtmSource(int $order_id)
    {
        $utm_source = $this->order_data->read($order_id, $this->ping3_data::ORDER_FROM_PARTNER);
        if ($utm_source) {
            // Проверим и обработаем процесс изменения автозаявки ping3
            $crm_auto_approve_order_id = $this->order_data->read($order_id, self::PING3_CRM_AUTO_APPROVE);
            if (!empty($crm_auto_approve_order_id)) {
                return $utm_source;
            }

            // Проверим и обработаем процесс изменения cross_order ping3
            $cross_order_order_id = $this->order_data->read($order_id, self::PING3_CRM_CROSS_ORDER);
            if (!empty($cross_order_order_id)) {
                return $utm_source;
            }
        }

        return false;
    }
}
