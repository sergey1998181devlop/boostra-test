<?php

namespace api\DbModels;

use api\interfaces\AdditionalPaysDbReportInterface;
use Database;

class CreditDoctorPaysReportDbModel extends Database implements AdditionalPaysDbReportInterface
{
    /**
     * @inheritDoc
     */
    public function getPays(array $data_filters = [])
    {
        $query = $this->db->placehold("
                SELECT
                    'Кредитный доктор' as name_pay,
                    CONCAT_WS(' ', u.lastname, u.firstname, u.patronymic) as fio,
                    c.number as contract_number,
                    cd.credit_doctor_condition_id as product_key,
                    cd.status,
                    cd.amount,
                    cd.date_added,
                    btp.operation as pay_operation_id,
                    btp.amount / 100 as pay_amount,
                    btp.created as pay_date,
                    btr.operation as return_operation_id,
                    cd.return_status,
                    cd.return_amount,
                    cd.return_date,
                    btp.reason_code
                FROM 
                    s_credit_doctor_to_user AS cd
                    LEFT JOIN s_users u ON u.id = cd.user_id
                    LEFT JOIN s_contracts c ON c.order_id = cd.order_id
                    LEFT JOIN b2p_transactions btp ON btp.id = cd.transaction_id
                    LEFT JOIN b2p_transactions btr ON btr.id = cd.return_transaction_id
                WHERE 1
                    AND DATE(cd.date_added) <= ?
                    AND DATE(cd.date_added) >= ?
                ORDER BY cd.date_added DESC
            ", $data_filters['filter_date_end'], $data_filters['filter_date_start']);

        $this->db->query($query);
        return $this->db->results();
    }
}
