<?php

require_once 'Simpla.php';

/**
 * Class CompanyOrders
 */
class CompanyOrders extends Simpla
{
    /**
     * Новая заявка
     */
    const STATUS_NEW = 1;

    /**
     * Одобренная заявка
     */
    const STATUS_APPROVED = 2;

    /**
     * Отказная заявка
     */
    const STATUS_REJECT = 3;

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateItem(int $id, array $data = [])
    {
        $query = $this->db->placehold("UPDATE s_company_orders SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * @param array $filter_data
     * @param bool $get_total
     * @return array|false
     */
    public function getItems(array $filter_data = [], bool $get_total = false)
    {
        $where = [];
        $order_by = "co.id";
        $order_sort = "DESC";
        $select = 'co.id, co.amount, co.status, co.created_at, u.inn, u.phone_mobile, u.email';
        $limit = '';

        $query = "SELECT 
                    -- {{select}}
                FROM s_company_orders AS co 
                    LEFT JOIN s_users u ON u.id = co.user_id
                WHERE 1
                -- {{where}}
                -- {{order}}
                -- {{limit}}
                ";

        if (!empty($filter_data['status'])) {
            $where[] = $this->db->placehold("co.status = ?", (int)$filter_data['status']);
        }

        if (!empty($filter_data['user'])) {
            foreach ($filter_data['user'] as $key => $value) {
                $where[] = $this->db->placehold("u.$key = ?", $value);
            }
        }

        if ($get_total) {
            $select = 'COUNT(*) AS total';
        }

        if (!empty($filter_data['limit'])) {
            if (!empty($filter_data['offset'])) {
                $limit = 'LIMIT ' . $filter_data['offset'] . ', ' . $filter_data['limit'];
            } else {
                $limit = 'LIMIT ' . $filter_data['limit'];
            }
        }

        if (!empty($filter_data['phone'])) {
            $where[] = $this->db->placehold("phone = ?", $filter_data['phone']);
        }

        if (!empty($filter_data['inn'])) {
            $where[] = $this->db->placehold("inn = ?", $filter_data['inn']);
        }

        $query = strtr($query, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
            '-- {{order}}' => "ORDER BY " . $order_by . " " . $order_sort,
            '-- {{select}}' => $select,
            '-- {{limit}}' => $limit,
        ]);

        $sql = $this->db->placehold($query);
        $this->db->query($sql);

        if ($get_total) {
            return $this->db->result('total');
        } else {
            return $this->db->results();
        }
    }

    /**
     * @param int $id
     * @return false|int
     */
    public function getItem(int $id)
    {
        $query = $this->db->placehold("SELECT 
            co.*,
            t.name as credit_target_name
        FROM s_company_orders co
        LEFT JOIN s_co_credit_targets t ON t.id = co.co_credit_target_id
        WHERE co.id = ?", $id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * @return int[]
     */
    public static function getStatuses(): array
    {
        return [
             self::STATUS_NEW => 'Новая',
             self::STATUS_APPROVED => 'Одобрена',
             self::STATUS_REJECT => 'Отказ',
        ];
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function deleteItem(int $id)
    {
        $query = $this->db->placehold("DELETE FROM s_company_orders WHERE id = ?", $id);
        return $this->db->query($query);
    }

    /**
     * Получает цели кредитования
     * @return array|false
     */
    public function getCreditTargets()
    {
        $sql = $this->db->placehold("SELECT * FROM s_co_credit_targets");
        $this->db->query($sql);
        return $this->db->results();
    }
}
