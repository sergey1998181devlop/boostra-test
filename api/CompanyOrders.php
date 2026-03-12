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

    public function addItem(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_company_orders SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Проверяет лимит заявок за текущий день
     * @return bool
     */
    public function checkShowHref(): bool
    {
        $date_start = date("Y-m-d 00:00:00");
        $date_end = date("Y-m-d 23:59:59");

        $query = $this->db->placehold("SELECT COUNT(*) as total FROM s_company_orders WHERE created_at >= ? AND created_at <= ?", $date_start, $date_end);
        $this->db->query($query);
        return (int)$this->db->result('total') < (int)($this->config->company_form_day_limit ?: 100);
    }

    /**
     * @param array $filter_data
     * @param bool $get_total
     * @return array|false
     */
    public function getItems(array $filter_data = [], bool $get_total = false)
    {
        $where = [];
        $order_by = "id";
        $order_sort = "DESC";
        $select = '*';
        $limit = '';

        $query = "SELECT 
                    -- {{select}}
                FROM s_company_orders
                WHERE 1
                -- {{where}}
                -- {{order}}
                -- {{limit}}
                ";

        if (!empty($filter_data)) {
            foreach ($filter_data as $key => $value) {
                if (is_array($value)) {
                    $where[] = $this->db->placehold("$key IN (?@)", $value);
                } else {
                    $where[] = $this->db->placehold("$key = ?", $value);
                }
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
