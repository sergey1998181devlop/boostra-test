<?php

require_once (dirname(__DIR__) . '/api/Simpla.php');

/**
 * Класс для работы с телемедициной
 * Class TVMedical
 */
class TVMedical extends Simpla
{
    /**
     * Статус оплаченной транзакции за телемедицину
     */
    public const TV_MEDICAL_PAYMENT_STATUS_SUCCESS = 'SUCCESS';
    public const LICENSE_SERVICE_TYPE_TV_MEDICAL = 'vitamed';


    /**
     * TVMedical::getReturnPaymentsForSend()
     * 
     * @return array
     */
    public function getReturnPaymentsForSend()
    {
        $query = $this->db->placehold("
            SELECT * FROM s_tv_medical_payments
            WHERE return_sent IN (0, 3) 
            AND return_status = 2
            LIMIT 5 
        ");
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * Получает платеж по телемедецине
     * @param int $id
     * @return false|int
     */
    public function getPaymentById(int $id)
    {
        $query = $this->db->placehold("SELECT * FROM s_tv_medical_payments WHERE id = ?", $id);
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

    public function getPaymentsWithInfo(array $filter_data, bool $return_all = true)
    {
        $where = [];
        $sql = "SELECT 
                    p.id as payment_id,
                    p.amount,
                    p.date_added,
                    p.payment_id as payment_payment_id,
                    p.order_id,
                    p.return_status,
                    p.amount_total_returned,
                    m.name,
                    p.return_amount,
                    p.return_date,
                    p.return_transaction_id,
                    p.organization_id
                FROM s_tv_medical_payments p
                LEFT JOIN s_tv_medical m ON m.id = p.tv_medical_id
                WHERE 1
                 -- {{where}}";

        if (isset($filter_data['filter_order_id'])) {
            $where[] = $this->db->placehold("p.order_id = ?", (int)$filter_data['filter_order_id']);
        }

        if (isset($filter_data['filter_status'])) {
            $where[] = $this->db->placehold("p.status = ?", $this->db->escape($filter_data['filter_status']));
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

        if (!empty($filter_data['filter_action_type'])) {
            $where[] = $this->db->placehold("action_type IN (?@)", (array)$filter_data['filter_action_type']);
        }

        if (isset($filter_data['filter_sent_to_api'])) {
            $where[] = $this->db->placehold("sent_to_api = ?", (int)$filter_data['filter_sent_to_api']);
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
     * Получает данные для отчёта по Телемедицине
     * @param array $filter_data
     * @return array
     */
    public function getPayReport(array $filter_data = []): array
    {
        $where = [];
        $limit = '';

        $sql = "SELECT
                tvmed.order_id,
                u.id as user_id,
                CONCAT_WS(' ', u.lastname, u.firstname, u.patronymic) as fio,
                u.birth,
                tvmed.amount,
                tvmed.date_added,
                tvmed.payment_method,
                tvmed.amount,
                tvmed.return_amount as rp_amount,
                p.prolongation,
                0 as is_full,
                b.zaim_number,
                b.zaim_summ
            FROM
                s_tv_medical_payments tvmed
                    LEFT JOIN s_orders o ON o.id = tvmed.order_id
                    LEFT JOIN s_users u ON u.id = o.user_id
                    LEFT JOIN b2p_payments p ON p.id = tvmed.payment_id
                    LEFT JOIN s_user_balance b ON b.user_id = u.id
            WHERE 1=1
            -- {{where}}
            -- {{limit}}";

        if (!empty($filter_data['status'])) {
            $where[] = $this->db->placehold('tvmed.status = ?', $this->db->escape($filter_data['status']));
        }

        if (!empty($filter_data['filter_date_added'])) {
            $where[] = $this->db->placehold("tvmed.date_added BETWEEN ? AND ?", $filter_data['filter_date_added']['filter_date_start'] . ' 00:00:00', $filter_data['filter_date_added']['filter_date_end'] . ' 23:59:59');
        }

//        if (!empty($filter_data['filter_limit'])) {
//            $limit = $this->db->placehold("LIMIT ?, ?", $filter_data['filter_limit']['offset'] ?? 0, intval($filter_data['filter_limit']['limit']));
//        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
            '-- {{limit}}' => $limit,
        ]);

        $query = $this->db->placehold($query);
        $this->db->query($query);

        $results = $this->db->results();

        if (empty($results)) {
            return [];
        }

        return $this->setFullForLastNonProlongationItemByDate($results);
    }

    /**
     * Получает все тарифы по телемедицине
     * @return array|false
     */
    public function getAllTariffs()
    {
        $query = "SELECT * FROM s_tv_medical";
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * @param array $items
     * @return array
     */
    public function setFullForLastNonProlongationItemByDate(array $items): array
    {

        // Group items by order_id
        $groupedByOrderId = [];

        foreach ($items as $item) {
            if (!isset($groupedByOrderId[$item->order_id])) {
                $groupedByOrderId[$item->order_id] = [];
            }
            $groupedByOrderId[$item->order_id][] = $item;
        }

        // set is_full = 1 for the last none prolongation item
        foreach ($groupedByOrderId as $orderId => $group) {
            usort($group, static function($a, $b) {
                return strtotime($a->date_added) - strtotime($b->date_added);
            });

            if ($group[0]->zaim_summ && (int)substr($group[0]->zaim_number,4) === $orderId){
                continue;
            }

            // Find the last item where prolongation is 0
            for ($i = count($group) - 1; $i >= 0; $i--) {
                if ((int)$group[$i]->prolongation === 0) {
                    $group[$i]->is_full = 1;
                    break;
                }
            }

            // Update the original array with the modified group
            $groupedByOrderId[$orderId] = $group;
        }

        // Flatten the grouped array back to a single array
        return array_merge([], ...$groupedByOrderId);
    }

    /**
     * @param int $order_id
     * @param int $user_id
     * @return null|object
     */
    public function getTVMedical(int $order_id, int $user_id, ?string $status = null, ?string $dateAdded = null, ?int $amount = null, ?string $action_type = null)
    {
        $sql = "SELECT * FROM s_tv_medical_payments WHERE order_id = ? AND user_id = ? ";

        if ($action_type !== null) {
            $sql .= $this->db->placehold(" AND action_type = ?", $action_type);
        }

        if ($status !== null) {
            $sql .= $this->db->placehold(" AND status = ?", $status);
        }

        if ($amount !== null) {
            $sql .= $this->db->placehold(" AND amount = ?", $amount);
        }

        if ($dateAdded !== null) {
            $sql .= $this->db->placehold(" AND DATE(date_added) = ?", $dateAdded);
        }

        $query = $this->db->placehold($sql, $order_id, $user_id);
        $this->db->query($query);
        return $this->db->result();
    }

    public function     getTVMedicalConditionsList(array $filters = []) {
        $sql = "SELECT * FROM __vita_med_conditions";
        $sql .= " ORDER BY from_amount ASC";
        $query = $this->db->placehold($sql);
        $this->db->query($query);
        $results = $this->db->results();
        return $results;
    }

    public function updateVitamedCondition($id, $data)
    {
        $this->db->query("UPDATE __vita_med_conditions SET ?% WHERE id=?", $data, $id);
    }

    public function createVitamedCondition($data)
    {
        $this->db->query("INSERT INTO __vita_med_conditions SET ?%", $data);
        return $this->db->insert_id();
    }

    public function deleteVitamedCondition($id)
    {
        $this->db->query("DELETE FROM __vita_med_conditions WHERE id=?", $id);
    }

    /**
     * @param $order
     * @param $dopService
     * @param string $contract_number
     * @return void
     */
    public function createDocument($order, $dopService, string $contract_number = ''): void
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
        $params->amount = (int)$dopService->amount;
        $params->insurer = $order->insurer;

        try {
            $key = $this->fetchGeneratedKey((int)$order->order_id, (int)$order->user_id, $this->organizations::FINTEHMARKET_ID, (int)$dopService->id, (int)$dopService->amount);
        } catch (Throwable $e) {
            $this->logging(__METHOD__, 'TM fetchGeneratedKey', [
                'order_id' => (int)$order->order_id,
                'user_id' => (int)$order->user_id,
                'service_id' => (int)$dopService->id,
                'amount' => (int)$dopService->amount,
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
                'type' => $this->documents::CONTRACT_TV_MEDICAL,
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $this->organizations::FINTEHMARKET_ID,
            ]
        );

        $this->documents->create_document(
            [
                'type' => $this->documents::ACCEPT_TELEMEDICINE,
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $this->organizations::FINTEHMARKET_ID,
            ]
        );

        $this->documents->create_document(
            [
                'type' => $this->documents::ORDER_FOR_EXECUTION_TV_MEDICAL,
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $order->organization_id,
            ]
        );
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
        $url = rtrim($this->config->front_url, '/') . "/ajax/generate_dop_license_key.php";
        $postData = [
            'license_type' => self::LICENSE_SERVICE_TYPE_TV_MEDICAL,
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
        $host = parse_url($this->config->front_url, PHP_URL_HOST);
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
