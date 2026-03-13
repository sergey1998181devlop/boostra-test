<?php

require_once 'View.php';

class CollectionSummaryReportView extends View
{
    private const PAGE_CAPACITY = 15;
    private const TICKET_TYPE_REPORT = 'ticketType';
    private const ANALYSIS_CALL_TYPE_REPORT = 'analysisCallType';
    private const COLLECTION_SUBJECT_ID = 9;
    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;
    private array $filters = [];
    private string $filtersWhere = '';
    private string $orderBy;
    private string $dateFrom;
    private string $dateTo;

    // Общие поля для всех типов отчётов
    private static array $commonFields = [
        ['key' => 'client', 'label' => 'Клиент', 'sort_key' => 'client'],
        ['key' => 'phone_mobile', 'label' => 'Телефон клиента', 'sort_key' => 'phone_mobile'],
        ['key' => 'has_loan', 'label' => 'Займ/нет займа', 'sort_key' => 'has_loan'],
    ];

    private static array $ticketExtraFields = [
        ['key' => 'date', 'label' => 'Дата жалобы', 'sort_key' => 'date'],
        ['key' => 'time', 'label' => 'Время жалобы', 'sort_key' => 'time'],
        ['key' => 'ticket_type', 'label' => 'Тип жалобы', 'sort_key' => 'ticket_type'],
    ];

    private static array $analysisCallExtraFields = [
        ['key' => 'date', 'label' => 'Дата анализа', 'sort_key' => 'date'],
        ['key' => 'time', 'label' => 'Время анализа', 'sort_key' => 'time'],
        ['key' => 'conversation_topic', 'label' => 'Тема разговора', 'sort_key' => 'conversation_topic'],
    ];

    private static array $assessmentField = [
        ['key' => 'total_assessment', 'label' => 'Оценка', 'sort_key' => 'total_assessment', 'type' => 'number'],
    ];

    private array $reportConfigurations;

    public function __construct()
    {
        parent::__construct();

        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $this->setupDateRange();

        $this->filters = $this->setFilters();
        $this->filtersWhere = $this->setFiltersWhere($this->filters);
        $this->orderBy = $this->setOrderBy();

        $this->reportConfigurations = [
            self::TICKET_TYPE_REPORT => array_merge(
                self::$commonFields,
                self::$ticketExtraFields
            ),
            self::ANALYSIS_CALL_TYPE_REPORT => array_merge(
                self::$commonFields,
                self::$analysisCallExtraFields,
                self::$assessmentField
            )
        ];

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

        $typeReport = $this->filters['type_report'] ?? self::TICKET_TYPE_REPORT;
        $columns = $this->reportConfigurations[$typeReport] ?? $this->reportConfigurations[self::TICKET_TYPE_REPORT];

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
            'phone_mobile_filter' => $this->filters['phone_mobile'],
            'conversation_topic_filter' => $this->filters['conversation_topic'] ?? '',
            'ticket_type_filter' => $this->filters['ticket_type'] ?? '',
            'filterConfigurations' => $this->getFilterConfiguration(),
        ]);

        return $this->design->fetch('collection_summary_report.tpl');
    }

    private function prepareReportRows(array $items, array $columns): array
    {
        $rows = [];
        foreach ($items as $item) {
            if ($this->filters['type_report'] ?? self::ANALYSIS_CALL_TYPE_REPORT) {
                $analysis = json_decode($item->analysis, true);
            }

            $row = [];
            foreach ($columns as $col) {
                $row[$col['key']] = $this->getColumnValue($item, $col['key'], $analysis ?? []);
            }
            $row['client_id'] = $item->user_id;
            $rows[] = $row;
        }
        return $rows;
    }

    private function getColumnValue(object $item, string $key, array $analysis = []): string
    {
        switch ($key) {
            case 'client':
                return trim("$item->lastname $item->firstname $item->patronymic");
            case 'phone_mobile':
                return $item->phone_mobile ?? '';
            case 'date':
                if ($this->filters['type_report'] === self::TICKET_TYPE_REPORT) {
                    return date('d.m.Y', strtotime($item->created_at));
                } else {
                    return date('d.m.Y', strtotime($item->created));
                }
            case 'time':
                if ($this->filters['type_report'] === self::TICKET_TYPE_REPORT) {
                    return date('H:i:s', strtotime($item->created_at));
                } else {
                    return date('H:i:s', strtotime($item->created));
                }
            case 'has_loan':
                return $item->has_loan ?? '';
            case 'ticket_type':
                return $item->ticket_type;
            case 'conversation_topic':
                return $analysis['conversation_topic'] ?? '';
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

        switch ($this->filters['type_report'] ?? self::TICKET_TYPE_REPORT) {
            case self::TICKET_TYPE_REPORT:
                $this->db->query("
                        SELECT
                            u.lastname, 
                            u.firstname, 
                            u.patronymic,
                            u.id AS user_id,
                            u.phone_mobile,
                            mt.created_at,
                            mtsub.name as ticket_type,
                            IF(EXISTS (
                                    SELECT 1 
                                    FROM s_orders o2 
                                    WHERE o2.user_id = mt.client_id 
                                      AND o2.date BETWEEN mt.created_at AND DATE_ADD(mt.created_at, INTERVAL 14 DAY)
                                ), 'Да', 'Нет') AS has_loan
                        FROM s_mytickets mt
                        LEFT JOIN s_mytickets_subjects mtsub ON mt.subject_id = mtsub.id
                        LEFT JOIN s_users u ON mt.client_id = u.id
                        WHERE DATE(mt.created_at) BETWEEN ? AND ? " . $where . "
                        ORDER BY " . $orderBy . "
                        LIMIT ? OFFSET ?",
                    $this->dateFrom, $this->dateTo, self::PAGE_CAPACITY, $offset
                );
                break;
            case self::ANALYSIS_CALL_TYPE_REPORT:
                $this->db->query("
                        SELECT 
                            u.lastname, 
                            u.firstname, 
                            u.patronymic,
                            u.id AS user_id,
                            u.phone_mobile,
                            cra.analysis,
                            cra.created,
                            IF(EXISTS (
                                        SELECT 1 
                                        FROM s_orders o2 
                                        WHERE o2.user_id = cra.user_id 
                                          AND o2.date BETWEEN cra.created AND DATE_ADD(cra.created, INTERVAL 14 DAY)
                                    ), 'Да', 'Нет') AS has_loan
                        FROM s_comment_record_analysis cra 
                        LEFT JOIN s_users u ON u.id = cra.user_id
                        WHERE DATE(cra.created) BETWEEN ? AND ? " . $where . "
                        ORDER BY " . $orderBy . "
                        LIMIT ? OFFSET ?",
                    $this->dateFrom, $this->dateTo, self::PAGE_CAPACITY, $offset
                );
                break;
            default:
                return [];
        }

        return $this->db->results();
    }

    /**
     * @throws Exception
     */
    private function getAllResults(string $where = '', string $orderBy = '')
    {
        switch ($this->filters['type_report'] ?? self::TICKET_TYPE_REPORT) {
            case self::TICKET_TYPE_REPORT:
                $this->db->query("
                        SELECT
                            u.lastname, 
                            u.firstname, 
                            u.patronymic,
                            u.id AS user_id,
                            u.phone_mobile,
                            mt.created_at,
                            mtsub.name as ticket_type,
                            IF(EXISTS (
                                    SELECT 1 
                                    FROM s_orders o2 
                                    WHERE o2.user_id = mt.client_id 
                                      AND o2.date BETWEEN mt.created_at AND DATE_ADD(mt.created_at, INTERVAL 14 DAY)
                                ), 'Да', 'Нет') AS has_loan
                        FROM s_mytickets mt
                        LEFT JOIN s_mytickets_subjects mtsub ON mt.subject_id = mtsub.id
                        LEFT JOIN s_users u ON mt.client_id = u.id
                        WHERE DATE(mt.created_at) BETWEEN ? AND ? " . $where . "
                        ORDER BY " . $orderBy,
                    $this->dateFrom, $this->dateTo
                );
                break;
            case self::ANALYSIS_CALL_TYPE_REPORT:
                $this->db->query("
                        SELECT 
                            u.lastname, 
                            u.firstname, 
                            u.patronymic,
                            u.id AS user_id,
                            u.phone_mobile,
                            cra.analysis,
                            cra.created,
                            IF(EXISTS (
                                        SELECT 1 
                                        FROM s_orders o2 
                                        WHERE o2.user_id = cra.user_id 
                                          AND o2.date BETWEEN cra.created AND DATE_ADD(cra.created, INTERVAL 14 DAY)
                                    ), 'Да', 'Нет') AS has_loan
                        FROM s_comment_record_analysis cra 
                        LEFT JOIN s_users u ON u.id = cra.user_id
                        WHERE DATE(cra.created) BETWEEN ? AND ? " . $where . "
                        ORDER BY " . $orderBy,
                    $this->dateFrom, $this->dateTo
                );
                break;
            default:
                return [];
        }

        return $this->db->results();
    }

    private function getChunkedResults(string $where = '', string $orderBy = '', int $chunkSize = 100): Generator
    {
        $offset = 0;
        while (true) {
            switch ($this->filters['type_report'] ?? self::TICKET_TYPE_REPORT) {
                case self::TICKET_TYPE_REPORT:
                    $this->db->query("
                        SELECT
                            u.lastname, 
                            u.firstname, 
                            u.patronymic,
                            u.id AS user_id,
                            u.phone_mobile,
                            mt.created_at,
                            mtsub.name as ticket_type,
                            IF(EXISTS (
                                    SELECT 1 
                                    FROM s_orders o2 
                                    WHERE o2.user_id = mt.client_id 
                                      AND o2.date BETWEEN mt.created_at AND DATE_ADD(mt.created_at, INTERVAL 14 DAY)
                                ), 'Да', 'Нет') AS has_loan
                        FROM s_mytickets mt
                        LEFT JOIN s_mytickets_subjects mtsub ON mt.subject_id = mtsub.id
                        LEFT JOIN s_users u ON mt.client_id = u.id
                        WHERE DATE(mt.created_at) BETWEEN ? AND ? " . $where . "
                        ORDER BY " . $orderBy . "
                        LIMIT ? OFFSET ?",
                        $this->dateFrom, $this->dateTo, $chunkSize, $offset
                    );
                    break;
                case self::ANALYSIS_CALL_TYPE_REPORT:
                    $this->db->query("
                        SELECT 
                            u.lastname, 
                            u.firstname, 
                            u.patronymic,
                            u.id AS user_id,
                            u.phone_mobile,
                            cra.analysis,
                            cra.created,
                            IF(EXISTS (
                                        SELECT 1 
                                        FROM s_orders o2 
                                        WHERE o2.user_id = cra.user_id 
                                          AND o2.date BETWEEN cra.created AND DATE_ADD(cra.created, INTERVAL 14 DAY)
                                    ), 'Да', 'Нет') AS has_loan
                        FROM s_comment_record_analysis cra 
                        LEFT JOIN s_users u ON u.id = cra.user_id
                        WHERE DATE(cra.created) BETWEEN ? AND ? " . $where . "
                        ORDER BY " . $orderBy . "
                        LIMIT ? OFFSET ?",
                        $this->dateFrom, $this->dateTo, $chunkSize, $offset
                    );
                    break;
                default:
                    break;
            }

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
        switch ($this->filters['type_report'] ?? self::TICKET_TYPE_REPORT) {
            case self::TICKET_TYPE_REPORT:
                $this->db->query("
                    SELECT
                        COUNT(mt.id) AS total
                    FROM s_mytickets mt
                    LEFT JOIN s_mytickets_subjects mtsub ON mt.subject_id = mtsub.id
                    LEFT JOIN s_users u ON mt.client_id = u.id
                    WHERE DATE(mt.created_at) BETWEEN ? AND ? " . $where . "
                ",
                    $this->dateFrom, $this->dateTo
                );
                break;
            case self::ANALYSIS_CALL_TYPE_REPORT:
                $this->db->query("
                    SELECT 
                        COUNT(cra.id) AS total 
                    FROM s_comment_record_analysis cra 
                    LEFT JOIN s_users u ON u.id = cra.user_id
                    WHERE DATE(cra.created) BETWEEN ? AND ? " . $where,
                    $this->dateFrom, $this->dateTo
                );
                break;
            default:
                return 0;
        }

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

        $typeReport = $this->filters['type_report'] ?? self::TICKET_TYPE_REPORT;
        $columns = $this->reportConfigurations[$typeReport] ?? $this->reportConfigurations[self::TICKET_TYPE_REPORT];

        $header = [];
        foreach ($columns as $col) {
            $header[$col['label']] = $col['type'] ?? 'string';
        }

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $header);

        foreach ($this->getChunkedResults($this->filtersWhere, $this->orderBy) as $item) {
            if ($this->filters['type_report'] ?? self::ANALYSIS_CALL_TYPE_REPORT) {
                $analysis = json_decode($item->analysis, true);
            }

            $row = [];
            foreach ($columns as $col) {
                $row[] = $this->getColumnValue($item, $col['key'], $analysis ?? []);
            }

            $writer->writeSheetRow('Отчёт', $row);
        }

        $filename = 'collection_summary_report_' . date('Y-m-d') . '.xlsx';

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
            'phone_mobile' => $this->request->get('phone_mobile') ?? null,
            'conversation_topic' => $this->request->get('conversation_topic') ?? null,
            'ticket_type' => $this->request->get('ticket_type') ?? null,
            'type_report' => $this->request->get('type_report') ?? self::TICKET_TYPE_REPORT,
        ];
    }

    private function getConversationTopic(string $where = '')
    {
        if ($this->filters['type_report'] === self::TICKET_TYPE_REPORT) {
            return [];
        }

        $this->db->query("
            SELECT 
                JSON_UNQUOTE(JSON_EXTRACT(cra.analysis, '$.conversation_topic')) AS name
            FROM s_comment_record_analysis cra
            WHERE DATE(cra.created) BETWEEN ? AND ? " . $where . "
            GROUP BY name
            ORDER BY name",
            $this->dateFrom, $this->dateTo
        );

        return $this->db->results();
    }

    private function getTicketType(string $where = '')
    {
        if ($this->filters['type_report'] === self::ANALYSIS_CALL_TYPE_REPORT) {
            return [];
        }

        $this->db->query("
                    SELECT
                        mtsub.name
                    FROM s_mytickets mt
                    LEFT JOIN s_mytickets_subjects mtsub ON mt.subject_id = mtsub.id
                    LEFT JOIN s_users u ON mt.client_id = u.id
                    WHERE DATE(mt.created_at) BETWEEN ? AND ? " . $where . "
                    GROUP BY name
                    ORDER BY name",
            $this->dateFrom, $this->dateTo
        );

        return $this->db->results();
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
                    case 'phone_mobile':
                        $where .= $this->filterPhoneMobileWhere($value);
                        break;
                    case 'ticket_type':
                        $where .= $this->filterTicketTypeWhere($value);
                        break;
                    case 'conversation_topic':
                        $where .= $this->filterConversationTopicsWhere($value);
                        break;
                    case 'type_report':
                        $where .= $this->filterTypeReportWhere($value);
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

    private function filterPhoneMobileWhere(string $value): string
    {
        if (!empty($value)) {
            $phoneSearch = '%' . $this->db->escape($value) . '%';
            return " AND u.phone_mobile LIKE '$phoneSearch'";
        }
        return '';
    }

    private function filterConversationTopicsWhere(string $value): string
    {
        if (!empty($value) && $this->filters['type_report'] === self::ANALYSIS_CALL_TYPE_REPORT) {
            $convTopicSearch = '%' . $this->db->escape('"conversation_topic":"' . $value . '"') . '%';
            return " AND cra.analysis LIKE '$convTopicSearch'";
        }
        return '';
    }

    private function filterTicketTypeWhere(string $value): string
    {
        if (!empty($value) && $this->filters['type_report'] === self::TICKET_TYPE_REPORT) {
            return " AND mtsub.name = '$value'";
        }
        return '';
    }

    private function filterTypeReportWhere(string $value): string
    {
        if (!empty($value)) {
            if ($value === self::TICKET_TYPE_REPORT) {
                return " AND (mtsub.id = " . self::COLLECTION_SUBJECT_ID . " OR mtsub.parent_id = " . self::COLLECTION_SUBJECT_ID . ") ";
            } elseif ($value === self::ANALYSIS_CALL_TYPE_REPORT) {
                return 'AND (cra.analysis LIKE \'%Жалоба на отдел взыскания%\' OR cra.analysis LIKE \'%Вопросы по рекуррентным списаниям%\')';
            }
        }
        return '';
    }

    private function setOrderBy(): string
    {
        if ($this->filters['type_report'] === self::TICKET_TYPE_REPORT) {
            $orderBy = 'mt.created_at DESC';
        } else {
            $orderBy = 'cra.created DESC';
        }

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
                    case 'phone_mobile':
                        $orderBy = "u.phone_mobile $direction";
                        break;
                    case 'date':
                    case 'time':
                        if ($this->filters['type_report'] === self::TICKET_TYPE_REPORT) {
                            $orderBy = 'mt.created_at DESC';
                        } else {
                            $orderBy = 'cra.created DESC';
                        }
                        break;
                    case 'ticket_type':
                        $orderBy = "mtsub.name $direction";
                        break;
                    case 'conversation_topic':
                        $orderBy = "JSON_UNQUOTE(JSON_EXTRACT(cra.analysis, '$.conversation_topic')) $direction";
                        break;
                    case 'total_assessment':
                        $orderBy = "JSON_UNQUOTE(JSON_EXTRACT(cra.analysis, '$.total_assessment')) $direction";
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
        $filters = [
            [
                'name' => 'type_report',
                'label' => 'Тип отчёта',
                'type' => 'select',
                'value' => $this->filters['type_report'] ?? self::TICKET_TYPE_REPORT,
                'options' => [
                    self::TICKET_TYPE_REPORT => 'Тикеты',
                    self::ANALYSIS_CALL_TYPE_REPORT => 'Звонки',
                ],
            ],
            [
                'name' => 'user',
                'label' => 'Клиент',
                'type' => 'text',
                'value' => $this->filters['user'] ?? '',
                'placeholder' => 'Введите имя клиента'
            ],
            [
                'name' => 'phone_mobile',
                'label' => 'Телефон',
                'type' => 'text',
                'value' => $this->filters['phone_mobile'] ?? '',
                'placeholder' => 'Введите телефон'
            ],
        ];

        switch ($this->filters['type_report']) {
            case self::TICKET_TYPE_REPORT:
                $filters = array_merge($filters, [
                    [
                        'name' => 'ticket_type',
                        'label' => 'Тип жалобы',
                        'type' => 'select',
                        'value' => $this->filters['ticket_type'] ?? '',
                        'options' => $this->getTicketTypeFilterOptions(),
                        'option_value_field' => 'name',
                        'option_label_field' => 'name',
                    ]
                ]);
                break;
            case self::ANALYSIS_CALL_TYPE_REPORT:
                $filters = array_merge($filters, [
                    [
                        'name' => 'conversation_topic',
                        'label' => 'Тема разговора',
                        'type' => 'select',
                        'value' => $this->filters['conversation_topic'] ?? '',
                        'options' => $this->getConversationTopicFilterOptions(),
                        'option_value_field' => 'name',
                        'option_label_field' => 'name',
                    ]
                ]);
                break;
            default:
                break;
        }

        return $filters;
    }

    private function convertOptionsToArray(array $options): array
    {
        return array_map('get_object_vars', $options);
    }

    private function getTicketTypeFilterOptions(): array
    {
        $options = $this->getTicketType($this->filtersWhere);

        return $this->convertOptionsToArray($options);
    }

    private function getConversationTopicFilterOptions(): array
    {
        $options = $this->getConversationTopic($this->filtersWhere);

        return $this->convertOptionsToArray($options);
    }
}
