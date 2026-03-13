<?php

require_once 'Simpla.php';

/**
 * Дополнительные поля для заявок
 *
 * s_user_data
 */
class UserData extends Simpla
{

    const SHOW_EXTRA_DOCS = 'show_extra_docs';

    /** @var string Старый номер телефона (до смены номера телефона) */
    public const OLD_PHONES = 'old_phones';

    /** @var string Решение по IDX принадлежит ли номер телефона клиенту */
    public const IDX_DECISION = 'idx_decision';

    /** @var string Последний applicationId в акси, по которому проверяли IDX (сверка номера телефона с ФИО + дата рождения)  */
    public const IDX_DECISION_AXI_APPLICATION_ID = 'idx_decision_axi_application_id';

    /** @var string Кол-во попыток сменить номер телефона  */
    public const ATTEMPTS_AMOUNT_TO_CHANGE_PHONE = 'attempts_amount_to_change_phone';

    /** @var string Тестовый клиент*/
    public const TEST_USER = 'test_user';

    /**
     *  Признак проданности клиента партнёру - ключ `is_rejected_nk` в `user_data`:
     *  - `null` - Решение ещё не принималось (В том числе для старых клиентов).
     *  - `0` - Клиент хороший, с ним работаем.
     *  - `1` - Клиент может быть продан партнёрам, с ним не работаем.
     */
    public const IS_REJECTED_NK = 'is_rejected_nk';

    /** Новый пользователь идёт по автовыдаче */
    public const AUTOCONFIRM_FLOW = 'autoconfirm_flow';

    /** Новый пользователь идёт по автовыдачи 2.0 */
    public const AUTOCONFIRM_2_FLOW = 'autoconfirm_2_flow';

    /** Активный тип автоподписания для пользователя */
    public const ACTIVE_AUTOCONFIRM_FLOW = 'active_autoconfirm_flow';

    /** Значение активного тип автоподписания 1.0 */
    public const ACTIVE_AUTOCONFIRM_FLOW_VALUE = 'AUTOCONFIRM_FLOW';

    /** Значение активного тип автоподписания 2.0 */
    public const ACTIVE_AUTOCONFIRM_2_FLOW_VALUE = 'AUTOCONFIRM_2_FLOW';

    /** @var string Флаг, что клиент привязал счет по СБП для выплаты (таблица b2p_sbp_accounts) */
    public const IS_ADDED_SBP_DURING_REGISTRATION = 'is_added_sbp_during_registration';

    /**
     * Если зарегестрирвоался через Госслуги
     */
    public const IS_ESIA_NEW_USER = 'is_esia_new_user';

    /**
     * Если зарегестрирвоался через TBankId
     */
    public const IS_TID_NEW_USER = 'is_tid_new_user';
    /*
     * Белый список - выключаем все ДОПы*/
    public const WHITELIST_DOP = 'whitelist_dop';

    /** @var string ID банка для выплат по СБП по умолчанию */
    public const DEFAULT_BANK_ID_FOR_SBP_ISSUANCE = 'default_bank_id_for_sbp_issuance';

    /**
     * @var string Ответ партнеру (например, bankiru) об импортированном пользователе:
     * new, repeat, decline (/api/RestApiPartner.php)
     */
    public const PARTNER_USER_RESPONSE = 'partner_user_response';

    /**
     * @var string Признак что пользователь перешел по ссылке
     */
    public const PING3_VISIT = 'ping3_visit';

    /**
     * Флаг, при котором мы проставляем шаг фото
     */
    const FLAG_STEP_FILES = 'scorista_step_files';

    /**
     * Флаг, при котором мы проставляем шаг работа
     */
    const FLAG_STEP_ADDITIONAL_DATA = 'scorista_step_additional_data';

    /** Флаг, открывал ли клиент договор индивидуальных условий */
    const DID_USER_OPEN_IND_USLOVIYA_DOCUMENT = 'did_user_open_ind_usloviya_document';

    /**
     * Согласие клиента на запрос в БКИ (определяется из Axi)
     * JSON: {"consent": true, "timestamp": "Y-m-d H:i:s", "order_id": int}
     * Наличие записи = согласие дано
     */
    public const BKI_CONSENT = 'bki_consent';

    /**
     * Получение доп.поля из заявки.
     *
     * Для получения значения поля используйте `$field->value`, либо `$this->user_data->read($userId, $key)`
     * @param int $userId Id заявки из s_users
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return object
     */
    public function get(?int $userId, string $key)
    {
        $query = $this->db->placehold('SELECT * FROM __user_data WHERE `user_id` = ? AND `key` = ?', $userId, $key);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Чтение доп.поля из заявки.
     *
     * Отличается от get тем, что возвращает строку, а не объект
     * @param int $userId Id заявки из s_users
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return null|string
     */
    public function read(?int $userId, string $key)
    {
        $field = $this->get($userId, $key);
        if (isset($field))
            return $field->value;
        return null;
    }

    /**
     * Получение всех доп.полей из заявки.
     *
     * Для получений значения каждого поля используйте `$field->value`, либо `$this->user_data->readAll($userId)`
     * @param int|array $usersId Id заявок(-ки) из s_users
     * @return array|false
     */
    public function getAll($usersId)
    {
        if (!is_array($usersId)) {
            $usersId = [$usersId];
        }

        $query = $this->db->placehold('SELECT * FROM __user_data WHERE `user_id` IN (?@)', $usersId);
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Чтение всех доп.полей из заявки.
     *
     * Отличается от getAll тем, что возвращает ассоциативный массив, а не список объектов
     * ```
     * $fields = $this->user_data->readAll($userId);
     * // Результат:
     * $fields = [
     *      [KEY1] => 'Value1',
     *      [KEY2] => 'Value2',
     *      ...
     * ];```
     * @param int $userId Id заявки из s_users
     * @return array
     */
    public function readAll(int $userId)
    {
        $fields = $this->getAll($userId);
        if (empty($fields))
            return [];

        $result = [];
        foreach ($fields as $field)
            $result[$field->key] = $field->value;
        return $result;
    }

    /**
     * Запись доп.поля в заявку.
     * Можно использовать как для создания новых полей, так и для обновления существующих.
     *
     * Для удаления поля установите $value = null, либо просто не указывайте его.
     * @param int $userId Id заявки из s_users
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @param null|string $value Строковое значение или null, если вы хотите удалить запись
     * @return mixed
     */
    public function set(int $userId, string $key, $value = null)
    {
        if (is_null($value)) {
            return $this->delete($userId, $key);
        }

        return $this->replace($userId, $key, $value);
    }

    private function replace(int $userId, string $key, $value)
    {
        $query = $this->db->placehold(
            "INSERT INTO __user_data (`user_id`, `key`, `value`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `value`=?",
            $userId, $key, $value, $value
        );
        return $this->db->query($query);
    }


    private function delete(int $userId, string $key)
    {
        $query = $this->db->placehold("DELETE FROM __user_data WHERE `user_id` = ? AND `key` = ?", $userId, $key);
        return $this->db->query($query);
    }

    public function isTestUser(int $userId): bool
    {
        return (bool)$this->user_data->read($userId, 'test_user');
    }
}
