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

    /** @var string Тестовый клиент*/
    public const TEST_USER = 'test_user';

    /**
     * Если зарегестрирвоался через Госслуги
     */
    public const IS_ESIA_NEW_USER = 'is_esia_new_user';

    /**
     * Если зарегестрирвоался через TBankId
     */
    public const IS_TID_NEW_USER = 'is_tid_new_user';

    /** Новый пользователь идёт по автовыдачи */
    public const AUTOCONFIRM_FLOW = 'AUTOCONFIRM_FLOW';

    /** Новый пользователь идёт по автовыдачи 2.0 */
    public const AUTOCONFIRM_2_FLOW = 'AUTOCONFIRM_2_FLOW';

    /** Активный тип автоподписания для пользователя */
    public const ACTIVE_AUTOCONFIRM_FLOW = 'active_autoconfirm_flow';

    /** @var string ID банка для выплат по СБП по умолчанию */
    public const DEFAULT_BANK_ID_FOR_SBP_ISSUANCE = 'default_bank_id_for_sbp_issuance';

    /** @var string Белый список - высокорисковые клиенты, запрет автовыдачи */
    public const WHITELIST_DOP = 'whitelist_dop';

    /** Выводить ли уведомление о цессии в ЛК */
    public const SHOW_CESSION_INFO = 'show_cession_info';

    /** Выводить ли уведомление о передаче агентам (до цессии) в ЛК */
    public const SHOW_AGENT_INFO = 'show_agent_info';

    /** Флаг, открывал ли клиент договор индивидуальных условий */
    const DID_USER_OPEN_IND_USLOVIYA_DOCUMENT = 'did_user_open_ind_usloviya_document';

    /**
     * @var string Признак, что пользователь перешел по ссылке
     */
    public const PING3_VISIT = 'ping3_visit';

    /** @var int Флаг разрешено ли клиенту создавать ВКЛ*/
    public const ALLOW_TO_CREATE_RCL = 'allow_to_create_rcl';

    /** @var int Флаг скористы NO_NEED_FOR_UNDERWRITER */
    public const NO_NEED_FOR_UNDERWRITER = 'no_need_for_underwriter';

    /**
     * Получение доп.поля из заявки.
     *
     * Для получения значения поля используйте `$field->value`, либо `$this->user_data->read($userId, $key)`
     * @param int $userId Id заявки из s_users
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return object
     */
    public function get(int $userId, string $key)
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
    public function read(int $userId, string $key)
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
        if (is_null($value))
            return $this->delete($userId, $key);
        return $this->replace($userId, $key, $value);
    }

    private function replace(int $userId, string $key, $value)
    {
        $query = $this->db->placehold("REPLACE INTO __user_data (`user_id`, `key`, `value`) VALUES (?, ?, ?)", $userId, $key, $value);
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

    /**
     * Получение записей с определённым ключом и значением
     *
     * @param string $key
     * @param string $value
     * @param int $limit
     * @return array|false
     */
    public function getRecords(string $key, string $value, int $limit)
    {
        $query = $this->db->placehold(
            'SELECT user_id, `key`, `value` FROM __user_data WHERE `key` = ? AND `value` = ? ORDER BY user_id ASC LIMIT ?',
            $key,
            $value,
            $limit,
        );
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Массовое обновление записей
     * @param array $records Массив ['user_id' => ..., 'key' => ...]
     * @param int $value Новое значение
     */
    public function updateRecords(array $records, int $value): void
    {
        if (empty($records)) {
            return;
        }

        $conditions = [];
        $params = [];

        foreach ($records as $record) {
            $conditions[] = "(?, ?)";
            $params[] = $record['user_id'];
            $params[] = $record['key'];
        }

        if (empty($conditions)) {
            return;
        }

        $whereClause = implode(',', $conditions);

        // Используем placehold для подготовки запроса
        $query = $this->db->placehold("
            UPDATE __user_data SET `value` = ?
            WHERE (user_id, `key`) IN ($whereClause)
        ", $value, ...$params);

        $this->db->query($query);
    }

    /**
     * Массовое удаление записей
     * @param array $records Массив ['user_id' => ..., 'key' => ..., 'value' => ...]
     */
    public function deleteRecords(array $records): void
    {
        if (empty($records)) {
            return;
        }

        $conditions = [];
        $params = [];

        foreach ($records as $record) {
            $conditions[] = "(?, ?, ?)";
            $params[] = $record['user_id'];
            $params[] = $record['key'];
            $params[] = $record['value'];
        }

        if (empty($conditions)) {
            return;
        }

        $whereClause = implode(',', $conditions);

        $query = $this->db->placehold("
            DELETE FROM __user_data
            WHERE (user_id, `key`, `value`) IN ($whereClause)
        ", ...$params);

        $this->db->query($query);
    }
}
