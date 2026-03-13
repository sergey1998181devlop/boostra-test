<?php

use Carbon\Carbon;
use Carbon\CarbonInterface;

require_once 'View.php';

class ApprovedClientCallReportView extends View
{
    private const PAGE_CAPACITY = 15;
    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;
    private string $dateFrom;
    private string $dateTo;

    public function __construct()
    {
        parent::__construct();

        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $this->setupDateRange();
        $this->totalItems = $this->getTotals();
        $this->pagesNum = (int) ceil($this->totalItems / self::PAGE_CAPACITY);

        $this->handleAction();
    }

    private function setupDateRange(): void
    {
        $daterange = $this->request->get('daterange');
        if (empty($daterange)) {
            $daterange = Carbon::now()->subDays(7)->format('d.m.Y') . ' - ' . Carbon::now()->format('d.m.Y');
        }

        [$from, $to] = explode(' - ', $daterange);

        $fromDate = Carbon::createFromFormat('d.m.Y', $from)->startOfDay();
        $toDate = Carbon::createFromFormat('d.m.Y', $to)->endOfDay();

        if ($fromDate->equalTo($toDate)) {
            $toDate = $fromDate->endOfDay();
        }

        $this->dateFrom = $fromDate->format('Y-m-d H:i:s');
        $this->dateTo = $toDate->format('Y-m-d H:i:s');
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
        $items = $this->getResults($this->currentPage);

        $this->design->assign_array([
            'items' => $this->formatResults($items),
            'current_page_num' => $this->currentPage,
            'total_pages_num' => $this->pagesNum,
            'total_items' => $this->totalItems,
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
            'date_from' => Carbon::createFromFormat('Y-m-d H:i:s', $this->dateFrom)->format('d.m.Y'),
            'date_to' => Carbon::createFromFormat('Y-m-d H:i:s', $this->dateTo)->format('d.m.Y'),
        ]);

        return $this->design->fetch('approved_client_call_report.tpl');
    }

    private function getResults(int $currentPage): array
    {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);

        $this->db->query(
            "SELECT
                o.id AS order_id,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.id AS user_id,
                o.date,
                o.approve_date,
                atv.send_time,
                c.issuance_date AS issued_at,
                CASE 
                    WHEN oa.order_id IS NOT NULL THEN 'System'
                    ELSE m.name
                END AS approved_by,
                o.manager_id
            FROM approved_to_vox atv
                LEFT JOIN s_orders o ON o.id = atv.order_id
                LEFT JOIN s_contracts c ON c.order_id = o.id
                LEFT JOIN s_users u ON u.id = o.user_id
                LEFT JOIN s_orders_auto_approve oa ON oa.order_id = o.id
                LEFT JOIN s_managers m ON m.id = o.manager_id
            WHERE o.date BETWEEN ? AND ?
            ORDER BY o.date DESC
            LIMIT ? OFFSET ?",
            $this->dateFrom, $this->dateTo, self::PAGE_CAPACITY, $offset
        );

        return $this->db->results();
    }

    private function getAllResults(): array
    {
        $this->db->query(
            "SELECT
                o.id AS order_id,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.id AS user_id,
                o.date,
                o.approve_date,
                atv.send_time,
                c.issuance_date AS issued_at,
                CASE 
                    WHEN oa.order_id IS NOT NULL THEN 'System'
                    ELSE m.name
                END AS approved_by
            FROM approved_to_vox atv
                LEFT JOIN s_orders o ON o.id = atv.order_id
                LEFT JOIN s_contracts c ON c.order_id = o.id
                LEFT JOIN s_users u ON u.id = o.user_id
                LEFT JOIN s_orders_auto_approve oa ON oa.order_id = o.id
                LEFT JOIN s_managers m ON m.id = o.manager_id
            WHERE o.date BETWEEN ? AND ?
            ORDER BY o.date DESC",
            $this->dateFrom, $this->dateTo
        );

        return $this->db->results();
    }

    private function getTotals(): int
    {
        $query = $this->db->placehold(
            "SELECT COUNT(o.id) AS total 
            FROM approved_to_vox atv
                LEFT JOIN s_orders o ON o.id = atv.order_id
            WHERE o.date BETWEEN ? AND ?",
            $this->dateFrom, $this->dateTo
        );
        $this->db->query($query);
        return (int) $this->db->result('total');
    }

    private function download(): void
    {
        $header = [
            'Клиент'                        => 'string',
            'Номер заявки'                  => 'string',
            'Дата подачи заявки'            => 'string',
            'Дата одобрения заявки'         => 'string',
            'Дата звонка клиенту'           => 'string',
            'Дата выдачи займа'             => 'string',
            'Время до получения займа после звонка' => 'string',
            'Кто одобрил' => 'string',
        ];

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $header);

        $items = $this->getAllResults();
        $items = $this->formatResults($items);

        foreach ($items as $item) {
            $fio = trim("{$item->lastname} {$item->firstname} {$item->patronymic}");

            $submissionDate = !empty($item->date) ? Carbon::parse($item->date)->format('d.m.Y H:i') : '';
            $approvalDate = !empty($item->approve_date) ? Carbon::parse($item->approve_date)->format('d.m.Y H:i') : '';
            $callDate = !empty($item->send_time) ? Carbon::parse($item->send_time)->format('d.m.Y H:i') : '';
            $issuanceDate = !empty($item->issued_at) ? Carbon::parse($item->issued_at)->format('d.m.Y H:i') : '';

            $writer->writeSheetRow('Отчёт', [
                $fio,
                $item->order_id,
                $submissionDate,
                $approvalDate,
                $callDate,
                $issuanceDate,
                $item->time_to_issuance,
                $item->approved_by
            ]);
        }

        $filename = 'approved_client_call_report_' . Carbon::now()->format('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    private function formatResults(array $results): array
    {
        Carbon::setLocale('ru');
        foreach ($results as &$result) {
            if (!empty($result->send_time) && !empty($result->issued_at)) {
                $sendTime = Carbon::parse($result->send_time);
                $issuedAt = Carbon::parse($result->issued_at);

                $result->time_to_issuance = $sendTime->diffForHumans($issuedAt, [
                    'parts' => 2,
                    'join' => ', ',
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);
            } else {
                $result->time_to_issuance = 'Не получено';
            }
        }

        return $results;
    }
}
