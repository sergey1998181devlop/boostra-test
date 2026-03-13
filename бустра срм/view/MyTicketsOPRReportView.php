<?php

use api\MyTicketsReportTemplates;

require_once 'View.php';

class MyTicketsOPRReportView extends View
{
    private const PAGE_CAPACITY = 15;
    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;
    private array $filters = [];
    private string $filtersWhere = '';
    private string $orderBy;
    private MyTicketsReportTemplates $templates;

    private static array $columns = [
        ['key' => 'id', 'label' => 'Номер жалобы', 'sort_key' => 'id'],
        ['key' => 'created_at', 'label' => 'Дата создания', 'sort_key' => 'created_at'],
        ['key' => 'closed_at', 'label' => 'Дата закрытия', 'sort_key' => 'closed_at'],
        ['key' => 'working_time', 'label' => 'Время решения'],
        ['key' => 'company_name', 'label' => 'Компания', 'sort_key' => 'company_name'],
        ['key' => 'client_name', 'label' => 'Клиент', 'sort_key' => 'client_name'],
        ['key' => 'order_id', 'label' => 'Номер заявки', 'sort_key' => 'order_id'],
        ['key' => 'subject_name', 'label' => 'Тема', 'sort_key' => 'subject_name'],
        ['key' => 'parent_subject_name', 'label' => 'Тип обращения', 'sort_key' => 'parent_subject_name'],
        ['key' => 'description', 'label' => 'Описание'],
        ['key' => 'status_name', 'label' => 'Статус', 'sort_key' => 'status_name'],
        ['key' => 'priority_name', 'label' => 'Приоритет', 'sort_key' => 'priority_name'],
        ['key' => 'manager_name', 'label' => 'Менеджер', 'sort_key' => 'manager_name'],
        ['key' => 'initiator_name', 'label' => 'Инициатор', 'sort_key' => 'initiator_name'],
        ['key' => 'channel_name', 'label' => 'Канал поступления жалобы', 'sort_key' => 'channel_name'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->templates = new MyTicketsReportTemplates();
        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);

        $this->filters = $this->setFilters();
        $this->filtersWhere = $this->setFiltersWhere($this->filters);
        $this->orderBy = $this->setOrderBy();

        $this->totalItems = $this->getTotals($this->filtersWhere);
        $this->pagesNum = (int)ceil($this->totalItems / self::PAGE_CAPACITY);

        $this->handleAction();
    }

    private function handleAction(): void
    {
        $action = $this->request->get('action') ?: $this->request->post('action');
        if ($action && method_exists($this, $action)) {
            $this->$action();
        }
    }

    public function fetch(): string
    {
        $items = $this->getResults($this->currentPage, $this->filtersWhere, $this->orderBy);

        $items = is_array($items) ? $items : [];

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
            'ticket_id_filter' => $this->filters['ticket_id'],
            'created_at_filter' => $this->filters['created_at'],
            'closed_at_filter' => $this->filters['closed_at'],
            'company_filter' => $this->filters['company'],
            'client_filter' => $this->filters['client'],
            'subject_filter' => $this->filters['subject'],
            'parent_subject_filter' => $this->filters['parent_subject'],
            'status_filter' => $this->filters['status'],
            'priority_filter' => $this->filters['priority'],
            'manager_filter' => $this->filters['manager'],
            'initiator_filter' => $this->filters['initiator'],
            'channel_filter' => $this->filters['channel'],
            'filterConfigurations' => $this->getFilterConfiguration(),
            'templates' => $this->getTemplates(),
            'availableFields' => $this->getAvailableFields(),
            'templateFilterConfigurations' => $this->getTemplateFilterConfiguration(),
            'template_id' => $this->request->get('template_id', 'integer') ?: null,
        ]);

        return $this->design->fetch('my_tickets_opr_report.tpl');
    }

    private function prepareReportRows(array $items, array $columns): array
    {
        $rows = [];
        foreach ($items as $item) {
            $row = [];
            foreach ($columns as $col) {
                $row[$col['key']] = $this->getColumnValue($item, $col['key']);
            }
            $row['client_id'] = $item->client_id;
            $rows[] = $row;
        }
        return $rows;
    }

    private function getColumnValue(object $item, string $key): string
    {
        switch ($key) {
            case 'id':
                return $item->id ?? '';
            case 'created_at':
                return date('d.m.Y H:i', strtotime($item->created_at)) ?? '';
            case 'closed_at':
                return $item->closed_at ? date('d.m.Y H:i', strtotime($item->closed_at)) : '';
            case 'working_time':
                $seconds = (int)$item->working_time;
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);

                return sprintf('%02d', $hours) . ':' . sprintf('%02d', $minutes);
            case 'company_name':
                return $item->company_name ?? '';
            case 'client_name':
                return $item->client_name ?? '';
            case 'order_id':
                return $item->order_id ?? '';
            case 'subject_name':
                return $item->subject_name ?? '';
            case 'parent_subject_name':
                return $item->parent_subject_name ?? '';
            case 'description':
                return $item->description ?? '';
            case 'status_name':
                return $item->status_name ?? '';
            case 'priority_name':
                return $item->priority_name ?? '';
            case 'manager_name':
                return $item->manager_name ?? '';
            case 'initiator_name':
                return $item->initiator_name ?? '';
            case 'channel_name':
                return $item->channel_name ?? '';
            default:
                return '';
        }
    }

    private function getResults(int $currentPage, string $where = '', string $orderBy = '')
    {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);

        $this->db->query("
            SELECT 
                tickets.id,
                tickets.created_at,
                tickets.closed_at,
                tickets.working_time,
                organizations.short_name as company_name,
                user.id AS client_id,
                TRIM(CONCAT(user.lastname, ' ', user.firstname, ' ', user.patronymic)) as client_name,
                tickets.order_id,
                ticket_subjects.name as subject_name,
                ticket_parent_subject.name AS parent_subject_name,
                tickets.description,
                ticket_statuses.name as status_name,
                ticket_priority.name as priority_name,
                manager.name_1c as manager_name,
                initiator.name_1c as initiator_name,
                ticket_channels.name as channel_name
            FROM s_mytickets tickets 
            LEFT JOIN s_mytickets_subjects ticket_subjects ON tickets.subject_id = ticket_subjects.id
            LEFT JOIN s_mytickets_subjects ticket_parent_subject ON ticket_parent_subject.id = ticket_subjects.parent_id
            LEFT JOIN s_mytickets_channels ticket_channels ON tickets.chanel_id = ticket_channels.id
            LEFT JOIN s_mytickets_statuses ticket_statuses ON tickets.status_id = ticket_statuses.id
            LEFT JOIN s_mytickets_priority ticket_priority ON tickets.priority_id = ticket_priority.id
            LEFT JOIN s_organizations organizations ON tickets.company_id = organizations.id
            LEFT JOIN s_users user ON tickets.client_id = user.id 
            LEFT JOIN s_managers manager ON tickets.manager_id = manager.id 
            LEFT JOIN s_managers initiator ON tickets.initiator_id = initiator.id
            WHERE 1=1 " . $where . "
            ORDER BY " . $orderBy . "
            LIMIT ? OFFSET ?",
            self::PAGE_CAPACITY, $offset
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
                tickets.id,
                tickets.created_at,
                tickets.closed_at,
                tickets.working_time,
                organizations.short_name as company_name,
                user.id AS client_id,
                TRIM(CONCAT(user.lastname, ' ', user.firstname, ' ', user.patronymic)) as client_name,
                tickets.order_id,
                ticket_subjects.name as subject_name,
                ticket_parent_subject.name AS parent_subject_name,
                tickets.description,
                ticket_statuses.name as status_name,
                ticket_priority.name as priority_name,
                manager.name_1c as manager_name,
                initiator.name_1c as initiator_name,
                ticket_channels.name as channel_name
            FROM s_mytickets tickets 
            LEFT JOIN s_mytickets_subjects ticket_subjects ON tickets.subject_id = ticket_subjects.id
            LEFT JOIN s_mytickets_subjects ticket_parent_subject ON ticket_parent_subject.id = ticket_subjects.parent_id
            LEFT JOIN s_mytickets_channels ticket_channels ON tickets.chanel_id = ticket_channels.id
            LEFT JOIN s_mytickets_statuses ticket_statuses ON tickets.status_id = ticket_statuses.id
            LEFT JOIN s_mytickets_priority ticket_priority ON tickets.priority_id = ticket_priority.id
            LEFT JOIN s_organizations organizations ON tickets.company_id = organizations.id
            LEFT JOIN s_users user ON tickets.client_id = user.id 
            LEFT JOIN s_managers manager ON tickets.manager_id = manager.id 
            LEFT JOIN s_managers initiator ON tickets.initiator_id = initiator.id
            WHERE 1=1 " . $where . "
            ORDER BY " . $orderBy,
        );
        return $this->db->results();
    }

    private function getChunkedResults(string $where = '', string $orderBy = '', int $chunkSize = 100): Generator
    {
        $offset = 0;
        while (true) {
            $this->db->query("
            SELECT 
                tickets.id,
                tickets.created_at,
                tickets.closed_at,
                tickets.working_time,
                organizations.short_name as company_name,
                TRIM(CONCAT(user.lastname, ' ', user.firstname, ' ', user.patronymic)) as client_name,
                user.id AS client_id,
                tickets.order_id,
                ticket_subjects.name as subject_name,
                ticket_parent_subject.name AS parent_subject_name,
                tickets.description,
                ticket_statuses.name as status_name,
                ticket_priority.name as priority_name,
                manager.name_1c as manager_name,
                initiator.name_1c as initiator_name,
                ticket_channels.name as channel_name
            FROM s_mytickets tickets 
            LEFT JOIN s_mytickets_subjects ticket_subjects ON tickets.subject_id = ticket_subjects.id
            LEFT JOIN s_mytickets_subjects ticket_parent_subject ON ticket_parent_subject.id = ticket_subjects.parent_id
            LEFT JOIN s_mytickets_channels ticket_channels ON tickets.chanel_id = ticket_channels.id
            LEFT JOIN s_mytickets_statuses ticket_statuses ON tickets.status_id = ticket_statuses.id
            LEFT JOIN s_mytickets_priority ticket_priority ON tickets.priority_id = ticket_priority.id
            LEFT JOIN s_organizations organizations ON tickets.company_id = organizations.id
            LEFT JOIN s_users user ON tickets.client_id = user.id 
            LEFT JOIN s_managers manager ON tickets.manager_id = manager.id 
            LEFT JOIN s_managers initiator ON tickets.initiator_id = initiator.id
            WHERE 1=1 " . $where . "
            ORDER BY " . $orderBy . "
            LIMIT ? OFFSET ?",
                $chunkSize, $offset
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

    private function getChunkedResultsForExport(array $selectedFields, string $where = '', string $orderBy = '', int $chunkSize = 100): Generator
    {
        // Маппинг полей к SQL выражениям
        $fieldMapping = [
            'id' => 'tickets.id',
            'created_at' => 'tickets.created_at',
            'closed_at' => 'tickets.closed_at',
            'working_time' => 'tickets.working_time',
            'company_name' => 'organizations.name as company_name',
            'client_name' => 'TRIM(CONCAT(user.lastname, \' \', user.firstname, \' \', user.patronymic)) as client_name',
            'order_id' => 'tickets.order_id',
            'subject_name' => 'ticket_subjects.name as subject_name',
            'parent_subject_name' => 'ticket_parent_subject.name AS parent_subject_name',
            'description' => 'tickets.description',
            'status_name' => 'ticket_statuses.name as status_name',
            'priority_name' => 'ticket_priority.name as priority_name',
            'manager_name' => 'manager.name_1c as manager_name',
            'initiator_name' => 'initiator.name_1c as initiator_name',
            'channel_name' => 'ticket_channels.name as channel_name'
        ];
        
        // Формируем SELECT только для выбранных полей
        $selectFields = [];
        foreach ($selectedFields as $field) {
            if (isset($fieldMapping[$field])) {
                $selectFields[] = $fieldMapping[$field];
            }
        }
        
        // Если нет выбранных полей, выбираем все
        if (empty($selectFields)) {
            $selectFields = array_values($fieldMapping);
        }
        
        $selectClause = implode(', ', $selectFields);
        
        $offset = 0;
        while (true) {
            $this->db->query("
            SELECT 
                $selectClause
            FROM s_mytickets tickets 
            LEFT JOIN s_mytickets_subjects ticket_subjects ON tickets.subject_id = ticket_subjects.id
            LEFT JOIN s_mytickets_subjects ticket_parent_subject ON ticket_parent_subject.id = ticket_subjects.parent_id
            LEFT JOIN s_mytickets_channels ticket_channels ON tickets.chanel_id = ticket_channels.id
            LEFT JOIN s_mytickets_statuses ticket_statuses ON tickets.status_id = ticket_statuses.id
            LEFT JOIN s_mytickets_priority ticket_priority ON tickets.priority_id = ticket_priority.id
            LEFT JOIN s_organizations organizations ON tickets.company_id = organizations.id
            LEFT JOIN s_users user ON tickets.client_id = user.id 
            LEFT JOIN s_managers manager ON tickets.manager_id = manager.id 
            LEFT JOIN s_managers initiator ON tickets.initiator_id = initiator.id
            WHERE 1=1 " . $where . "
            ORDER BY " . $orderBy . "
            LIMIT ? OFFSET ?",
                $chunkSize, $offset
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
            SELECT COUNT(tickets.id) AS total
            FROM s_mytickets tickets 
            LEFT JOIN s_mytickets_subjects ticket_subjects ON tickets.subject_id = ticket_subjects.id
            LEFT JOIN s_mytickets_subjects ticket_parent_subject ON ticket_parent_subject.id = ticket_subjects.parent_id
            LEFT JOIN s_mytickets_channels ticket_channels ON tickets.chanel_id = ticket_channels.id
            LEFT JOIN s_mytickets_statuses ticket_statuses ON tickets.status_id = ticket_statuses.id
            LEFT JOIN s_mytickets_priority ticket_priority ON tickets.priority_id = ticket_priority.id
            LEFT JOIN s_organizations organizations ON tickets.company_id = organizations.id
            LEFT JOIN s_users user ON tickets.client_id = user.id 
            LEFT JOIN s_managers manager ON tickets.manager_id = manager.id 
            LEFT JOIN s_managers initiator ON tickets.initiator_id = initiator.id
            WHERE 1=1 " . $where,
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

        // Получаем поля для экспорта из параметров запроса (из шаблона)
        $selectedFields = $this->request->get('fields', 'array');
        $templateId = $this->request->get('template_id', 'integer');
        
        // Переменные для фильтров и проверки периода
        $filtersToUse = $this->filters;
        $filtersWhereToUse = $this->filtersWhere;
        
        // Если указан шаблон, загружаем его настройки
        if ($templateId) {
            $template = $this->templates->getTemplate($templateId);
            if ($template) {
                $templateData = json_decode($template->data, true);
                
                // Применяем поля из шаблона
                if (isset($templateData['fields']) && is_array($templateData['fields'])) {
                    $selectedFields = $templateData['fields'];
                }
                
                // Применяем фильтры из шаблона
                if (isset($templateData['filters']) && is_array($templateData['filters'])) {
                    $templateFilters = $templateData['filters'];
                    
                    // Объединяем фильтры: приоритет у шаблона, но сохраняем дефолтные значения
                    $filtersToUse = array_merge($this->filters, $templateFilters);
                    
                    // Пересчитываем WHERE условия на основе фильтров шаблона
                    $filtersWhereToUse = $this->setFiltersWhere($filtersToUse);
                }
            }
        }
        
        // Если поля не выбраны, используем все доступные
        if (empty($selectedFields)) {
            $selectedFields = array_column(self::$columns, 'key');
        }
        
        // Проверяем лимит периода по фильтру created_at (он всегда должен быть установлен)
        if (empty($filtersToUse['created_at'])) {
            $this->json_output(['status' => 'error', 'message' => 'Не указан период для экспорта данных.']);
            return;
        }

        $dateRange = $filtersToUse['created_at'];
        if (strpos($dateRange, ' - ') !== false) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $startDate = DateTime::createFromFormat('d.m.Y', trim($dates[0]));
                $endDate = DateTime::createFromFormat('d.m.Y', trim($dates[1]));
                if ($startDate && $endDate) {
                    $diffInDays = ($endDate->getTimestamp() - $startDate->getTimestamp()) / (60 * 60 * 24);
                    
                    // Проверка, что выбранный диапазон не превышает 1 год
                    if ($diffInDays > $maxPeriod) {
                        $this->json_output(['status' => 'error', 'message' => 'Выбранный период превышает допустимый лимит в 1 год.']);
                        return;
                    }
                } else {
                    $this->json_output(['status' => 'error', 'message' => 'Некорректный формат даты в фильтре.']);
                    return;
                }
            } else {
                $this->json_output(['status' => 'error', 'message' => 'Некорректный формат периода в фильтре.']);
                return;
            }
        } else {
            $this->json_output(['status' => 'error', 'message' => 'Некорректный формат периода в фильтре.']);
            return;
        }

        // Формируем заголовки только для выбранных полей
        $selectedColumns = array_filter(self::$columns, function($col) use ($selectedFields) {
            return in_array($col['key'], $selectedFields);
        });

        $header = [];
        foreach ($selectedColumns as $col) {
            $header[$col['label']] = $col['type'] ?? 'string';
        }

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $header);

        foreach ($this->getChunkedResultsForExport($selectedFields, $filtersWhereToUse, $this->orderBy) as $item) {
            $row = [];
            foreach ($selectedColumns as $col) {
                $row[] = $this->getColumnValue($item, $col['key']);
            }
            $writer->writeSheetRow('Отчёт', $row);
        }

        $filename = 'my_tickets_report';
        if ($templateId && isset($template)) {
            $cleanName = $template->name;
            $cleanName = preg_replace('/\s+/', '_', $cleanName);
            $cleanName = preg_replace('/[^a-zA-Z0-9а-яА-Я_\-]/u', '', $cleanName);
            $cleanName = preg_replace('/_+/', '_', $cleanName);
            $cleanName = trim($cleanName, '_');
            
            if (!empty($cleanName)) {
                $filename .= '_' . $cleanName;
            }
        }
        $filename .= '_' . date('Y-m-d') . '.xlsx';

        // Отправка файла для загрузки
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    private function setFilters(): array
    {
        // Устанавливаем фильтр по дате создания по умолчанию на последний месяц
        $defaultCreatedAt = $this->request->get('created_at');
        if (empty($defaultCreatedAt)) {
            $defaultCreatedAt = date('d.m.Y', strtotime('-1 month')) . ' - ' . date('d.m.Y');
        }
        
        return [
            'ticket_id' => $this->request->get('ticket_id') ?: null,
            'created_at' => $defaultCreatedAt,
            'closed_at' => $this->request->get('closed_at') ?: null,
            'company' => (int)$this->request->get('company') ?: null,
            'client' => (int)$this->request->get('client') ?: null,
            'subject' => (int)$this->request->get('subject') ?: null,
            'parent_subject' => (int)$this->request->get('parent_subject') ?: null,
            'status' => (int)$this->request->get('status') ?: null,
            'priority' => (int)$this->request->get('priority') ?: null,
            'manager' => (int)$this->request->get('manager') ?: null,
            'initiator' => (int)$this->request->get('initiator') ?: null,
            'channel' => (int)$this->request->get('channel') ?: null,
        ];
    }

    private function setFiltersWhere(array $filters): string
    {
        $where = '';
        foreach ($filters as $key => $value) {
            if ($value) {
                switch ($key) {
                    case 'ticket_id':
                        $where .= $this->filterTicketIdWhere($value);
                        break;
                    case 'created_at':
                        $where .= $this->filterCreatedAtWhere($value);
                        break;
                    case 'closed_at':
                        $where .= $this->filterClosedAtWhere($value);
                        break;
                    case 'company':
                        $where .= $this->filterCompanyWhere($value);
                        break;
                    case 'client':
                        $where .= $this->filterClientWhere($value);
                        break;
                    case 'subject':
                        $where .= $this->filterSubjectWhere($value);
                        break;
                    case 'parent_subject':
                        $where .= $this->filterParentSubjectWhere($value);
                        break;
                    case 'status':
                        $where .= $this->filterStatusWhere($value);
                        break;
                    case 'priority':
                        $where .= $this->filterPriorityWhere($value);
                        break;
                    case 'manager':
                        $where .= $this->filterManagerWhere($value);
                        break;
                    case 'initiator':
                        $where .= $this->filterInitiatorWhere($value);
                        break;
                    case 'channel':
                        $where .= $this->filterChannelWhere($value);
                        break;
                    default:
                        break;
                }
            }
        }
        return $where;
    }

    private function filterTicketIdWhere(string $value): string
    {
        if (!empty($value)) {
            $ticketIdSearch = '%' . $this->db->escape($value) . '%';
            return " AND tickets.id LIKE '$ticketIdSearch'";
        }
        return '';
    }

    private function filterCreatedAtWhere(string $value): string
    {
        return $this->filterDateRangeWhere($value, 'tickets.created_at');
    }

    private function filterClosedAtWhere(string $value): string
    {
        return $this->filterDateRangeWhere($value, 'tickets.closed_at');
    }

    private function filterDateRangeWhere(string $value, string $column): string
    {
        if (empty($value)) {
            return '';
        }

        if (strpos($value, ' - ') !== false) {
            $dates = explode(' - ', $value);
            if (count($dates) === 2) {
                $startDate = DateTime::createFromFormat('d.m.Y', trim($dates[0]));
                $endDate = DateTime::createFromFormat('d.m.Y', trim($dates[1]));

                if ($startDate && $endDate) {
                    $startSql = $startDate->format('Y-m-d');
                    $endSql = $endDate->format('Y-m-d');
                    return " AND DATE($column) BETWEEN '$startSql' AND '$endSql'";
                }
            }
        }

        return '';
    }

    private function filterCompanyWhere(int $value): string
    {
        if ($value > 0) {
            return " AND tickets.company_id = " . $value;
        }
        return '';
    }

    private function filterClientWhere(int $value): string
    {
        if ($value > 0) {
            return " AND tickets.client_id = " . $value;
        }
        return '';
    }

    private function filterSubjectWhere(int $value): string
    {
        if ($value > 0) {
            return " AND tickets.subject_id = " . $value;
        }
        return '';
    }

    private function filterParentSubjectWhere(int $value): string
    {
        if ($value > 0) {
            return " AND ticket_parent_subject.id = " . $value;
        }
        return '';
    }

    private function filterStatusWhere(int $value): string
    {
        if ($value > 0) {
            return " AND tickets.status_id = " . $value;
        }
        return '';
    }

    private function filterPriorityWhere(int $value): string
    {
        if ($value > 0) {
            return " AND tickets.priority_id = " . $value;
        }
        return '';
    }

    private function filterManagerWhere(int $value): string
    {
        if ($value > 0) {
            return " AND tickets.manager_id = " . $value;
        }
        return '';
    }

    private function filterInitiatorWhere(int $value): string
    {
        if ($value > 0) {
            return " AND tickets.initiator_id = " . $value;
        }
        return '';
    }

    private function filterChannelWhere(int $value): string
    {
        if ($value > 0) {
            return " AND tickets.chanel_id = " . $value;
        }
        return '';
    }

    private function setOrderBy(): string
    {
        $orderBy = 'tickets.created_at DESC';

        $sort = $this->request->get('sort') ?? null;
        if ($sort) {
            $pos = strrpos($sort, '_');
            if ($pos !== false) {
                $field = substr($sort, 0, $pos);
                $direction = substr($sort, $pos + 1);
                $direction = (strtoupper($direction) === 'ASC') ? 'ASC' : 'DESC';

                switch ($field) {
                    case 'id':
                        $orderBy = "tickets.id $direction";
                        break;
                    case 'created_at':
                        $orderBy = "tickets.created_at $direction";
                        break;
                    case 'closed_at':
                        $orderBy = "tickets.closed_at $direction";
                        break;
                    case 'company_name':
                        $orderBy = "organizations.name $direction";
                        break;
                    case 'client_name':
                        $orderBy = "user.lastname $direction, user.firstname $direction, user.patronymic $direction";
                        break;
                    case 'order_id':
                        $orderBy = "tickets.order_id $direction";
                        break;
                    case 'subject_name':
                        $orderBy = "ticket_subjects.name $direction";
                        break;
                    case 'parent_subject_name':
                        $orderBy = "ticket_parent_subject.name $direction";
                        break;
                    case 'status_name':
                        $orderBy = "ticket_statuses.name $direction";
                        break;
                    case 'priority_name':
                        $orderBy = "ticket_priority.name $direction";
                        break;
                    case 'manager_name':
                        $orderBy = "manager.name_1c $direction";
                        break;
                    case 'initiator_name':
                        $orderBy = "initiator.name_1c $direction";
                        break;
                    case 'channel_name':
                        $orderBy = "ticket_channels.name $direction";
                        break;
                    case 'working_time':
                        $orderBy = "tickets.working_time $direction";
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
                'name' => 'ticket_id',
                'label' => 'Номер жалобы',
                'type' => 'text',
                'value' => $this->filters['ticket_id'] ?? '',
                'placeholder' => 'Введите номер жалобы'
            ],
            [
                'name' => 'created_at',
                'label' => 'Дата создания',
                'type' => 'daterange',
                'value' => $this->filters['created_at'] ?? '',
                'placeholder' => 'Выберите период'
            ],
            [
                'name' => 'closed_at',
                'label' => 'Дата закрытия',
                'type' => 'daterange',
                'value' => $this->filters['closed_at'] ?? '',
                'placeholder' => 'Выберите период'
            ],
            [
                'name' => 'client',
                'label' => 'Клиент',
                'type' => 'select',
                'value' => $this->filters['client'] ?? '',
                'options' => $this->getClientFilterOptions(),
                'option_value_field' => 'id',
                'option_label_field' => 'name',
            ],
            [
                'name' => 'company',
                'label' => 'Компания',
                'type' => 'select',
                'value' => $this->filters['company'] ?? '',
                'options' => $this->getCompanyFilterOptions(),
                'option_value_field' => 'id',
                'option_label_field' => 'name',
            ],
            [
                'name' => 'subject',
                'label' => 'Тема',
                'type' => 'select',
                'value' => $this->filters['subject'] ?? '',
                'options' => $this->getSubjectFilterOptions(),
                'option_value_field' => 'id',
                'option_label_field' => 'name',
            ],
            [
                'name' => 'parent_subject',
                'label' => 'Тип обращения',
                'type' => 'select',
                'value' => $this->filters['parent_subject'] ?? '',
                'options' => $this->getParentSubjectFilterOptions(),
                'option_value_field' => 'id',
                'option_label_field' => 'name',
            ],
            [
                'name' => 'status',
                'label' => 'Статус',
                'type' => 'select',
                'value' => $this->filters['status'] ?? '',
                'options' => $this->getStatusFilterOptions(),
                'option_value_field' => 'id',
                'option_label_field' => 'name',
            ],
            [
                'name' => 'priority',
                'label' => 'Приоритет',
                'type' => 'select',
                'value' => $this->filters['priority'] ?? '',
                'options' => $this->getPriorityFilterOptions(),
                'option_value_field' => 'id',
                'option_label_field' => 'name',
            ],
            [
                'name' => 'manager',
                'label' => 'Менеджер',
                'type' => 'select',
                'value' => $this->filters['manager'] ?? '',
                'options' => $this->getManagerFilterOptions(),
                'option_value_field' => 'id',
                'option_label_field' => 'name',
            ],
            [
                'name' => 'initiator',
                'label' => 'Инициатор',
                'type' => 'select',
                'value' => $this->filters['initiator'] ?? '',
                'options' => $this->getInitiatorFilterOptions(),
                'option_value_field' => 'id',
                'option_label_field' => 'name',
            ],
            [
                'name' => 'channel',
                'label' => 'Канал',
                'type' => 'select',
                'value' => $this->filters['channel'] ?? '',
                'options' => $this->getChannelFilterOptions(),
                'option_value_field' => 'id',
                'option_label_field' => 'name',
            ],
        ];
    }

    private function convertOptionsToArray(array $options): array
    {
        return array_map('get_object_vars', $options);
    }

    private function getClientFilterOptions(): array
    {
        $options = $this->getAllClients();

        return $this->convertOptionsToArray($options);
    }

    private function getCompanyFilterOptions(): array
    {
        $options = $this->getAllCompanies();

        return $this->convertOptionsToArray($options);
    }

    private function getSubjectFilterOptions(): array
    {
        $options = $this->getAllSubjects();

        return $this->convertOptionsToArray($options);
    }

    private function getParentSubjectFilterOptions(): array
    {
        $options = $this->getAllParentSubjects();

        return $this->convertOptionsToArray($options);
    }

    private function getStatusFilterOptions(): array
    {
        $options = $this->getAllStatuses();

        return $this->convertOptionsToArray($options);
    }

    private function getPriorityFilterOptions(): array
    {
        $options = $this->getAllPriorities();

        return $this->convertOptionsToArray($options);
    }

    private function getManagerFilterOptions(): array
    {
        $options = $this->getAllManagers();

        return $this->convertOptionsToArray($options);
    }

    private function getInitiatorFilterOptions(): array
    {
        $options = $this->getAllInitiators();

        return $this->convertOptionsToArray($options);
    }

    private function getChannelFilterOptions(): array
    {
        $options = $this->getAllChannels();

        return $this->convertOptionsToArray($options);
    }

    private function getAllClients()
    {
        $this->db->query("
            SELECT 
                user.id,
                TRIM(CONCAT(user.lastname, ' ', user.firstname, ' ', user.patronymic)) AS name
            FROM s_mytickets tickets 
            LEFT JOIN s_users user ON tickets.client_id = user.id
            WHERE user.id IS NOT NULL
            GROUP BY user.id, name
            ORDER BY name
        ");

        return $this->db->results();
    }

    private function getAllCompanies()
    {
        $this->db->query("
            SELECT 
                organizations.id,
                organizations.short_name
            FROM s_mytickets tickets
            LEFT JOIN s_organizations organizations ON tickets.company_id = organizations.id
            WHERE organizations.id IS NOT NULL
            GROUP BY organizations.id, organizations.short_name
            ORDER BY organizations.short_name
        ");

        return $this->db->results();
    }

    private function getAllSubjects()
    {
        $this->db->query("
            SELECT 
                ticket_subjects.id,
                ticket_subjects.name
            FROM s_mytickets tickets
            LEFT JOIN s_mytickets_subjects ticket_subjects ON tickets.subject_id = ticket_subjects.id
            WHERE ticket_subjects.id IS NOT NULL
            GROUP BY ticket_subjects.id, ticket_subjects.name
            ORDER BY ticket_subjects.name
        ");

        return $this->db->results();
    }

    private function getAllParentSubjects()
    {
        $this->db->query("
            SELECT 
                ticket_parent_subject.id,
                ticket_parent_subject.name
            FROM s_mytickets_subjects ticket_subjects
            LEFT JOIN s_mytickets_subjects ticket_parent_subject ON ticket_parent_subject.id = ticket_subjects.parent_id
            WHERE ticket_parent_subject.id IS NOT NULL
            GROUP BY ticket_parent_subject.id, ticket_parent_subject.name
            ORDER BY ticket_parent_subject.name
        ");

        return $this->db->results();
    }

    private function getAllStatuses()
    {
        $this->db->query("
            SELECT 
                ticket_statuses.id,
                ticket_statuses.name
            FROM s_mytickets tickets
            LEFT JOIN s_mytickets_statuses ticket_statuses ON tickets.status_id = ticket_statuses.id
            WHERE ticket_statuses.id IS NOT NULL
            GROUP BY ticket_statuses.id, ticket_statuses.name
            ORDER BY ticket_statuses.name
        ");

        return $this->db->results();
    }

    private function getAllPriorities()
    {
        $this->db->query("
            SELECT 
                ticket_priority.id,
                ticket_priority.name
            FROM s_mytickets tickets
            LEFT JOIN s_mytickets_priority ticket_priority ON tickets.priority_id = ticket_priority.id
            WHERE ticket_priority.id IS NOT NULL
            GROUP BY ticket_priority.id, ticket_priority.name
            ORDER BY ticket_priority.name
        ");

        return $this->db->results();
    }

    private function getAllManagers()
    {
        $this->db->query("
            SELECT 
                manager.id,
                manager.name_1c as name
            FROM s_mytickets tickets
            LEFT JOIN s_managers manager ON tickets.manager_id = manager.id
            WHERE manager.id IS NOT NULL
            GROUP BY manager.id, manager.name_1c
            ORDER BY manager.name_1c
        ");

        return $this->db->results();
    }

    private function getAllInitiators()
    {
        $this->db->query("
            SELECT 
                initiator.id,
                initiator.name_1c as name
            FROM s_mytickets tickets
            LEFT JOIN s_managers initiator ON tickets.initiator_id = initiator.id
            WHERE initiator.id IS NOT NULL
            GROUP BY initiator.id, initiator.name_1c
            ORDER BY initiator.name_1c
        ");

        return $this->db->results();
    }

    private function getAllChannels()
    {
        $this->db->query("
            SELECT 
                ticket_channels.id,
                ticket_channels.name
            FROM s_mytickets tickets
            LEFT JOIN s_mytickets_channels ticket_channels ON tickets.chanel_id = ticket_channels.id
            WHERE ticket_channels.id IS NOT NULL
            GROUP BY ticket_channels.id, ticket_channels.name
            ORDER BY ticket_channels.name
        ");

        return $this->db->results();
    }

    /**
     * Получить все доступные шаблоны
     */
    private function getTemplates(): array
    {
        return $this->templates->getTemplates();
    }

    /**
     * Создать новый шаблон
     */
    private function createTemplate(): void
    {
        if ($this->request->method('post')) {
            $name = trim($this->request->post('name', 'string'));
            $data = $this->request->post('data', 'array');

            if (empty($name)) {
                $this->json_output(['success' => false, 'message' => 'Название шаблона не может быть пустым']);
                return;
            }

            if (!$this->templates->isNameUnique($name)) {
                $this->json_output(['success' => false, 'message' => 'Шаблон с таким названием уже существует']);
                return;
            }

            $templateId = $this->templates->createTemplate($name, $data);
            
            if ($templateId) {
                $this->json_output(['success' => true, 'message' => 'Шаблон успешно создан', 'template_id' => $templateId]);
            } else {
                $this->json_output(['success' => false, 'message' => 'Ошибка при создании шаблона']);
            }
        }
    }

    /**
     * Обновить существующий шаблон
     */
    private function updateTemplate(): void
    {
        if ($this->request->method('post')) {
            $id = $this->request->post('id', 'integer');
            $name = trim($this->request->post('name', 'string'));
            $data = $this->request->post('data', 'array');

            if (empty($name)) {
                $this->json_output(['success' => false, 'message' => 'Название шаблона не может быть пустым']);
                return;
            }

            if (!$this->templates->isNameUnique($name, $id)) {
                $this->json_output(['success' => false, 'message' => 'Шаблон с таким названием уже существует']);
                return;
            }

            $success = $this->templates->updateTemplate($id, $name, $data);
            
            if ($success) {
                $this->json_output(['success' => true, 'message' => 'Шаблон успешно обновлен']);
            } else {
                $this->json_output(['success' => false, 'message' => 'Ошибка при обновлении шаблона']);
            }
        }
    }

    /**
     * Удалить шаблон
     */
    private function deleteTemplate(): void
    {
        if ($this->request->method('post')) {
            $id = $this->request->post('id', 'integer');
            
            $success = $this->templates->deleteTemplate($id);
            
            if ($success) {
                $this->json_output(['success' => true, 'message' => 'Шаблон успешно удален']);
            } else {
                $this->json_output(['success' => false, 'message' => 'Ошибка при удалении шаблона']);
            }
        }
    }

    /**
     * Получить данные шаблона
     */
    private function getTemplate(): void
    {
        $id = $this->request->get('id', 'integer');
        $template = $this->templates->getTemplate($id);
        
        if ($template) {
            $template->data = json_decode($template->data, true);
            $this->json_output(['success' => true, 'template' => $template]);
        } else {
            $this->json_output(['success' => false, 'message' => 'Шаблон не найден']);
        }
    }

    /**
     * Получить доступные поля для экспорта
     */
    private function getAvailableFields(): array
    {
        return array_map(function($column) {
            return [
                'key' => $column['key'],
                'label' => $column['label']
            ];
        }, self::$columns);
    }

    /**
     * Получить конфигурацию фильтров для шаблонов (все данные)
     */
    private function getTemplateFilterConfiguration(): array
    {
        return [
            'company' => [
                'type' => 'select',
                'options' => $this->convertOptionsToArray($this->getAllCompanies())
            ],
            'client' => [
                'type' => 'select',
                'options' => $this->convertOptionsToArray($this->getAllClients())
            ],
            'subject' => [
                'type' => 'select',
                'options' => $this->convertOptionsToArray($this->getAllSubjects())
            ],
            'parent_subject' => [
                'type' => 'select',
                'options' => $this->convertOptionsToArray($this->getAllParentSubjects())
            ],
            'status' => [
                'type' => 'select',
                'options' => $this->convertOptionsToArray($this->getAllStatuses())
            ],
            'priority' => [
                'type' => 'select',
                'options' => $this->convertOptionsToArray($this->getAllPriorities())
            ],
            'manager' => [
                'type' => 'select',
                'options' => $this->convertOptionsToArray($this->getAllManagers())
            ],
            'initiator' => [
                'type' => 'select',
                'options' => $this->convertOptionsToArray($this->getAllInitiators())
            ],
            'channel' => [
                'type' => 'select',
                'options' => $this->convertOptionsToArray($this->getAllChannels())
            ]
        ];
    }
}
