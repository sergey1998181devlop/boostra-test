<?php

require_once 'Simpla.php';

/**
 * Дополнительные поля для заявок
 *
 * s_order_data
 */
class OrderData extends Simpla {
    /** @var string Данные о браузере и системе пользователя для JuicyScore */
    public const USERAGENT = 'USERAGENT';

    /** @var string Согласие на переуступку права требования, 0 согласен, 1 не согласен */
    public const AGREE_CLAIM_VALUE = 'AGREE_CLAIM_VALUE';
    /** @var string Вита-мед при пролонгации, 1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_TV_MED = 'additional_service_tv_med';
    /** @var string Консьерж при пролонгации,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_MULTIPOLIS = 'additional_service_multipolis';
    /** @var string Доп. услуга на частичном закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_PARTIAL_REPAYMENT = 'additional_service_partial_repayment';
    /** @var string  Доп. услуга на частичном закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT = 'half_additional_service_partial_repayment';
    /** @var string Доп. услуга на закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_REPAYMENT = 'additional_service_repayment';
    /** @var string Доп. услуга при выдаче, 1 отключен, 0 включен */
    public const DISABLE_ADDITIONAL_SERVICE_ON_ISSUE = 'disable_additional_service_on_issue';
    /** @var string Доп. услуга на закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_REPAYMENT = 'half_additional_service_repayment';
    /** @var string Звездный Оракул(SO) на закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_SO_REPAYMENT = 'additional_service_so_repayment';
    /** @var string Звездный Оракул(SO) на закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_SO_REPAYMENT = 'half_additional_service_so_repayment';
    /** @var string Звездный Оракул(SO) на частичном закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT = 'additional_service_so_partial_repayment';
    /** @var string Звездный Оракул(SO) на частичном закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT = 'half_additional_service_so_partial_repayment';
    /** @var integer Код АСП для автоподписания */
    public const AUTOCONFIRM_ASP = 'autoconfirm_asp';
    /** @var integer Код АСП для автоподписания кросс-ордера */
    public const AUTOCONFIRM_ASP_CROSS = 'autoconfirm_asp_cross';
    /** @var integer Флаг, указывающий, что выполнилась автовыдача кросс-заявка  */
    public const IS_AUTOCONFIRM_CROSS = 'is_autoconfirm_cross';
    /** @var integer Сумма запрошенная клиентом при подаче заявки*/
    public const USER_AMOUNT = 'user_amount';
    /** @var integer Заявка одобрена автоматически по скорингу */
    public const SCOR_APPROVE = 'scor_approve';

    /** @var string отсрочка платежа ,1 одобрен, 0 отклонен */
    public const PAYMENT_DEFERMENT = 'payment_deferment';

    /** @var integer s_visitors айди входа клиента перед подачей заявки */
    public const VISITOR_ID = 'visitor_id';

    /**
     * Источник ответа скористы. Возможные значения:
     * - `crm` - Скориста проводилась в CRM, ответ скористы не был получени из АксиНБКИ.
     * - `aksi` - Скориста проводилась в Акси, получили оттуда её результат и загрузили в СРМ.
     * @var string
     */
    public const SCORISTA_SOURCE = 'scorista_source';

    /**
     * АСП при подписании на кнопке погасить и взять новый заем
     */
    public const REPEAT_ORDER_AUTO_CONFIRM_ASP = 'repeat_order_auto_confirm_asp';

    public const ADDITIONAL_SERVICE_KEYS = [
        self::ADDITIONAL_SERVICE_TV_MED,
        self::ADDITIONAL_SERVICE_MULTIPOLIS,
        self::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
        self::HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
        self::ADDITIONAL_SERVICE_REPAYMENT,
        self::DISABLE_ADDITIONAL_SERVICE_ON_ISSUE,
        self::HALF_ADDITIONAL_SERVICE_REPAYMENT,
        self::ADDITIONAL_SERVICE_SO_REPAYMENT,
        self::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT,
        self::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
        self::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
    ];

    public const PDN_CALCULATION_DATE = 'pdn_date';

    /** @var string Флаг нужно ли авто-подтверждение для авто-одобренной заявки */
    public const NEED_AUTO_CONFIRM = 'need_auto_confirm';

    /** @var string Увеличенная сумма для автоповтора при высоком балле скористы */
    public const INCREASED_ORDER_AMOUNT_FOR_AUTORETRY = 'increased_order_amount_for_autoretry';

    /** @var string Запуск ```$this->leadgid->reject_actions($order_id)``` уже проводился */
    public const HAS_REJECT_ACTIONS = 'has_reject_actions';

    /** @var string Отключение проверки ССП и КИ отчетов для заявки */
    public const DISABLE_CHECK_REPORTS_FOR_LOAN = 'disable_check_reports_for_loan';

    /** @var integer Сумма одобрения из скоринга hyper_c (таблица s_hyper_c.approve_amount) */
    public const HYPER_C_APPROVE_AMOUNT = 'hyper_c_approve_amount';

    /** @var string ID банка для выплаты по СБП для автозаявок, у которых установлена карта */
    public const BANK_ID_FOR_SBP_ISSUANCE = 'bank_id_for_sbp_issuance';

    /** @var string Количество часов, которое заявка находилась в статусе охлаждения(17) */
    public const HOURS_IN_COOLING = 'hours_in_cooling_period';

    /** @var string Полученна от акси рекомендуемая сумма, используется когда скориста дала отказ по заявке */
    public const FAKE_SCORISTA_AMOUNT = 'fake_scorista_amount';

    /** @var string Флаг решение по заявке с учетом скоринга hyper_c или нет */
    public const IS_ORDER_DECISION_WITH_HYPER_C = 'is_order_decision_with_hyper_c';

    /** @var string Результат переключения организации */
    public const ORDER_ORG_SWITCH_RESULT = 'order_org_switch_result';

    /** @var string order_id исходной заявки при смене организации */
    public const ORDER_ORG_SWITCH_PARENT_ORDER_ID = 'order_org_switch_parent_order_id';

    /** @var string Кол-во попыток расчета ПДН непосредственно перед выдачей для проверки вхождения в МПЛ */
    public const PDN_CALCULATION_ATTEMPTS = 'pdn_calculation_attempts';

    /** @var int Флаг, чтобы в акси не запрашивались ССП и КИ отчеты */
    public const AXI_WITHOUT_CREDIT_REPORTS = 'axi_without_credit_reports';

    /** Флаг, открывал ли клиент договор индивидуальных условий */
    const DID_USER_OPEN_IND_USLOVIYA_DOCUMENT = 'did_user_open_ind_usloviya_document';

    /** @var string Флаг перевода заявки в ВКЛ */
    public const RCL_LOAN = 'rcl_loan';

    /** @var string Рекомендуемая сумма выдачи в ВКЛ, полученная при сравнении суммы скористы и суммы из сервиса ПДН (без учета допов) */
    public const RCL_AMOUNT = 'rcl_amount';

    /** @var string Максимальная сумма выдачи в ВКЛ, полученная из сервиса ПДН (с учетом допов) */
    public const RCL_MAX_AMOUNT = 'rcl_max_amount';

    /** @var boolean Первый транш ВКЛ-контракта */
    public const RCL_FIRST_TRANCHE = 'rcl_first_tranche';

    /** @var string Самозанятый по заявке (0 или 1), может отсутствовать в s_order_data */
    public const SELF_EMPLOYEE_ORDER = 'self_employee_order';

    /** Время создания виртуальной карты */
    public const CREATED_AT_VIRTUAL_CARD_TIMESTAMP = 'waiting_virtual_card_created_at_timestamp';

    /** Увеличение суммы по заявке для клиентов с флагом NO_NEED_FOR_UNDERWRITER */
    public const INCREASE_AMOUNT_FOR_NNU = 'increase_amount_for_nnu';

    /** @var string Ссылка на заявку с положительной сокристой с другим site_id */
    public const LINK_ORDER_SCORISTA = 'link_order_scorista';

    /**
     * Получение доп.поля из заявки.
     *
     * Для получения значения поля используйте `$field->value`, либо `$this->order_data->read($orderId, $key)`
     * @param int $orderId Id заявки из s_orders
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return object
     */
    public function get(int $orderId, string $key) {
        $query = $this->db->placehold('SELECT * FROM __order_data WHERE `order_id` = ? AND `key` = ?', $orderId, $key);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получение доп.полей для нескольких заявок
     *
     * @param array $orderIds Массив из Id заявок
     * @param string $keys Нужные ключи. Если оставить пустым - ищем все ключи
     * @return array Массив строк из s_order_data
     * @throws Exception
     * @see get
     */
    public function getMany(array $orderIds, array $keys = []) {
        if (empty($orderIds))
            return [];

        if (is_array($orderIds[0]))
            throw new Exception('$orderIds должен содержать список из id, а не сами заявки.');

        if (empty($keys))
            $query = $this->db->placehold('SELECT * FROM __order_data WHERE `order_id` IN (?@)', $orderIds);
        else
            $query = $this->db->placehold('SELECT * FROM __order_data WHERE `order_id` IN (?@) AND `key` IN (?@)', $orderIds, $keys);

        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Чтение доп.поля из заявки.
     *
     * Отличается от get тем, что возвращает строку, а не объект
     * @param int $orderId Id заявки из s_orders
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return null|string
     */
    public function read(int $orderId, string $key) {
        $field = $this->get($orderId, $key);
        if (isset($field))
            return $field->value;
        return null;
    }

    /**
     * Получение всех доп.полей из заявки.
     *
     * Для получений значения каждого поля используйте `$field->value`, либо `$this->order_data->readAll($orderId)`
     * @param int $orderId Id заявки из s_orders
     * @return array|false
     */
    public function getAll(int $orderId) {
        $query = $this->db->placehold('SELECT * FROM __order_data WHERE `order_id` = ?', $orderId);
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Чтение всех доп.полей из заявки.
     *
     * Отличается от getAll тем, что возвращает ассоциативный массив, а не список объектов
     * ```
     * $fields = $this->order_data->readAll($orderId);
     * // Результат:
     * $fields = [
     *      [KEY1] => 'Value1',
     *      [KEY2] => 'Value2',
     *      ...
     * ];```
     * @param int $orderId Id заявки из s_orders
     * @return array
     */
    public function readAll(int $orderId) {
        $fields = $this->getAll($orderId);
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
     * @param int $orderId Id заявки из s_orders
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @param null|string $value Строковое значение или null, если вы хотите удалить запись
     * @return mixed
     */
    public function set(int $orderId, string $key, $value = null) {
        if (is_null($value))
            return $this->delete($orderId, $key);
        return $this->replace($orderId, $key, $value);
    }

    private function replace(int $orderId, string $key, $value) {
        $valueHash = md5($value, true);
        $query = $this->db->placehold("REPLACE INTO __order_data (`order_id`, `key`, `value`, `value_hash`) VALUES (?, ?, ?, ?)", $orderId, $key, $value, $valueHash);
        return $this->db->query($query);
    }

    /**
     * Поиск OrderData по ключу и значению, может вернуть несколько совпадений.
     *
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @param mixed $value Не может быть null
     * @return array
     * @throws Exception
     * @see self::get()
     */
    public function getByValue(string $key, $value): array
    {
        if (is_null($value)) {
            throw new Exception("Parameter 'value' for getByValue cannot be null.");
        }

        $this->db->query(
            "SELECT * FROM __order_data WHERE `key` = ? AND value_hash = UNHEX(MD5(?)) AND `value` = ?",
            $key,
            $value, // value_hash с индексом
            $value  // Повторная фильтрация по value, избегаем коллизий md5 хэша (маловероятное событие)
        );

        return $this->db->results() ?: [];
    }

    /**
     * Проверяет и отключает все активные доп.услуги по заявке.
     * Каждое изменение записывается в changelog.
     *
     * @param int $orderId ID заявки
     * @param int $clientId ID клиента
     * @param int $managerId ID менеджера
     */
    public function disableAdditionalServices(int $orderId, int $clientId, int $managerId): array {
        $orderData = $this->readAll($orderId);
        $disabledKeys = [];

        foreach (self::ADDITIONAL_SERVICE_KEYS as $key) {
            $currentValue = $orderData[$key] ?? null;

            if ((string)$currentValue !== '1') {
                $this->set($orderId, $key, '1');

                $this->changelogs->add_changelog([
                    'manager_id' => $managerId,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => $key,
                    'old_values' => 'Включение',
                    'new_values' => 'Выключение',
                    'user_id' => $clientId,
                    'order_id' => $orderId,
                ]);

                $disabledKeys[] = $key;
            }
        }

        if (!empty($disabledKeys)) {
            $message = "Отключены доп.услуги: <strong>" . implode(', ', $disabledKeys) . "</strong> по заявке $orderId";

            $this->notificationsManagers->sendNotification([
                'from_user' => $clientId,
                'to_user' => $managerId,
                'subject' => 'Отключение доп.услуг',
                'message' => $message
            ]);
        }
        
        return $disabledKeys;
    }

    private function delete(int $orderId, string $key) {
        $query = $this->db->placehold("DELETE FROM __order_data WHERE `order_id` = ? AND `key` = ?", $orderId, $key);
        return $this->db->query($query);
    }
    /**
     * Получение записей
     *
     * @param string $key
     * @param string $value
     * @param int $limit
     * @return array|false
     */
    public function getRecords(string $key, string $value, int $limit)
    {
        $query = $this->db->placehold(
            'SELECT order_id, `key`, `value` FROM __order_data WHERE `key` = ? AND `value` = ? ORDER BY order_id ASC LIMIT ?',
            $key,
            $value,
            $limit,
        );
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Массовое обновление записей
     * @param array $records Массив ['order_id' => ..., 'key' => ...]
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
            $params[] = $record['order_id'];
            $params[] = $record['key'];
        }

        if (empty($conditions)) {
            return;
        }

        $whereClause = implode(',', $conditions);

        $query = $this->db->placehold("
            UPDATE __order_data SET `value` = ?
            WHERE (order_id, `key`) IN ($whereClause)
        ", $value, ...$params);

        $this->db->query($query);
    }

    /**
     * Массовое удаление записей
     * @param array $records Массив ['order_id' => ..., 'key' => ..., 'value' => ...]
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
            $params[] = $record['order_id'];
            $params[] = $record['key'];
            $params[] = $record['value'];
        }

        if (empty($conditions)) {
            return;
        }

        $whereClause = implode(',', $conditions);

        $query = $this->db->placehold("
            DELETE FROM __order_data
            WHERE (order_id, `key`, `value`) IN ($whereClause)
        ", ...$params);

        $this->db->query($query);
    }
}
