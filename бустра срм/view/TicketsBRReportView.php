<?php

require_once 'View.php';

use App\Modules\BRReport\Dto\BRReportFilterDto;
use App\Modules\BRReport\Mappers\BRReportMapper;
use App\Modules\BRReport\Services\BRReportService;

/**
 * View для отчета БР (Банк России)
 *
 * Делегирует бизнес-логику в BRReport модуль
 */
class TicketsBRReportView extends View
{
    private const PAGE_CAPACITY = 15;

    /** @var int */
    private $currentPage;

    /** @var int */
    private $totalItems;

    /** @var int */
    private $pagesNum;

    /** @var string */
    private $filtersWhere = '';

    /** @var string */
    private $orderBy;

    /** @var string */
    private $dateFrom;

    /** @var string */
    private $dateTo;

    /** @var BRReportService */
    private $brReportService;

    public function __construct()
    {
        parent::__construct();

        $this->brReportService = new BRReportService($this->db);

        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $this->setupDateRange();
        $this->orderBy = $this->setOrderBy();

        $filter = $this->createFilter();
        $this->totalItems = $this->brReportService->getTotals($filter);
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
        $filter = $this->createFilter();
        $reportData = $this->brReportService->prepareReportForDisplay($filter);

        $this->design->assign_array([
            'reportHeaders' => $reportData['headers'],
            'reportRows' => $reportData['rows'],
            'items' => $reportData['rows'],
            'current_page_num' => $this->currentPage,
            'total_pages_num' => $this->pagesNum,
            'total_items' => $this->totalItems,
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
            'can_see_client_url' => in_array('clients', $this->manager->permissions),
            'date_from' => date('d.m.Y', strtotime($this->dateFrom)),
            'date_to' => date('d.m.Y', strtotime($this->dateTo)),
        ]);

        return $this->design->fetch('tickets_br_report.tpl');
    }

    /**
     * Скачать отчет в формате XLSX
     *
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

        $filter = $this->createFilter();
        $columns = $this->brReportService->getColumns();

        $header = [];
        foreach ($columns as $col) {
            $header[$col['label']] = isset($col['type']) ? $col['type'] : 'string';
        }

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $header);

        foreach ($this->brReportService->getChunkedReportData($filter) as $item) {
            $row = [];
            foreach ($columns as $col) {
                $row[] = $this->brReportService->getColumnValue($item, $col['key']);
            }
            $writer->writeSheetRow('Отчёт', $row);
        }

        $filename = 'tickets_br_report_' . date('Y-m-d') . '.xlsx';

        // Отправка файла для загрузки
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    /**
     * Настройка диапазона дат
     */
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

    /**
     * Настройка сортировки
     */
    private function setOrderBy(): string
    {
        $orderBy = 't.created_at DESC';

        $sort = $this->request->get('sort') ?? null;
        if ($sort) {
            $pos = strrpos($sort, '_');
            if ($pos !== false) {
                $field = substr($sort, 0, $pos);
                $direction = substr($sort, $pos + 1);
                $direction = (strtoupper($direction) === 'ASC') ? 'ASC' : 'DESC';

                switch ($field) {
                    case 'id':
                        $orderBy = "t.id $direction";
                        break;
                    case 'created':
                        $orderBy = "t.created_at $direction";
                        break;
                    case 'company':
                        $orderBy = "company_name $direction";
                        break;
                    case 'client':
                        $orderBy = "client_name $direction";
                        break;
                    default:
                        break;
                }
            }
        }

        return $orderBy;
    }

    /**
     * Создать DTO фильтра
     */
    private function createFilter(): BRReportFilterDto
    {
        return new BRReportFilterDto(
            $this->dateFrom,
            $this->dateTo,
            $this->currentPage,
            self::PAGE_CAPACITY,
            $this->orderBy,
            $this->filtersWhere
        );
    }
}
