<?php

namespace api\DbModels;

use api\interfaces\AdditionalPaysDbReportInterface;
use Database;

class MultipolisPaysReportDbModel extends Database implements AdditionalPaysDbReportInterface
{
    /**
     * @inheritDoc
     */
    public function getPays(array $data_filters = [])
    {
        $query = $this->db->placehold("
                SELECT
                    'Консьерж сервис' as name_pay,
                    CONCAT_WS(' ', u.lastname, u.firstname, u.patronymic) as fio,
                    c.number as contract_number,
                    m.number as product_key,
                    m.status,
                    m.amount,
                    m.date_added,
                    bp.operation_id as pay_operation_id,
                    bp.amount as pay_amount,
                    bp.created as pay_date,
                    bt.operation as return_operation_id,
                    m.return_status,
                    m.return_amount,
                    m.return_date,
                    bp.reason_code
                FROM 
                    s_multipolis AS m
                    LEFT JOIN s_users u ON u.id = m.user_id
                    LEFT JOIN s_contracts c ON c.order_id = m.order_id
                    LEFT JOIN b2p_payments bp ON bp.id = m.payment_id
                    LEFT JOIN b2p_transactions bt ON bt.id = m.return_transaction_id
                WHERE 1
                    AND DATE(m.date_added) <= ?
                    AND DATE(m.date_added) >= ?
                ORDER BY m.date_added DESC
            ", $data_filters['filter_date_end'], $data_filters['filter_date_start']);

        $this->db->query($query);
        return $this->db->results();
    }
}
