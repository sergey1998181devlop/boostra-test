<?php

require_once 'View.php';

class DormantClientsReportView extends View
{
    private const PAGE_CAPACITY = 15;
    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;
    private string $dateFrom;
    private string $dateTo;
    private array $filters = [];
    private string $sort = '';
    private array $allowedSortFields = [
        'date' => 'o.last_order_date',
        '-date' => 'o.last_order_date DESC',
        'scorista' => 's.scorista_ball',
        '-scorista' => 's.scorista_ball DESC'
    ];

    public function __construct()
    {
        parent::__construct();

        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $this->setupFilters();
        $this->setupSort();

        $this->handleAction();
    }

    private function setupFilters(): void
    {
        $search = $this->request->get('search') ?? [];

        $daterange = $search['daterange'] ?? null;
        if (empty($daterange)) {
            $daterange = date('d.m.Y', strtotime('-6 months')) . ' - ' . date('d.m.Y', strtotime('-3 months'));
        }

        [$from, $to] = explode(' - ', $daterange);
        $this->filters['date_from'] = date('Y-m-d', strtotime($from));
        $this->filters['date_to'] = date('Y-m-d', strtotime($to));

        // Обработка остальных фильтров
        $this->filters = array_merge($this->filters, [
            'closed_before_due' => $search['closed_before_due'] ?? null,
            'has_additional_services' => $search['has_additional_services'] ?? null,
            'returned_additional_service' => $search['returned_additional_service'] ?? null,
            'scorista_ball_min' => isset($search['scorista_ball_min']) ? (int)$search['scorista_ball_min'] : null,
            'scorista_ball_max' => isset($search['scorista_ball_max']) ? (int)$search['scorista_ball_max'] : null,
            'has_complaint' => $search['has_complaint'] ?? null,
            'company_id' => $search['company_id'] ?? null,
        ]);
    }


    private function setupSort(): void
    {
        $sort = $this->request->get('sort');
        if ($sort && isset($this->allowedSortFields[$sort])) {
            $this->sort = $sort;
        }
    }

    private function handleAction(): void
    {
        $action = $this->request->get('action');
        $allowedActions = ['download'];
        if ($action && in_array($action, $allowedActions, true) && method_exists($this, $action)) {
            $this->$action();
        }
    }

    public function fetch(): string
    {
        $items = $this->getResults($this->currentPage);

        $this->design->assign_array([
            'items' => $items,
            'current_page_num' => $this->currentPage,
            'total_pages_num' => $this->pagesNum,
            'total_items' => $this->totalItems,
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
            'date_from' => date('d.m.Y', strtotime($this->filters['date_from'])),
            'date_to' => date('d.m.Y', strtotime($this->filters['date_to'])),
            'companies' => $this->getCompanies(),
            'filters' => $this->filters,
            'sort' => $this->sort
        ]);

        return $this->design->fetch('dormant_clients_report.tpl');
    }

    private function getBaseQuery(): array
    {
        $query = "
            SELECT SQL_CALC_FOUND_ROWS
                o.order_id AS order_id,
                o.last_order_date AS date,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.phone_mobile,
                u.id AS user_id,
                c.number AS contract,
                c.close_date,
                org.short_name AS organization_name,
                s.scorista_ball,
                s.string_result,
                (c.return_date >= c.close_date) AS closed_before_due,
                (tv.order_id IS NOT NULL) AS has_additional_services,
                (tv.return_date IS NOT NULL) AS returned_additional_service,
                (t.client_id IS NOT NULL) AS has_complaint
            FROM s_users u
            JOIN (
                SELECT user_id, id AS order_id, date AS last_order_date
                FROM (
                    SELECT
                        o.user_id,
                        o.id,
                        o.date,
                        ROW_NUMBER() OVER (PARTITION BY o.user_id ORDER BY o.date DESC) AS rnk
                    FROM s_orders o
                    WHERE o.date BETWEEN ? AND ?
                      AND (
                          o.`1c_status` = '6.Закрыт'
                          OR (
                              o.approve_date IS NOT NULL 
                              AND credit_getted = 0
                          )
                      )
                ) ranked_orders
                WHERE rnk = 1
            ) o ON u.id = o.user_id

            LEFT JOIN s_contracts c ON c.order_id = o.order_id
            LEFT JOIN s_organizations org ON org.id = (
                SELECT o3.organization_id FROM s_orders o3 WHERE o3.id = o.order_id
            )
            LEFT JOIN s_user_balance ub ON ub.user_id = u.id
            LEFT JOIN (
                SELECT order_id, MAX(return_date) AS return_date
                FROM s_tv_medical_payments
                GROUP BY order_id
            ) tv ON tv.order_id = o.order_id
            LEFT JOIN (
                SELECT order_id, scorista_ball, string_result
                FROM s_scorings
                WHERE type = 1
                    AND id = (
                        SELECT MAX(id)
                        FROM s_scorings s2
                        WHERE s2.order_id = s_scorings.order_id
                )
            ) s ON s.order_id = o.order_id
            LEFT JOIN s_mytickets t ON t.client_id = o.user_id
            WHERE NOT EXISTS (
                SELECT 1
                FROM s_orders o2
                WHERE o2.user_id = u.id
                  AND o2.date > o.last_order_date
            )
            AND NOT EXISTS (
                SELECT 1
                FROM s_blacklist b
                WHERE b.user_id = u.id
            )
            AND NOT ub.sale_info = 'Договор продан'
        ";

        $filterData = $this->buildFilterConditions();
        $params = [$this->filters['date_from'], $this->filters['date_to'], ...$filterData['params']];

        return [$query . $filterData['conditions'], $params];
    }

    private function getResults(int $currentPage): array
    {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);
        [$query, $params] = $this->getBaseQuery();

        $query .= " ORDER BY " . ($this->sort && isset($this->allowedSortFields[$this->sort])
                ? $this->allowedSortFields[$this->sort]
                : "o.last_order_date DESC");

        $query .= " LIMIT ? OFFSET ?";
        $params[] = self::PAGE_CAPACITY;
        $params[] = $offset;

        $this->db->query($query, ...$params);
        $results = $this->db->results();

        // Выполняем запрос для получения общего количества записей
        $this->db->query("SELECT FOUND_ROWS() AS total");
        $this->totalItems = (int) $this->db->result('total');
        $this->pagesNum = (int) ceil($this->totalItems / self::PAGE_CAPACITY);

        return $results;
    }
    
    private function download(): void
    {
        [$query, $params] = $this->getBaseQuery();
        $query .= " ORDER BY o.last_order_date DESC";
        $this->db->query($query, ...$params);
        $items = $this->db->results();
        
        $writer = new XLSXWriter();

        $header = [
            'Клиент' => 'string',
            'Номер телефона' => 'string',
            'Номер займа' => 'string',
            'Последний займ' => 'string',
            'Компания' => 'string',
            'Закрылся до просрочки' => 'string',
            'Брал доп. услуги' => 'string',
            'Возвращал доп. услуги' => 'string',
            'Балл скориста' => 'integer',
        ];

        $writer->writeSheetHeader('Отчёт', $header);

        foreach ($items as $item) {
            $writer->writeSheetRow('Отчёт', [
                trim("{$item->lastname} {$item->firstname} {$item->patronymic}"),
                $item->phone_mobile,
                $item->order_id,
                date('d.m.Y', strtotime($item->date)),
                $item->organization_name ?? 'N/A',
                $item->closed_before_due ? 'Да' : 'Нет',
                $item->has_additional_services ? 'Да' : 'Нет',
                $item->returned_additional_service ? 'Да' : 'Нет',
                $item->scorista_ball ?? 'N/A',
            ]);
        }

        $filename = 'dormant_clients_report_' . $this->filters['date_from'] . '_' . $this->filters['date_to'] . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    private function buildFilterConditions(): array
    {
        $filterMap = [
            'closed_before_due' => [
                'condition' => 'closed_before_due = ?',
                'value' => fn($val) => $val === 'yes',
                'type' => 'having'
            ],
            'has_additional_services' => [
                'condition' => 'has_additional_services = ?',
                'value' => fn($val) => $val === 'yes',
                'type' => 'having'
            ],
            'returned_additional_service' => [
                'condition' => 'returned_additional_service = ?',
                'value' => fn($val) => $val === 'yes',
                'type' => 'having'
            ],
            'scorista_ball_min' => [
                'condition' => 'scorista_ball >= ?',
                'value' => fn($val) => $val,
                'type' => 'having'
            ],
            'scorista_ball_max' => [
                'condition' => 'scorista_ball <= ?',
                'value' => fn($val) => $val,
                'type' => 'having'
            ],
            'has_complaint' => [
                'condition' => 'has_complaint = ?',
                'value' => fn($val) => $val === 'yes',
                'type' => 'having'
            ],
            'company_id' => [
                'condition' => 'org.id = ?',
                'value' => fn($val) => $val,
                'type' => 'where'
            ]
        ];

        $conditions = [
            'where' => [],
            'having' => []
        ];
        $params = [];

        foreach ($filterMap as $key => $filter) {
            if ($this->filters[$key] !== null) {
                $type = $filter['type'] ?? 'having';
                $conditions[$type][] = $filter['condition'];
                $params[] = $filter['value']($this->filters[$key]);
            }
        }

        $sql = '';
        if (!empty($conditions['where'])) {
            $sql .= ' AND ' . implode(' AND ', $conditions['where']);
        }
        if (!empty($conditions['having'])) {
            $sql .= ' HAVING ' . implode(' AND ', $conditions['having']);
        }

        return [
            'conditions' => $sql,
            'params' => $params
        ];
    }
    
    private function getCompanies()
    {
        $this->db->query('SELECT id, short_name FROM __organizations');
        return $this->db->results();
    }
}
