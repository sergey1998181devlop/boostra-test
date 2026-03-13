<?php

require_once (dirname(__DIR__) . '/api/Simpla.php');


/**
 * Класс для работы с телемедициной
 * Class TVMedical
 */
class TVMedical extends Simpla
{
    public const ISSUANCE_AMOUNT = 350;
    
    /**
     * Статус новой попытки списания средств за телемедицину
     */
    const TV_MEDICAL_PAYMENT_STATUS_NEW = 'NEW';

    /**
     * Статус оплаченной транзакции за телемедицину
     */
    const TV_MEDICAL_PAYMENT_STATUS_SUCCESS = 'SUCCESS';

    /**
     * Минимальный scorista_ball для бесплатной телемедицины
     */
    public const SCORISTA_BALL_FREE_THRESHOLD = 750;

    /**
     * Получает все тарифы по телемедицине
     * @return array|false
     */
    public function getAllTariffs()
    {
        $query = "SELECT * FROM __vita_med_conditions";
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * Получает тариф телемедицины
     * @param int $id
     * @return false|int
     */
    public function getTvMedicalById(int $id)
    {
        $query = $this->db->placehold("SELECT * FROM s_tv_medical_payments WHERE id = ?", $id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получает тариф телемедицины
     * @param int $amount
     * @return false|int
     */
    public function getClosestTvMedicalPriceByAmount(int $amount)
    {
        $query = $this->db->placehold("SELECT * FROM __vita_med_conditions WHERE price <= ? order by price desc ", $amount);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Добавляет оплату по телемедицине
     * @param array $data
     * @return mixed
     */
    public function addPayment(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_tv_medical_payments SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Добавляет пользователя по телемедицине
     * @param array $data
     * @return mixed
     */
    public function addTvMedicalUser(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_tv_medical_users SET ?%", $data);
        return $this->db->query($query);
    }

    /**
     * Получает пользователя телемедецины
     * @param int $user_id
     * @return false|int
     */
    public function getTvMedicalUser(int $user_id)
    {
        $query = $this->db->placehold("SELECT * FROM s_tv_medical_users WHERE user_id = ?", $user_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Обновляет оплату по телемедицине
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updatePayment(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE s_tv_medical_payments SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Выбирает оплаты по телемедицине
     * @param array $filter_data
     * @param bool $return_all
     * @return array|false|int
     */
    public function selectPayments(array $filter_data, bool $return_all = true)
    {
        $where = [];
        $sql = "SELECT * FROM s_tv_medical_payments WHERE 1
                 -- {{where}}";

        if (!empty($filter_data['filter_payment_id'])) {
            $where[] = $this->db->placehold("payment_id = ?", (int)$filter_data['filter_payment_id']);
        }

        if (!empty($filter_data['filter_payment_method'])) {
            $where[] = $this->db->placehold("payment_method = ?", $this->db->escape($filter_data['filter_payment_method']));
        }

        if (isset($filter_data['filter_user_id'])) {
            $where[] = $this->db->placehold("user_id = ?", (int)$filter_data['filter_user_id']);
        }

        if (isset($filter_data['filter_order_id'])) {
            $where[] = $this->db->placehold("order_id = ?", (int)$filter_data['filter_order_id']);
        }

        if (isset($filter_data['filter_status'])) {
            $where[] = $this->db->placehold("status = ?", $this->db->escape($filter_data['filter_status']));
        }

        if (isset($filter_data['filter_sent_to_api'])) {
            $where[] = $this->db->placehold("sent_to_api = ?", (int)$filter_data['filter_sent_to_api']);
        }

        if( isset( $filter_data['filter_refunded'] ) ){
            $where[] = $filter_data['filter_refunded']
                ? $this->db->placehold('amount = return_amount')
                : $this->db->placehold('amount > return_amount');
        }
        
        /**
         * Время жизни телемидицины
         */
        if (isset($filter_data['filter_limit_live_days'])) {
            $where[] = $this->db->placehold("datediff(NOW(), date_added) <= ?", (int)$filter_data['filter_limit_live_days']);
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);

        if ($return_all) {
            return $this->db->results();
        } else {
            return $this->db->result();
        }
    }

    /**
     * Получает тариф по фильтру
     *
     * @param array $filter_data
     * @return array|null
     */
    public function getTariffByFilter(array $filter_data)
    {
        $where = [];
        $sql = "SELECT * FROM s_vita_med_conditions WHERE 1
                 -- {{where}}";

        if (!empty($filter_data['filter_tariff'])) {
            $where[] = $this->db->placehold("tariff = ?", $this->db->escape($filter_data['filter_tariff']));
        }

        if (!empty($filter_data['filter_amount'])) {
            $where[] = $this->db->placehold("? BETWEEN from_amount AND to_amount", (int)$filter_data['filter_amount']);
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Генерируем документы в ЛК по телемеду
     * @param $user
     * @param $send_payment
     * @param int $order_id
     * @param int|null $organization_id
     * @return void
     */
    public function generatePayDocs($user, $send_payment, int $order_id, $organization_id = null, $tvmed_key = null)
    {
        $condition = $this->getVitamedConditionByAmount($send_payment->amount);
        $licenseKeyDays = $condition ? $condition->license_key_days : null;

        $params = [
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'patronymic' => $user->patronymic,
            'birth' => $user->birth,
            'passport_serial' => $user->passport_serial,
            'passport_issued' => $user->passport_issued,
            'Regregion' => $user->Regregion,
            'Regcity' => $user->Regcity,
            'Regstreet' => $user->Regstreet,
            'Regbuilding' => $user->Regbuilding,
            'Reghousing' => $user->Reghousing,
            'Regroom' => $user->Regroom,
            'Faktregion' => $user->Faktregion,
            'Faktdistrict' => $user->Faktdistrict,
            'Faktcity' => $user->Faktcity,
            'Faktstreet' => $user->Faktstreet,
            'Faktbuilding' => $user->Faktbuilding,
            'Fakthousing' => $user->Fakthousing,
            'Faktroom' => $user->Faktroom,
            'phone_mobile' => $user->phone_mobile,
            'email' => $user->email,
            'products' => [
                [
                    'name' => 'Телемедицина',
                    'price' => $send_payment->tv_medical->amount,
                    'date_start' => date('d.m.Y', strtotime('+ 16 day')),
                    'date_pay' => date('d.m.Y'),
                ]
            ],
            'date_pay' => date('d.m.Y H:i:s'),
            'asp_code' => $send_payment->asp,
            'license_key' => $tvmed_key,
            'license_key_days' => $licenseKeyDays,
        ];

        $this->documents->create_document(
            [
                'type' => $this->documents::ACCEPT_TELEMEDICINE,
                'user_id' => $user->id,
                'order_id' => $order_id,
                'contract_number' => $send_payment->contract_number,
                'params' => $params,
                'organization_id' => $organization_id,
            ]
        );

        $this->documents->create_document(
            [
                'type' => $this->documents::CONTRACT_TV_MEDICAL,
                'user_id' => $user->id,
                'order_id' => $order_id,
                'contract_number' => $send_payment->contract_number,
                'params' => $params,
                'organization_id' => $organization_id,
            ]
        );
    }

    public function getVitamedConditionByAmount($amount, $is_new = true)
    {
        if (!$amount) {
            return null;
        }

        $query = $this->db->placehold("
            SELECT * 
            FROM __vita_med_conditions 
            WHERE is_new = ? 
              AND from_amount <= ? 
              AND to_amount >= ?
            ORDER BY from_amount ASC
            LIMIT 1
        ", $is_new, $amount, $amount);

        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получает тариф исходя из суммы займа
     *
     * @param int $amount
     * @param bool $is_new_client
     * @return false|int
     */
    public function getVItaMedPrice(int $amount, bool $is_new_client = true)
    {
        $query = $this->db->placehold(
            'SELECT id, price FROM __vita_med_conditions 
                 WHERE is_new      = ?
                    AND from_amount <= ?
                    AND to_amount   >= ?
                 ORDER BY to_amount ASC LIMIT 1',
            $is_new_client,
            $amount - 1,
            $amount - 1
        );
        $this->db->query($query);
        return $this->db->result();
    }


    /**
     * Получает тариф исходя из суммы займа
     *
     * @param int $price
     * @param bool $is_new_client
     * @return false|int
     */
    public function getVitaMedByPrice(int $price, bool $is_new_client = true)
    {
        $query = $this->db->placehold(
            'SELECT id, price FROM __vita_med_conditions WHERE is_new = ? AND price = ? ORDER BY id ASC LIMIT 1',
            $is_new_client,
            $price
        );
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получает тариф Вита-мед
     * @param int $id
     * @return false|int
     */
    public function getVitaMedById(int $id)
    {
        $query = $this->db->placehold("SELECT * FROM __vita_med_conditions WHERE id = ?", $id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получает все тарифы
     *
     * @param bool $is_new_client
     * @return array|false
     */
    public function getAllVitaMedPrices(bool $is_new_client = true)
    {
        $query = $this->db->placehold('SELECT id, price FROM __vita_med_conditions WHERE is_new = ?', $is_new_client);
        $this->db->query($query);
        return $this->db->results();
    }
}
