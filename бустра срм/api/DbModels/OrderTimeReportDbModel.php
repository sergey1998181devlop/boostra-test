<?php

namespace api\DbModels;

use Database;
use Orders;

class OrderTimeReportDbModel extends Database
{
    /**
     * Получает данные для отчета "Время обработки заявок"
     * @param array $data_filters
     * @return array|false
     */
    public function getOrdersTime(array $data_filters)
    {
        $query = $this->db->placehold("
                SELECT
                    o.manager_id,
                    o.have_close_credits,
                    o.status,
                    (o.status IN (" . implode(',', [Orders::ORDER_STATUS_CRM_APPROVED, Orders::ORDER_STATUS_CRM_ISSUED]) . ")
                        OR 
                    o.1c_status = '" . Orders::ORDER_1C_STATUS_ISSUED . "') as finished,
                    TIME_TO_SEC(TIMEDIFF(COALESCE(o.confirm_date, NOW()), o.accept_date)) as time_seconds_diff
                FROM s_orders AS o
                WHERE 1
                    AND DATE(o.accept_date) <= ?
                    AND DATE(o.accept_date) >= ?
                    AND o.status NOT IN (" . implode(',', [Orders::ORDER_STATUS_CRM_REJECT]) . ")
                    " . $data_filters['filter_client_query'] . "
                    " . $data_filters['filter_source_query'] . "
                    " . $data_filters['filter_manager_query'] . "
            ", $data_filters['date_to'], $data_filters['date_from']);
        $this->db->query($query);
        return $this->db->results();
    }
}
