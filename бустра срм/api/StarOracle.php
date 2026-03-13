<?php

require_once 'Simpla.php';

class StarOracle extends Simpla
{
    public const AMOUNT = 300;

    public const ACTION_TYPE_PROLONGATION = 'prolongation';
    public const ACTION_TYPE_PARTIAL_PAYMENT = 'partial_payment';
    public const ACTION_TYPE_FULL_PAYMENT = 'full_payment';
    public const ACTION_TYPE_RECURRING_PARTIAL_PAYMENT = 'recurring_partial_payment';
    public const ACTION_TYPE_RECURRING_FULL_PAYMENT = 'recurring_full_payment';
    public const ACTION_TYPE_ISSUANCE = 'issuance';
    public const LICENSE_SERVICE_TYPE_STAR_ORACLE = 'star_oracle';

    public const ACTION_TYPE_PAYMENT = [
        self::ACTION_TYPE_FULL_PAYMENT,
        self::ACTION_TYPE_PARTIAL_PAYMENT,
        self::ACTION_TYPE_PROLONGATION,
        self::ACTION_TYPE_RECURRING_PARTIAL_PAYMENT,
        self::ACTION_TYPE_RECURRING_FULL_PAYMENT,
    ];
    
    /**
     * Статус оплаченного SO
     */
    public const STAR_ORACLE_STATUS_SUCCESS = 'SUCCESS';

    public function getStarOracleById($star_oracle_id)
    {
        $query = $this->db->placehold("
            SELECT * FROM s_star_oracle 
            WHERE id = ? 
        ", (int)$star_oracle_id);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * @param int $order_id
     * @param int $user_id
     * @param string $action_type
     * @return null|object
     */
    public function getStarOracle(int $order_id, int $user_id, string $action_type = '', ?string $status = null, ?string $dateAdded = null, ?int $amount = null)
    {
        $sql = "SELECT * FROM s_star_oracle WHERE order_id = ? AND user_id = ? ";

        if (!empty($action_type)) {
            $sql .= $this->db->placehold(" AND action_type = ?", $action_type);
        }

        if ($amount !== null) {
            $sql .= $this->db->placehold(" AND amount = ?", $amount);
        }

        if ($dateAdded !== null) {
            $sql .= $this->db->placehold(" AND DATE(date_added) = ?", $dateAdded);
        }

        if ($status !== null) {
            $sql .= $this->db->placehold(" AND status = ?", $status);
        }

        $sql .= " ORDER BY id DESC";

        $query = $this->db->placehold($sql, $order_id, $user_id);
        $this->db->query($query);
        return $this->db->result();
    }

    public function getAllSuccessStarOracleByOrderId($order_id)
    {
        $query = $this->db->placehold("
            SELECT * FROM s_star_oracle 
            WHERE order_id = ? AND status = ?
        ", (int)$order_id, self::STAR_ORACLE_STATUS_SUCCESS);
        $this->db->query($query);

        return $this->db->results();
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
    public function updateStarOracleData(int $id, array $data): void
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
     * Создает заявление для SO
     * @param $order
     * @param int $amount
     * @param string $contract_number
     * @return void
     */
    public function createDocument($order, $star_oracle, string $contract_number = ''): void
    {
        $params = new StdClass();

        $params->lastname = $order->lastname;
        $params->firstname = $order->firstname;
        $params->patronymic = $order->patronymic;
        $params->birth = $order->birth;
        $params->passport_serial = $order->passport_serial;
        $params->passport_issued = $order->passport_issued;
        $params->passport_date = $order->passport_date;
        $params->subdivision_code = $order->subdivision_code;
        $params->phone_mobile = $order->phone_mobile;
        $params->accept_sms = $order->accept_sms;
        $params->amount = (int)$star_oracle->amount;
        $params->insurer = $order->insurer;

        try {
            $key = $this->fetchGeneratedKey((int)$order->order_id, (int)$order->user_id, $this->organizations::FINTEHMARKET_ID, (int)$star_oracle->id, (int)$star_oracle->amount);
        } catch (Throwable $e) {
            $this->logging(__METHOD__, 'SO fetchGeneratedKey', [
                'order_id' => (int)$order->order_id,
                'user_id' => (int)$order->user_id,
                'star_oracle' => (int)$star_oracle->id,
                'amount' => (int)$star_oracle->amount,
                'organization_id' => $this->organizations::FINTEHMARKET_ID,
            ], [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 'dop_api.txt');
            $key = null;
        }

        $params->license_key = $key;
        
        $this->documents->create_document(
            [
                'type' => $this->documents::CONTRACT_STAR_ORACLE,
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $this->organizations::FINTEHMARKET_ID,
            ]
        );
       
        $this->documents->create_document(
            [
                'type' => $this->documents::STAR_ORACLE_POLICY,
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $this->organizations::FINTEHMARKET_ID,
            ]
        );

        $this->documents->create_document(
            [
                'type' => $this->documents::ORDER_FOR_EXECUTION_STAR_ORACLE,
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $order->organization_id,
            ]
        );

    }

    public function getReturnStarOracleForSend()
    {
        $query = $this->db->placehold("
            SELECT * FROM s_star_oracle
            WHERE return_sent IN (0, 3) AND return_status = 2 and return_transaction_id <> 0 
            LIMIT 20
        ");
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
        
        if (!empty($filter_data['filter_action_type'])) {
            $where[] = $this->db->placehold("action_type IN (?@)", (array)$filter_data['filter_action_type']);
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
     * Запрашивает сгенерированный ключ для лицензии "Звездный оракул"
     * 
     * @param int $orderId ID заказа
     * @param int $userId ID пользователя
     * @param int $organizationId ID организации
     * @param int $serviceId ID услуги
     * @param int $amount Сумма
     * @return string|null Возвращает ключ лицензии или null при ошибке
     */
    private function fetchGeneratedKey(int $orderId, int $userId, int $organizationId, int $serviceId, int $amount): ?string
    {
        $user = $this->users->get_user($userId);
        $site_url = $this->sites->getDomainBySiteId($user->site_id);

        $url = rtrim('https://' . $site_url, '/') . "/ajax/generate_dop_license_key.php";
        $postData = [
            'license_type' => self::LICENSE_SERVICE_TYPE_STAR_ORACLE,
            'order_id' => $orderId,
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'service_id' => $serviceId,
            'amount' => $amount,
        ];

        // Настройка запроса
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ];
        
        // Добавление аутентификации если нужно
        $host = parse_url(rtrim('https://' . $site_url, '/'), PHP_URL_HOST);
        $authUsed = 'None';
        if ($host === 'localhost' ||
            $host === '127.0.0.1' ||
            preg_match('/^(www\.)?dev\d*\.boostra\.ru$/', $host)) {
            $options[CURLOPT_USERPWD] = 'crm-developer:ZHfphf,2024!';
            $authUsed = 'Basic Auth';
        }
        
        // Выполнение запроса
        $ch = curl_init($url);
        if (!$ch) {
            return null; // Не удалось инициализировать cURL
        }
        
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);
        
        // Логирование результата
        $this->logging(__METHOD__, $url, [
            'url' => $url,
            'headers' => $options[CURLOPT_HTTPHEADER],
            'auth_used' => $authUsed,
            'post_data' => $postData,
            'curl_errno' => $curlErrno
        ], [
            'http_code' => $httpCode,
            'body' => $response,
            'error' => $curlError,
        ], 'dop_api.txt');
        
        // Проверка на ошибки cURL
        if ($curlErrno !== 0) {
            return null;
        }
        
        // Обработка ответа
        if ($httpCode === 200 && $response !== false) {
            $responseData = json_decode($response, true);
            if (!empty($responseData['success']) && $responseData['license_key']) {
                return $responseData['license_key'];
            }
        }
        
        return null;
    }


}
