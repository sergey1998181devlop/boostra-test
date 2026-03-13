<?php

use App\Modules\VoxCallsArchive\Application\Service\VoxCallsArchiveQueryService;

class MissingService
{
    private Database $db;

    /** @var VoxCallsArchiveQueryService|null */
    private $archiveQueryService = null;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Получить сервис чтения из архива
     *
     * @return VoxCallsArchiveQueryService
     */
    private function getArchiveQueryService(): VoxCallsArchiveQueryService
    {
        if ($this->archiveQueryService === null) {
            $this->archiveQueryService = new VoxCallsArchiveQueryService();
        }
        return $this->archiveQueryService;
    }

    /**
     * Получает данные заявок для конкретного менеджера.
     * Использует агрегирующие подзапросы для звонков и заказов, чтобы избежать дублирования клиентов.
     *
     * @param int    $managerId ID менеджера
     * @param string $dateFrom  Дата начала диапазона (Y-m-d H:i:s)
     * @param string $dateTo    Дата конца диапазона (Y-m-d H:i:s)
     * @return array
     */
    public function getManagerIssueDetails(int $managerId, string $dateFrom, string $dateTo): array
    {
        $firstMissingDateExpr = $this->getFirstMissingDateExpr();
        $lastStageExpr        = $this->getLastStageExpr();

        // Запрос без звонков - звонки получим из архива отдельно
        $sql = "
        SELECT
            u.id,
            u.created,
            u.utm_source,
            u.phone_mobile,
            u.stage_in_contact,
            u.call_status,
            u.continue_order,
            u.additional_data_added,
            u.additional_data_added_date,
            u.personal_data_added_date,
            u.address_data_added_date,
            u.accept_data_added_date,
            u.card_added_date,
            u.files_added_date,
            $firstMissingDateExpr AS first_missing_date,
            m.name_1c AS manager_name,
            IF(u.additional_data_added = 1, 'Да', 'Нет') AS completed,
            CASE
                WHEN u.call_status = 2 THEN 'Не дозвонились'
                WHEN u.call_status = 1 THEN 'Дозвонились'
                ELSE ''
            END AS call_status_text,
            CASE
                WHEN u.continue_order = 2 THEN 'Нет'
                WHEN u.continue_order = 1 THEN 'Да'
                ELSE ''
            END AS continue_order_text,
            $lastStageExpr AS last_step,
            loan.loan_issued
        FROM s_users u
        LEFT JOIN s_user_data ud ON ud.user_id = u.id AND ud.key = 'is_rejected_nk'
        LEFT JOIN s_managers m ON m.id = u.missing_manager_id
        LEFT JOIN (
            SELECT user_id, IF(MAX(CASE WHEN o.`1c_status` = '5.Выдан' THEN 1 ELSE 0 END) > 0, 'Да', 'Нет') AS loan_issued
            FROM s_orders o
            GROUP BY user_id
        ) loan ON loan.user_id = u.id
        WHERE (ud.value IS NULL OR ud.value = 0)
          AND u.missing_manager_id = ?
          AND ($firstMissingDateExpr BETWEEN ? AND ?)
        ORDER BY first_missing_date DESC
    ";

        $query = $this->db->placehold($sql, $managerId, $dateFrom, $dateTo);
        $this->db->query($query);
        $results = $this->db->results() ?: [];

        if (empty($results)) {
            return $results;
        }

        // Собираем user_id для запроса звонков из архива
        $userIds = array_map(function($row) {
            return (int)$row->id;
        }, $results);

        // Получаем звонки из архивной БД
        $callsAggregated = $this->getArchiveQueryService()->getCallsAggregatedByUser($dateFrom, $dateTo, $userIds);

        // Мержим данные звонков
        foreach ($results as $row) {
            $userId = (int)$row->id;
            if (isset($callsAggregated[$userId])) {
                $row->last_call = $callsAggregated[$userId]['last_call'] ?? '';
            } else {
                $row->last_call = '';
            }
        }

        return $results;
    }


    /**
     * Получает данные заявок (детализированный список).
     * Использует CTE для фильтрации пользователей и агрегации звонков.
     *
     * @param string $dateFrom   Дата начала диапазона (Y-m-d H:i:s)
     * @param string $dateTo     Дата конца диапазона (Y-m-d H:i:s)
     * @param array  $managerIds Массив ID менеджеров
     * @return array
     */
    public function getIssueDetails(string $dateFrom, string $dateTo, array $managerIds = []): array
    {
        // готовим строку для IN (...)
        $managerIdsStr = implode(',', array_map('intval', $managerIds));

        // Запрос без CTE calls_per_user - звонки получим из архива
        $sql = "
        WITH users_filtered AS (
            SELECT
                u.id,
                u.phone_mobile,
                u.utm_source,
                u.stage_in_contact,
                u.call_status,
                u.continue_order,
                u.additional_data_added,
                u.additional_data_added_date,
                u.personal_data_added_date,
                u.address_data_added_date,
                u.accept_data_added_date,
                u.card_added_date,
                u.files_added_date,
                u.missing_manager_id,
                COALESCE(ud.value,0) AS bonon,
                -- стадия
                CASE
                    WHEN u.personal_data_added_date IS NULL THEN 1
                    WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN 2
                    WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN 3
                    WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN 4
                    WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN 5
                    WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN 6
                    ELSE 7
                END AS stage,
                -- первая дата для диапазона
                DATE(
                    CASE
                        WHEN u.personal_data_added_date IS NULL THEN u.created
                        WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                        WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                        WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                        WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                        WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                    END
                ) AS first_missing_date
            FROM s_users u
            LEFT JOIN s_user_data ud
                ON ud.user_id = u.id AND ud.key = 'is_rejected_nk'
            WHERE u.missing_manager_id IN ($managerIdsStr)
              AND (
                CASE
                    WHEN u.personal_data_added_date IS NULL THEN u.created
                    WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                    WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                    WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                    WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                    WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                END
              ) BETWEEN ? AND ?
        )

        SELECT
            u.id,
            u.first_missing_date,
            u.phone_mobile,
            u.utm_source,
            COALESCE(m.name_1c, m.name, m.login, '') AS manager_name,
            u.stage_in_contact,
            u.call_status,
            CASE
                WHEN u.call_status = 2 THEN 'Не дозвонились'
                WHEN u.call_status = 1 THEN 'Дозвонились'
                ELSE '—'
            END AS call_status_text,
            u.continue_order,
            CASE
                WHEN u.continue_order = 2 THEN 'Нет'
                WHEN u.continue_order = 1 THEN 'Да'
                ELSE '—'
            END AS continue_order_text,
            u.additional_data_added,
            CASE WHEN u.additional_data_added = 1 THEN 'Да' ELSE 'Нет' END AS completed,
            u.stage AS last_step,
            -- bonon
            u.bonon AS bonon,
            CASE WHEN u.bonon = 1 THEN 'БонОн' ELSE 'Не БонОн' END AS bonon_text,
            -- заказы напрямую
            MAX(CASE WHEN o.`1c_status` = '5.Выдан' THEN 1 ELSE 0 END) AS has_issued_loan,
            MAX(CASE WHEN o.`1c_status` = '3.Одобрено' THEN 1 ELSE 0 END) AS has_approved_loan,
            MAX(CASE WHEN o.status = 3 THEN 1 ELSE 0 END) AS has_rejected_loan,
            CASE WHEN MAX(CASE WHEN o.`1c_status` = '5.Выдан' THEN 1 ELSE 0 END) = 1 THEN 'Да' ELSE 'Нет' END AS loan_issued
        FROM users_filtered u
        LEFT JOIN s_managers m
            ON m.id = u.missing_manager_id
        LEFT JOIN s_orders o
            ON o.user_id = u.id
            AND o.date BETWEEN ? AND ?
        GROUP BY u.id, u.first_missing_date, u.phone_mobile, u.utm_source,
                 manager_name, u.stage_in_contact, u.call_status,
                 u.continue_order, u.additional_data_added, u.stage, u.bonon
        ORDER BY u.first_missing_date DESC
    ";

        $query = $this->db->placehold($sql, $dateFrom, $dateTo, $dateFrom, $dateTo);
        $this->db->query($query);
        $results = $this->db->results() ?: [];

        if (empty($results)) {
            return $results;
        }

        // Собираем user_id для запроса звонков из архива
        $userIds = array_map(function($row) {
            return (int)$row->id;
        }, $results);

        // Получаем звонки из архивной БД
        $callsAggregated = $this->getArchiveQueryService()->getCallsAggregatedByUser($dateFrom, $dateTo, $userIds);

        // Мержим данные звонков
        foreach ($results as $row) {
            $userId = (int)$row->id;
            if (isset($callsAggregated[$userId])) {
                $callData = $callsAggregated[$userId];
                $row->last_call = $callData['last_call'] ?? '';
                $row->total_calls = (int)($callData['total_calls'] ?? 0);
                $row->accepted_calls = (int)($callData['accepted_calls'] ?? 0);
                $row->not_accepted_calls = (int)($callData['not_accepted_calls'] ?? 0);
                $row->total_duration_accepted_calls = (int)($callData['total_duration_accepted_calls'] ?? 0);
            } else {
                $row->last_call = '';
                $row->total_calls = 0;
                $row->accepted_calls = 0;
                $row->not_accepted_calls = 0;
                $row->total_duration_accepted_calls = 0;
            }
        }

        return $results;
    }



    /**
     * Получение статистики по менеджерам (role = 'contact_center_plus')
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param array  $managerIds Список ID менеджеров для выборки
     * @return array
     */
    public function getManagerStatistics(string $dateFrom, string $dateTo, array $managerIds = []): array
    {
        $sql = $this->prepareManagerStatisticsQuery($managerIds);

        $query = $this->db->placehold(
            $sql,
            $dateFrom, $dateTo, // users_filtered
            $dateFrom, $dateTo, // orders
        );

        $this->db->query($query);
        $rows = $this->db->results() ?: [];

        if (empty($rows)) {
            return $rows;
        }

        // Собираем все user_id через отдельный запрос
        $userIds = $this->getUserIdsForManagerStatistics($dateFrom, $dateTo, $managerIds);

        // Получаем звонки из архивной БД
        $callsAggregated = $this->getArchiveQueryService()->getCallsAggregatedByUser($dateFrom, $dateTo, $userIds);

        // Мержим данные звонков (агрегируем по менеджерам)
        $callsByManager = $this->aggregateCallsByManager($userIds, $callsAggregated, $dateFrom, $dateTo, $managerIds);

        foreach ($rows as $row) {
            $managerId = (int)$row->manager_id;
            if (isset($callsByManager[$managerId])) {
                $calls = $callsByManager[$managerId];
                $row->total_calls = (int)($calls['total_calls'] ?? 0);
                $row->accepted_calls = (int)($calls['accepted_calls'] ?? 0);
                $row->not_accepted_calls = (int)($calls['not_accepted_calls'] ?? 0);
                $row->total_duration_accepted_calls = (int)($calls['total_duration_accepted_calls'] ?? 0);
                $row->avg_accepted_duration = round((float)($calls['avg_accepted_duration'] ?? 0), 2);
            } else {
                $row->total_calls = 0;
                $row->accepted_calls = 0;
                $row->not_accepted_calls = 0;
                $row->total_duration_accepted_calls = 0;
                $row->avg_accepted_duration = 0;
            }
        }

        return $rows;
    }

    /**
     * Подготовка SQL-запроса для статистики менеджеров (без звонков)
     *
     * @param array $managerIds
     * @return string
     */
    private function prepareManagerStatisticsQuery(array $managerIds = []): string
    {
        // если список пустой, то ставим условие, которое ничего не вернёт
        $managerIdsSql = implode(',', array_map('intval', $managerIds));

        return "
            WITH users_filtered AS (
                SELECT
                    u.id,
                    u.call_status,
                    u.missing_manager_id,
                    u.additional_data_added,
                    u.additional_data_added_date,
                    u.accept_data_added_date,
                    u.continue_order,
                    u.created,
                    COALESCE(ud.value, 0) AS bonon
                FROM s_users u
                    LEFT JOIN s_user_data ud
                        ON ud.user_id = u.id AND ud.key = 'is_rejected_nk'
                WHERE u.missing_manager_id IN ($managerIdsSql)
                  AND (
                    CASE
                        WHEN u.personal_data_added_date IS NULL THEN u.created
                        WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                        WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                        WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                        WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                        WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                    END
                  ) BETWEEN ? AND ?
            )

            SELECT
                m.id AS manager_id,
                COALESCE(NULLIF(COALESCE(m.name, m.name_1c, m.login), ''), m.login) AS manager_name,
                COUNT(DISTINCT u.id) AS total_requests,

                -- Completed
                SUM(u.additional_data_added = 1) AS completed_total,
                ROUND(SUM(u.additional_data_added = 1) / COUNT(DISTINCT u.id) * 100, 2) AS conversion_completed,

                -- Loans
                SUM(CASE WHEN o.`1c_status` = '5.Выдан' THEN 1 ELSE 0 END) AS issued_count,
                ROUND(SUM(CASE WHEN o.`1c_status` = '5.Выдан' THEN 1 ELSE 0 END) / COUNT(DISTINCT u.id) * 100, 2) AS conversion_issued,

                SUM(CASE WHEN o.`1c_status` = '3.Одобрено' THEN 1 ELSE 0 END) AS approved_count,
                ROUND(SUM(CASE WHEN o.`1c_status` = '3.Одобрено' THEN 1 ELSE 0 END) / COUNT(DISTINCT u.id) * 100, 2) AS conversion_approved,

                SUM(CASE WHEN o.status = 3 THEN 1 ELSE 0 END) AS rejected_count,
                ROUND(SUM(CASE WHEN o.status = 3 THEN 1 ELSE 0 END) / COUNT(DISTINCT u.id) * 100, 2) AS conversion_rejected,

                -- Bonon
                SUM(CASE WHEN u.bonon = 1 THEN 1 ELSE 0 END) AS bonon_count,
                SUM(CASE WHEN u.bonon = 0 THEN 1 ELSE 0 END) AS not_bonon_count,
                ROUND(SUM(CASE WHEN u.bonon = 1 THEN 1 ELSE 0 END) / COUNT(DISTINCT u.id) * 100, 2) AS conversion_bonon

            FROM users_filtered u
                LEFT JOIN s_managers m
                    ON m.id = u.missing_manager_id
                       AND m.id IN ($managerIdsSql)
                LEFT JOIN s_orders o
                    ON o.user_id = u.id
                       AND o.date BETWEEN ? AND ?
            GROUP BY m.id, manager_name
            ORDER BY total_requests DESC
    ";
    }

    /**
     * Получить список user_id для статистики менеджеров
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param array  $managerIds
     * @return array
     */
    private function getUserIdsForManagerStatistics(string $dateFrom, string $dateTo, array $managerIds): array
    {
        $managerIdsSql = implode(',', array_map('intval', $managerIds));

        $sql = "
            SELECT u.id, u.missing_manager_id
            FROM s_users u
            WHERE u.missing_manager_id IN ($managerIdsSql)
              AND (
                CASE
                    WHEN u.personal_data_added_date IS NULL THEN u.created
                    WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                    WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                    WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                    WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                    WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                END
              ) BETWEEN ? AND ?
        ";

        $query = $this->db->placehold($sql, $dateFrom, $dateTo);
        $this->db->query($query);
        $rows = $this->db->results() ?: [];

        return $rows;
    }

    /**
     * Агрегировать звонки по менеджерам
     *
     * @param array $userManagerMap Массив объектов с user_id и missing_manager_id
     * @param array $callsAggregated Данные звонков по user_id
     * @param string $dateFrom
     * @param string $dateTo
     * @param array $managerIds
     * @return array
     */
    private function aggregateCallsByManager(array $userManagerMap, array $callsAggregated, string $dateFrom, string $dateTo, array $managerIds): array
    {
        $result = [];

        foreach ($userManagerMap as $row) {
            $userId = (int)$row->id;
            $managerId = (int)$row->missing_manager_id;

            if (!isset($result[$managerId])) {
                $result[$managerId] = [
                    'total_calls' => 0,
                    'accepted_calls' => 0,
                    'not_accepted_calls' => 0,
                    'total_duration_accepted_calls' => 0,
                    'count_users_with_calls' => 0,
                ];
            }

            if (isset($callsAggregated[$userId])) {
                $callData = $callsAggregated[$userId];
                $result[$managerId]['total_calls'] += (int)($callData['total_calls'] ?? 0);
                $result[$managerId]['accepted_calls'] += (int)($callData['accepted_calls'] ?? 0);
                $result[$managerId]['not_accepted_calls'] += (int)($callData['not_accepted_calls'] ?? 0);
                $result[$managerId]['total_duration_accepted_calls'] += (int)($callData['total_duration_accepted_calls'] ?? 0);
                if (($callData['accepted_calls'] ?? 0) > 0) {
                    $result[$managerId]['count_users_with_calls']++;
                }
            }
        }

        // Вычисляем среднее
        foreach ($result as $managerId => &$data) {
            if ($data['accepted_calls'] > 0) {
                $data['avg_accepted_duration'] = $data['total_duration_accepted_calls'] / $data['accepted_calls'];
            } else {
                $data['avg_accepted_duration'] = 0;
            }
        }

        return $result;
    }




    /**
     * Получение статистики по менеджерам с разделением по дням
     *
     * @param string $dateFrom   Дата начала диапазона (Y-m-d H:i:s)
     * @param string $dateTo     Дата конца диапазона (Y-m-d H:i:s)
     * @param array  $managerIds Список ID менеджеров
     * @return array
     */
    public function getManagerStatisticsByDays(string $dateFrom, string $dateTo, array $managerIds = []): array
    {
        $managerIdsSql = implode(',', array_map('intval', $managerIds));

        // Запрос без CTE calls_per_user
        $sql = "
        WITH users_filtered AS (
            SELECT
                u.id,
                u.call_status,
                u.missing_manager_id,
                u.additional_data_added,
                u.additional_data_added_date,
                u.accept_data_added_date,
                u.continue_order,
                u.created,
                COALESCE(ud.value, 0) AS bonon,
                DATE(
                    CASE
                        WHEN u.personal_data_added_date IS NULL THEN u.created
                        WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                        WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                        WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                        WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                        WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                    END
                ) AS day_created
            FROM s_users u
                LEFT JOIN s_user_data ud ON ud.user_id = u.id AND ud.key = 'is_rejected_nk'
            WHERE u.missing_manager_id IN ($managerIdsSql)
              AND (
                CASE
                    WHEN u.personal_data_added_date IS NULL THEN u.created
                    WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                    WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                    WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                    WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                    WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                END
              ) BETWEEN ? AND ?
        ),

        orders_per_user AS (
            SELECT
                o.user_id,
                MAX(CASE WHEN o.`1c_status` = '5.Выдан' THEN 1 ELSE 0 END) AS has_issued_loan,
                MAX(CASE WHEN o.`1c_status` = '3.Одобрено' THEN 1 ELSE 0 END) AS has_approved_loan,
                MAX(CASE WHEN o.status = 3 THEN 1 ELSE 0 END) AS has_rejected_loan
            FROM s_orders o
            WHERE o.date BETWEEN ? AND ?
            GROUP BY o.user_id
        )

        SELECT
            u.day_created,
            m.id AS manager_id,
            COALESCE(NULLIF(COALESCE(m.name, m.name_1c, m.login), ''), m.login) AS manager_name,
            COUNT(*) AS total_requests,

            -- Completed
            SUM(u.additional_data_added = 1) AS completed_total,
            ROUND(SUM(u.additional_data_added = 1) / COUNT(*) * 100, 2) AS conversion_completed,

            -- Loans
            SUM(COALESCE(o.has_issued_loan, 0)) AS issued_count,
            ROUND(SUM(COALESCE(o.has_issued_loan, 0)) / COUNT(*) * 100, 2) AS conversion_issued,

            SUM(COALESCE(o.has_approved_loan, 0)) AS approved_count,
            ROUND(SUM(COALESCE(o.has_approved_loan, 0)) / COUNT(*) * 100, 2) AS conversion_approved,

            SUM(COALESCE(o.has_rejected_loan, 0)) AS rejected_count,
            ROUND(SUM(COALESCE(o.has_rejected_loan, 0)) / COUNT(*) * 100, 2) AS conversion_rejected,

            -- Bonon metric
            SUM(CASE WHEN u.bonon = 1 THEN 1 ELSE 0 END) AS bonon_count,
            SUM(CASE WHEN u.bonon = 0 THEN 1 ELSE 0 END) AS not_bonon_count,
            ROUND(SUM(CASE WHEN u.bonon = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) AS conversion_bonon

        FROM users_filtered u
            LEFT JOIN s_managers m
                ON m.id = u.missing_manager_id
               AND m.id IN ($managerIdsSql)
            LEFT JOIN orders_per_user o ON o.user_id = u.id
        GROUP BY u.day_created, m.id, manager_name
        ORDER BY u.day_created, manager_name
        ";

        $query = $this->db->placehold(
            $sql,
            $dateFrom, $dateTo,   // users_filtered
            $dateFrom, $dateTo    // orders_per_user
        );

        $this->db->query($query);
        $results = $this->db->results() ?: [];

        if (empty($results)) {
            return $results;
        }

        // Получаем user_id с day_created для агрегации по дням
        $userDayData = $this->getUserDayDataForManagerStats($dateFrom, $dateTo, $managerIds);

        // Получаем звонки из архивной БД
        $userIds = array_unique(array_map(function($row) {
            return (int)$row->id;
        }, $userDayData));

        $callsAggregated = $this->getArchiveQueryService()->getCallsAggregatedByUser($dateFrom, $dateTo, $userIds);

        // Агрегируем звонки по (day_created, manager_id)
        $callsByDayManager = [];
        foreach ($userDayData as $row) {
            $userId = (int)$row->id;
            $managerId = (int)$row->missing_manager_id;
            $dayCreated = $row->day_created;
            $key = $dayCreated . '_' . $managerId;

            if (!isset($callsByDayManager[$key])) {
                $callsByDayManager[$key] = [
                    'total_calls' => 0,
                    'accepted_calls' => 0,
                    'not_accepted_calls' => 0,
                    'total_duration_accepted_calls' => 0,
                    'count_users_with_calls' => 0,
                ];
            }

            if (isset($callsAggregated[$userId])) {
                $callData = $callsAggregated[$userId];
                $callsByDayManager[$key]['total_calls'] += (int)($callData['total_calls'] ?? 0);
                $callsByDayManager[$key]['accepted_calls'] += (int)($callData['accepted_calls'] ?? 0);
                $callsByDayManager[$key]['not_accepted_calls'] += (int)($callData['not_accepted_calls'] ?? 0);
                $callsByDayManager[$key]['total_duration_accepted_calls'] += (int)($callData['total_duration_accepted_calls'] ?? 0);
                if (($callData['accepted_calls'] ?? 0) > 0) {
                    $callsByDayManager[$key]['count_users_with_calls']++;
                }
            }
        }

        // Вычисляем среднее
        foreach ($callsByDayManager as $key => &$data) {
            if ($data['accepted_calls'] > 0) {
                $data['avg_accepted_duration'] = $data['total_duration_accepted_calls'] / $data['accepted_calls'];
            } else {
                $data['avg_accepted_duration'] = 0;
            }
        }

        // Мержим данные звонков в результаты
        foreach ($results as $row) {
            $key = $row->day_created . '_' . $row->manager_id;
            if (isset($callsByDayManager[$key])) {
                $calls = $callsByDayManager[$key];
                $row->total_calls = (int)($calls['total_calls'] ?? 0);
                $row->accepted_calls = (int)($calls['accepted_calls'] ?? 0);
                $row->not_accepted_calls = (int)($calls['not_accepted_calls'] ?? 0);
                $row->total_duration_accepted_calls = (int)($calls['total_duration_accepted_calls'] ?? 0);
                $row->avg_accepted_duration = round((float)($calls['avg_accepted_duration'] ?? 0), 2);
            } else {
                $row->total_calls = 0;
                $row->accepted_calls = 0;
                $row->not_accepted_calls = 0;
                $row->total_duration_accepted_calls = 0;
                $row->avg_accepted_duration = 0;
            }
        }

        return $results;
    }

    /**
     * Получить данные пользователей с day_created для статистики по дням
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param array $managerIds
     * @return array
     */
    private function getUserDayDataForManagerStats(string $dateFrom, string $dateTo, array $managerIds): array
    {
        $managerIdsSql = implode(',', array_map('intval', $managerIds));

        $sql = "
            SELECT
                u.id,
                u.missing_manager_id,
                DATE(
                    CASE
                        WHEN u.personal_data_added_date IS NULL THEN u.created
                        WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                        WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                        WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                        WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                        WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                    END
                ) AS day_created
            FROM s_users u
            WHERE u.missing_manager_id IN ($managerIdsSql)
              AND (
                CASE
                    WHEN u.personal_data_added_date IS NULL THEN u.created
                    WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                    WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                    WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                    WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                    WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                END
              ) BETWEEN ? AND ?
        ";

        $query = $this->db->placehold($sql, $dateFrom, $dateTo);
        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Получение статистики по конкретному менеджеру
     *
     * @param int    $managerId ID менеджера (s_managers.id)
     * @param string $dateFrom  Дата начала диапазона (Y-m-d H:i:s)
     * @param string $dateTo    Дата конца диапазона (Y-m-d H:i:s)
     * @return object Объект статистики по менеджеру
     */
    public function getManagerStatisticsById(int $managerId, string $dateFrom, string $dateTo): object
    {
        $sql = $this->prepareManagerStatisticsByIdQuery();
        $query = $this->db->placehold($sql, $managerId, $dateFrom, $dateTo);
        $this->db->query($query);
        $result = $this->db->result();

        if (!$result) {
            $result = $this->createEmptyStatisticsObject();
        }

        // Получаем user_id для этого менеджера
        $userIds = $this->getUserIdsForManager($managerId, $dateFrom, $dateTo);

        // Получаем звонки из архива
        $callsAggregated = $this->getArchiveQueryService()->getCallsAggregatedByUser($dateFrom, $dateTo, $userIds);

        // Агрегируем звонки
        $totalCalls = 0;
        $acceptedCalls = 0;
        $notAcceptedCalls = 0;
        $totalDuration = 0;
        $countWithCalls = 0;

        foreach ($callsAggregated as $callData) {
            $totalCalls += (int)($callData['total_calls'] ?? 0);
            $acceptedCalls += (int)($callData['accepted_calls'] ?? 0);
            $notAcceptedCalls += (int)($callData['not_accepted_calls'] ?? 0);
            $totalDuration += (int)($callData['total_duration_all_calls'] ?? 0);
            if (($callData['accepted_calls'] ?? 0) > 0) {
                $countWithCalls++;
            }
        }

        $result->total_calls = $totalCalls;
        $result->accepted_calls = $acceptedCalls;
        $result->not_accepted_calls = $notAcceptedCalls;
        $result->total_duration_all_calls = $totalDuration;
        $result->avg_duration_accepted_calls = $acceptedCalls > 0
            ? round($totalDuration / $acceptedCalls, 2)
            : 0;

        // Post-process (аналогично getStatistics)
        $result->conversion = $result->in_progress > 0
            ? round(($result->completed / $result->in_progress) * 100)
            : 0;

        $result->call_success_percent = $result->totals > 0
            ? round(($result->could_call / $result->totals) * 100)
            : 0;

        $result->loans_issued_percent = $result->totals > 0
            ? round(($result->loans_issued_total / $result->totals) * 100)
            : 0;

        return $result;
    }

    /**
     * Получить user_id для конкретного менеджера
     *
     * @param int $managerId
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    private function getUserIdsForManager(int $managerId, string $dateFrom, string $dateTo): array
    {
        $firstMissingDateExpr = $this->getFirstMissingDateExpr();

        $sql = "
            SELECT u.id
            FROM s_users u
            LEFT JOIN s_user_data ud ON ud.user_id = u.id AND ud.key = 'is_rejected_nk'
            WHERE (ud.value IS NULL OR ud.value = 0)
              AND u.missing_manager_id = ?
              AND DATE($firstMissingDateExpr) BETWEEN ? AND ?
        ";

        $query = $this->db->placehold($sql, $managerId, $dateFrom, $dateTo);
        $this->db->query($query);
        $rows = $this->db->results() ?: [];

        return array_map(function($row) {
            return (int)$row->id;
        }, $rows);
    }

    /**
     * Подготовка SQL-запроса для статистики по конкретному менеджеру (без звонков)
     *
     * @return string SQL-запрос
     */
    private function prepareManagerStatisticsByIdQuery(): string
    {
        $firstMissingDateExpr = $this->getFirstMissingDateExpr();
        $lastStageExpr = $this->getLastStageExpr();

        return "
            WITH loans_per_user AS (
                SELECT
                    user_id,
                    COUNT(*) AS issued_loans_count,
                    MAX(CASE WHEN o.`1c_status` = '5.Выдан' THEN 1 ELSE 0 END) AS has_issued_loan
                FROM s_orders o
                WHERE o.`1c_status` = '5.Выдан'
                GROUP BY user_id
            )
            SELECT
                COUNT(DISTINCT u.id) AS totals,
                SUM(IF(u.call_status = 1, 1, 0)) AS could_call,
                SUM(IF(u.call_status = 2, 1, 0)) AS could_not_call,
                SUM(IF(u.missing_manager_id > 0, 1, 0)) AS in_progress,
                SUM(IF(u.missing_manager_id > 0 AND u.additional_data_added = 1, 1, 0)) AS completed,
                SUM(IF(COALESCE(lpu.has_issued_loan, 0) = 1, 1, 0)) AS users_loan_issued,
                SUM(COALESCE(lpu.issued_loans_count, 0)) AS loans_issued_total,
                SUM(
                  IF((
                    (COALESCE(u.missing_manager_id, 0) <= 0)
                    AND (u.additional_data_added <> 1
                         AND (u.additional_data_added_date IS NULL
                              OR DATE(u.additional_data_added_date) <> CURDATE())
                        AND (
                          SELECT COUNT(*)
                          FROM s_orders o
                          WHERE o.user_id = u.id
                            AND DATE(o.date) = DATE(u.accept_data_added_date)
                            AND o.status = 1
                        )
                    )
                  ), 1, 0)
                ) AS unhandled,
                SUM(IF(u.continue_order = 1, 1, 0)) AS continue_order,
                SUM(IF($lastStageExpr = 1, 1, 0)) AS stage1,
                SUM(IF($lastStageExpr = 2, 1, 0)) AS stage2,
                SUM(IF($lastStageExpr = 3, 1, 0)) AS stage3,
                SUM(IF($lastStageExpr = 4, 1, 0)) AS stage4,
                SUM(IF($lastStageExpr = 5, 1, 0)) AS stage5,
                SUM(IF($lastStageExpr = 6, 1, 0)) AS stage6,
                SUM(IF($lastStageExpr = 7, 1, 0)) AS stage7
            FROM s_users u
            LEFT JOIN s_user_data ud ON ud.user_id = u.id AND ud.key = 'is_rejected_nk'
            LEFT JOIN loans_per_user lpu ON lpu.user_id = u.id
            WHERE
                (ud.value IS NULL OR ud.value = 0)
                AND DATE($firstMissingDateExpr) BETWEEN ? AND ?
                AND u.missing_manager_id = ?
        ";
    }


        /**
         * Получение статистики
         *
         * @param string $dateFrom Дата начала диапазона (Y-m-d H:i:s)
         * @param string $dateTo Дата конца диапазона (Y-m-d H:i:s)
         * @return object Объект статистики с количеством и метриками
         */
        public function getStatistics(string $dateFrom, string $dateTo): object
        {
            $sql = $this->prepareStatisticsQuery();

            // Теперь только 2 диапазона дат: users_filtered, s_orders
            $query = $this->db->placehold(
                $sql,
                $dateFrom, $dateTo,   // users_filtered
                $dateFrom, $dateTo    // s_orders
            );

            $this->db->query($query);
            $result = $this->db->result();

            if (!$result) {
                $result = $this->createEmptyStatisticsObject();
            }

            // Получаем user_id для запроса звонков
            $userIds = $this->getAllUserIdsForStatistics($dateFrom, $dateTo);

            // Получаем звонки из архива
            $callsAggregated = $this->getArchiveQueryService()->getCallsAggregatedByUser($dateFrom, $dateTo, $userIds);

            // Агрегируем звонки
            $totalCalls = 0;
            $acceptedCalls = 0;
            $notAcceptedCalls = 0;
            $totalDuration = 0;
            $countWithCalls = 0;

            foreach ($callsAggregated as $callData) {
                $totalCalls += (int)($callData['total_calls'] ?? 0);
                $acceptedCalls += (int)($callData['accepted_calls'] ?? 0);
                $notAcceptedCalls += (int)($callData['not_accepted_calls'] ?? 0);
                $totalDuration += (int)($callData['total_duration_all_calls'] ?? 0);
                if (($callData['accepted_calls'] ?? 0) > 0) {
                    $countWithCalls++;
                }
            }

            $result->total_calls = $totalCalls;
            $result->accepted_calls = $acceptedCalls;
            $result->not_accepted_calls = $notAcceptedCalls;
            $result->total_duration_all_calls = $totalDuration;
            $result->avg_duration_accepted_calls = $acceptedCalls > 0
                ? round($totalDuration / $acceptedCalls, 2)
                : 0;

            return $result;
        }

        /**
         * Получить все user_id для статистики
         *
         * @param string $dateFrom
         * @param string $dateTo
         * @return array
         */
        private function getAllUserIdsForStatistics(string $dateFrom, string $dateTo): array
        {
            $sql = "
                SELECT u.id
                FROM s_users u
                WHERE (
                    CASE
                        WHEN u.personal_data_added_date IS NULL THEN u.created
                        WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                        WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                        WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                        WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                        WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                    END
                  ) BETWEEN ? AND ?
            ";

            $query = $this->db->placehold($sql, $dateFrom, $dateTo);
            $this->db->query($query);
            $rows = $this->db->results() ?: [];

            return array_map(function($row) {
                return (int)$row->id;
            }, $rows);
        }

        /**
         * Подготовка SQL-запроса для статистики (без звонков)
         *
         * @return string SQL-запрос
         */
        private function prepareStatisticsQuery(): string
        {
            return "
        WITH users_filtered AS (
            SELECT
                u.id,
                u.call_status,
                u.missing_manager_id,
                u.additional_data_added,
                u.additional_data_added_date,
                u.accept_data_added_date,
                u.continue_order,
                u.created,
                COALESCE(ud.value, 0) AS bonon, -- 1=бонон, 0=не бонон
                CASE
                    WHEN u.personal_data_added_date IS NULL THEN 1
                    WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN 2
                    WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN 3
                    WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN 4
                    WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN 5
                    WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN 6
                    ELSE 7
                END AS stage,
                DATE(
                    CASE
                        WHEN u.personal_data_added_date IS NULL THEN u.created
                        WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                        WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                        WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                        WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                        WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                    END
                ) AS day_created
            FROM s_users u
                LEFT JOIN s_user_data ud ON ud.user_id = u.id AND ud.key = 'is_rejected_nk'
            WHERE (
                CASE
                    WHEN u.personal_data_added_date IS NULL THEN u.created
                    WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                    WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                    WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                    WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                    WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                END
              ) BETWEEN ? AND ?
        )

        SELECT
            COUNT(*) AS totals,
            SUM(u.call_status = 2) AS could_call,
            SUM(u.call_status = 1) AS could_not_call,
            SUM(u.missing_manager_id > 0) AS in_progress,
            SUM(u.missing_manager_id = 0) AS unhandled,
            SUM(u.additional_data_added = 1) AS completed_total,
            SUM(u.missing_manager_id > 0 AND u.additional_data_added = 1) AS completed_with_manager,
            SUM(u.missing_manager_id = 0 AND u.additional_data_added = 1) AS completed_self,

            -- Loans
            SUM(COALESCE(o.has_issued_loan, 0)) AS users_loan_issued,
            ROUND(SUM(COALESCE(o.has_issued_loan, 0)) / COUNT(*) * 100, 2) AS conversion_total,

            SUM(COALESCE(o.has_approved_loan, 0)) AS users_loan_approved,
            ROUND(SUM(COALESCE(o.has_approved_loan, 0)) / COUNT(*) * 100, 2) AS conversion_approved,

            SUM(COALESCE(o.has_rejected_loan, 0)) AS users_loan_rejected,
            ROUND(SUM(COALESCE(o.has_rejected_loan, 0)) / COUNT(*) * 100, 2) AS conversion_rejected,

            -- Conversions completed
            ROUND(
                SUM(CASE WHEN u.missing_manager_id > 0 AND u.additional_data_added = 1 THEN COALESCE(o.has_issued_loan,0) ELSE 0 END)
                    / NULLIF(SUM(u.missing_manager_id > 0 AND u.additional_data_added = 1),0) * 100, 2
            ) AS conversion_completed_with_manager,
            ROUND(
                SUM(CASE WHEN u.missing_manager_id = 0 AND u.additional_data_added = 1 THEN COALESCE(o.has_issued_loan,0) ELSE 0 END)
                    / NULLIF(SUM(u.missing_manager_id = 0 AND u.additional_data_added = 1),0) * 100, 2
            ) AS conversion_completed_self,
            ROUND(
                SUM(COALESCE(o.has_issued_loan,0))
                    / NULLIF(SUM(u.additional_data_added = 1),0) * 100, 2
            ) AS conversion_completed_total,

            -- Continue
            SUM(u.continue_order = 2) AS continue_count,
            SUM(CASE WHEN u.continue_order = 2 THEN COALESCE(o.has_issued_loan,0) ELSE 0 END) AS issued_from_continue,
            ROUND(
                SUM(CASE WHEN u.continue_order = 2 THEN COALESCE(o.has_issued_loan,0) ELSE 0 END)
                    / NULLIF(SUM(u.continue_order = 2),0) * 100, 2
            ) AS conversion_continue,

            -- Bonon
            SUM(u.bonon = 1) AS bonon_count,
            SUM(u.bonon = 0) AS not_bonon_count,

            SUM(DATE(u.created) = u.day_created) AS new_clients_today,

            SUM(u.stage = 1) AS stage1,
            SUM(u.stage = 2) AS stage2,
            SUM(u.stage = 3) AS stage3,
            SUM(u.stage = 4) AS stage4,
            SUM(u.stage = 5) AS stage5,
            SUM(u.stage = 6) AS stage6,
            SUM(u.stage = 7) AS stage7

        FROM users_filtered u
        LEFT JOIN (
            SELECT
                o.user_id,
                MAX(CASE WHEN o.`1c_status` = '5.Выдан' THEN 1 ELSE 0 END) AS has_issued_loan,
                MAX(CASE WHEN o.`1c_status` = '3.Одобрено' THEN 1 ELSE 0 END) AS has_approved_loan,
                MAX(CASE WHEN o.status = 3 THEN 1 ELSE 0 END) AS has_rejected_loan
            FROM s_orders o
            WHERE o.date BETWEEN ? AND ?
            GROUP BY o.user_id
        ) o ON o.user_id = u.id
    ";
        }


    /**
     * Получение SQL-выражения для определения даты первого отвала
     *
     * @return string SQL CASE-выражение
     */
    private function getFirstMissingDateExpr(): string
    {
        return "CASE
                WHEN u.personal_data_added_date IS NULL THEN u.created
                WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date
                      OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date
                      OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date
                      OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date
                      OR u.files_added_date IS NULL) THEN u.card_added_date
                WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date
                      OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                ELSE NULL
            END";
    }


    /**
     * Получение SQL-выражения для определения последнего этапа
     *
     * @return string SQL CASE-выражение
     */
    private function getLastStageExpr(): string
    {
        return "CASE
                WHEN u.personal_data_added_date IS NULL THEN 1
                WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date
                      OR u.address_data_added_date IS NULL) THEN 2
                WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date
                      OR u.accept_data_added_date IS NULL) THEN 3
                WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date
                      OR u.card_added_date IS NULL) THEN 4
                WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date
                      OR u.files_added_date IS NULL) THEN 5
                WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date
                      OR u.additional_data_added_date IS NULL) THEN 6
                ELSE 7
            END";
    }


    /**
     * Создание пустого объекта статистики, когда данные отсутствуют
     *
     * @return object Пустой объект статистики
     */
    private function createEmptyStatisticsObject(): object
    {
        $result = new stdClass();
        $properties = [
            'totals','could_call','could_not_call','in_progress','unhandled',
            'completed_total','completed_with_manager','completed_self',
            'users_loan_issued','conversion_total',
            'users_loan_approved','conversion_approved',
            'users_loan_rejected','conversion_rejected',
            'conversion_completed_with_manager','conversion_completed_self','conversion_completed_total',
            'continue_count','issued_from_continue','conversion_continue',
            'bonon_count','not_bonon_count',
            'new_clients_today',
            'total_calls','accepted_calls','not_accepted_calls','avg_duration_accepted_calls','total_duration_all_calls',
            'stage1','stage2','stage3','stage4','stage5','stage6','stage7'
        ];

        foreach ($properties as $property) {
            $result->{$property} = 0;
        }

        return $result;
    }
}
