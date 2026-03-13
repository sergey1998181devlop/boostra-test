<?php

require_once 'Simpla.php';

/**
 * class CreditRating
 * класс для работы с КР
 */
class CreditRating extends Simpla
{
    public function getPayments(array $filter_data = [])
    {
        $where = [];
        $where_payment_type = $this->db->placehold(
            'payment_type IN (?@)',
            array_values($this->best2pay::PAYMENT_TYPE_CREDIT_RATING_MAPPING)
        );

        $sql = "SELECT * FROM (SELECT 
                    bp.amount,
                    u.phone_mobile,
                    CONCAT_WS(' ', u.lastname, u.firstname, u.patronymic) as fio,
                    bp.created
                FROM b2p_payments bp
                LEFT JOIN s_users u ON u.id = bp.user_id
                WHERE 1
                AND $where_payment_type
                AND reason_code = 1
                UNION ALL
                SELECT 
                    t.amount,
                    u.phone_mobile,
                    CONCAT_WS(' ', u.lastname, u.firstname, u.patronymic) as fio,
                    t.created
                FROM s_transactions t
                LEFT JOIN s_users u ON u.id = t.user_id
                WHERE 1
                AND $where_payment_type
                AND " . $this->db->placehold('status IN (?@)', $this->transactions::STATUSES_SUCCESS) . ") as r
                WHERE 1
                -- {{where}}";

        if (!empty($filter_data['filter_created_date'])) {
            $where[] = $this->db->placehold("r.created BETWEEN ? AND ?", $filter_data['filter_created_date']['filter_date_start'], $filter_data['filter_created_date']['filter_date_end'] . ' 23:59:59');
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);
        return $this->db->results();
    }
}
