<?php

/**
 * Rcl - Revolving Credit Line
 * Модель для работы с кредитными линиями и траншами
 */
class Rcl extends Simpla
{
    public const STATUS_NEW = 'NEW';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_CLOSED = 'CLOSED';

    public const SENT_ONEC_NEW = 0;
    public const SENT_ONEC_SUCCESS = 1;
    public const SENT_ONEC_ERROR = 3;

    /**
     * Срок жизни ВКЛ договора
     */
    public const CONTRACT_DURATION_YEARS = 5;

    /**
     * Максимальный срок жизни транша в днях
     */
    public const RCL_TRANCHE_MAX_PERIOD = 160;

    /**
     * Срок одной пролонгации ВКЛ-транша в днях
     */
    public const RCL_PROLONGATION_DAYS = 16;

    /**
     * Проверить, является ли заявка ВКЛ-траншем
     * 
     * @param int $order_id
     * @return bool
     */
    public function isRclTranche(int $order_id): bool
    {
        return !empty($this->get_tranche(['order_id' => $order_id]));
    }

    /**
     * Проверить, доступна ли пролонгация ВКЛ-транша.
     * Пролонгация недоступна, если today + 16 дней > zaim_date + 160 дней
     *
     * @param string $zaim_date Дата выдачи транша
     * @return bool
     */
    public function isProlongationAvailable(string $zaim_date): bool
    {
        $max_date = new DateTime(date('Y-m-d', strtotime($zaim_date)));
        $max_date->add(new DateInterval('P' . self::RCL_TRANCHE_MAX_PERIOD . 'D'));

        $after_prolongation = new DateTime(date('Y-m-d'));
        $after_prolongation->add(new DateInterval('P' . self::RCL_PROLONGATION_DAYS . 'D'));

        return $after_prolongation <= $max_date;
    }

    /**
     * Рассчитать новую дату возврата транша после пролонгации (today + 16 дней, но не более zaim_date+160)
     * @param string $zaim_date Дата выдачи транша
     * @return string Дата в формате Y-m-d
     */
    public function getNewPaymentDate(string $zaim_date): string
    {
        $max_date = new DateTime(date('Y-m-d', strtotime($zaim_date)));
        $max_date->add(new DateInterval('P' . self::RCL_TRANCHE_MAX_PERIOD . 'D'));

        $new_date = new DateTime(date('Y-m-d'));
        $new_date->add(new DateInterval('P' . self::RCL_PROLONGATION_DAYS . 'D'));

        // Не может превышать максимальную дату
        if ($new_date > $max_date) {
            $new_date = $max_date;
        }

        return $new_date->format('Y-m-d');
    }

    /**
     * Получить контракт по фильтру
     *
     * Примеры:
     *   get_contract(['id' => 5])
     *   get_contract(['user_id' => 10, 'status' => 'NEW'])
     *
     * @param array $params [field => value]
     * @return object|null
     */
    public function get_contract(array $params)
    {
        $condition = $this->buildWhere($params);
        if (!$condition) {
            return null;
        }

        $query = "SELECT * FROM rcl_contracts WHERE " . $condition['where'] . " ORDER BY id DESC LIMIT 1";
        $this->db->query($query, ...$condition['values']);

        return $this->db->result();
    }

    /**
     * Получить контракты по фильтру
     *
     * Примеры:
     *   get_contracts(['organization_id' => 1])
     *   get_contracts(['status' => 'NEW', 'sent_onec' => 0])
     *   get_contracts(['date_create' => ['from' => '2024-01-01', 'to' => '2024-12-31']])
     *   get_contracts(['status' => 'NEW', 'limit' => 10, 'offset' => 20])
     *
     * @param array $params [field => value] или [field => ['from' => val, 'to' => val]]
     * @return array
     */
    public function get_contracts(array $params = [])
    {
        $limit = null;
        $offset = null;

        if (isset($params['limit'])) {
            $limit = (int)$params['limit'];
            unset($params['limit']);
        }

        if (isset($params['offset'])) {
            $offset = (int)$params['offset'];
            unset($params['offset']);
        }

        $condition = $this->buildWhere($params);

        $query = "SELECT * FROM rcl_contracts";
        if ($condition) {
            $query .= " WHERE " . $condition['where'];
        }

        if ($limit !== null) {
            $query .= " LIMIT " . $limit;
            if ($offset !== null) {
                $query .= " OFFSET " . $offset;
            }
        }

        $this->db->query($query, ...($condition['values'] ?? []));

        return $this->db->results();
    }

    /**
     * Добавить контракт
     * @param object|array $contract
     * @return int|false
     */
    public function add_contract($contract)
    {
        $contract = (array)$contract;
        $this->db->query("INSERT INTO rcl_contracts SET ?%", $contract);
        return $this->db->insert_id();
    }

    /**
     * Обновить контракт
     * @param int $id
     * @param object|array $contract
     * @return bool
     */
    public function update_contract($id, $contract)
    {
        $contract = (array)$contract;
        $this->db->query("UPDATE rcl_contracts SET ?% WHERE id = ?", $contract, (int)$id);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Отметить результат отправки в 1С
     * @param int $id
     * @param int $sent_onec
     * @return bool
     */
    public function mark_sent_onec($id, int $sent_onec)
    {
        return $this->update_contract($id, [
            'sent_onec' => $sent_onec,
            'sent_onec_date' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Получить транш по фильтру
     *
     * Примеры:
     *   get_tranche(['id' => 5])
     *   get_tranche(['order_id' => 123])
     *
     * @param array $params [field => value]
     * @return object|null
     */
    public function get_tranche(array $params)
    {
        $condition = $this->buildWhere($params);
        if (!$condition) {
            return null;
        }

        $query = "SELECT * FROM rcl_tranches WHERE " . $condition['where'] . " LIMIT 1";
        $this->db->query($query, ...$condition['values']);

        return $this->db->result();
    }

    /**
     * Получить транши по фильтру
     *
     * Примеры:
     *   get_tranches(['rcl_contract_id' => 5])
     *   get_tranches(['order_id' => 123])
     *   get_tranches(['rcl_contract_id' => 5, 'limit' => 10, 'offset' => 20])
     *
     * @param array $params [field => value]
     * @return array
     */
    public function get_tranches(array $params = [])
    {
        $limit = null;
        $offset = null;

        if (isset($params['limit'])) {
            $limit = (int)$params['limit'];
            unset($params['limit']);
        }

        if (isset($params['offset'])) {
            $offset = (int)$params['offset'];
            unset($params['offset']);
        }

        $condition = $this->buildWhere($params);

        $query = "SELECT * FROM rcl_tranches";
        if ($condition) {
            $query .= " WHERE " . $condition['where'];
        }

        if ($limit !== null) {
            $query .= " LIMIT " . $limit;
            if ($offset !== null) {
                $query .= " OFFSET " . $offset;
            }
        }

        $this->db->query($query, ...($condition['values'] ?? []));

        return $this->db->results();
    }

    /**
     * Добавить транш
     * @param int $order_id
     * @param int $rcl_contract_id
     * @return int|false
     */
    public function add_tranche($order_id, $rcl_contract_id)
    {
        $this->db->query("INSERT INTO rcl_tranches SET order_id = ?, rcl_contract_id = ?",
            (int)$order_id, (int)$rcl_contract_id);
        return $this->db->insert_id();
    }

    /**
     * Создать номер контракта
     * @param int $contract_id
     * @return string
     */
    public function create_number(int $contract_id): string
    {
        return 'RCL-' . $contract_id;
    }

    /**
     * Создать транш для заявки
     * Если контракт не существует — создаёт его
     *
     * @param object $order
     * @return int|false ID транша или false при ошибке
     */
    public function create_tranche($order)
    {
        $contract = $this->get_contract([
            'user_id' => $order->user_id,
            'organization_id' => $order->organization_id
        ]);

        if (!$contract) {
            $max_amount = $this->order_data->read($order->order_id, $this->order_data::RCL_AMOUNT);
            $uid = exec($this->config->root_dir . 'generic/uidgen');

            $contract_id = $this->add_contract([
                'organization_id' => $order->organization_id,
                'user_id' => $order->user_id,
                'number' => null,
                'max_amount' => $max_amount,
                'status' => self::STATUS_APPROVED,
                'uid' => $uid,
                'asp_code' => $order->accept_sms,
                'date_create' => date('Y-m-d H:i:s'),
                'date_start' => date('Y-m-d'),
                'date_end' => date('Y-m-d', strtotime('+' . self::CONTRACT_DURATION_YEARS . ' years')),
                'sent_onec' => self::SENT_ONEC_NEW,
            ]);

            if (!$contract_id) {
                return false;
            }

            $number = $this->create_number($contract_id);
            $this->update_contract($contract_id, ['number' => $number]);

            $contract = $this->get_contract(['id' => $contract_id]);
        }

        return $this->add_tranche($order->order_id, $contract->id);
    }

    /**
     * Рассчитать ПСК
     * @param float $percent
     * @return float
     */
    public function calculatePsk(float $percent): float
    {
        return $percent * 365;
    }

    /**
     * Рассчитать ПСК в рублях
     * @param float $percent
     * @param float $max_amount
     * @return float
     */
    public function calculatePskRub(float $percent, float $max_amount): float
    {
        return $percent * $max_amount * self::RCL_TRANCHE_MAX_PERIOD / 100;
    }

    /**
     * Вернёт true, если у клиента есть закрытый транш
     * @param string|int $user_id
     * @return bool
     */
    public function hasClosedTranche($user_id): bool
    {
        $cache_key = "rcl:hasClosedTranche:$user_id";
        if ($this->caches->get($cache_key)) {
            return true;
        }

        $contract = $this->get_contract([
            'user_id' => $user_id,
            'status' => self::STATUS_APPROVED,
        ]);
        if (empty($contract)) {
            return false;
        }

        $tranches = $this->get_tranches(['rcl_contract_id' => $contract->id]);
        if (empty($tranches)) {
            return false;
        }

        foreach ($tranches as $t) {
            $order = $this->orders->get_order($t->order_id);
            if (!$order) {
                continue;
            }

            if (
                ($order->status == $this->orders::STATUS_CONFIRMED || $order->status == $this->orders::STATUS_CLOSED)
                && $order->status_1c == $this->orders::ORDER_1C_STATUS_CLOSED
            ) {
                $this->caches->set($cache_key, true, 3600); // Запоминаем результат на час
                return true;
            }

            $status_1c = $this->orders->get_1c_status($order->id_1c);
            if ($status_1c && $status_1c == $this->orders::ORDER_1C_STATUS_CLOSED) {
                $this->caches->set($cache_key, true, 3600); // Запоминаем результат на час
                return true;
            }
        }

        return false;
    }

    /**
     * Построить WHERE условие из параметров
     *
     * Форматы:
     *   [field => value] — точное совпадение (field = value)
     *   [field => ['from' => val]] — больше или равно (field >= val)
     *   [field => ['to' => val]] — меньше или равно (field <= val)
     *   [field => ['from' => val1, 'to' => val2]] — диапазон (field >= val1 AND field <= val2)
     *
     * @param array $params
     * @return array|null ['where' => string, 'values' => array]
     */
    private function buildWhere(array $params): ?array
    {
        if (empty($params)) {
            return null;
        }

        $where = [];
        $values = [];

        foreach ($params as $field => $value) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field)) {
                continue;
            }

            if (is_array($value)) {
                if (isset($value['from'])) {
                    $where[] = "`$field` >= ?";
                    $values[] = $value['from'];
                }
                if (isset($value['to'])) {
                    $where[] = "`$field` <= ?";
                    $values[] = $value['to'];
                }
                if (!isset($value['from']) && !isset($value['to'])) {
                    $where[] = "`$field` IN (?@)";
                    $values[] = $value;
                }
            } else {
                $where[] = "`$field` = ?";
                $values[] = $value;
            }
        }

        if (empty($where)) {
            return null;
        }

        return [
            'where' => implode(' AND ', $where),
            'values' => $values
        ];
    }
}
