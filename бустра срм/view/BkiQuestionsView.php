<?php

use api\handlers\ProcessBkiResponseHandler;
use App\Service\FileStorageService;

require_once 'View.php';

class BkiQuestionsView extends View {
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
        ['key' => 'created_at', 'label' => 'Дата запроса', 'sort_key' => 'created_at']
    ];

    public function __construct() {
        parent::__construct();

        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $this->setupDateRange();

        $this->filters = $this->setFilters();
        $this->filtersWhere = $this->setFiltersWhere($this->filters);
        $this->orderBy = $this->setOrderBy();

        $this->totalItems = $this->getTotals($this->filtersWhere);
        $this->pagesNum = (int)ceil($this->totalItems / self::PAGE_CAPACITY);

        $this->fileStorageService = new FileStorageService(
            $this->config->BKI_STORAGE['endpoint'],
            $this->config->BKI_STORAGE['region'],
            $this->config->BKI_STORAGE['key'],
            $this->config->BKI_STORAGE['secret'],
            $this->config->BKI_STORAGE['bucket']
        );

        $this->handleAction();
    }

    private function handleAction(): void {
        $action = $this->request->get('action');
        if ($action && method_exists($this, $action)) {
            $this->$action();
        }
    }

    public function fetch(): string {
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
        ]);

        return $this->design->fetch('bki_questions_report.tpl');
    }

    private function prepareReportRows(array $items, array $columns): array {
        $rows = [];
        foreach ($items as $item) {
            $row = [];

            foreach ($columns as $col) {
                $row[$col['key']] = $this->getColumnValue($item, $col['key']);
            }
            $row['id'] = $this->getColumnValue($item, 'id');
            $row['order_id'] = $this->getColumnValue($item, 'order_id');
            $row['name'] = $this->getColumnValue($item, 'name');
            $row['description'] = $this->getColumnValue($item, 'description');
            $row['status'] = $this->getColumnValue($item, 'status');

            $rows[] = $row;
        }
        return $rows;
    }

    private function getColumnValue(object $item, string $key): string {
        switch ($key) {
            case 'id':
                return $item->id ?? '';
            case 'client':
                return trim("$item->lastname $item->firstname $item->patronymic") ?? '';
            case 'user_id':
                return $item->user_id ?? '';
            case 'order_id':
                return $item->order_id ?? '';
            case 'contract_number':
                return $item->contract_number ?? '';
            case 'name':
                return $item->name ?? '';
            case 'description':
                return $item->description ?? '';
            case 'file':
                if (!empty($item->request_file)) {
                    return $this->fileStorageService->getViewUrl($item->request_file);
                }
                return '';
            case 'status':
                return $this->getStatusLabel($item->status ?? '');
            case 'created_at':
                return date('d.m.Y H:i:s', strtotime($item->created_at));
            default:
                return '';
        }
    }

    private function getStatusLabel(string $status): string {
        $statuses = [
            'new',
            'approved',
            'cancelled'
        ];
        return $statuses[$status] ?? $status;
    }

    private function setupDateRange(): void {
        $daterange = $this->request->get('daterange');
        if (empty($daterange)) {
            $daterange = date('d.m.Y', strtotime('-1 month')) . ' - ' . date('d.m.Y');
        }

        [$from, $to] = explode(' - ', $daterange);
        $this->dateFrom = date('Y-m-d 00:00:00', strtotime($from));
        $this->dateTo = date('Y-m-d 23:59:59', strtotime($to));
    }

    private function getResults(int $currentPage, string $where = '', string $orderBy = '') {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);

        $query = "
            SELECT 
                u.firstname,
                u.lastname,
                u.patronymic,
                u.id AS user_id,
                c.number AS contract_number,
                bki.id,
                bki.order_id,
                bki.name,
                bki.attachment AS request_file,
                bki.status, 
                bki.description, 
                bki.created_at
            FROM __bki_questions bki
                LEFT JOIN __users u ON u.id = bki.user_id
                LEFT JOIN __contracts c ON c.id = bki.contract_id
                WHERE 1
        ";

        if (!empty($where)) {
            $query .= " AND $where";
        }

        if (!empty($orderBy)) {
            $query .= " ORDER BY $orderBy";
        } else {
            $query .= " ORDER BY bki.created_at DESC";
        }

        $query .= " LIMIT $offset, " . self::PAGE_CAPACITY;

        $this->db->query($query);
        return $this->db->results();
    }

    private function getTotals(string $where = ''): int {
        $query = "
            SELECT 
                COUNT(*) AS total
            FROM __bki_questions bki
                LEFT JOIN __users u ON u.id = bki.user_id
                LEFT JOIN __contracts c ON c.id = bki.contract_id
                WHERE 1
        ";

        if (!empty($where)) {
            $query .= " AND $where";
        }

        $this->db->query($query);
        $result = $this->db->result();

        return $result->total ?? 0;
    }

    private function setFilters(): array {
        $filters = [];

        $filters['user'] = $this->request->get('user');
        $filters['status'] = $this->request->get('status');
        $filters['contract'] = $this->request->get('contract');

        return $filters;
    }

    private function setFiltersWhere(array $filters): string {
        $where = [];

        $where[] = "bki.created_at >= '$this->dateFrom' AND bki.created_at <= '$this->dateTo'";

        if (!empty($filters['user'])) {
            $where[] = "bki.user_id = " . (int)$filters['user'];
        }

        if (!empty($filters['status'])) {
            $where[] = "bki.status = '" . $this->db->escape($filters['status']) . "'";
        }

        if (!empty($filters['contract'])) {
            $where[] = "c.number LIKE '%" . $this->db->escape($filters['contract']) . "%'";
        }

        return implode(' AND ', $where);
    }

    private function setOrderBy(): string {
        $sortField = $this->request->get('sort');
        $sortOrder = $this->request->get('order', 'string');

        if (empty($sortField)) {
            return 'bki.created_at DESC';
        }

        $sortOrder = ($sortOrder == 'asc') ? 'ASC' : 'DESC';

        $availableSortFields = [
            'user_id' => 'bki.user_id',
            'contract_number' => 'c.number',
            'status' => 'bki.status',
            'created_at' => 'bki.created_at',
            'file' => 'bki.attachment',
        ];

        if (isset($availableSortFields[$sortField])) {
            return $availableSortFields[$sortField] . ' ' . $sortOrder;
        }

        return 'bki.created_at DESC';
    }

    private function getFilterConfiguration(): array {
        $statuses = [
            'new' => 'Новый',
            'approved' => 'Одобрено',
            'cancelled' => 'Отклонено',
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
            ],
            [
                'name' => 'contract',
                'label' => 'Договор',
                'type' => 'text',
                'value' => $this->filters['contract'] ?? ''
            ]
        ];
    }

    private function getChunkedResults(string $where = '', string $orderBy = '', int $chunkSize = 100): Generator {
        $offset = 0;
        while (true) {
            $query = "
                SELECT 
                    u.firstname,
                    u.lastname,
                    u.patronymic,
                    u.id AS user_id,
                    c.number AS contract_number,
                    bki.id,
                    bki.order_id,
                    bki.name,
                    bki.attachment AS request_file,
                    bki.status,
                    bki.description, 
                    bki.created_at
                FROM __bki_questions bki
                    LEFT JOIN __users u ON u.id = bki.user_id
                    LEFT JOIN __contracts c ON c.id = bki.contract_id
                    WHERE 1
            ";

            if (!empty($where)) {
                $query .= " AND $where";
            }

            if (!empty($orderBy)) {
                $query .= " ORDER BY $orderBy";
            } else {
                $query .= " ORDER BY bki.created_at DESC";
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

    public function download(): void {
        $maxPeriod = 365;

        $dateFromTimestamp = strtotime($this->dateFrom);
        $dateToTimestamp = strtotime($this->dateTo);
        $diffInDays = ($dateToTimestamp - $dateFromTimestamp) / (60 * 60 * 24);

        if ($diffInDays > $maxPeriod) {
            $this->json_output(['status' => 'error', 'message' => 'Выбранный период превышает допустимый лимит в 1 год.']);
            return;
        }

        $headers = [
            'Клиент' => 'string',
            'Номер клиента' => 'string',
            'Номер договора' => 'string',
            'Файл' => 'string',
            'Статус' => 'string',
            'Дата' => 'string',
        ];

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $headers);

        $userUrl = $this->config->back_url . "/client/";
        $orderUrl = $this->config->back_url . "/order/";

        foreach ($this->getChunkedResults($this->filtersWhere, $this->orderBy) as $item) {
            $userFullName = trim("$item->lastname $item->firstname $item->patronymic") ?? '';

            $requestFileLink = '';
            if (!empty($item->request_file)) {
                $requestFileLink = '=HYPERLINK("' . $this->fileStorageService->getViewUrl($item->request_file) . '", "Запрос")';
            }

            $userHyperlink = '=HYPERLINK("' . $userUrl . $item->user_id . '", "' . $userFullName . '")';
            $orderHyperlink = '=HYPERLINK("' . $orderUrl . $item->order_id . '", "' . $item->contract_number . '")';

            $writer->writeSheetRow('Отчёт', [
                $userHyperlink,
                $item->user_id,
                $orderHyperlink,
                $requestFileLink,
                $this->getStatusLabel($item->status),
                date('d.m.Y H:i', strtotime($item->created_at))
            ]);
        }

        $filename = 'bki_report_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    public function updateStatus(): void {
        if (!$this->manager->id) {
            $this->response->json_output(['status' => 'error', 'message' => 'Доступ запрещен']);
            return;
        }

        $id = (int)$this->request->get('id');
        $action = $this->request->get('status');

        if (empty($id) || empty($action)) {
            $this->response->json_output(['status' => 'error', 'message' => 'Недостаточно параметров']);
            return;
        }

        if (!in_array($action, ['approve', 'cancel'])) {
            $this->response->json_output(['status' => 'error', 'message' => 'Недопустимое действие']);
            return;
        }

        $this->db->query("SELECT id, user_id, order_id FROM __bki_questions WHERE id = ?", $id);
        $request = $this->db->result();

        if (!$request) {
            $this->response->json_output(['status' => 'error', 'message' => 'Запрос не найден']);
            return;
        }

        $newStatus = $action === 'approve' ? 'approved' : 'cancelled';

        $this->db->query(
            "UPDATE __bki_questions SET status = ?, updated_at = NOW() WHERE id = ?",
            $newStatus,
            $id
        );

        $this->response->json_output(['status' => 'success']);
    }
}