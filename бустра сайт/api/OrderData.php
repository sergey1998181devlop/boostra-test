<?php

require_once 'Simpla.php';

/**
 * Дополнительные поля для заявок
 *
 * s_order_data
 */
class OrderData extends Simpla
{
    /** @var string Данные о браузере и системе пользователя для JuicyScore */
    public const USERAGENT = 'USERAGENT';
    public const HTTP_REFERER = 'REFERER';

    /** @var string Согласие на переуступку права требования, 0 согласен, 1 не согласен */
    public const AGREE_CLAIM_VALUE = 'AGREE_CLAIM_VALUE';

    /** @var string Флаг безопасного флоу при выдаче займа, 1 безопасное, 0 опасное */
    public const SAFETY_FLOW = 'SAFETY_FLOW';

    /* WARNING: В методах get_order_by_1c, get_order и get_crm_order в api/Orders  Настройки ДОПов изменены:  0 отключен, 1 включен, чтобы во фронте было удобно обрабатывать */
    /** @var string Вита-мед при пролонгации, 1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_TV_MED = 'additional_service_tv_med';
    /** @var string Консьерж при пролонгации,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_MULTIPOLIS = 'additional_service_multipolis';
    /** @var string Доп. услуга на частичном закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_PARTIAL_REPAYMENT = 'additional_service_partial_repayment';
    /** @var string Доп. услуга на закрытии,1 отключен, 0 включен */

    public const ADDITIONAL_SERVICE_REPAYMENT = 'additional_service_repayment';
    /** @var string Доп. услуга при выдаче, 1 отключен, 0 включен */
    public const DISABLE_ADDITIONAL_SERVICE_ON_ISSUE = 'disable_additional_service_on_issue';


    /** @var string Был ли куплен отказником причина (узнать причину) , 1-куплен */
    public const PAYMENT_REFUSER = 'payment_refuser';

    /** @var string Доп. услуга на закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_REPAYMENT = 'half_additional_service_repayment';

    /** @var string  Доп. услуга на частичном закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT = 'half_additional_service_partial_repayment';

    /** @var string Звездный Оракул(SO) на закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_SO_REPAYMENT = 'additional_service_so_repayment';
    /** @var string Звездный Оракул(SO) на закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_SO_REPAYMENT = 'half_additional_service_so_repayment';

    /** @var string Звездный Оракул(SO) на частичном закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT = 'additional_service_so_partial_repayment';
    /** @var string Звездный Оракул(SO) на частичном закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT = 'half_additional_service_so_partial_repayment';

    /** @var string Доп. услуга на закрытии,0 отключен, 1 включен */
    public const ADDITIONAL_SERVICE_DEFAULT_VALUE = 1;

    /** @var string Данные от банки ру после отправленной заявки */
    public const SENT_TO_BANKI_RU = 'sent_to_banki_ru';

    /** @var string Ссылка для редиректа в Вonondo */
    public const BONONDO_CLIENT_URL = "bonondoClientUrl";
    public const BONONDO_REDIRECT_COUNT = "bonondoRedirectCount";

    /* @var string К одобренному займу привязали новую карту */
    public const IS_NEW_CARD_LINKED = "is_new_card_linked";

    /** @var string Запуск ```$this->leadgid->reject_actions($order_id)``` уже проводился */
    public const HAS_REJECT_ACTIONS = 'has_reject_actions';

    /** @var string Количество часов, которое заявка находилась в статусе охлаждения(17) */
    public const HOURS_IN_COOLING = 'hours_in_cooling_period';

    /**
     * АСП при подписании на кнопке погасить заём полностью
     */
    public const REPEAT_ORDER_AUTO_CONFIRM_ASP = 'repeat_order_auto_confirm_asp';

    /** @var string Результат переключения организации */
    public const ORDER_ORG_SWITCH_RESULT = 'order_org_switch_result';

    /** @var string order_id исходной заявки при смене организации */
    public const ORDER_ORG_SWITCH_PARENT_ORDER_ID = 'order_org_switch_parent_order_id';

    /** @var int Флаг, чтобы в акси не запрашивались ССП и КИ отчеты */
    public const AXI_WITHOUT_CREDIT_REPORTS = 'axi_without_credit_reports';

    public const RCL_LOAN = 'rcl_loan';
    public const RCL_AMOUNT = 'rcl_amount';
    public const RCL_MAX_AMOUNT = 'rcl_max_amount';
    /** @var integer s_visitors айди входа клиента перед подачей заявки */
    public const VISITOR_ID = 'visitor_id';

    public const ADDITIONAL_SERVICES = [
        self::ADDITIONAL_SERVICE_TV_MED,
        self::ADDITIONAL_SERVICE_MULTIPOLIS,
        self::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
        self::ADDITIONAL_SERVICE_REPAYMENT,
        self::HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
        self::HALF_ADDITIONAL_SERVICE_REPAYMENT,
        self::ADDITIONAL_SERVICE_SO_REPAYMENT,
        self::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
        self::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT,
        self::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
    ];

    /** @var string Флаг нужно ли авто-подтверждение для авто-одобренной заявки */
    public const NEED_AUTO_CONFIRM = 'need_auto_confirm';

    /** @var string Решение по наличию у клиента самозапрета перед выдачей */
    public const SELF_DEC_DECISION = 'self_dec_decision';

    /** @var string Последний applicationId в акси, по которому проверяли, есть ли у клиента перед выдачей самозапрет */
    public const SELF_DEC_AXI_APPLICATION_ID = 'self_dec_axi_application_id';
    /** @var integer Код АСП для автоподписания */
    public const AUTOCONFIRM_ASP = 'autoconfirm_asp';
    /** @var integer Код АСП для автоподписания кросс-ордера */
    public const AUTOCONFIRM_ASP_CROSS = 'autoconfirm_asp_cross';
    /** @var integer Флаг, указывающий, что выполнилась автовыдача кросс-заявка  */
    public const IS_AUTOCONFIRM_CROSS = 'is_autoconfirm_cross';
    /** @var string Флаг "Кредитный доктор" при автоподписании основной заявки */
    public const AUTOCONFIRM_CREDIT_DOCTOR = 'is_user_credit_doctor_autoconfirm';
    /** @var string Флаг "Телемедицина" при автоподписании основной заявки */
    public const AUTOCONFIRM_TV_MEDICAL = 'is_tv_medical_autoconfirm';
    
    /** @var string Флаг "Кредитный доктор" при автоподписании кросс-ордера */
    public const AUTOCONFIRM_CROSS_CREDIT_DOCTOR = 'is_user_credit_doctor_cross_autoconfirm';
    /** @var string Флаг "Телемедицина" при автоподписании кросс-ордера */
    public const AUTOCONFIRM_CROSS_TV_MEDICAL = 'is_tv_medical_cross_autoconfirm';
    
    /** @var integer Сумма запрошенная клиентом при подаче заявки*/
    public const USER_AMOUNT = 'user_amount';

    /**
     * Источник ответа скористы. Возможные значения:
     * - `crm` - Скориста проводилась в CRM, ответ скористы не был получени из АксиНБКИ.
     * - `aksi` - Скориста проводилась в Акси, получили оттуда её результат и загрузили в СРМ.
     * @var string
     */
    public const SCORISTA_SOURCE = 'scorista_source';

    /** @var string ID банка для выплаты по СБП */
    public const BANK_ID_FOR_SBP_ISSUANCE = 'bank_id_for_sbp_issuance';

    /** @var string Полученна от акси рекомендуемая сумма, используется когда скориста дала отказ по заявке */
    public const FAKE_SCORISTA_AMOUNT = 'fake_scorista_amount';

    /**
     * @var string Заявка создана от партнера (например, bankiru):
     */
    public const ORDER_FROM_PARTNER = 'order_from_partner';

    /**
     * Статус пользователя, который отдавали для заявки
     */
    public const PING3_USER_STATUS = 'ping3_user_status';

    /** @var integer Сумма одобрения из скоринга hyper_c (таблица s_hyper_c.approve_amount) */
    public const HYPER_C_APPROVE_AMOUNT = 'hyper_c_approve_amount';
    /** @var integer Кол-во попыток перевыдачи заявки (см. Orders.php::canRepeatIssuanceNotIssuedOrder) */
    public const REPEAT_ISSUANCE_COUNT = 'repeat_issuance_count';
    /** Время создания виртуальной карты */
    public const CREATED_AT_VIRTUAL_CARD_TIMESTAMP = 'waiting_virtual_card_created_at_timestamp';

    /**
     * Получение доп.поля из заявки.
     *
     * Для получения значения поля используйте `$field->value`, либо `$this->order_data->read($orderId, $key)`
     * @param int $orderId Id заявки из s_orders
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return object
     */
    public function get(int $orderId, string $key)
    {
        $query = $this->db->placehold('SELECT * FROM __order_data WHERE `order_id` = ? AND `key` = ?', $orderId, $key);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Чтение доп.поля из заявки.
     *
     * Отличается от get тем, что возвращает строку, а не объект
     * @param int $orderId Id заявки из s_orders
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return null|string
     */
    public function read(int $orderId, string $key)
    {
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
    public function getAll(int $orderId)
    {
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
    public function readAll(int $orderId)
    {
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
    public function set(int $orderId, string $key, $value = null)
    {
        if (is_null($value))
            return $this->delete($orderId, $key);
        return $this->replace($orderId, $key, $value);
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

    private function replace(int $orderId, string $key, $value)
    {
        $valueHash = md5($value, true);
        $query = $this->db->placehold("REPLACE INTO __order_data (`order_id`, `key`, `value`, `value_hash`) VALUES (?, ?, ?, ?)", $orderId, $key, $value, $valueHash);
        return $this->db->query($query);
    }

    private function delete(int $orderId, string $key)
    {
        $query = $this->db->placehold("DELETE FROM __order_data WHERE `order_id` = ? AND `key` = ?", $orderId, $key);
        return $this->db->query($query);
    }

    /**
     * @param $orderId
     * @return array
     */
    public function getAdditionalServices($orderId): array
    {
        $query = $this->db->placehold('SELECT * FROM __order_data WHERE `order_id` = ? and `key` in (?@)', $orderId, self::ADDITIONAL_SERVICES);
        $this->db->query($query);
        $resultDb = $this->db->results() ?? [];

        $result = [];
        foreach ($resultDb as $item) {
            $result[$item->key] = $item->value;
        }
        return $result;
    }

    /**
     * Получение количества заявок с доп.полем из данных
     *
     * @param array $orderIds Массив Id заявок из s_orders
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return object
     */
    public function countByKeyAndOrder(array $orderIds, string $key)
    {
        $query = $this->db->placehold('SELECT * FROM __order_data WHERE `order_id` IN (?@) AND `key` = ?', array_map('intval', (array) $orderIds), $key);
        $this->db->query($query);

        return $this->db->num_rows();
    }
}
