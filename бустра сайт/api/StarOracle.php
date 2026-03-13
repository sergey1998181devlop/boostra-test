<?php

require_once 'Simpla.php';

class StarOracle extends Simpla
{
    public const AMOUNT = 350;

    /**
     * Статус новой записи о SO
     */
    public const STAR_ORACLE_STATUS_NEW = 'NEW';

    /**
     * Статус оплаченного SO
     */
    public const STAR_ORACLE_STATUS_SUCCESS = 'SUCCESS';

    public const ACTION_TYPE_PROLONGATION = 'prolongation';
    public const ACTION_TYPE_PARTIAL_PAYMENT = 'partial_payment';
    public const ACTION_TYPE_FULL_PAYMENT = 'full_payment';
    public const ACTION_TYPE_RECURRING_PARTIAL_PAYMENT = 'recurring_partial_payment';
    public const ACTION_TYPE_RECURRING_FULL_PAYMENT = 'recurring_full_payment';
    public const ACTION_TYPE_ISSUANCE = 'issuance';

    public const ACTION_TYPE_PAYMENT = [
        self::ACTION_TYPE_FULL_PAYMENT,
        self::ACTION_TYPE_PARTIAL_PAYMENT,
        self::ACTION_TYPE_PROLONGATION,
        self::ACTION_TYPE_RECURRING_PARTIAL_PAYMENT,
        self::ACTION_TYPE_RECURRING_FULL_PAYMENT,
    ];
    
    /**
     * Получает SO
     * @param int $order_id
     * @param int $user_id
     * @param string $status
     * @return false|int
     */
    public function getStarOracle(int $order_id, int $user_id, string $status = '')
    {
        $sql = "SELECT * FROM s_star_oracle WHERE order_id = ? AND user_id = ? ";

        if (!empty($status)) {
            $sql .= $this->db->placehold(" AND status = ?", $status);
        }

        $query = $this->db->placehold($sql, $order_id, $user_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получает SO по id
     * @param int $star_oracle_id
     * @return false|int
     */
    public function getStarOracleById(int $star_oracle_id)
    {
        $sql = "SELECT * FROM s_star_oracle WHERE id = ?";

        $query = $this->db->placehold($sql, $star_oracle_id);
        $this->db->query($query);

        return $this->db->result();
    }
    
    
    /**
     * Добавляет информацию о SO к пользователю
     * @param array $data
     * @return mixed
     */
    public function addStarOracleData(array $data)
    {
        $query = $this->db->placehold("INSERT INTO __star_oracle SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновляет информацию о SO пользователя
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateStarOracleData(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE __star_oracle SET ?% WHERE id = ?", $data, $id);
        $this->db->query($query);
    }

    /**
     * Удаляет запись о SO
     * @param int $id
     * @return void
     */
    public function deleteStarOracle(int $id)
    {
        $query = $this->db->placehold("DELETE FROM __star_oracle WHERE id = ?", $id);
        $this->db->query($query);
    }

    /**
     * Получает тариф исходя из суммы займа
     *
     * @param int $amount
     * @param bool $is_new_client
     * @return false|int
     */
    public function getStarOraclePrice(int $amount, bool $is_new_client = true)
    {
        $query = $this->db->placehold(
            'SELECT id, price FROM __star_oracle_conditions WHERE is_new = ? AND ? BETWEEN from_amount AND to_amount ORDER BY to_amount ASC LIMIT 1',
            $is_new_client,
            $amount
        );
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получает все тарифы
     * @return array|false
     */
    public function getAllTariffs()
    {
        $query = "SELECT * FROM __star_oracle_conditions";
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * Поиск звездного оракла по фильтру
     * @param array $filter_data
     * @param bool $return_all
     * @return array|false
     */
    public function selectAll(array $filter_data, bool $return_all = true)
    {
        $where = [];
        $sql = "SELECT * FROM s_star_oracle WHERE 1
                 -- {{where}}";

        if (!empty($filter_data['filter_transaction_id'])) {
            $where[] = $this->db->placehold("transaction_id = ?", (int)$filter_data['filter_transaction_id']);
        }

        if (!empty($filter_data['filter_payment_method'])) {
            $where[] = $this->db->placehold("payment_method = ?", $this->db->escape($filter_data['filter_payment_method']));
        }

        if (!empty($filter_data['filter_action_type'])) {
            $where[] = $this->db->placehold("action_type IN (?@)", (array)$filter_data['filter_action_type']);
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

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);

        if ($return_all) {
            return $this->db->results();
        }

        return $this->db->result();
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
        $sql = "SELECT * FROM s_star_oracle_conditions WHERE 1
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
     * Получаем данные ЗО
     * @param $order
     * @param $amount
     * @param $action_type
     * @return array
     */
    public function getStarOracleRepaymentData($order, $amount, string $action_type): array
    {
        $star_oracle = self::getStarOraclePrice($amount);

        if ($star_oracle === null) {
            return [];
        }

        $isFullPayment = strpos($action_type, 'full') !== false;
        $isPartialPayment = strpos($action_type, 'partial') !== false;

        // Определяем тип сервиса
        $serviceType = null;
        if ($isFullPayment) {
            if ($order->additional_service_so_repayment) {
                $serviceType = 'full';
            } elseif ($order->half_additional_service_so_repayment) {
                $serviceType = 'half_full';
            }
        } elseif ($isPartialPayment) {
            if ($order->additional_service_so_partial_repayment) {
                $serviceType = 'partial';
            } elseif ($order->half_additional_service_so_partial_repayment) {
                $serviceType = 'half_partial';
            }
        }

        if ($serviceType === null) {
            return [];
        }

        switch ($serviceType) {
            case 'full':
            case 'partial':
                $oracle_amount = $star_oracle->price;
                break;
            case 'half_full':
            case 'half_partial':
                $oracle_amount = round($star_oracle->price / 2);
                break;
            default:
                return [];
        }

        return [
            'star_oracle' => [
                'id' => $star_oracle->id,
                'amount' => $oracle_amount,
            ]
        ];
    }

    /**
     * Создает заявление для SO
     * @param $order
     * @param $star_oracle
     * @param string $contract_number
     * @param bool $organization_id
     * @return void
     */
    public function createDocument($order, $star_oracle, string $contract_number = '', $organization_id = null): void
    {
        $user = $this->users->get_user_by_id($order->user_id);

        $params = new StdClass();

        $params->lastname = $user->lastname;
        $params->firstname = $user->firstname;
        $params->patronymic = $user->patronymic;
        $params->birth = $user->birth;
        $params->passport_serial = $user->passport_serial;
        $params->passport_issued = $user->passport_issued;
        $params->passport_date = $user->passport_date;
        $params->subdivision_code = $user->subdivision_code;
        $params->phone_mobile = $user->phone_mobile;
        $params->accept_sms = $order->accept_sms;
        $params->amount = (int)$star_oracle->amount;
        $params->insurer = $order->insurer;

        $key = $this->dop_license->createLicenseWithKey(
            $this->dop_license::SERVICE_STAR_ORACLE,
            [
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'service_id' => $star_oracle->id,
                'organization_id' => $organization_id ?? $this->organizations::FINTEHMARKET_ID,
                'amount' => $star_oracle->amount,
            ]
        );

        $params->license_key = $key;

        $this->documents->create_document(
            [
                'type' => $this->documents::CONTRACT_STAR_ORACLE,
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $organization_id ?? $this->organizations::FINTEHMARKET_ID,
            ]
        );

        $this->documents->create_document(
            [
                'type' => $this->documents::STAR_ORACLE_POLICY,
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $organization_id ?? $this->organizations::FINTEHMARKET_ID,
            ]
        );

        $this->documents->create_document(
            [
                'type' => $this->documents::ORDER_FOR_EXECUTION_STAR_ORACLE,
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $organization_id ?? $order->organization_id,
            ]
        );
    }
}
