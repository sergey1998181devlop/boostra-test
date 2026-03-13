<?php

require_once 'View.php';

class AdditionalAnalysisCallsReportView extends View
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

    private static array $columns = [
        ['key' => 'client', 'label' => 'Клиент', 'sort_key' => 'client'],
        ['key' => 'phone_mobile', 'label' => 'Телефон клиента', 'sort_key' => 'phone_mobile'],
        ['key' => 'record', 'label' => 'Запись звонка'],
        ['key' => 'duration', 'label' => 'Продолжительность звонка'],
        ['key' => 'date', 'label' => 'Дата звонка', 'sort_key' => 'date'],
        ['key' => 'time', 'label' => 'Время звонка', 'sort_key' => 'time'],
        ['key' => 'operator', 'label' => 'ФИО оператора', 'sort_key' => 'operator'],
        ['key' => 'tag', 'label' => 'Тэг', 'sort_key' => 'tag'],
        ['key' => 'menu', 'label' => 'Выбрал меню', 'sort_key' => 'menu'],
        ['key' => 'client_assessment', 'label' => 'Оценка клиента', 'sort_key' => 'assessment', 'type' => 'integer'],
        ['key' => 'conversation_topic', 'label' => 'Тема разговора'],
        ['key' => 'sale', 'label' => 'Продажа', 'type' => 'boolean'],
        ['key' => 'active_listening_assessment', 'label' => 'Балл за Активное слушание', 'type' => 'integer'],
        ['key' => 'active_listening_justification', 'label' => 'Что можно улучшить1'],
        ['key' => 'product_promotion_assessment', 'label' => 'Название и описание услуги', 'type' => 'integer'],
        ['key' => 'product_promotion_justification', 'label' => 'Что можно улучшить2'],
        ['key' => 'objection_handling_assessment', 'label' => 'Отработка возражений', 'type' => 'integer'],
        ['key' => 'objection_handling_justification', 'label' => 'Что можно улучшить3'],
        ['key' => 'farewell_assessment', 'label' => 'Прощание', 'type' => 'integer'],
        ['key' => 'farewell_justification', 'label' => 'Что можно улучшить4'],
        ['key' => 'final_sale_improvements', 'label' => 'Что можно улучшить5'],
        ['key' => 'final_sale_sale_recommendations', 'label' => 'Какие аспекты повлияли на Продажу или отказ от допа'],
        ['key' => 'total_assessment', 'label' => 'Общая оценка', 'sort_key' => 'total_assessment', 'type' => 'number'],
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
            'phone_mobile_filter' => $this->filters['phone_mobile'],
            'operators_filter' => $this->filters['operators'],
            'tags_filter' => $this->filters['tags'],
            'menu_filter' => $this->filters['menu'],
            'assessment_filter' => $this->filters['assessment'],
            'conversation_topics_filter' => $this->filters['conversation_topics'],
            'filterConfigurations' => $this->getFilterConfiguration(),
        ]);

        return $this->design->fetch('additional_analysis_calls_report.tpl');
    }

    private function prepareReportRows(array $items, array $columns): array
    {
        $rows = [];
        foreach ($items as $item) {
            $callData = json_decode($item->call_data, true);
            $analysis = json_decode($item->analysis, true);

            $row = [];
            foreach ($columns as $col) {
                $row[$col['key']] = $this->getColumnValue($item, $col['key'], $callData, $analysis);
            }
            $rows[] = $row;
        }
        return $rows;
    }

    private function getColumnValue(object $item, string $key, array $callData, array $analysis): string
    {
        switch ($key) {
            case 'client':
                return trim("$item->lastname $item->firstname $item->patronymic");
            case 'phone_mobile':
                return $item->phone_mobile ?? '';
            case 'record':
                return $callData['record_url'] ?? '';
            case 'duration':
                return $callData['record_duration'] ?? '';
            case 'date':
                return date('d.m.Y', strtotime($item->created));
            case 'time':
                return date('H:i:s', strtotime($item->created));
            case 'operator':
                return !empty($callData['operator_name']) ? $callData['operator_name'] : 'Оператор не определён';
            case 'tag':
                return $callData['operator_tag'] ?? '';
            case 'menu':
                return $callData['tag'] ?? '';
            case 'client_assessment':
                return $callData['assessment'] ?? '';
            case 'conversation_topic':
                return $analysis['conversation_topic'] ?? '';
            case 'sale':
                return isset($analysis['sale']) ? ($analysis['sale'] ? 'Да' : 'Нет') : '';
            case 'active_listening_assessment':
                return $analysis['active_listening']['assessment'] ?? '';
            case 'active_listening_justification':
                return $analysis['active_listening']['justification'] ?? '';
            case 'product_promotion_assessment':
                return $analysis['product_promotion']['assessment'] ?? '';
            case 'product_promotion_justification':
                return $analysis['product_promotion']['justification'] ?? '';
            case 'objection_handling_assessment':
                return $analysis['objection_handling']['assessment'] ?? '';
            case 'objection_handling_justification':
                return $analysis['objection_handling']['justification'] ?? '';
            case 'farewell_assessment':
                return $analysis['farewell']['assessment'] ?? '';
            case 'farewell_justification':
                return $analysis['farewell']['justification'] ?? '';
            case 'final_sale_improvements':
                return $analysis['final_sale']['improvements'] ?? '';
            case 'final_sale_sale_recommendations':
                return $analysis['final_sale']['sale_recommendations'] ?? '';
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
                u.phone_mobile,
                cra.analysis,
                c.text AS call_data,
                c.created
            FROM s_comment_record_analysis cra 
            LEFT JOIN s_comments c ON c.id = cra.comment_id
            LEFT JOIN s_users u ON u.id = cra.user_id
            WHERE DATE(c.created) BETWEEN ? AND ? AND cra.analysis NOT LIKE '%\"sale\":\"\"%' AND cra.analysis LIKE '%sale%'
            ORDER BY " . $orderBy . "
            LIMIT ? OFFSET ?",
            $this->dateFrom, $this->dateTo, self::PAGE_CAPACITY, $offset
        );

        return $this->db->results();
    }

    /**
     * @throws Exception
     */
    private function getAllResults(string $where = '', string $orderBy = ''): array
    {
        $this->db->query("
            SELECT 
                u.lastname, 
                u.firstname, 
                u.patronymic,
                u.id AS user_id,
                u.phone_mobile,
                cra.analysis,
                c.text AS call_data,
                c.created
            FROM s_comment_record_analysis cra 
            LEFT JOIN s_comments c ON c.id = cra.comment_id
            LEFT JOIN s_users u ON u.id = cra.user_id
            WHERE DATE(c.created) BETWEEN ? AND ? AND cra.analysis NOT LIKE '%\"sale\":\"\"%' AND cra.analysis LIKE '%sale%'
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
                u.phone_mobile,
                cra.analysis,
                c.text AS call_data,
                c.created
            FROM s_comment_record_analysis cra 
            LEFT JOIN s_comments c ON c.id = cra.comment_id
            LEFT JOIN s_users u ON u.id = cra.user_id
            WHERE DATE(c.created) BETWEEN ? AND ? AND cra.analysis NOT LIKE '%\"sale\":\"\"%' AND cra.analysis LIKE '%sale%' 
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
        $query = $this->db->placehold("
            SELECT COUNT(cra.id) AS total 
            FROM s_comment_record_analysis cra 
            LEFT JOIN s_comments c ON c.id = cra.comment_id
            LEFT JOIN s_users u ON u.id = cra.user_id
            WHERE DATE(c.created) BETWEEN ? AND ? AND cra.analysis NOT LIKE '%\"sale\":\"\"%' AND cra.analysis LIKE '%sale%'",
            $this->dateFrom, $this->dateTo
        );
        $this->db->query($query);
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
            $header[$col['label']] = isset($col['type']) ? $col['type'] : 'string';
        }

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $header);

        foreach ($this->getChunkedResults($this->filtersWhere, $this->orderBy) as $item) {
            $callData = json_decode($item->call_data, true);
            $analysis = json_decode($item->analysis, true);
            $row = [];
            foreach ($columns as $col) {
                $row[] = $this->getColumnValue($item, $col['key'], $callData, $analysis);
            }
            $writer->writeSheetRow('Отчёт', $row);
        }

        $filename = 'additional_analysis_calls_report_' . date('Y-m-d') . '.xlsx';

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
            'operators' => $this->request->get('operators') ?? null,
            'tags' => $this->request->get('tags') ?? null,
            'menu' => $this->request->get('menu') ?? null,
            'assessment' => $this->request->get('assessment') ?? null,
            'conversation_topics' => $this->request->get('conversation_topics') ?? null,
        ];
    }

    private function getTags(string $where = '')
    {
        $this->db->query("
            SELECT 
                JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.operator_tag')) AS operator_tag
            FROM s_comment_record_analysis cra 
            LEFT JOIN s_comments c ON c.id = cra.comment_id
            WHERE DATE(c.created) BETWEEN ? AND ? AND cra.analysis NOT LIKE '%\"sale\":\"\"%' AND cra.analysis LIKE '%sale%' " . $where . "
            GROUP BY operator_tag
            ORDER BY operator_tag",
            $this->dateFrom, $this->dateTo
        );

        return $this->db->results();
    }

    private function getOperators(string $where = '')
    {
        $this->db->query("
            SELECT 
                JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.operator_name')) AS operator_name
            FROM s_comment_record_analysis cra 
            LEFT JOIN s_comments c ON c.id = cra.comment_id
            WHERE DATE(c.created) BETWEEN ? AND ? AND cra.analysis NOT LIKE '%\"sale\":\"\"%' AND cra.analysis LIKE '%sale%' " . $where . "
            GROUP BY operator_name
            ORDER BY operator_name",
            $this->dateFrom, $this->dateTo
        );

        return $this->db->results();
    }

    private function getMenu(string $where = '')
    {
        $this->db->query("
            SELECT 
                JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.tag')) AS name
            FROM s_comment_record_analysis cra 
            LEFT JOIN s_comments c ON c.id = cra.comment_id
            WHERE DATE(c.created) BETWEEN ? AND ? AND cra.analysis NOT LIKE '%\"sale\":\"\"%' AND cra.analysis LIKE '%sale%' " . $where . "
            GROUP BY name
            ORDER BY name",
            $this->dateFrom, $this->dateTo
        );

        return $this->db->results();
    }

    private function getConversationTopic(string $where = '')
    {
        $this->db->query("
            SELECT 
                JSON_UNQUOTE(JSON_EXTRACT(cra.analysis, '$.conversation_topic')) AS name
            FROM s_comment_record_analysis cra 
            LEFT JOIN s_comments c ON c.id = cra.comment_id
            WHERE DATE(c.created) BETWEEN ? AND ? AND cra.analysis NOT LIKE '%\"sale\":\"\"%' AND cra.analysis LIKE '%sale%' " . $where . "
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
                    case 'operators':
                        $where .= $this->filterOperatorsWhere($value);
                        break;
                    case 'tags':
                        $where .= $this->filterTagsWhere($value);
                        break;
                    case 'menu':
                        $where .= $this->filterMenuWhere($value);
                        break;
                    case 'assessment':
                        $where .= $this->filterAssessmentWhere($value);
                        break;
                    case 'conversation_topics':
                        $where .= $this->filterConversationTopicsWhere($value);
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

    private function filterOperatorsWhere(string $value): string
    {
        if (!empty($value)) {
            $operatorsSearch = '%' . $this->db->escape('"operator_name":"' . $value . '"') . '%';
            return " AND c.text LIKE '$operatorsSearch'";
        }
        return '';
    }

    private function filterTagsWhere(string $value): string
    {
        if (!empty($value)) {
            $tagsSearch = '%' . $this->db->escape('"operator_tag":"' . $value . '"') . '%';
            return " AND c.text LIKE '$tagsSearch'";
        }
        return '';
    }

    private function filterMenuWhere(string $value): string
    {
        if (!empty($value)) {
            $menuSearch = '%' . $this->db->escape('"tag":"' . $value . '"') . '%';
            return " AND c.text LIKE '$menuSearch'";
        }
        return '';
    }

    private function filterAssessmentWhere(string $value): string
    {
        if (!empty($value)) {
            $assessmentSearch = '%' . $this->db->escape('"assessment":"' . $value . '"') . '%';
            return " AND c.text LIKE '$assessmentSearch'";
        }
        return '';
    }

    private function filterConversationTopicsWhere(string $value): string
    {
        if (!empty($value)) {
            $convTopicSearch = '%' . $this->db->escape('"conversation_topic":"' . $value . '"') . '%';
            return " AND cra.analysis LIKE '$convTopicSearch'";
        }
        return '';
    }

    private function setOrderBy(): string
    {
        $orderBy = 'c.created DESC';

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
                        $orderBy = "c.created $direction";
                        break;
                    case 'operator':
                        $orderBy = "JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.operator_name')) $direction";
                        break;
                    case 'tag':
                        $orderBy = "JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.operator_tag')) $direction";
                        break;
                    case 'menu':
                        $orderBy = "JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.tag')) $direction";
                        break;
                    case 'assessment':
                        $orderBy = "JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.assessment')) $direction";
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
        return [
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
            [
                'name' => 'operators',
                'label' => 'Оператор',
                'type' => 'select',
                'value' => $this->filters['operators'] ?? '',
                'options' => $this->getOperatorsFilterOptions(),
                'option_value_field' => 'operator_name',
                'option_label_field' => 'operator_name',
            ],
            [
                'name' => 'tags',
                'label' => 'Тэг',
                'type' => 'select',
                'value' => $this->filters['tags'] ?? '',
                'options' => $this->getTagsFilterOptions(),
                'option_value_field' => 'operator_tag',
                'option_label_field' => 'operator_tag',
            ],
            [
                'name' => 'menu',
                'label' => 'Выбрал меню',
                'type' => 'select',
                'value' => $this->filters['menu'] ?? '',
                'options' => $this->getMenuFilterOptions(),
                'option_value_field' => 'name',
                'option_label_field' => 'name',
            ],
            [
                'name' => 'assessment',
                'label' => 'Оценка клиента',
                'type' => 'select',
                'value' => $this->filters['assessment'] ?? '',
                'options' => ['' => 'Все', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'],
            ],
            [
                'name' => 'conversation_topics',
                'label' => 'Тема разговора',
                'type' => 'select',
                'value' => $this->filters['conversation_topics'] ?? '',
                'options' => $this->getConversationTopicsFilterOptions(),
                'option_value_field' => 'name',
                'option_label_field' => 'name',
            ]
        ];
    }

    private function convertOptionsToArray(array $options): array
    {
        return array_map('get_object_vars', $options);
    }

    private function getOperatorsFilterOptions(): array
    {
        $options = $this->getOperators();

        return $this->convertOptionsToArray($options);
    }

    private function getTagsFilterOptions(): array
    {
        $options = $this->getTags();

        return $this->convertOptionsToArray($options);
    }

    private function getMenuFilterOptions(): array
    {
        $options = $this->getMenu();

        return $this->convertOptionsToArray($options);
    }

    private function getConversationTopicsFilterOptions(): array
    {
        $options = $this->getConversationTopic();

        return $this->convertOptionsToArray($options);
    }
}
