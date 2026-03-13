<?php

use api\handlers\AddUserPaymentHandler;
use App\Service\FileStorageService;
use App\Enums\PaymentRejectReasonEnum;

require_once 'View.php';

class PaymentsRsReportView extends View
{
    private const PAGE_CAPACITY = 100;
    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;
    private array $filters = [];
    private string $filtersWhere = '';
    private string $orderBy;
    private string $dateFrom;
    private string $dateTo;
    private FileStorageService $fileStorageService;

    private static array $columns = [
        ['key' => 'client', 'label' => 'Клиент', 'sort_key' => 'client'],
        ['key' => 'user_id', 'label' => 'Номер клиента', 'sort_key' => 'user_id'],
        ['key' => 'contract_number', 'label' => 'Номер договора', 'sort_key' => 'contract_number'],
        ['key' => 'file', 'label' => 'Файл'],
        ['key' => 'status', 'label' => 'Статус', 'sort_key' => 'status'],
        ['key' => 'created_at', 'label' => 'Дата', 'sort_key' => 'created_at'],
        ['key' => 'source', 'label' => 'Источник'],
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

        $this->fileStorageService = new FileStorageService(
            $this->config->PAYMENTS_RS_STORAGE['endpoint'],
            $this->config->PAYMENTS_RS_STORAGE['region'],
            $this->config->PAYMENTS_RS_STORAGE['key'],
            $this->config->PAYMENTS_RS_STORAGE['secret'],
            $this->config->PAYMENTS_RS_STORAGE['bucket']
        );

        $this->handleAction();
    }

    private function handleAction(): void
    {
        // Обработка других действий
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
            'items' => $items,
            'current_page_num' => $this->currentPage,
            'total_pages_num' => $this->pagesNum,
            'total_items' => $this->totalItems,
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
            'can_see_client_url' => in_array('clients', $this->manager->permissions),
            'date_from' => date('d.m.Y', strtotime($this->dateFrom)),
            'date_to' => date('d.m.Y', strtotime($this->dateTo)),
            'user_filter' => $this->filters['user'] ?? '',
            'status_filter' => $this->filters['status'] ?? '',
            'filterConfigurations' => $this->getFilterConfiguration(),
            'rejectReasonOptions' => PaymentRejectReasonEnum::getOptionsForSelect(),
        ]);

        return $this->design->fetch('payments_rs_report.tpl');
    }

    private function prepareReportRows(array $items, array $columns): array
    {
        $rows = [];
        foreach ($items as $item) {
            $row = [];

            foreach ($columns as $col) {
                $row[$col['key']] = $this->getColumnValue($item, $col['key']);
            }
            $row['id'] = $this->getColumnValue($item, 'id');
            $row['name'] = $this->getColumnValue($item, 'name');
            $row['firstname'] = $this->getColumnValue($item, 'firstname');
            $row['lastname'] = $this->getColumnValue($item, 'lastname');
            $row['patronymic'] = $this->getColumnValue($item, 'patronymic');

            $rows[] = $row;
        }
        return $rows;
    }

    private function getColumnValue(object $item, string $key): string
    {
        switch ($key) {
            case 'id':
                return $item->id ?? '';
            case 'client':
                return trim("$item->lastname $item->firstname $item->patronymic") ?? '';
            case 'user_id':
                return $item->user_id ?? '';
            case 'contract_number':
                return $item->contract_number ?? '';
            case 'name':
                return $item->name ?? '';
            case 'firstname':
                return $item->firstname ?? '';
            case 'lastname':
                return $item->lastname ?? '';
            case 'patronymic':
                return $item->patronymic ?? '';
            case 'file':
                if (!empty($item->attachment)) {
                    return $this->fileStorageService->getViewUrl($item->attachment);
                }
                return  '';
            case 'status':
                return $item->status ?? '';
            case 'created_at':
                return date('d.m.Y H:i:s', strtotime($item->created_at));
            case 'source':
                return $this->detectSource($item->utm_term ?? null);
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
        $this->dateFrom = date('Y-m-d 00:00:00', strtotime($from));
        $this->dateTo = date('Y-m-d 23:59:59', strtotime($to));
    }

    private function getResults(int $currentPage, string $where = '', string $orderBy = '')
    {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);

        $query = "
            SELECT 
                u.firstname,
                u.lastname,
                u.patronymic,
                u.id AS user_id,
                c.number AS contract_number,
                prs.id,
                prs.order_id,
                prs.name,
                prs.attachment,
                prs.status,
                prs.created_at,
                o.utm_term
            FROM __payments_rs prs
                LEFT JOIN __users u ON u.id = prs.user_id
                LEFT JOIN __contracts c ON c.id = prs.contract_id
                LEFT JOIN __orders o ON o.id = prs.order_id
                WHERE 1
        ";
        
        if (!empty($where)) {
            $query .= " AND $where";
        }
        
        if (!empty($orderBy)) {
            $query .= " ORDER BY $orderBy";
        } else {
            $query .= " ORDER BY prs.created_at DESC";
        }
        
        $query .= " LIMIT $offset, " . self::PAGE_CAPACITY;
        
        $this->db->query($query);
        return $this->db->results();
    }

    private function getTotals(string $where = ''): int
    {
        $query = "
            SELECT 
                COUNT(*) AS total
            FROM __payments_rs prs
                LEFT JOIN __users u ON u.id = prs.user_id
                LEFT JOIN __contracts c ON c.id = prs.contract_id
                WHERE 1
        ";
        
        if (!empty($where)) {
            $query .= " AND $where";
        }
        
        $this->db->query($query);
        $result = $this->db->result();
        
        return $result->total ?? 0;
    }

    private function setFilters(): array
    {
        $filters = [];
        
        // Фильтр по пользователю
        $filters['user'] = $this->request->get('user');
        
        // Фильтр по статусу
        $filters['status'] = $this->request->get('status');
        
        return $filters;
    }

    private function setFiltersWhere(array $filters): string
    {
        $where = [];
        
        // Фильтр по дате
        $where[] = "prs.created_at >= '$this->dateFrom' AND prs.created_at <= '$this->dateTo'";
        
        // Фильтр по пользователю
        if (!empty($filters['user'])) {
            $where[] = "prs.user_id = " . (int)$filters['user'];
        }
        
        // Фильтр по статусу
        if (!empty($filters['status'])) {
            $where[] = "prs.status = '" . $this->db->escape($filters['status']) . "'";
        }
        
        return implode(' AND ', $where);
    }

    private function setOrderBy(): string
    {
        $sortField = $this->request->get('sort');
        $sortOrder = $this->request->get('order', 'string');
        
        if (empty($sortField)) {
            return 'prs.created_at DESC';
        }
        
        $sortOrder = ($sortOrder == 'asc') ? 'ASC' : 'DESC';
        
        $availableSortFields = [
            'user_id' => 'prs.user_id',
            'contract_number' => 'c.contract_number',
            'status' => 'prs.status',
            'created_at' => 'prs.created_at'
        ];
        
        if (isset($availableSortFields[$sortField])) {
            return $availableSortFields[$sortField] . ' ' . $sortOrder;
        }
        
        return 'prs.created_at DESC';
    }

    private function getFilterConfiguration(): array
    {
        // Получаем список статусов для фильтра
        $statuses = [
            'new' => 'Новый',
            'approved' => 'Одобрен',
            'cancelled' => 'Отклонен'
        ];
        
        return [
            [
                'name' => 'user',
                'label' => 'Клиент',
                'type' => 'text',
                'value' => $this->filters['user'] ?? ''
            ],
            [
                'name' => 'status',
                'label' => 'Статус',
                'type' => 'select',
                'options' => $statuses,
                'value' => $this->filters['status'] ?? ''
            ]
        ];
    }
    
    // Метод для получения данных порциями
    private function getChunkedResults(string $where = '', string $orderBy = '', int $chunkSize = 100): Generator
    {
        $offset = 0;
        while (true) {
            $query = "
                SELECT 
                    u.firstname,
                    u.lastname,
                    u.patronymic,
                    u.id AS user_id,
                    c.number AS contract_number,
                    prs.id,
                    prs.order_id,
                    prs.name,
                    prs.attachment,
                    prs.status,
                    prs.created_at,
                    o.utm_term
                FROM __payments_rs prs
                    LEFT JOIN __users u ON u.id = prs.user_id
                    LEFT JOIN __contracts c ON c.id = prs.contract_id
                    LEFT JOIN __orders o ON o.id = prs.order_id
                    WHERE 1
            ";
            
            if (!empty($where)) {
                $query .= " AND $where";
            }
            
            if (!empty($orderBy)) {
                $query .= " ORDER BY $orderBy";
            } else {
                $query .= " ORDER BY prs.created_at DESC";
            }
            
            $query .= " LIMIT $chunkSize OFFSET $offset";
            
            $this->db->query($query);
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
    
    // Метод для выгрузки отчета в Excel
    public function download(): void
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
        
        // Определяем заголовки
        $headers = [
            'Клиент' => 'string',
            'Номер клиента' => 'string',
            'Номер договора' => 'string',
            'Файл' => 'string',
            'Статус' => 'string',
            'Дата' => 'string',
            'Источник' => 'string',
        ];

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $headers);


        $userUrl = $this->config->back_url . "/client/";
        $orderUrl = $this->config->back_url . "/order/";
        
        // Обрабатываем данные порциями
        foreach ($this->getChunkedResults($this->filtersWhere, $this->orderBy) as $item) {
            $userFullName = trim("$item->lastname $item->firstname $item->patronymic") ?? '';
            
            $status = '';
            switch ($item->status) {
                case 'new':
                    $status = 'Новый';
                    break;
                case 'approved':
                    $status = 'Одобрен';
                    break;
                case 'cancelled':
                    $status = 'Отклонен';
                    break;
            }

            $fileHyperlink = '';
            if (!empty($item->attachment)) {
                $fileLink = $this->fileStorageService->getViewUrl($item->attachment);
                $fileHyperlink = '=HYPERLINK("' . $fileLink . '", "' . $item->name . '")';
            }

            $userHyperlink = '=HYPERLINK("' . $userUrl . $item->user_id . '", "' . $userFullName . '")';
            $orderHyperlink = '=HYPERLINK("' . $orderUrl . $item->order_id . '", "' . $item->contract_number . '")';

            $source = $this->detectSource($item->utm_term ?? null);

            $writer->writeSheetRow('Отчёт', [
                $userHyperlink,
                $item->user_id,
                $orderHyperlink,
                $fileHyperlink,
                $status,
                date('d.m.Y H:i', strtotime($item->created_at)),
                $source,
            ]);
        }

        $filename = 'payments_rs_report_' . date('Y-m-d') . '.xlsx';

        // Отправка файла для загрузки
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }
    
    /**
     * Обработка AJAX-запроса для обновления статуса платежа
     */
    public function updateStatus(): void
    {
        if (!$this->manager->id) {
            $this->response->json_output(['status' => 'error', 'message' => 'Доступ запрещен']);
            return;
        }
        
        $id = (int)$this->request->get('id');
        $status = $this->request->get('status');
        $reason = $this->request->get('reason');
        
        if (empty($id) || empty($status)) {
            $this->response->json_output(['status' => 'error', 'message' => 'Недостаточно параметров']);
            return;
        }
        
        if (!in_array($status, ['approved', 'cancelled'])) {
            $this->response->json_output(['status' => 'error', 'message' => 'Недопустимый статус']);
            return;
        }

        if ($status === 'cancelled' && empty($reason)) {
            $this->response->json_output(['status' => 'error', 'message' => 'Необходимо указать причину отклонения']);
            return;
        }

        if ($status === 'cancelled' && !in_array($reason, PaymentRejectReasonEnum::toArray())) {
            $this->response->json_output(['status' => 'error', 'message' => 'Недопустимая причина отклонения']);
            return;
        }

        $this->db->query("SELECT id FROM __payments_rs WHERE id = ?", $id);
        if (!$this->db->result()) {
            $this->response->json_output(['status' => 'error', 'message' => 'Запись не найдена']);
            return;
        }

        $rejectReason = ($status === 'cancelled') ? $reason : '';

        $this->db->query(
            "UPDATE __payments_rs SET status = ?, reject_reason = ?, updated_at = NOW() WHERE id = ?",
            $status,
            $rejectReason,
            $id
        );

        $this->db->query("SELECT order_id, user_id, created_at, attachment, name, contract_id FROM __payments_rs WHERE id = ?", $id);
        $result = $this->db->result();

        if ($status === 'approved') {
            (new AddUserPaymentHandler)->handle(
                $result->order_id,
                $result->user_id,
                $result->created_at,
                $this->manager,
                $result->attachment
            );
        }
        
        $this->response->json_output(['status' => 'success']);
    }

    /**
     * @param string|null $utmTerm
     * @return string
     */
    private function detectSource(?string $utmTerm): string
    {
        if (empty($utmTerm)) {
            return 'Сайт';
        }

        $term = mb_strtolower($utmTerm);

        if (mb_strpos($term, 'app_android') !== false) {
            return 'Android';
        }

        if (mb_strpos($term, 'app_ios') !== false) {
            return 'iOS';
        }

        return 'Сайт';
    }
}