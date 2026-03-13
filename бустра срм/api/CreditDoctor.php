<?php

require_once( __DIR__ . '/../api/Simpla.php');

/**
 * Класс для работы с Кредитным Доктором
 * Class CreditDoctor
 */
class CreditDoctor extends Simpla {

    /**
     * Статус оплаченного КД
     */
    public const CREDIT_DOCTOR_STATUS_SUCCESS = 'SUCCESS';

    public function getReturnUserCreditDoctorForSend()
    {
        $query = $this->db->placehold("
            SELECT * FROM s_credit_doctor_to_user 
            WHERE return_sent IN (0, 3) AND return_status = 2 and return_transaction_id <> 0 
            LIMIT 20
        ");
        $this->db->query($query);

        return $this->db->results();
    }

    public function getReturnedCreditDoctorByOrder($order_id)
    {
        $query = $this->db->placehold(
            "
            SELECT * FROM s_credit_doctor_to_user 
            WHERE order_id = ? AND return_status = 2 and return_transaction_id <> 0 
            LIMIT 20
        ",
            (int)$order_id
        );
        $this->db->query($query);

        return $this->db->results();
    }
    
    public function getAllSuccessUserCreditDoctorByOrder($order_id)
    {
        $query = $this->db->placehold("
            SELECT * FROM s_credit_doctor_to_user 
            WHERE order_id = ? AND status = ?
        ", (int)$order_id, self::CREDIT_DOCTOR_STATUS_SUCCESS);
        $this->db->query($query);
        
        return $this->db->results();
    }
    
    public function getCreditDoctor($credit_doctor_id)
    {
        $query = $this->db->placehold("
            SELECT * FROM s_credit_doctor_to_user 
            WHERE id = ? 
        ", (int)$credit_doctor_id);
        $this->db->query($query);
        
        return $this->db->result();
    }
    
    /**
     * @param int $order_id
     * @param int $user_id
     * @param string $status
     * @return false|int|null|object
     */
    public function getUserCreditDoctor(int $order_id, int $user_id, string $status = '', ?string $dateAdded = null, ?int $amount = null)
    {
        $sql = "SELECT * FROM s_credit_doctor_to_user WHERE order_id = ? AND user_id = ? ";

        if (!empty($status)) {
            $sql .= $this->db->placehold(" AND status = ?", $status);
        }

        if ($amount !== null) {
            $sql .= $this->db->placehold(" AND amount = ?", $amount);
        }

        if ($dateAdded !== null) {
            $sql .= $this->db->placehold(" AND DATE(date_added) = ?", $dateAdded);
        }

        $sql .= " ORDER BY id DESC";

        $query = $this->db->placehold($sql, $order_id, $user_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получает КД по сумме
     * @param int $amount
     * @param bool $is_new_client
     * @return false|mixed
     */
    public function getAmountCreditDoctor(int $amount, bool $is_new_client = true)
    {
        $query = $this->db->placehold('SELECT id, price FROM __credit_doctor_conditions WHERE is_new = ? AND ? BETWEEN from_amount AND to_amount',
            $is_new_client, $amount-1);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * @return array
     */
    public function getUserCreditDoctorList(): array
    {
        $sql = "SELECT `level_number`, `level`, COUNT(DISTINCT a.user_id) AS 'user_count' FROM (
            SELECT 
                    a.user_id,
                    ll.title AS `level`,
                    a.level AS `level_number`
            FROM 
            (
                SELECT COUNT(user_id) level, user_id, MAX(credit_doctor_condition_id) condition_id FROM s_credit_doctor_to_user 
                WHERE `status` = ? GROUP BY user_id
            ) a
            INNER JOIN s_credit_doctor_condition_to_lessons cl ON cl.condition_id = a.condition_id
            INNER JOIN s_credit_doctor_lessons l ON l.id = cl.lesson_id AND l.level_id = a.level
            INNER JOIN s_credit_doctor_levels ll ON ll.id = l.level_id
        ) a GROUP BY `level`";
        $query = $this->db->placehold($sql, self::CREDIT_DOCTOR_STATUS_SUCCESS);
        $this->db->query($query);

        return $this->db->results() ?: [];
    }

    /**
     * Обновляет информацию о КД пользователя
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateUserCreditDoctorData(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE s_credit_doctor_to_user SET ?% WHERE id = ?", $data, $id);
        $this->db->query($query);
    }

    /**
     * Создает заявление для КД
     * @param $order
     * @param int $amount
     * @param string $contract_number
     * @return void
     */
    public function createDocument($order, int $amount, string $contract_number = '')
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
        $params->amount = $amount;
        $params->insurer = $order->insurer;
        $params->organization_id = $order->organization_id;

        $this->documents->create_document(
            [
                'type' => $this->documents::CONTRACT_USER_CREDIT_DOCTOR,
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $order->organization_id,
            ]
        );

        try {
            $key = $this->fetchGeneratedKey($order->order_id);
        } catch (Throwable $e) {
            $this->logging(__METHOD__, 'CD fetchGeneratedKey', [
                'order_id' => (int)$order->order_id,
                'user_id' => (int)$order->user_id,
                'amount' => $amount,
                'organization_id' => (int)$order->organization_id,
            ], [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 'dop_api.txt');
            $key = null;
        }

        $params->license_key = $key ? $this->extractLicenseKeyFromUrl($key) : null;

        $orderData = $this->order_data->get((int)$order->order_id, 'SAFETY_FLOW');

        if ($orderData && $orderData->value == 1) {
            $condition = $this->credit_doctor->getCreditDoctorConditionByAmount($amount, 'safety_flow_prices');
            $params->license_key_days = $condition ? $condition->license_key_days : null;
        }

        $this->documents->create_document(
            [
                'type' => $this->documents::CREDIT_DOCTOR_POLICY,
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $this->receipts::ORGANIZATION_FINTEHMARKET,
            ]
        );

        if (isset($params->license_key_days)) {
            unset($params->license_key_days);
        }

        $this->documents->create_document(
            [
                'type' => $this->documents::ORDER_FOR_EXECUTION_CREDIT_DOCTOR,
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'contract_number' => $contract_number,
                'params' => $params,
                'organization_id' => $order->organization_id,
            ]
        );

    }

    /**
     * Получает сгенерированный ключ для пользователя.
     * @param $orderId
     * @return mixed|null
     */
    private function fetchGeneratedKey($orderId)
    {
        $order = $this->orders->get_order($orderId);
        $user = $this->users->get_user($order->user_id);
        $site_url = $this->sites->getDomainBySiteId($user->site_id);

        $url = rtrim('https://' . $site_url, '/') . "/ajax/generate_fd_key.php";
        $postData = [
            'order_id' => $orderId,
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

        $headers = [
            'Accept: application/json',
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $authUsed = false;
        $host = parse_url(rtrim('https://' . $site_url, '/'), PHP_URL_HOST);

        if (preg_match('/^(www\.)?dev\d*\.boostra\.ru$/', $host)) {
            $username = 'crm-developer';
            $password = 'ZHfphf,2024!';
            curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
            $authUsed = true;
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        $this->logging(__METHOD__, $url, [
            'url' => $url,
            'headers' => $headers,
            'auth_used' => $authUsed ? 'Basic Auth' : 'None',
            'post_data' => $postData,
            'curl_errno' => $curlErrno,
        ], [
            'http_code' => $httpCode,
            'body' => $response,
            'error' => $curlError,
        ], 'fetchGeneratedKey.txt');

        if ($httpCode === 200 && $response !== false) {
            $responseData = json_decode($response, true);
            if (isset($responseData['success']) && $responseData['success']) {
                return $responseData['login_url'] ?? null;
            }
        }

        return null;
    }

    /**
     * @param string $url
     * @return string|null
     */
    private function extractLicenseKeyFromUrl($url)
    {
        $queryString = parse_url($url, PHP_URL_QUERY);

        if ($queryString) {
            parse_str($queryString, $queryParams);
            return $queryParams['license'] ?? null;
        }

        return null;
    }

    /**
     * Получает данные для отчёта по КД
     * @param array $filter_data
     * @return array
     */
    public function getPayReport(array $filter_data = []): array
    {
        $where = [];
        $limit = '';

        $sql = "SELECT
                    cdu.order_id,
                    cdu.user_id,
                    CONCAT_WS(' ', u.lastname, u.firstname, u.patronymic) as fio,
                    u.birth,
                    CONCAT_WS('/', '" . $this->config->front_url . "/document', cdu.user_id, d.id) as doc_url,
                    cdu.amount,
                    cdu.date_added,
                    o.card_id,
                    cdu.payment_method,
                    IF(o.b2p, bc.pan, tc.pan) as pan,
                    u.UID,
                    o.b2p,
                    bpt.amount as payment_amount,
                    cdu.return_amount,
                    cdu.return_date,
                    u.phone_mobile
                FROM
                    s_credit_doctor_to_user cdu
                        LEFT JOIN s_users u ON u.id = cdu.user_id
                        LEFT JOIN s_orders o ON o.id = cdu.order_id
                        LEFT JOIN s_documents d ON d.user_id = cdu.user_id AND d.order_id = cdu.order_id AND d.type = ? 
                        LEFT JOIN b2p_cards bc ON bc.id = o.card_id
                        LEFT JOIN s_tinkoff_cards tc ON tc.card_id = o.card_id
                        LEFT JOIN b2p_transactions bpt ON bpt.id = cdu.transaction_id
                WHERE 1=1
                -- {{where}}
                -- {{limit}}";

        if (!empty($filter_data['status'])) {
            $where[] = $this->db->placehold('cdu.status = ?', $this->db->escape($filter_data['status']));
        }

        if (!empty($filter_data['filter_date_added'])) {
            $where[] = $this->db->placehold("cdu.date_added BETWEEN ? AND ?", $filter_data['filter_date_added']['filter_date_start'] . ' 00:00:00', $filter_data['filter_date_added']['filter_date_end'] . ' 23:59:59');
        }

        if (!empty($filter_data['filter_limit'])) {
            $limit = $this->db->placehold("LIMIT ?, ?", $filter_data['filter_limit']['offset'] ?? 0, intval($filter_data['filter_limit']['limit']));
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
            '-- {{limit}}' => $limit,
        ]);
            
        $query = $this->db->placehold($query,  $this->documents::CONTRACT_USER_CREDIT_DOCTOR);

        $this->db->query($query);

        $results = $this->db->results();
        array_walk($results, function (&$item) {
            if (empty($item->pan) && empty($item->b2p)) {
                $response = $this->tinkoff->get_cardlist($item->UID);
                if (empty($response['error'])) {
                    foreach ($response as $card) {
                        $filter_card = [
                            'user_id' => $item->user_id,
                            'card_id' => $item->card_id,
                        ];

                        if (empty($this->tinkoff->getCardsByFilter($filter_card))) {
                            $card_data = [
                                'user_id' => $item->user_id,
                                'card_id' => (int)$card->CardId,
                                'pan' => trim($card->Pan),
                                'status' => trim($card->Status),
                                'rebill_id' => (int)$card->RebillId,
                                'card_type' => (int)$card->CardType,
                                'exp_date' => trim($card->ExpDate),
                                'auto_debiting' => (int)!empty($card->AutoDebiting),
                            ];
                            $this->tinkoff->addCardToDb($card_data);
                        }

                        if ($item->card_id == $card->CardId) {
                            $item->pan = trim($card->Pan);
                        }
                    }
                }
            }
        });

        return $results;
    }

    public function getCreditDoctorConditionsList(array $filters = []) {
        $sql = "SELECT * FROM __credit_doctor_conditions";

        if (!empty($filters) && !empty($filters['price_group'])) {
            $sql .= $this->db->placehold(" WHERE price_group = ?", $filters['price_group']);
        } else {
            $sql .= $this->db->placehold(" WHERE price_group IS NULL");
        }

        $sql .= " ORDER BY from_amount ASC";
        $query = $this->db->placehold($sql);
        $this->db->query($query);
        $results = $this->db->results();
        return $results;
    }

    public function updateCreditDoctorCondition($id, $data)
    {
        $this->db->query("UPDATE __credit_doctor_conditions SET ?% WHERE id=?", $data, $id);
    }

    public function createCreditDoctorCondition($data)
    {
        $this->db->query("INSERT INTO __credit_doctor_conditions SET ?%", $data);
        return $this->db->insert_id();
    }

    public function deleteCreditDoctorCondition($id)
    {
        $this->db->query("DELETE FROM __credit_doctor_conditions WHERE id=?", $id);
    }

    public function getCreditDoctorConditionByAmount($amount, $priceGroup = 'safety_flow_prices')
    {
        if (!$amount || !$priceGroup) {
            return null;
        }

        $query = $this->db->placehold("
            SELECT * 
            FROM __credit_doctor_conditions 
            WHERE price_group = ? 
              AND from_amount <= ? 
              AND to_amount >= ?
            ORDER BY from_amount ASC
            LIMIT 1
        ", $priceGroup, $amount, $amount);

        $this->db->query($query);
        return $this->db->result();
    }
}
