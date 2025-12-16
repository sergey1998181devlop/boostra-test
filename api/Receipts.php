<?php

require_once 'Simpla.php';
require_once 'interface/BaseModel.php';

class Receipts extends Simpla implements BaseModel
{
    /**
     * id организации бустра в таблице s_organizations
     */
    public const ORGANIZATION_BOOSTRA = 1;
    public const ORGANIZATION_SPLIT_FINTEH = 5;
    public const ORGANIZATION_AKVARIUS = 6;
    public const ORGANIZATION_FINTEHMARKET = 8;
    public const ORGANIZATION_FINLAB = 11;
    public const ORGANIZATION_VIPZAIM = 12;
    public const ORGANIZATION_RZS = 13;
    public const ORGANIZATION_LORD = 15;
    public const ORGANIZATION_MOREDENEG = 17;
    public const ORGANIZATION_FRIDA = 20;

    /**
     * Штрафной КД
     */
    public const PAYMENT_TYPE_PENALTY_CREDIT_DOCTOR = 'penalty_credit_doctor';

    /**
     * Кредитный рейтинг
     */
    public const PAYMENT_TYPE_CREDIT_RATING = 'credit_rating';

    /**
     * Кредитный Доктор
     */
    public const PAYMENT_TYPE_CREDIT_DOCTOR = 'credit_doctor';

    /**
     * Мультиполис
     */
    public const PAYMENT_TYPE_MULTIPOLIS = 'multipolis';

    /**
     * Телемедецина
     */
    public const PAYMENT_TYPE_TV_MEDICAL = 'tv_medical';
    
    /**
     * Звездный Оракул
     */
    public const PAYMENT_TYPE_STAR_ORACLE = 'star_oracle';

    /**
     * Возврат по Кредитному Доктору
     */
    public const PAYMENT_TYPE_RETURN_CREDIT_DOCTOR = 'return_credit_doctor';

    /**
     * Возврат по Мультиполису
     */
    public const PAYMENT_TYPE_RETURN_MULTIPOLIS = 'return_multipolis';

    /**
     * Возврат по Телемедецине
     */
    public const PAYMENT_TYPE_RETURN_TV_MEDICAL = 'return_tv_medical';

    /**
     * Причина отказа
     */
    public const PAYMENT_TYPE_REFUSER = 'refuser';
    
    /**
     * Описания услуг
     */
    public const PAYMENT_DESCRIPTIONS = [

        // Оплата
        self::PAYMENT_TYPE_TV_MEDICAL => 'ПО «ВитаМед»',
        self::PAYMENT_TYPE_MULTIPOLIS => 'ПО «Boostra Concierge»',
        self::PAYMENT_TYPE_CREDIT_RATING => 'Услуга Кредитный рейтинг',
        self::PAYMENT_TYPE_CREDIT_DOCTOR => 'ПО «Финансовый Доктор»',

        // Штрафы ?
        self::PAYMENT_TYPE_PENALTY_CREDIT_DOCTOR => 'Услуга Кредитный Доктор',
        
        // Возвраты
        self::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR => 'Возврат за ПО «Финансовый Доктор»',
        self::PAYMENT_TYPE_RETURN_MULTIPOLIS => 'Возврат за ПО «Boostra Concierge»',
        self::PAYMENT_TYPE_RETURN_TV_MEDICAL => 'Возврат за ПО «ВитаМед»',
        self::PAYMENT_TYPE_REFUSER => 'Услуга «Узнать причину»',
        self::PAYMENT_TYPE_STAR_ORACLE => 'ПО «Звездный Оракул»',
    ];

    /**
     * Список оплат которые относятся к приходам
     * @return string[]
     */
    public static function getIncomeTypes(): array
    {
        return [
            self::PAYMENT_TYPE_CREDIT_DOCTOR,
            self::PAYMENT_TYPE_MULTIPOLIS,
            self::PAYMENT_TYPE_TV_MEDICAL,
            self::PAYMENT_TYPE_PENALTY_CREDIT_DOCTOR,
            self::PAYMENT_TYPE_CREDIT_RATING,
            self::PAYMENT_TYPE_STAR_ORACLE,
        ];
    }

    /**
     * Добавляет новый чек
     * @param array $data
     * @return mixed
     */
    public function addItem(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_receipts SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновляет данные о чеках
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateItem(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE s_receipts SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Получает чек
     * @param int $id
     * @return false|int
     */
    public function selectItemById(int $id)
    {
        $query = $this->db->placehold("SELECT * FROM s_receipts WHERE id = ?", $id);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Получает чеки по фильтрам
     * @param array $filter_data
     * @param bool $return_all
     * @return array|false|int|mixed
     */
    public function selectAll(array $filter_data, bool $return_all = true)
    {
        $where = [];
        $sql = "SELECT * FROM s_receipts WHERE 1
                 -- {{where}}";

        if (isset($filter_data['filter_is_sent'])) {
            $where[] = $this->db->placehold("is_sent = ?", (int)$filter_data['filter_is_sent']);
        }
        if (isset($filter_data['filter_not_empty_user'])) {
            $where[] = $this->db->placehold("user_id is not null");
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        if (!empty($filter_data['order_by'])) {
            $query .= PHP_EOL . "ORDER BY " . trim($filter_data['order_by']);
        }

        if (!empty($filter_data['limit'])) {
            $query .= PHP_EOL . "LIMIT " . intval($filter_data['limit']);
        }

        $this->db->query($query);

        if ($return_all) {
            return $this->db->results();
        } else {
            return $this->db->result();
        }
    }

    public function deleteItem(int $id)
    {}
}
