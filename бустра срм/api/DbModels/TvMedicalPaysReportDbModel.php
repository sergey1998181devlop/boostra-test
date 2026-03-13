<?php

namespace api\DbModels;

use api\interfaces\AdditionalPaysDbReportInterface;
use Database;

class TvMedicalPaysReportDbModel extends Database implements AdditionalPaysDbReportInterface
{
    /**
     * @inheritDoc
     */
    public function getPays(array $data_filters = [])
    {
        $query = $this->db->placehold("
                SELECT
                    'Теле-медицина' as name_pay,
                    CONCAT_WS(' ', u.lastname, u.firstname, u.patronymic) as fio,
                    c.number as contract_number,
                    tvm.api_doc_id as product_key,
                    tvmp.status,
                    tvmp.amount,
                    tvmp.date_added,
                    bp.operation_id as pay_operation_id,
                    bp.amount as pay_amount,
                    bp.created as pay_date,
                    bt.operation as return_operation_id,
                    tvmp.return_status,
                    tvmp.return_amount,
                    tvmp.return_date,
                    bp.reason_code
                FROM 
                    s_tv_medical_payments AS tvmp
                    LEFT JOIN s_tv_medical tvm ON tvm.id = tvmp.tv_medical_id
                    LEFT JOIN s_users u ON u.id = tvmp.user_id
                    LEFT JOIN s_contracts c ON c.order_id = tvmp.order_id
                    LEFT JOIN b2p_payments bp ON bp.id = tvmp.payment_id
                    LEFT JOIN b2p_transactions bt ON bt.id = tvmp.return_transaction_id
                WHERE 1
                    AND DATE(tvmp.date_added) <= ?
                    AND DATE(tvmp.date_added) >= ?
                ORDER BY tvmp.date_added DESC
            ", $data_filters['filter_date_end'], $data_filters['filter_date_start']);

        $this->db->query($query);
        return $this->db->results();
    }
}
