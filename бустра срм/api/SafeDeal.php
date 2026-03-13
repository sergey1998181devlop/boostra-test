<?php

require_once 'Simpla.php';

class SafeDeal extends Simpla
{
    public const FULL_NAME = 'Услуга «Безопасная сделка»';

    public const PERCENT = 20;

    /**
     * Статус новой записи
     */
    public const STATUS_NEW = 'NEW';

    /**
     * Статус оплаченного
     */
    public const STATUS_SUCCESS = 'SUCCESS';

    /**
     * Возвращает стоимость услуги
     * @param $loanAmount
     * @return float
     */
    public function getPrice($loanAmount): float
    {
        return round($loanAmount * self::PERCENT/100, 2);
    }

    /**
     * Получает запись
     * @param int $order_id
     * @param int $user_id
     * @param string $status
     * @return false|int
     */
    public function get(int $order_id, int $user_id, string $status = '')
    {
        $sql = "SELECT * FROM s_safe_deal WHERE order_id = ? AND user_id = ? ";

        if (!empty($status)) {
            $sql .= $this->db->placehold(" AND status = ?", $status);
        }

        $query = $this->db->placehold($sql, $order_id, $user_id);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Получает все записи
     * @param int $limit
     * @param string $order
     * @return array
     */
    public function all(int $limit = 0, string $order = 'ASC'): array
    {
        $order = in_array(strtoupper($order), ['ASC', 'DESC'], true) ? strtoupper($order) : 'ASC';

        $sql = "SELECT * FROM s_safe_deal ORDER BY id " . $order;

        if ($limit > 0) {
            $sql .= " LIMIT " . $limit;
        }

        $query = $this->db->placehold($sql);
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * Получаем последнюю запись
     * Можно получить только конкретное поле последней записи
     * @param string $field
     * @return false|int
     */
    public function getLast($field = '*')
    {
        $allowed = ['*', 'id', 'status', 'amount'];

        $field = in_array($field, $allowed, true) ? $field : '*';

        $sql = "SELECT {$field} FROM s_safe_deal ORDER BY id DESC LIMIT 1";

        $query = $this->db->placehold($sql);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Поиск по фильтру
     * @param array $filter_data
     * @param bool $return_all
     * @return array|false
     */
    public function selectAll(array $filter_data, bool $return_all = true)
    {
        $where = [];
        $sql = "SELECT * FROM s_safe_deal WHERE 1
                 -- {{where}}";

        if (!empty($filter_data['filter_transaction_id'])) {
            $where[] = $this->db->placehold("transaction_id = ?", (int)$filter_data['filter_transaction_id']);
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
     * Получает по id
     * @param int $id
     * @return object|null
     */
    public function getById(int $id): ?object
    {
        $sql = "SELECT * FROM s_safe_deal WHERE id = ?";

        $query = $this->db->placehold($sql, $id);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Получает по order_id
     * @param int $order_id
     * @return array|false
     */
    public function getAllSuccessByOrderId(int $order_id)
    {
        $query = $this->db->placehold("
            SELECT * FROM s_safe_deal 
            WHERE order_id = ? AND status = ?
        ", (int)$order_id, self::STATUS_SUCCESS);
        $this->db->query($query);

        return $this->db->results();
    }
    
    /**
     * Добавляет информацию
     * @param array $data
     * @return mixed
     */
    public function store(array $data)
    {
        $query = $this->db->placehold("INSERT INTO __safe_deal SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновляет информацию
     * @param int $id
     * @param array $data
     * @return void
     */
    public function update(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE __safe_deal SET ?% WHERE id = ?", $data, $id);
        $this->db->query($query);
    }

    /**
     * Удаляет запись
     * @param int $id
     * @return void
     */
    public function delete(int $id)
    {
        $query = $this->db->placehold("DELETE FROM __safe_deal WHERE id = ?", $id);
        $this->db->query($query);
    }

    public function getReturnSafeDealForSend()
    {
        $query = $this->db->placehold("
            SELECT * FROM s_safe_deal
            WHERE return_sent IN (0, 3) AND return_status = 2 and return_transaction_id <> 0 
            LIMIT 20
        ");
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * Создает документы
     * @param $order
     * @param $safe_deal
     * @param string $contract_number
     * @param bool $organization_id
     * @return void
     */
    public function createDocuments(object $order, $safe_deal, string $contract_number = '', $organization_id = null): void
    {
        $user = $this->users->getUserByOrderId($order->order_id);
        $lastRow = self::getLast('id');
        $number = $lastRow ? $lastRow->id + 1 : 1;

        $params = new StdClass();

        $params->lastname = $user->lastname;
        $params->firstname = $user->firstname;
        $params->patronymic = $user->patronymic;
        $params->full_name =  $this->helpers::getFIO($user);
        $params->birth = $user->birth;
        $params->regaddress_full = "{$user->Regindex}, {$user->Regregion}, {$user->Regcity}, {$user->Regstreet}, д. {$user->Reghousing}, кв. {$user->Regroom}";
        $params->passport_serial = $user->passport_serial;
        $params->passport_issued = $user->passport_issued;
        $params->passport_date = $user->passport_date;
        $params->subdivision_code = $user->subdivision_code;
        $params->phone_mobile = $user->phone_mobile;
        $params->accept_sms = $order->accept_sms;
        $params->amount = $safe_deal->amount;
        $params->percent = self::PERCENT;
        $params->insurer = $order->insurer;
        $params->current_date = date('d.m.Y');
        $params->number = $number;

        $docTypes = [
            $this->documents::OFFER_SAFE_DEAL,
            $this->documents::ORDER_FOR_EXECUTION_SAFE_DEAL,
            $this->documents::REPORT_SAFE_DEAL,
            $this->documents::NOTIFICATION_SAFE_DEAL,
            $this->documents::CONTRACT_SAFE_DEAL,
        ];

        foreach ($docTypes as $docType) {
            $data = $docType == $this->documents::OFFER_SAFE_DEAL ? [] : $params;

            $this->documents->create_document(
                [
                    'type' => $docType,
                    'user_id' => $order->user_id,
                    'order_id' => $order->order_id,
                    'contract_number' => $contract_number,
                    'params' => $data,
                    'organization_id' => $organization_id ?? $this->organizations::MOREDENEG_ID,
                ]
            );
        }
    }
}
