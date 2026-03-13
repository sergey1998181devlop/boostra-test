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
     * Период для расчёта ПСК
     */
    public const RCL_TRANCHE_PERIOD = 160;

    /**
     * Срок жизни ВКЛ договора
     */
    public const CONTRACT_DURATION_YEARS = 5;

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
            $max_amount = $this->order_data->read($order->order_id, $this->order_data::RCL_MAX_AMOUNT);
            $uid = exec($this->config->root_dir . 'generic/uidgen');

            $psk = $this->calculatePsk($this->orders::BASE_PERCENTS);
            $psk_rub = $this->calculatePskRub($this->orders::BASE_PERCENTS, $max_amount);
            $pdnRow = $this->pdnCalculation->getPdnRow($order->order_id);
            $pdn_calculation_id = $pdnRow ? $pdnRow->id : null;
            $without_ch = $this->order_data->read($order->order_id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS);

            $contract_id = $this->add_contract([
                'organization_id' => $order->organization_id,
                'user_id' => $order->user_id,
                'number' => null,
                'max_amount' => $max_amount,
                'psk' => $psk,
                'psk_rub' => $psk_rub,
                'pdn_calculation_id' => $pdn_calculation_id,
                'without_ch' => $without_ch ?: null,
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

            $this->order_data->set($order->order_id, $this->order_data::RCL_FIRST_TRANCHE, 1);

            $contract = $this->get_contract(['id' => $contract_id]);
        }

        return $this->add_tranche($order->order_id, $contract->id);
    }

    /**
     * Получить первый транш контракта
     * @param int $rcl_contract_id
     * @return object|null
     */
    public function get_first_tranche(int $rcl_contract_id)
    {
        $this->db->query("
        SELECT t.* 
        FROM rcl_tranches t
        JOIN s_order_data od ON od.order_id = t.order_id 
            AND od.`key` = ? 
            AND od.value = '1'
        WHERE t.rcl_contract_id = ?
        LIMIT 1
    ", $this->order_data::RCL_FIRST_TRANCHE, $rcl_contract_id);

        return $this->db->result();
    }

    /**
     * Рассчитать ПСК
     * 
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
        return $percent * $max_amount * self::RCL_TRANCHE_PERIOD / 100;
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
