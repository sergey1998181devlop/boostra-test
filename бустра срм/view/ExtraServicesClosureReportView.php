<?php

require_once 'View.php';

class ExtraServicesClosureReportView extends View
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
        if ($action && method_exists($this, $action)) {
            $this->$action();
        }
    }

    public function fetch(): string
    {
        $items = $this->getResults($this->currentPage);

        $this->design->assign_array(array(
            'items' => $items,
            'current_page_num' => $this->currentPage,
            'total_pages_num' => $this->pagesNum,
            'total_items' => $this->totalItems,
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
            'can_see_manager_url' => in_array('verificators', $this->manager->permissions),
            'can_see_client_url' => in_array('clients', $this->manager->permissions),
            'date_from' => date('d.m.Y', strtotime($this->dateFrom)),
            'date_to' => date('d.m.Y', strtotime($this->dateTo)),
        ));

        return $this->design->fetch('extra_services_closure_report.tpl');
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

    private function getResults(int $currentPage)
    {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);

        $this->db->query("
            SELECT 
                u.lastname, u.firstname, u.patronymic,
                u.birth, u.id AS user_id,
                c.number AS contract,
                m.name AS manager_name, m.id AS manager_id,
                cl.new_values, cl.created
            FROM s_changelogs cl 
            LEFT JOIN s_contracts c ON c.order_id = cl.order_id
            LEFT JOIN s_users u ON u.id = c.user_id
            LEFT JOIN s_managers m ON m.id = cl.manager_id 
            WHERE cl.`type` = 'additional_service_repayment'
                AND DATE(cl.created) BETWEEN ? AND ?
            ORDER BY cl.id DESC
            LIMIT ? OFFSET ?",
            $this->dateFrom, $this->dateTo, self::PAGE_CAPACITY, $offset
        );

        return $this->db->results();
    }

    /**
     * @throws Exception
     */
    private function getAllResults(): array
    {
        $this->db->query("
            SELECT 
                u.lastname, u.firstname, u.patronymic,
                u.birth,
                u.id AS user_id,
                c.number AS contract,
                m.name AS manager_name,
                m.id AS manager_id,
                cl.new_values, cl.created
            FROM s_changelogs cl 
            LEFT JOIN s_contracts c ON c.order_id = cl.order_id
            LEFT JOIN s_users u ON u.id = c.user_id
            LEFT JOIN s_managers m ON m.id = cl.manager_id 
            WHERE cl.`type` = 'additional_service_repayment'
                AND DATE(cl.created) BETWEEN ? AND ?
            ORDER BY cl.created",
            $this->dateFrom, $this->dateTo
        );

        return $this->db->results();
    }

    private function getTotals(): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(id) AS total 
            FROM s_changelogs 
            WHERE `type` = 'additional_service_repayment' 
                AND DATE(created) BETWEEN ? AND ?",
            $this->dateFrom, $this->dateTo
        );
        $this->db->query($query);
        return (int) $this->db->result('total');
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
        }

        $header = [
            'Клиент'           => 'string',
            'Дата рождения'    => 'string',
            'Договор'          => 'string',
            'ФИО менеджера'    => 'string',
            'Действие'         => 'string',
            'Дата действия'    => 'string',
        ];

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $header);

        $items = $this->getAllResults();

        foreach ($items as $item) {
            $fio = trim("{$item->lastname} {$item->firstname} {$item->patronymic}");
            $createdDate = date('d.m.Y H:i:s', strtotime($item->created));

            $writer->writeSheetRow('Отчёт', [
                $fio,
                $item->birth,
                $item->contract,
                $item->manager_name,
                $item->new_values,
                $createdDate
            ]);
        }

        $filename = 'extra_services_closure_report_' . date('Y-m-d') . '.xlsx';

        // Отправка файла для загрузки
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }
}
