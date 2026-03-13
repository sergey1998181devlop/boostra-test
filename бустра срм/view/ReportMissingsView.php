<?php

class ReportMissingsView extends View
{
    /**
     * Точка входа: получает daterange, менеджера,
     * строит отчёт и отдаёт его в шаблон.
     *
     * @throws Exception
     */
    public function fetch()
    {
        if ($dateRange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $dateRange);

            $dateFrom = new DateTime(str_replace('.', '-', $from));
            $dateTo   = new DateTime(str_replace('.', '-', $to));

            $dateFrom = $dateFrom->format('Y-m-d 00:00:00');
            $dateTo   = $dateTo->format('Y-m-d 23:59:59');

            $this->design->assign('from', trim($from));
            $this->design->assign('to', trim($to));
            $this->design->assign('date_from', $dateFrom);
            $this->design->assign('date_to', $dateTo);

            $managerId = $this->request->get('manager_id', 'integer');
            $this->design->assign('filter_manager_id', $managerId);

            $report = $this->getAggregatedReport($dateFrom, $dateTo, $managerId);

            $this->design->assign('report', $report);
        }

        return $this->design->fetch('report_missings.tpl');
    }

    /**
     * Собирает данные «глобально» и «по менеджерам», объединяет в один массив
     */
    private function getAggregatedReport(string $date_from, string $date_to, ?int $managerId = null): array
    {
        $globalResults = $this->getGlobalData($date_from, $date_to, $managerId);

        $managerResults = $this->getManagerData($date_from, $date_to, $managerId);

        $report = [];

        foreach ($globalResults as $row) {
            $report[$row['report_date']] = [
                'date'           => $row['report_date'],
                'totals'         => $row['totals'],
                'totalCompleted' => $row['totalCompleted'],
                'unhandled'      => $row['unhandled'],
                'conversion'     => ($row['totals'] > 0)
                    ? round($row['totalCompleted'] / $row['totals'] * 100, 1)
                    : 0,
                'managers'       => []
            ];
        }

        foreach ($managerResults as $mr) {
            $reportDate = $mr['report_date'];
            if (!isset($report[$reportDate])) {
                $report[$reportDate] = [
                    'date'           => $reportDate,
                    'totals'         => 0,
                    'totalCompleted' => 0,
                    'unhandled'      => 0,
                    'conversion'     => 0,
                    'managers'       => []
                ];
            }

            // Эффективность сотрудника: (completed / inProgress)
            $managerEfficiency = ($mr['inProgress'] > 0)
                ? round($mr['completed'] / $mr['inProgress'] * 100, 1)
                : 0;

            $managerConversion = ($mr['inProgress'] > 0)
                ? round($mr['completed'] / $mr['inProgress'] * 100, 1)
                : 0;

            $report[$reportDate]['managers'][$mr['manager_id']] = [
                'manager_id'        => $mr['manager_id'],
                'manager_name'      => $mr['manager_name'],
                'inProgress'        => $mr['inProgress'],
                'completed'         => $mr['completed'],
                'loans'             => $mr['loans'],
                'amount'            => $mr['amount'],
                'managerEfficiency' => $managerEfficiency,
                'conversion'        => $managerConversion
            ];
        }

        return $report;
    }

    /**
     * Возвращает строку CASE, которая вычисляет «дату отвала» для пользователя
     */
    private function getFirstMissingDateExpr(): string
    {
        return "
            CASE 
                WHEN (u.personal_data_added_date IS NULL) THEN u.created
                WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL) THEN u.card_added_date
                WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                ELSE NULL
            END
        ";
    }

    /**
     * Делает «глобальный» запрос, агрегирующий данные по дате
     */
    private function getGlobalData(string $dateFrom, string $dateTo, ?int $managerId): array
    {
        $firstMissingDateExpr = $this->getFirstMissingDateExpr();

        $globalSelectFields = "
            DATE_FORMAT($firstMissingDateExpr, '%d.%m.%Y') AS report_date,
            COUNT(DISTINCT u.id) AS totals,
            SUM(CASE WHEN u.additional_data_added = 1 THEN 1 ELSE 0 END) AS totalCompleted,

            -- Необработанные
            SUM(
              CASE WHEN (
                (COALESCE(u.missing_manager_id, 0) <= 0)
                AND (u.additional_data_added <> 1)
                AND (
                    u.additional_data_added_date IS NULL
                    OR DATE(u.additional_data_added_date) <> CURDATE()
                )
                AND (
                  SELECT COUNT(*)
                  FROM s_orders o
                  WHERE o.user_id = u.id
                    AND DATE(o.date) = DATE(u.accept_data_added_date)
                    AND o.status = 1
                )
              )
              THEN 1 ELSE 0 END
            ) AS unhandled
        ";

        $globalSql = "
            SELECT $globalSelectFields
            FROM s_users u
            LEFT JOIN s_user_data ud ON ud.user_id = u.id AND ud.key = 'is_rejected_nk'
            WHERE (ud.value IS NULL OR ud.value = 0)
              AND $firstMissingDateExpr BETWEEN ? AND ?
        ";

        $params = [
            $dateFrom,
            $dateTo,
        ];

        if (!empty($managerId)) {
            $globalSql .= " AND u.missing_manager_id = ? ";
            $params[] = $managerId;
        }

        $globalSql .= "
            GROUP BY report_date
            ORDER BY STR_TO_DATE(report_date, '%d.%m.%Y') DESC
        ";

        // Выполняем
        $phSql = $this->db->placehold($globalSql, ...$params);
        $this->db->query($phSql);

        $results = [];
        foreach ($this->db->results() as $r) {
            $results[] = [
                'report_date'    => $r->report_date,
                'totals'         => (int)$r->totals,
                'totalCompleted' => (int)$r->totalCompleted,
                'unhandled'      => (int)$r->unhandled,
            ];
        }
        return $results;
    }

    /**
     * Делает запрос «по менеджерам» (дата + менеджер),
     * но без поля 'unhandled'.
     */
    private function getManagerData(string $dateFrom, string $dateTo, ?int $managerId = null): array
    {
        $firstMissingDateExpr = "
            IF(u.personal_data_added_date IS NULL,u.created,
            IF(DATE_ADD(u.personal_data_added_date,INTERVAL 5 MINUTE) <= u.address_data_added_date OR u.address_data_added_date IS NULL, u.personal_data_added_date,
            IF(DATE_ADD(u.address_data_added_date,INTERVAL 5 MINUTE) <= u.accept_data_added_date OR u.accept_data_added_date IS NULL, u.address_data_added_date,
            IF(DATE_ADD(u.accept_data_added_date,INTERVAL 5 MINUTE) <= u.card_added_date OR u.card_added_date IS NULL, u.accept_data_added_date,
            IF(DATE_ADD(u.card_added_date,INTERVAL 5 MINUTE) <= u.files_added_date OR u.files_added_date IS NULL, u.card_added_date,
            IF(DATE_ADD(u.files_added_date,INTERVAL 5 MINUTE) <= u.additional_data_added_date OR u.additional_data_added_date IS NULL, u.files_added_date,NULL)))))) 
        ";

        $managerSql = <<<SQL
            WITH first_loans AS (
                SELECT
                    o.user_id,
                    MIN(o.id) AS first_order_id,
                    MIN(o.approve_date) AS first_approve_date
                FROM s_orders o
                WHERE o.credit_getted = 1
                GROUP BY o.user_id
            )
            
            SELECT 
                DATE_FORMAT($firstMissingDateExpr,'%d.%m.%Y') AS report_date,
                u.missing_manager_id AS manager_id,
                m.name AS manager_name,
                COUNT(DISTINCT u.id) AS inProgress,
                SUM(IF(u.additional_data_added=1, 1, 0)) AS completed,
                COUNT(fl.user_id) AS loans,
                SUM(
                    IF(fl.first_order_id IS NOT NULL, (SELECT amount FROM s_orders WHERE id = fl.first_order_id), 0)
                ) AS amount
            FROM s_users u
            LEFT JOIN s_user_data ud ON ud.user_id=u.id AND ud.key='is_rejected_nk'
            LEFT JOIN s_managers m ON m.id=u.missing_manager_id
            LEFT JOIN first_loans fl ON fl.user_id=u.id
            WHERE (ud.value IS NULL OR ud.value=0)
              AND u.missing_manager_id > 0
              AND $firstMissingDateExpr BETWEEN ? AND ?
        SQL;

        $params = [$dateFrom, $dateTo];

        if (!empty($managerId)) {
            $managerSql .= " AND u.missing_manager_id = ? ";
            $params[] = $managerId;
        }

        $managerSql .= "
            GROUP BY report_date, u.missing_manager_id
            ORDER BY STR_TO_DATE(report_date, '%d.%m.%Y') DESC
        ";

        $phManager = $this->db->placehold($managerSql, ...$params);
        $this->db->query($phManager);

        $results = [];
        foreach ($this->db->results() as $r) {
            $results[] = [
                'report_date'  => $r->report_date,
                'manager_id'   => (int)$r->manager_id,
                'manager_name' => $r->manager_name,
                'inProgress'   => (int)$r->inProgress,
                'completed'    => (int)$r->completed,
                'loans'        => (int)$r->loans,
                'amount'       => (float)$r->amount,
            ];
        }

        return $results;
    }
}
