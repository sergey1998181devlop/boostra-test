<?php

use Carbon\Carbon;

require_once 'View.php';

class ClientActivityReportView extends View
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
 
    private function handleAction(): void
    {
        $action = $this->request->get('action');
        $allowedActions = ['download'];
        if ($action && in_array($action, $allowedActions, true) && method_exists($this, $action)) {
            $this->$action();
        }
    }

    private function setupDateRange(): void
    {
        $daterange = $this->request->get('daterange');

        if (empty($daterange)) {
            $daterange = Carbon::now()->format('d.m.Y') . ' - ' . Carbon::now()->format('d.m.Y');
        }

        [$from, $to] = explode(' - ', $daterange);

        $fromDate = Carbon::createFromFormat('d.m.Y', $from)->startOfDay();
        $toDate = Carbon::createFromFormat('d.m.Y', $to)->endOfDay();

        $this->dateFrom = $fromDate->format('Y-m-d H:i:s');
        $this->dateTo = $toDate->format('Y-m-d H:i:s');
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
            'date_from' => date('d.m.Y', strtotime($this->dateFrom)),
            'date_to' => date('d.m.Y', strtotime($this->dateTo)),
        ]);

        return $this->design->fetch('client_activity_report.tpl');
    }

    private function getBaseQuery(): string
    {
        return "
            SELECT
                comm.user_id AS user_id,
                CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) AS full_name,
                c.order_id,
                comm.created AS call_date,
                comm.text AS call_comment,
                IF(JSON_EXTRACT(comm.text, '$.handled_by') = 'aviar', 'АВИАР', JSON_UNQUOTE(JSON_EXTRACT(comm.text, '$.operator_name'))) AS operator_name,
                c.days_overdue AS days_overdue,
                p.amount AS payment_amount,
                p.created AS payment_date,
                p.is_prolongation,
                IF(p.is_prolongation, 'Продление', 'Оплата') AS client_action
            FROM
                s_comments comm
            LEFT JOIN (
                SELECT
                    c1.user_id,
                    c1.order_id,
                    c1.return_date,
                    DATEDIFF(CURRENT_DATE, c1.return_date) AS days_overdue
                FROM
                    s_contracts c1
                WHERE
                    c1.return_date = (
                        SELECT MAX(c2.return_date) -- Последняя дата
                        FROM s_contracts c2
                        WHERE c2.user_id = c1.user_id
                          AND c2.return_date < CURRENT_DATE -- Только просроченные
                    )
            ) c ON comm.user_id = c.user_id
            LEFT JOIN (
                SELECT
                    p.id,
                    p.user_id,
                    p.amount,
                    p.prolongation AS is_prolongation,
                    p.created
                FROM
                    b2p_payments p
                WHERE
                    p.payment_type = 'debt'
            ) p ON comm.user_id = p.user_id AND p.created >= comm.created
            LEFT JOIN s_users u ON comm.user_id = u.id
            WHERE
                comm.block = 'incomingCall'
                AND DATE(comm.created) = CURRENT_DATE
                AND comm.created BETWEEN ? AND ?
                AND p.created BETWEEN ? AND ?
                AND p.amount IS NOT NULL
        ";
    }

    private function getResults(int $currentPage): array
    {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);
        $query = $this->getBaseQuery() . "ORDER BY comm.created DESC LIMIT ? OFFSET ?";

        $this->db->query($query, $this->dateFrom, $this->dateTo, $this->dateFrom, $this->dateTo, self::PAGE_CAPACITY, $offset);
        return $this->db->results();
    }

    public function getEmployeeReport()
    {
        $this->db->query("
            SELECT
                IF(JSON_EXTRACT(comm.text, '$.handled_by') = 'aviar', 'АВИАР', JSON_UNQUOTE(JSON_EXTRACT(comm.text, '$.operator_name'))) AS operator_name,
                COUNT(comm.id) AS total_calls, -- Общее количество звонков
                SUM(IF(p.amount IS NOT NULL, 1, 0)) AS successful_calls, -- Успешные звонки
                ROUND((SUM(IF(p.amount IS NOT NULL, 1, 0)) / COUNT(comm.id)) * 100, 2) AS success_rate, -- Процент успешных звонков
                AVG(c.days_overdue) AS avg_days_overdue -- Средняя глубина просрочки
            FROM
                s_comments comm
            LEFT JOIN (
                SELECT
                    c1.user_id,
                    DATEDIFF(CURRENT_DATE, c1.return_date) AS days_overdue
                FROM
                    s_contracts c1
                WHERE
                    c1.return_date = (
                        SELECT MAX(c2.return_date)
                        FROM s_contracts c2
                        WHERE c2.user_id = c1.user_id
                          AND c2.return_date < CURRENT_DATE
                    )
            ) c ON comm.user_id = c.user_id
            LEFT JOIN (
                SELECT
                    p.user_id,
                    p.amount,
                    p.prolongation
                FROM
                    b2p_payments p
                WHERE
                    p.payment_type = 'debt'
            ) p ON comm.user_id = p.user_id AND p.created >= comm.created
            WHERE
                comm.block = 'incomingCall'
                AND JSON_EXTRACT(comm.text, '$.handled_by') = 'operator'
                AND comm.created BETWEEN ? AND ?
                AND p.created BETWEEN ? AND ?
            GROUP BY operator_name
            ORDER BY success_rate DESC;
        ", $this->dateFrom, $this->dateFrom, $this->dateFrom, $this->dateFrom);
        
        return $this->db->results();
    }


    private function getTotals(): int
    {
        $query = "SELECT COUNT(*) AS total FROM (" . $this->getBaseQuery() . ") t";
        $this->db->query($query, $this->dateFrom, $this->dateTo, $this->dateFrom, $this->dateTo);
        return (int) $this->db->result('total');
    }

    private function download(): void
    {
        $query = $this->getBaseQuery();
        $this->db->query($query, $this->dateFrom, $this->dateTo, $this->dateFrom, $this->dateTo);
        $items = $this->db->results();

        $writer = new XLSXWriter();

        $header = [
            'Клиент' => 'string',
            'Дата звонка' => 'string',
            'Количество дней просрочки' => 'string',
            'Действие после звонка' => 'string',
            'Сумма платежа' => 'string',
            'Дата оплаты/продления' => 'string',
            'Ответственный сотрудник' => 'string',
            'Тег' => 'string',
            'Стадия звонка' => 'string',
            'Ссылка на запись звонка' => 'string',
        ];

        $writer->writeSheetHeader('Отчёт', $header);

        foreach ($items as $item) {
            $callResult = json_decode($item->call_comment);
            
            $writer->writeSheetRow('Отчёт', [
                $item->full_name,
                $item->call_date,
                $item->days_overdue,
                $item->client_action,
                $item->payment_amount,
                $item->payment_date,
                $item->operator_name,
                $callResult->tag,
                $callResult->stage,
                $callResult->record_url,
            ]);
        }

        $filename = 'client_activity_report_' . $this->dateFrom . '_' . $this->dateTo . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }
}
