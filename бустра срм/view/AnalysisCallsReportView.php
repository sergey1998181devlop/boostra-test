<?php

require_once 'View.php';

class AnalysisCallsReportView extends View
{
    private const PAGE_CAPACITY = 15;
    private const OUTGOING_CALL_TYPE_REPORT = 'outgoingCall';
    private const INCOMING_CALL_TYPE_REPORT = 'incomingCall';
    private const FROMTECH_INCOMING_CALL_TYPE_REPORT = 'fromtechIncomingCall';
    private const ADDITIONAL_SERVICE_TYPE_REPORT = 'additionalService';
    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;
    private array $filters;
    private string $filtersWhere;
    private string $orderBy;
    private string $dateFrom;
    private string $dateTo;

    // Общие поля для всех типов отчётов
    private static array $commonFields = [
        ['key' => 'client', 'label' => 'Клиент', 'sort_key' => 'client'],
        ['key' => 'phone_mobile', 'label' => 'Телефон клиента', 'sort_key' => 'phone_mobile'],
        ['key' => 'record', 'label' => 'Запись звонка'],
        ['key' => 'duration', 'label' => 'Продолжительность звонка'],
        ['key' => 'date', 'label' => 'Дата звонка', 'sort_key' => 'date'],
        ['key' => 'time', 'label' => 'Время звонка', 'sort_key' => 'time'],
        ['key' => 'provider', 'label' => 'Телефония', 'sort_key' => 'provider'],
        ['key' => 'operator', 'label' => 'ФИО оператора', 'sort_key' => 'operator'],
    ];

    // Дополнительные поля для входящих звонков
    private static array $incomingCallExtraFields = [
        ['key' => 'tag', 'label' => 'Тэг', 'sort_key' => 'tag'],
        ['key' => 'menu', 'label' => 'Выбрал меню', 'sort_key' => 'menu'],
        ['key' => 'client_assessment', 'label' => 'Оценка клиента', 'sort_key' => 'assessment', 'type' => 'integer'],
        ['key' => 'transcribe', 'label' => 'Транскрибация'],
        ['key' => 'conversation_topic', 'label' => 'Тема разговора'],
        ['key' => 'active_listening_score', 'label' => 'Активное слушание', 'type' => 'integer'],
        ['key' => 'politeness', 'label' => 'Вежливость', 'type' => 'integer'],
        ['key' => 'resume', 'label' => 'Резюмирование', 'type' => 'integer'],
        ['key' => 'correct_solution', 'label' => 'Правильность решения', 'type' => 'integer'],
        ['key' => 'client_satisfaction', 'label' => 'Удовлетворенность клиента', 'type' => 'integer'],
        ['key' => 'client_conflict', 'label' => 'Клиент конфликтоген', 'type' => 'integer'],
        ['key' => 'manager_conflict', 'label' => 'Менеджер конфликтоген'],
        ['key' => 'explanation', 'label' => 'Объяснение'],
        ['key' => 'recommendations', 'label' => 'Рекомендации'],
    ];

    // Дополнительные поля для звонков с доп. услугами
    private static array $additionalExtraFields = [
        ['key' => 'sale', 'label' => 'Продажа', 'type' => 'boolean'],
        ['key' => 'use_name_additional_software_assessment', 'label' => 'Использовал название дополнительного ПО (оценка)', 'type' => 'integer'],
        ['key' => 'use_name_additional_software_justification', 'label' => 'Использовал название дополнительного ПО (обоснование)'],
        ['key' => 'use_script_assessment', 'label' => 'Использование скрипта (оценка)', 'type' => 'integer'],
        ['key' => 'use_script_justification', 'label' => 'Использование скрипта (обоснование)'],
        ['key' => 'final_sale_improvements', 'label' => 'Финал продажа (Что улучшить)'],
        ['key' => 'final_sale_sale_recommendations', 'label' => 'Финал продажа (Рекомендации продажа)'],
    ];


    // Дополнительные поля для исходящих звонков
    private static array $outgoingCallExtraFields = [
        ['key' => 'identification_procedures_assessment', 'label' => 'Соблюдение процедур идентификации и верификации (оценка)', 'type' => 'integer'],
        ['key' => 'identification_procedures_justification', 'label' => 'Соблюдение процедур идентификации и верификации (обоснование)'],
        ['key' => 'call_objective_and_motivation_assessment', 'label' => 'Доведение цели звонка и мотивации (оценка)', 'type' => 'integer'],
        ['key' => 'call_objective_and_motivation_justification', 'label' => 'Доведение цели звонка и мотивации (обоснование)'],
        ['key' => 'compliance_230FZ_assessment', 'label' => 'Соответствие 230-ФЗ (оценка)', 'type' => 'integer'],
        ['key' => 'compliance_230FZ_justification', 'label' => 'Соответствие 230-ФЗ (обоснование)'],
        ['key' => 'argumentation_and_outcomes_assessment', 'label' => 'Аргументация и итоги (оценка)', 'type' => 'integer'],
        ['key' => 'argumentation_and_outcomes_justification', 'label' => 'Аргументация и итоги (обоснование)'],
        ['key' => 'recommendations', 'label' => 'Рекомендации по улучшению работы'],
    ];

    private static array $outgoingCallMangoExtraFields = [
        ['key' => 'greeting', 'label' => 'Приветствие', 'type' => 'integer'],
        ['key' => 'position_and_company', 'label' => 'Должность и компания', 'type' => 'integer'],
        ['key' => 'notified_about_recording', 'label' => 'Уведомил о записи', 'type' => 'integer'],
        ['key' => 'client_identification', 'label' => 'Идентификация клиента', 'type' => 'integer'],
        ['key' => 'no_third_party_discussion', 'label' => 'Не говорил с 3-м лицом', 'type' => 'integer'],
        ['key' => 'debt_amount', 'label' => 'Сумма долга', 'type' => 'integer'],
        ['key' => 'repayment_term', 'label' => 'Срок погашения', 'type' => 'integer'],
        ['key' => 'was_a_dialogue', 'label' => 'Был диалогом', 'type' => 'integer'],
        ['key' => 'call_at_permitted_time', 'label' => 'Звонок в разрешенное время', 'type' => 'integer'],
        ['key' => 'no_conflicts', 'label' => 'Не было конфликтов', 'type' => 'integer'],
        ['key' => 'official_address', 'label' => 'Официальное обращение', 'type' => 'integer'],
        ['key' => 'strong_arguments', 'label' => 'Сильные аргументы', 'type' => 'integer'],
        ['key' => 'final_summary', 'label' => 'Итоговое резюме', 'type' => 'integer'],
        ['key' => 'logical_sequence', 'label' => 'Логичная последовательность', 'type' => 'integer'],
    ];

    private static array $totalAssessmentField = [
        ['key' => 'total_assessment', 'label' => 'Общая оценка', 'sort_key' => 'total_assessment', 'type' => 'number'],
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
            self::INCOMING_CALL_TYPE_REPORT => array_merge(
                self::$commonFields,
                self::$incomingCallExtraFields,
                self::$totalAssessmentField
            ),
            self::FROMTECH_INCOMING_CALL_TYPE_REPORT => array_merge(
                self::$commonFields,
                self::$incomingCallExtraFields,
                self::$totalAssessmentField
            ),
            self::ADDITIONAL_SERVICE_TYPE_REPORT => array_merge(
                self::$commonFields,
                self::$incomingCallExtraFields,
                self::$additionalExtraFields,
                self::$totalAssessmentField
            ),
            self::OUTGOING_CALL_TYPE_REPORT => array_merge(
                self::$commonFields,
                ($this->filters['provider'] ?? '') === 'mango' ? self::$outgoingCallMangoExtraFields : self::$outgoingCallExtraFields,
                self::$totalAssessmentField
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

        $typeReport = $this->filters['type_report'] ?? self::INCOMING_CALL_TYPE_REPORT;
        $columns = $this->reportConfigurations[$typeReport] ?? $this->reportConfigurations[self::INCOMING_CALL_TYPE_REPORT];

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
            'type_report_filter' => $this->filters['type_report'],
            'provider_filter' => $this->filters['provider'],
            'filterConfigurations' => $this->getFilterConfiguration(),
        ]);

        return $this->design->fetch('analysis_calls_report.tpl');
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
            $row['client_id'] = $item->user_id;
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
                if (isset($callData['provider']) && $callData['provider'] === 'mango'){
                    return $this->getPublicUrlForMango($callData['record_url']);
                }

                return $callData['record_url'] ?? '';
            case 'duration':
                return $callData['record_duration'] ?? $callData['duration'] ?? '';
            case 'date':
                return date('d.m.Y', strtotime($item->created));
            case 'time':
                return date('H:i:s', strtotime($item->created));
            case 'operator':
                return !empty($callData['operator_name']) ? $callData['operator_name'] : 'Оператор не определён';
            case 'provider':
                return ucfirst($callData['provider'] ?? 'Voximplant');
            case 'recommendations':
                return $analysis['recommendations'] ?? '';
            // Дополнительные поля для входящих звонков
            case 'tag':
                return $callData['operator_tag'] ?? '';
            case 'menu':
                return $callData['tag'] ?? '';
            case 'client_assessment':
                return $callData['assessment'] ?? '';
            case 'transcribe':
                return $analysis['transcribe'] ?? '';
            case 'conversation_topic':
                return $analysis['conversation_topic'] ?? '';
            case 'politeness':
                return $analysis['politeness'] ?? '';
            case 'active_listening_score':
                return $analysis['active_listening_score'] ?? '';
            case 'resume':
                return $analysis['resume'] ?? '';
            case 'correct_solution':
                return $analysis['correct_solution'] ?? '';
            case 'client_satisfaction':
                return $analysis['client_satisfaction'] ?? '';
            case 'client_conflict':
                return $analysis['client_conflict'] ?? '';
            case 'manager_conflict':
                return $analysis['manager_conflict'] ?? '';
            case 'explanation':
                return $analysis['explanation'] ?? '';
            // Дополнительные поля для звонков с доп. услугами:
            case 'sale':
                return isset($analysis['sale']) ? ($analysis['sale'] ? 'Да' : 'Нет') : '';
            case 'use_name_additional_software_assessment':
                return $analysis['use_name_additional_software']['assessment'] ?? '';
            case 'use_name_additional_software_justification':
                return $analysis['use_name_additional_software']['justification'] ?? '';
            case 'use_script_assessment':
                return $analysis['use_script']['assessment'] ?? '';
            case 'use_script_justification':
                return $analysis['use_script']['justification'] ?? '';
            case 'final_sale_improvements':
                return $analysis['final_sale']['improvements'] ?? '';
            case 'final_sale_sale_recommendations':
                return $analysis['final_sale']['sale_recommendations'] ?? '';
            // Дополнительные поля для исходящих звонков
            case 'identification_procedures_assessment':
                return $analysis['identification_procedures']['assessment'] ?? '';
            case 'identification_procedures_justification':
                return $analysis['identification_procedures']['justification'] ?? '';
            case 'call_objective_and_motivation_assessment':
                return $analysis['call_objective_and_motivation']['assessment'] ?? '';
            case 'call_objective_and_motivation_justification':
                return $analysis['call_objective_and_motivation']['justification'] ?? '';
            case 'compliance_230FZ_assessment':
                return $analysis['compliance_230FZ']['assessment'] ?? '';
            case 'compliance_230FZ_justification':
                return $analysis['compliance_230FZ']['justification'] ?? '';
            case 'argumentation_and_outcomes_assessment':
                return $analysis['argumentation_and_outcomes']['assessment'] ?? '';
            case 'argumentation_and_outcomes_justification':
                return $analysis['argumentation_and_outcomes']['justification'] ?? '';
            // Дополнительные для исходящих звонков с провайдером Mango
            case 'greeting':
                return $analysis['greeting'] ?? '';
            case 'position_and_company':
                return $analysis['position_and_company'] ?? '';
            case 'notified_about_recording':
                return $analysis['notified_about_recording'] ?? '';
            case 'client_identification':
                return $analysis['client_identification'] ?? '';
            case 'no_third_party_discussion':
                return $analysis['no_third_party_discussion'] ?? '';
            case 'debt_amount':
                return $analysis['debt_amount'] ?? '';
            case 'repayment_term':
                return $analysis['repayment_term'] ?? '';
            case 'was_a_dialogue':
                return $analysis['was_a_dialogue'] ?? '';
            case 'call_at_permitted_time':
                return $analysis['call_at_permitted_time'] ?? '';
            case 'no_conflicts':
                return $analysis['no_conflicts'] ?? '';
            case 'official_address':
                return $analysis['official_address'] ?? '';
            case 'strong_arguments':
                return $analysis['strong_arguments'] ?? '';
            case 'final_summary':
                return $analysis['final_summary'] ?? '';
            case 'logical_sequence':
                return $analysis['logical_sequence'] ?? '';

            // Общая оценка
            case 'total_assessment':
                return $analysis['total_assessment'] ?? '';
            default:
                return '';
        }
    }

    private function getPublicUrlForMango($mangoRecordUrl): string
    {
        $parsed = parse_url($mangoRecordUrl);
        $s3_name = ltrim(str_replace('/call-storage/', '', $parsed['path']), '/');

        $this->s3_api_client->setBucket('call-storage');

        return $this->s3_api_client->getPublicUrl($s3_name);
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
            FROM s_comments c
            JOIN s_comment_record_analysis cra
              ON cra.comment_id = c.id
            JOIN s_users u
              ON u.id = cra.user_id
            WHERE DATE(c.created) BETWEEN ? AND ? " . $where . "
            ORDER BY " . $orderBy . "
            LIMIT ? OFFSET ?",
            $this->dateFrom, $this->dateTo, self::PAGE_CAPACITY, $offset);

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
            WHERE DATE(c.created) BETWEEN ? AND ? " . $where . "
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
            WHERE DATE(c.created) BETWEEN ? AND ? " . $where . "
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
            WHERE DATE(c.created) BETWEEN ? AND ? " . $where,
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

        $typeReport = $this->filters['type_report'] ?? self::INCOMING_CALL_TYPE_REPORT;
        $columns = $this->reportConfigurations[$typeReport] ?? $this->reportConfigurations[self::INCOMING_CALL_TYPE_REPORT];

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

        $filename = 'analysis_calls_report_' . date('Y-m-d') . '.xlsx';

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
            'type_report' => $this->request->get('type_report') ?? self::INCOMING_CALL_TYPE_REPORT,
            'provider' => $this->request->get('provider') ?? null,
        ];
    }

    private function getTags(string $where = '')
    {
        $this->db->query("
            SELECT 
                JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.operator_tag')) AS operator_tag
            FROM s_comment_record_analysis cra 
            LEFT JOIN s_comments c ON c.id = cra.comment_id
            WHERE DATE(c.created) BETWEEN ? AND ? " . $where . "
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
            WHERE DATE(c.created) BETWEEN ? AND ? " . $where . "
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
            WHERE DATE(c.created) BETWEEN ? AND ? " . $where . "
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
            WHERE DATE(c.created) BETWEEN ? AND ? " . $where . "
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
                    case 'type_report':
                        $where .= $this->filterTypeReportWhere($value);
                        break;
                    case 'provider':
                        $where .= $this->filterByProviderWhere($value);
                        break;
                    default:
                        break;
                }
            }
        }
        return $where;
    }

    private function filterByProviderWhere(string $value): string
    {
        if (!empty($value)) {
            $providerSearch = '%' . $this->db->escape('"provider":"' . $value . '"') . '%';
            return " AND c.text LIKE '$providerSearch'";
        }
        return '';
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

    private function filterTypeReportWhere(string $value): string
    {
        if (!empty($value)) {
            if (in_array($value, [self::INCOMING_CALL_TYPE_REPORT, self::FROMTECH_INCOMING_CALL_TYPE_REPORT, self::OUTGOING_CALL_TYPE_REPORT])) {
                return ' AND c.block = "' . $value . '" AND (cra.analysis LIKE \'%"sale":""%\' OR cra.analysis NOT LIKE \'%sale%\')';
            } elseif ($value === self::ADDITIONAL_SERVICE_TYPE_REPORT) {
                return ' AND cra.analysis NOT LIKE \'%"sale":""%\' AND cra.analysis LIKE \'%sale%\'';
            }
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
        $filters = array_filter([
            [
                'name' => 'type_report',
                'label' => 'Тип отчёта',
                'type' => 'select',
                'value' => $this->filters['type_report'] ?? self::INCOMING_CALL_TYPE_REPORT,
                'options' => [
                    'incomingCall' => 'Входящие звонки',
                    'fromtechIncomingCall' => 'Входящие звонки от робота',
                    'outgoingCall' => 'Исходящие звонки',
                    'additionalService' => 'Доп. услуги'
                ],
            ],
            $this->filters['type_report'] === self::OUTGOING_CALL_TYPE_REPORT ?
                [
                    'name' => 'provider',
                    'label' => 'Телефония',
                    'type' => 'select',
                    'value' => $this->filters['provider'] ?? '',
                    'options' => [
                        'voximplant' => 'Voximplant',
                        'mango' => 'Mango',
                    ]
                ]
                : null,
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
        ]);

        if (in_array($this->filters['type_report'], [self::INCOMING_CALL_TYPE_REPORT, self::ADDITIONAL_SERVICE_TYPE_REPORT])) {
            $filters = array_merge($filters, [
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
                ]]);
        }

        return $filters;
    }

    private function convertOptionsToArray(array $options): array
    {
        return array_map('get_object_vars', $options);
    }

    private function getOperatorsFilterOptions(): array
    {
        $where = $this->filterTypeReportWhere($this->filters['type_report']);

        $options = $this->getOperators($where);

        return $this->convertOptionsToArray($options);
    }

    private function getTagsFilterOptions(): array
    {
        $where = $this->filterTypeReportWhere($this->filters['type_report']);

        $options = $this->getTags($where);

        return $this->convertOptionsToArray($options);
    }

    private function getMenuFilterOptions(): array
    {
        $where = $this->filterTypeReportWhere($this->filters['type_report']);

        $options = $this->getMenu($where);

        return $this->convertOptionsToArray($options);
    }

    private function getConversationTopicsFilterOptions(): array
    {
        $where = $this->filterTypeReportWhere($this->filters['type_report']);

        $options = $this->getConversationTopic($where);

        return $this->convertOptionsToArray($options);
    }
}
