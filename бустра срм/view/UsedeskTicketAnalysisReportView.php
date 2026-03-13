<?php

require_once 'View.php';

class UsedeskTicketAnalysisReportView extends View
{
    private const PAGE_CAPACITY = 15;
    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;
    private array $filters = [];
    private string $filtersWhere = '';
    private string $orderBy;
    private string $dateFrom;
    private string $dateTo;

    // Общие поля для всех типов отчётов
    private static array $columns = [
        ['key' => 'client', 'label' => 'Клиент', 'sort_key' => 'client'],
        ['key' => 'email', 'label' => 'Email клиента'],
        ['key' => 'ticket', 'label' => 'Тикет', 'sort_key' => 'ticket'],
        ['key' => 'date', 'label' => 'Дата', 'sort_key' => 'date'],
        ['key' => 'time', 'label' => 'Время', 'sort_key' => 'time'],
        ['key' => 'completeness_response_assessment', 'label' => 'Полнота ответа (оценка)', 'type' => 'integer'],
        ['key' => 'completeness_response_recommendations', 'label' => 'Полнота ответа (рекомендации)'],
        ['key' => 'problem_solving_efficiency_assessment', 'label' => 'Эффективность решения проблемы (оценка)', 'type' => 'integer'],
        ['key' => 'problem_solving_efficiency_solution', 'label' => 'Эффективность решения проблемы (решение)', 'type' => 'boolean'],
        ['key' => 'problem_solving_efficiency_recommendations', 'label' => 'Эффективность решения проблемы (рекомендации)'],
        ['key' => 'answer_politeness_assessment', 'label' => 'Вежливость ответа (оценка)', 'type' => 'integer'],
        ['key' => 'answer_politeness_recommendations', 'label' => 'Вежливость ответа (рекомендации)'],
        ['key' => 'answer_literacy_assessment', 'label' => 'Грамотность ответа (оценка)', 'type' => 'integer'],
        ['key' => 'answer_literacy_recommendations', 'label' => 'Грамотность ответа (рекомендации)'],
        ['key' => 'recommendations', 'label' => 'Общие рекомендации'],
        ['key' => 'total_assessment', 'label' => 'Оценка', 'sort_key' => 'total_assessment', 'type' => 'number'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $this->setupDateRange();

        $this->filters = $this->setFilters();
        $this->filtersWhere = $this->setFiltersWhere($this->filters);
        $this->orderBy = $this->setOrderBy();

        $this->totalItems = $this->getTotals($this->filtersWhere);
        $this->pagesNum = (int)ceil($this->totalItems / self::PAGE_CAPACITY);

        $this->handleAction();
    }

    private function handleAction(): void
    {
        $action = $this->request->get('action');
        if ($action && method_exists($this, $action)) {
            $this->$action();
        }
    }

    public function fetch(): string
    {
        $items = $this->getResults($this->currentPage, $this->filtersWhere, $this->orderBy);

        $columns = self::$columns;

        $reportHeaders = [];
        foreach ($columns as $col) {
            $reportHeaders[] = [
                'key' => $col['key'],
                'label' => $col['label'],
                'sort_key' => $col['sort_key'] ?? null,
            ];
        }

        $reportRows = $this->prepareReportRows($items, $columns);

        $this->design->assign_array([
            'reportHeaders' => $reportHeaders,
            'reportRows' => $reportRows,
            'items' => $reportRows,
            'current_page_num' => $this->currentPage,
            'total_pages_num' => $this->pagesNum,
            'total_items' => $this->totalItems,
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
            'can_see_client_url' => in_array('clients', $this->manager->permissions),
            'date_from' => date('d.m.Y', strtotime($this->dateFrom)),
            'date_to' => date('d.m.Y', strtotime($this->dateTo)),
            'user_filter' => $this->filters['user'],
            'ticket_filter' => $this->filters['ticket'],
            'email_filter' => $this->filters['email'],
            'filterConfigurations' => $this->getFilterConfiguration(),
        ]);

        return $this->design->fetch('usedesk_ticket_analysis_report.tpl');
    }

    private function prepareReportRows(array $items, array $columns): array
    {
        $rows = [];
        foreach ($items as $item) {
            $analysis = json_decode($item->analysis, true);

            $row = [];
            foreach ($columns as $col) {
                $row[$col['key']] = $this->getColumnValue($item, $col['key'], $analysis ?? []);
            }
            $row['ticket_id'] = $item->ticket_id;
            $row['client_id'] = $item->user_id;
            $rows[] = $row;
        }
        return $rows;
    }

    private function getColumnValue(object $item, string $key, array $analysis = [], bool $download = false): string
    {
        switch ($key) {
            case 'client':
                return trim("$item->lastname $item->firstname $item->patronymic") ?? '';
            case 'email':
                return $item->email ?? '';
            case 'ticket':
                $ticket = "https://secure.usedesk.ru/tickets/$item->ticket_id";
                if ($download) {
                    return '=HYPERLINK("' . $ticket . '", "' . $item->ticket_id . '")';
                }
                return $ticket;
            case 'date':
                return date('d.m.Y', strtotime($item->created));
            case 'time':
                return date('H:i:s', strtotime($item->created));
            case 'completeness_response_assessment':
                return $analysis['completeness_response']['assessment'] ?? '';
            case 'completeness_response_recommendations':
                return $analysis['completeness_response']['recommendations'] ?? '';
            case 'problem_solving_efficiency_assessment':
                return $analysis['problem_solving_efficiency']['assessment'] ?? '';
            case 'problem_solving_efficiency_solution':
                return $analysis['problem_solving_efficiency']['solution'] === true ? 'Да' : 'Нет' ?? '';
            case 'problem_solving_efficiency_recommendations':
                return $analysis['problem_solving_efficiency']['recommendations'] ?? '';
            case 'answer_politeness_assessment':
                return $analysis['answer_politeness']['assessment'] ?? '';
            case 'answer_politeness_recommendations':
                return $analysis['answer_politeness']['recommendations'] ?? '';
            case 'answer_literacy_assessment':
                return $analysis['answer_literacy']['assessment'] ?? '';
            case 'answer_literacy_recommendations':
                return $analysis['answer_literacy']['recommendations'] ?? '';
            case 'recommendations':
                return $analysis['recommendations'] ?? '';
            case 'total_assessment':
                return $analysis['total_assessment'] ?? '';
            default:
                return '';
        }
    }

    private function setupDateRange(): void
    {
        $daterange = $this->request->get('daterange');
        if (empty($daterange)) {
            $daterange = date('d.m.Y', strtotime('-1 month')) . ' - ' . date('d.m.Y');
        }

        [$from, $to] = explode(' - ', $daterange);
        $this->dateFrom = date('Y-m-d', strtotime($from));
        $this->dateTo = date('Y-m-d', strtotime($to));
    }

    private function getResults(int $currentPage, string $where = '', string $orderBy = '')
    {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);

        $this->db->query("
                        SELECT
                            u.lastname, 
                            u.firstname, 
                            u.patronymic,
                            u.id AS user_id,
                            IF(u.email IS NOT NULL AND LENGTH(TRIM(u.email)) > 0, u.email, (
                                    SELECT ue.email 
                                    FROM s_user_emails ue 
                                    WHERE ue.user_id = u.id 
                                    LIMIT 1
                                )) AS email,
                            uta.created,
                            uta.ticket_id,
                            uta.analysis
                        FROM s_usedesk_ticket_analysis uta
                        LEFT JOIN s_users u ON uta.user_id = u.id
                        WHERE DATE(uta.created) BETWEEN ? AND ? " . $where . "
                        ORDER BY " . $orderBy . "
                        LIMIT ? OFFSET ?",
            $this->dateFrom, $this->dateTo, self::PAGE_CAPACITY, $offset
        );

        return $this->db->results();
    }

    /**
     * @throws Exception
     */
    private function getAllResults(string $where = '', string $orderBy = '')
    {
        $this->db->query("
                        SELECT
                            u.lastname, 
                            u.firstname, 
                            u.patronymic,
                            u.id AS user_id,
                            IF(u.email IS NOT NULL AND LENGTH(TRIM(u.email)) > 0, u.email, (
                                    SELECT ue.email 
                                    FROM s_user_emails ue 
                                    WHERE ue.user_id = u.id 
                                    LIMIT 1
                                )) AS email,
                            uta.created,
                            uta.ticket_id,
                            uta.analysis
                        FROM s_usedesk_ticket_analysis uta
                        LEFT JOIN s_users u ON uta.user_id = u.id
                        WHERE DATE(uta.created) BETWEEN ? AND ? " . $where . "
                        ORDER BY " . $orderBy,
            $this->dateFrom, $this->dateTo
        );

        return $this->db->results();
    }

    private function getChunkedResults(string $where = '', string $orderBy = '', int $chunkSize = 100): Generator
    {
        $offset = 0;
        while (true) {
            $this->db->query("
                        SELECT
                            u.lastname, 
                            u.firstname, 
                            u.patronymic,
                            u.id AS user_id,
                            IF(u.email IS NOT NULL AND LENGTH(TRIM(u.email)) > 0, u.email, (
                                    SELECT ue.email 
                                    FROM s_user_emails ue 
                                    WHERE ue.user_id = u.id 
                                    LIMIT 1
                                )) AS email,
                            uta.created,
                            uta.ticket_id,
                            uta.analysis
                        FROM s_usedesk_ticket_analysis uta
                        LEFT JOIN s_users u ON uta.user_id = u.id
                        WHERE DATE(uta.created) BETWEEN ? AND ? " . $where . "
                        ORDER BY " . $orderBy . "
                        LIMIT ? OFFSET ?",
                $this->dateFrom, $this->dateTo, $chunkSize, $offset
            );

            $items = $this->db->results();
            if (empty($items)) {
                break;
            }
            foreach ($items as $item) {
                yield $item;
            }
            $offset += $chunkSize;
        }
    }

    private function getTotals(string $where = ''): int
    {
        $this->db->query("
                    SELECT
                        COUNT(uta.id) AS total
                    FROM s_usedesk_ticket_analysis uta
                    LEFT JOIN s_users u ON uta.user_id = u.id
                    WHERE DATE(uta.created) BETWEEN ? AND ? " . $where,
            $this->dateFrom, $this->dateTo
        );

        return (int)$this->db->result('total');
    }

    /**
     * @throws Exception
     */
    private function download(): void
    {
        $maxPeriod = 365; // 1 год в днях

        $dateFromTimestamp = strtotime($this->dateFrom);
        $dateToTimestamp = strtotime($this->dateTo);
        $diffInDays = ($dateToTimestamp - $dateFromTimestamp) / (60 * 60 * 24);

        // Проверка, что выбранный диапазон не превышает 1 год
        if ($diffInDays > $maxPeriod) {
            $this->json_output(['status' => 'error', 'message' => 'Выбранный период превышает допустимый лимит в 1 год.']);
            return;
        }

        $columns = self::$columns;

        $header = [];
        foreach ($columns as $col) {
            $header[$col['label']] = $col['type'] ?? 'string';
        }

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $header);

        foreach ($this->getChunkedResults($this->filtersWhere, $this->orderBy) as $item) {
            $analysis = json_decode($item->analysis, true);

            $row = [];
            foreach ($columns as $col) {
                $row[] = $this->getColumnValue($item, $col['key'], $analysis ?? [], true);
            }

            $writer->writeSheetRow('Отчёт', $row);
        }

        $filename = 'usedesk_ticket_analysis_report_' . date('Y-m-d') . '.xlsx';

        // Отправка файла для загрузки
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    private function setFilters(): array
    {
        return [
            'user' => $this->request->get('user') ?? null,
            'ticket' => $this->request->get('ticket') ?? null,
            'email' => $this->request->get('email') ?? null,
        ];
    }

    private function setFiltersWhere(array $filters): string
    {
        $where = '';
        foreach ($filters as $key => $value) {
            if ($value) {
                switch ($key) {
                    case 'user':
                        $where .= $this->filterUserWhere($value);
                        break;
                    case 'email':
                        $where .= $this->filterEmailWhere($value);
                        break;
                    case 'ticket':
                        $where .= $this->filterTicketWhere($value);
                        break;
                    default:
                        break;
                }
            }
        }
        return $where;
    }

    private function filterUserWhere(string $value): string
    {
        if (!empty($value)) {
            $userSearch = '%' . $this->db->escape($value) . '%';
            return " AND (u.lastname LIKE '$userSearch' OR u.firstname LIKE '$userSearch' OR u.patronymic LIKE '$userSearch')";
        }
        return '';
    }

    private function filterEmailWhere(string $value): string
    {
        if (!empty($value)) {
            $emailSearch = '%' . $this->db->escape($value) . '%';
            return " AND (u.email LIKE '$emailSearch' OR (SELECT ue.email FROM s_user_emails ue WHERE ue.user_id = u.id AND ue.email LIKE '$emailSearch' LIMIT 1) IS NOT NULL)";
        }
        return '';
    }

    private function filterTicketWhere(string $value): string
    {
        if (!empty($value)) {
            $ticketSearch = '%' . $this->db->escape($value) . '%';
            return " AND uta.ticket_id LIKE '$ticketSearch'";
        }
        return '';
    }

    private function setOrderBy(): string
    {
        $orderBy = 'uta.created DESC';

        $sort = $this->request->get('sort') ?? null;
        if ($sort) {
            $pos = strrpos($sort, '_');
            if ($pos !== false) {
                $field = substr($sort, 0, $pos);
                $direction = substr($sort, $pos + 1);
                $direction = (strtoupper($direction) === 'ASC') ? 'ASC' : 'DESC';

                switch ($field) {
                    case 'client':
                        $orderBy = "u.lastname $direction, u.firstname $direction";
                        break;
                    case 'date':
                    case 'time':
                        $orderBy = "uta.created $direction";
                        break;
                    case 'ticket':
                        $orderBy = "uta.ticket_id $direction";
                        break;
                    case 'total_assessment':
                        $orderBy = "JSON_UNQUOTE(JSON_EXTRACT(uta.analysis, '$.total_assessment')) $direction";
                        break;
                    default:
                        break;
                }
            }
        }

        return $orderBy;
    }

    private function getFilterConfiguration(): array
    {
        return [
            [
                'name' => 'user',
                'label' => 'Клиент',
                'type' => 'text',
                'value' => $this->filters['user'] ?? '',
                'placeholder' => 'Введите имя клиента'
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'text',
                'value' => $this->filters['email'] ?? '',
                'placeholder' => 'Введите почту'
            ],
            [
                'name' => 'ticket',
                'label' => 'Тикет',
                'type' => 'text',
                'value' => $this->filters['ticket'] ?? '',
                'placeholder' => 'Введите тикет'
            ],
        ];
    }

    private function convertOptionsToArray(array $options): array
    {
        return array_map('get_object_vars', $options);
    }
}
