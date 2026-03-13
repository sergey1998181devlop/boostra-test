<?php

namespace App\Repositories;

use Database;

class MindboxDbRepository
{
    /** @var Database */
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @param array $userIds
     * @return array
     */
    public function getUserDataBatch(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $query = $this->db->placehold("
            SELECT
                u.id, u.created, u.phone_mobile, u.email, u.lastname, u.firstname,
                u.patronymic, u.birth, u.gender, u.bankrupt,
                u.maratorium_id, u.maratorium_date, u.Regregion, u.partner_name,
                u.factual_address_id,
                u.utm_source, u.utm_medium, u.utm_campaign, u.utm_content, u.utm_term,
                u.webmaster_id, u.personal_data_added, u.personal_data_added_date,
                u.address_data_added, u.address_data_added_date, u.accept_data_added,
                u.accept_data_added_date, u.additional_data_added, u.additional_data_added_date,
                u.files_added, u.files_added_date, u.card_added, u.card_added_date,
                u.stage_sms_sended, u.last_lk_visit_time, u.quantity_loans, u.service_sms,
                bsa.created_at as block_sms_created_at,
                bl.created_date as blacklist_created,
                ua.region_code as factual_region_code
            FROM __users u
            LEFT JOIN __block_sms_adv bsa ON u.id = bsa.user_id
            LEFT JOIN __blacklist bl ON u.id = bl.user_id
            LEFT JOIN users_addresses ua ON ua.id = u.factual_address_id
            WHERE u.id IN(?@)
        ", $userIds);

        $this->db->query($query);
        $results = $this->db->results();
        if (!is_array($results)) {
            return [];
        }

        $usersMap = [];
        foreach ($results as $user) {
            $usersMap[$user->id] = $user;
        }

        return $usersMap;
    }

    /**
     * @param array $orderIds
     * @return array
     */
    public function getOrdersDataBatch(array $orderIds): array
    {
        if (empty($orderIds)) {
            return [];
        }

        $this->db->query("
            SELECT 
                u.phone_mobile,
                o.id, 
                CASE 
                    WHEN o.is_credit_doctor = 1 
                        OR o.additional_service = 1 
                        OR o.additional_service_repayment = 1 
                        OR o.additional_service_partial_repayment = 1 
                        OR o.cancellation_additional_services_by_phone = 1
                    THEN 1 ELSE 0
                END as addition_services, 
                o.id as order_id, 
                o.user_id, 
                o.percent, 
                o.amount as req_amount, 
                o.confirm_date, 
                o.reason_id, 
                o.approve_amount, 
                o.period, 
                o.date, 
                o.uid, 
                o.`1c_status`,
                o.status,
                o.utm_source, 
                o.utm_medium, 
                o.utm_campaign, 
                o.webmaster_id, 
                ROUND(o.pdn_nkbi_loan, 2) as pdn_nkbi, 
                o.scorista_ball, 
                o.`1c_id`, 
                o.first_loan, 
                SUM(op.amount) as amount_payments, 
                SUM(op.loan_body_summ) as body_sum, 
                c.issuance_date, 
                c.close_date, 
                c.`number` as contract,
                c.prolongation_count
            FROM __orders o 
            LEFT JOIN __users u ON u.id = o.user_id
            LEFT JOIN __contracts c ON c.order_id = o.id  
            LEFT JOIN __operations op ON op.order_id = o.id 
            WHERE o.id IN(?@)
            GROUP BY o.id
        ", $orderIds);

        $results = $this->db->results();
        if (!is_array($results)) {
            return [];
        }

        $ordersMap = [];
        foreach ($results as $order) {
            $ordersMap[$order->id] = $order;
        }

        return $ordersMap;
    }

    /**
     * @param array $orderIds
     * @return array
     */
    public function getOrderAddonsBatch(array $orderIds): array
    {
        if (empty($orderIds)) {
            return [];
        }

        $this->db->query("
            SELECT 
                dl.order_id,
                dl.service_type, 
                dl.license_key, 
                so.return_amount, 
                so.return_date, 
                so.date_added, 
                so.status,
                so.amount
            FROM __dop_licenses dl
            LEFT JOIN __star_oracle so ON so.id = dl.service_id
            WHERE dl.service_type = 'star_oracle' AND dl.order_id IN(?@)

            UNION ALL

            SELECT 
                dl.order_id,
                dl.service_type, 
                dl.license_key, 
                tv.return_amount, 
                tv.return_date, 
                tv.date_added, 
                tv.status,
                tv.amount
            FROM __dop_licenses dl
            LEFT JOIN __tv_medical_payments tv ON tv.id = dl.service_id
            WHERE dl.service_type = 'vitamed' AND dl.order_id IN(?@)

            UNION ALL

            SELECT 
                dl.order_id,
                dl.service_type, 
                dl.license_key, 
                mp.return_amount, 
                mp.return_date, 
                mp.date_added, 
                mp.status,
                mp.amount
            FROM __dop_licenses dl
            LEFT JOIN __multipolis mp ON mp.id = dl.service_id
            WHERE dl.service_type = 'concierge' AND dl.order_id IN(?@)

            UNION ALL

            SELECT 
                cd.order_id,
                'credit_doctor' as service_type, 
                '' as license_key, 
                cd.return_amount, 
                cd.return_date, 
                cd.date_added, 
                cd.status,
                cd.amount
            FROM __credit_doctor_to_user cd
            WHERE cd.order_id IN(?@)

            ORDER BY order_id DESC
        ", $orderIds, $orderIds, $orderIds, $orderIds);

        $results = $this->db->results();
        if (!is_array($results)) {
            return [];
        }

        $addonsMap = [];
        foreach ($results as $addon) {
            if (!isset($addonsMap[$addon->order_id])) {
                $addonsMap[$addon->order_id] = [];
            }
            $addonsMap[$addon->order_id][] = $addon;
        }

        return $addonsMap;
    }

    /**
     * Получение данных пользователей для экспорта
     * @param string $startDate
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getUserDataForExport(string $startDate, int $offset = 0, int $limit = 1000): array
    {
        $sql = "
            SELECT 
                u.id, u.created, u.phone_mobile, u.email, u.lastname, u.firstname,
                u.patronymic, u.birth, u.gender, u.bankrupt, u.maratorium_date, u.Regregion,
                u.partner_name, u.utm_source, u.utm_medium, u.utm_campaign, u.utm_content, u.utm_term,
                u.personal_data_added, u.personal_data_added_date, u.accept_data_added,
                u.accept_data_added_date, u.card_added, u.card_added_date,
                bsa.created_at as block_sms_created_at,
                ua.region_code as factual_region_code,
                ua.region as factual_region_name
            FROM __users u
            LEFT JOIN __block_sms_adv bsa ON u.id = bsa.user_id
            LEFT JOIN users_addresses ua ON ua.id = u.factual_address_id
            WHERE u.created >= ?
              AND u.site_id = 'boostra'
            ORDER BY u.id ASC
            LIMIT ? OFFSET ?
        ";
        $query = $this->db->placehold($sql, $startDate, $limit, $offset);
        $this->db->query($query);
        $result = $this->db->results();
        if (!is_array($result)) {
            return [];
        }
        return $result;
    }

    /**
     * Подсчет количества пользователей для экспорта
     * @param string $startDate
     * @return int
     */
    public function countUserDataForExport(string $startDate): int
    {
        $sql = "
            SELECT COUNT(*) as cnt
            FROM __users u
            WHERE u.created >= ?
              AND u.site_id = 'boostra'
        ";
        $query = $this->db->placehold($sql, $startDate);
        $this->db->query($query);
        $result = $this->db->result();
        return (int)($result->cnt ?? 0);
    }

    /**
     * Получение данных заказов для экспорта
     * @param string $startDate
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getOrdersDataForExport(string $startDate, int $offset = 0, int $limit = 1000): array
    {
        $sql = "
            SELECT 
                u.phone_mobile,
                u.email,
                u.Regregion,
                u.factual_address_id,
                ua.region_code as factual_region_code,
                o.id, 
                CASE 
                    WHEN o.is_credit_doctor = 1 
                        OR o.additional_service = 1 
                        OR o.additional_service_repayment = 1 
                        OR o.additional_service_partial_repayment = 1 
                        OR o.cancellation_additional_services_by_phone = 1
                    THEN 1 ELSE 0
                END as addition_services,
                o.user_id,
                o.percent, 
                o.amount as req_amount, 
                o.confirm_date, 
                o.reason_id, 
                o.approve_amount, 
                o.period, 
                o.date,
                o.uid, 
                o.`1c_status`,
                o.status,
                o.utm_source, 
                o.utm_medium, 
                o.utm_campaign, 
                o.webmaster_id, 
                ROUND(o.pdn_nkbi_loan, 2) as pdn_nkbi, 
                o.scorista_ball, 
                o.`1c_id`, 
                o.first_loan, 
                o.modified,
                SUM(op.amount) as amount_payments, 
                SUM(op.loan_body_summ) as body_sum, 
                c.issuance_date, 
                c.close_date, 
                c.`number` as contract,
                c.prolongation_count
            FROM __orders o 
            LEFT JOIN __users u ON u.id = o.user_id
            LEFT JOIN users_addresses ua ON ua.id = u.factual_address_id
            LEFT JOIN __contracts c ON c.order_id = o.id  
            LEFT JOIN __operations op ON op.order_id = o.id 
            WHERE o.date >= ?
              AND u.phone_mobile IS NOT NULL 
              AND o.`1c_status` IN ('1.Рассматривается', '3.Одобрено', '5.Выдан', '6.Закрыт')
            GROUP BY o.id
            ORDER BY o.id ASC
            LIMIT ? OFFSET ?
        ";
        $query = $this->db->placehold($sql, $startDate, $limit, $offset);
        $this->db->query($query);
        $result = $this->db->results();
        if (!is_array($result)) {
            return [];
        }
        // Индексируем по id для удобства
        $indexedResult = [];
        foreach ($result as $row) {
            if ($row->date && $row->modified < $row->date) {
                $row->modified = $row->date;
            }
            $indexedResult[$row->id] = $row;
        }
        return $indexedResult;
    }

    /**
     * Подсчет количества заказов для экспорта
     * @param string $startDate
     * @return int
     */
    public function countOrdersDataForExport(string $startDate): int
    {
        $sql = "
            SELECT COUNT(DISTINCT o.id) as cnt
            FROM __orders o 
            LEFT JOIN __contracts c ON c.order_id = o.id  
            LEFT JOIN __operations op ON op.order_id = o.id 
            LEFT JOIN __users u ON u.id = o.user_id
            WHERE o.date >= ?
              AND u.phone_mobile IS NOT NULL 
              AND o.`1c_status` IN ('1.Рассматривается', '3.Одобрено', '5.Выдан', '6.Закрыт')
        ";
        $query = $this->db->placehold($sql, $startDate);
        $this->db->query($query);
        $result = $this->db->result();
        return (int)($result->cnt ?? 0);
    }
}