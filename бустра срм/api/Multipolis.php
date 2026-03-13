<?php

require_once 'Simpla.php';

/**
 * Class Multipolis
 * Класс для работы с мультиполисами
 */
class Multipolis extends Simpla
{
    public const STATUS_SUCCESS = 'SUCCESS';
    public const STATUS_NEW = 'NEW';
    public const STATUS_MAPPING = [
        self::STATUS_SUCCESS,
        self::STATUS_NEW,
    ];

    public function getReturnMultipolisForSend()
    {
        $query = $this->db->placehold("
            SELECT * FROM s_multipolis
            WHERE return_sent IN (0, 3) 
            AND return_status = 2
            LIMIT 5 
        ");
        $this->db->query($query);
        
        return $this->db->results();
    }

    public function get_multipolis($id)
    {
        $query = $this->db->placehold("
            SELECT * FROM s_multipolis WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        
        $result = $this->db->result();
    
        return $result;
    }

    public function update_multipolis($id, $multipolis)
    {
		$query = $this->db->placehold("
            UPDATE s_multipolis SET ?% WHERE id = ?
        ", (array)$multipolis, (int)$id);
        $this->db->query($query);
        
        return $id;
    }


    /**
     * Поиск мультиполисов по фильтру
     * @param array $filter_data
     * @param bool $return_all
     * @return array|false
     */
    public function selectAll(array $filter_data, bool $return_all = true)
    {
        $where = [];
        $sql = "SELECT * FROM s_multipolis WHERE 1
                 -- {{where}}";

        if (!empty($filter_data['filter_payment_id'])) {
            $where[] = $this->db->placehold("payment_id = ?", (int)$filter_data['filter_payment_id']);
        }

        if (!empty($filter_data['filter_payment_method'])) {
            $where[] = $this->db->placehold("payment_method = ?", $this->db->escape($filter_data['filter_payment_method']));
        }

        if (isset($filter_data['filter_is_sent'])) {
            $where[] = $this->db->placehold("is_sent = ?", (int)$filter_data['filter_is_sent']);
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
        } else {
            return $this->db->result();
        }
    }

    /**
     * Get all multipolises with user data
     *
     * @return array|false
     */
    public function getAllWithUsersData(
        array $filters,
        bool $getOrderData = false,
        bool $getPaymentData = false,
        bool $getDocumentData = false
    ): array {
        $where = [];
        $groupBy = '';
        if ($filters['filter_group_by'] === 'day' || $filters['filter_group_by'] === 'month') {
            $groupBy = 'date_filter';
        } elseif ($filters['filter_group_by'] === 'multipolis_id') {
            $groupBy = 'multipolis_id';
        }

        if (!empty($filters['filter_date_start']) && !empty($filters['filter_date_end'])) {
            $where = " DATE(m.date_added) BETWEEN '" . $filters['filter_date_start'] . "' AND '"
                . $filters['filter_date_end'];
        }

        if (!empty($filters['filter_status']) && in_array($filters['filter_status'], self::STATUS_MAPPING)) {
            $where .= "' AND m.status = '" . $filters['filter_status'] . "'";
        }

        $select = [
            "DATE_FORMAT(m.date_added, '%d-%m-%Y') AS date_filter",
            "m.id as multipolis_id",
            "m.number",
            "m.payment_method",
            "m.is_sent",
            "m.amount",
            "m.return_amount",
            "m.date_added",
            "CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) AS username",
            "u.id AS user_id",
            "u.birth",
            "u.phone_mobile",
        ];

        $query = "SELECT 
            -- {{select}}
            FROM s_multipolis m
            LEFT JOIN s_users u ON u.id = m.user_id
        ";

        if ($getOrderData === true) {
            $selectValues = [
                "o.id as order_id",
            ];
            $select[] = implode(',', $selectValues);
            $query .= " LEFT JOIN s_orders o ON o.id = m.order_id";
        }

        if ($getPaymentData === true) {
            $selectValues = [
                "o.card_id",
                "IF(o.b2p, bc.pan, tc.pan) as pan",
                "p.amount AS payment_sum",
                "p.created AS payment_date",
            ];
            $select[] = implode(',', $selectValues);
            $query .= " LEFT JOIN b2p_payments p ON m.payment_id = p.id
                LEFT JOIN b2p_cards bc ON bc.id = o.card_id AND o.b2p = 1
                LEFT JOIN s_tinkoff_cards tc ON tc.card_id = o.card_id 
                    AND m.user_id = tc.user_id AND o.b2p = 0 ";
        }

        if ($getDocumentData === true) {
            $selectValues = [
                "CONCAT_WS('/', '" . $this->config->front_url . "/document', m.user_id, d.id) as doc_url"
            ];
            $select[] = implode(',', $selectValues);
            $query .= " LEFT JOIN s_documents d ON m.user_id = d.user_id AND d.order_id = m.order_id AND d.type = ? 
                AND m.`number`= d.contract_number";
        }

        $query .= " -- {{where}} -- {{group}}";

        if ($filters['filter_group_by'] === 'day' || $filters['filter_group_by'] === 'month') {
            $dateSelectFormat = $filters['filter_group_by'] === 'month' ? '%Y.%m' : '%d-%m-%Y';
            $select = [
                "DATE_FORMAT(date_added, '" . $dateSelectFormat . "') AS date_filter",
                "COUNT(id) AS polis_count",
                "SUM(is_sent) AS sent_count"
            ];

            $query = "SELECT 
                -- {{select}}
                FROM s_multipolis m
                -- {{where}}
                -- {{group}}
            ";
        }

        $query = strtr($query, [
            '-- {{select}}' => !empty($select) ? implode(", ", $select) : '',
            '-- {{where}}' => !empty($where) ? "WHERE " . $where : '',
            '-- {{group}}' => !empty($groupBy) ? "GROUP BY " . $groupBy : '',
        ]);

        $sql = $this->db->placehold($query, $this->documents::DOC_MULTIPOLIS);
        $this->db->query($sql);

        return $this->db->results() ?: [];
    }

    /**
     * @param int $order_id
     * @param int $user_id
     * @return null|object
     */
    public function getMultipolis(int $order_id, int $user_id, ?string $status = null, ?string $dateAdded = null, ?int $amount = null)
    {
        $sql = "SELECT * FROM s_multipolis WHERE order_id = ? AND user_id = ? ";

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
}
