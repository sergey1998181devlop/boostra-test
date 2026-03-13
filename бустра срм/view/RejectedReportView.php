<?php

declare(strict_types=1);

ini_set( 'max_execution_time', '0' );
ini_set( 'memory_limit', '-1' );

require_once 'lib/autoloader.php';
require_once dirname( __DIR__ ) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname( __DIR__ ) . '/api/Helpers.php';
require_once 'View.php';

class RejectedReportView extends View
{
    /**
     * Лимит
     */
    public const PAGE_CAPACITY = 15;

    private int $currentPage;

    private int $totalItems;

    private int $pagesNum;

    private $date_from;

    private $date_to;

    public function __construct()
    {
        parent::__construct();

        $this->currentPage = max(1, $this->request->get('page', 'integer'));

        $daterange = $this->request->get('daterange');
        if (empty($daterange)) {
            $daterange = date('d.m.Y', strtotime('- 1 month')) . ' - ' . date('d.m.Y');
        }
        list($from, $to) = explode('-', $daterange);
        $this->date_from = date('Y-m-d', strtotime($from));
        $this->date_to = date('Y-m-d', strtotime($to));

        $this->design->assign('date_from', $this->date_from);
        $this->design->assign('date_to', $this->date_to);
        $this->design->assign('from', $from);
        $this->design->assign('to', $to);

        $this->totalItems = $this->getTotals();
        $this->pagesNum = (int)ceil($this->totalItems / self::PAGE_CAPACITY);
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {
        if ($this->request->get('action') === 'download') {
            $this->download();
        } else {
            $offset = self::PAGE_CAPACITY * ($this->currentPage - 1);

            $this->design->assign('items', $this->getResults(self::PAGE_CAPACITY, $offset));

            $this->design->assign('managers', $this->getManagers());
            $this->design->assign('current_page_num', $this->currentPage);
            $this->design->assign('total_pages_num', $this->pagesNum);
            $this->design->assign('total_items', $this->totalItems);
            $this->design->assign('rejectedUri', strtok($_SERVER['REQUEST_URI'], '?' ));

            $this->design->assign('can_see_manager_url', in_array('verificators', $this->manager->permissions));
            $this->design->assign('can_see_client_url', in_array('clients', $this->manager->permissions));

            return $this->design->fetch('rejected_report.tpl');
        }
    }

    /**
     * Генерация данных
     */
    private function getResults(int $limit = null, int $offset = null): array
    {
        // фильтр
        $andWhere = $this->buildSqlWhere();

        $limitSQl = '';
        if (is_numeric($limit)) {
            $limitSQl .= $this->db->placehold(" LIMIT ? ", $limit);
        }
        if (is_numeric($offset)) {
            $limitSQl .= $this->db->placehold(" OFFSET ? ", $offset);
        }


        // Получаем информацию о займах
        $query = $this->db->placehold("
            SELECT
                s_orders.id as order_id,
                date,
                have_close_credits,
                status,
                approve_date,
                manager_id,
                concat(u.lastname, ' ', u.firstname, ' ', u.patronymic) as fio,
                u.id as user_id,
                u.birth,
                r.admin_name as reason_admin_name
            FROM s_orders
            INNER JOIN s_users AS u ON u.id = s_orders.user_id
            INNER JOIN s_reasons AS r ON r.id = s_orders.reason_id
            WHERE " . implode(' AND ', $andWhere) . "
            ORDER BY `date` ASC
            " . $limitSQl);
        $this->db->query($query);

        $orders = [];
        foreach ($this->db->results() as $order) {
            $orders[$order->order_id] = $order;
        }
        return $orders;
    }

    private function buildSqlWhere(): array
    {
        $andWhere = [
            "`manager_id` != 50",
            $this->db->placehold("date >= ?", $this->date_from),
            $this->db->placehold("date <= ?", $this->date_to),
            $this->db->placehold("status IN (3, 4)"),
        ];
        return $andWhere;
    }

    /**
     * Получаем итого
     */
    private function getTotals(): int
    {
        $andWhere = $this->buildSqlWhere();
        $sql = "SELECT COUNT(*) as total
            FROM s_orders
            INNER JOIN s_users AS u ON u.id = s_orders.user_id
            INNER JOIN s_reasons AS r ON r.id = s_orders.reason_id
            WHERE " . implode(" AND ", $andWhere);
        $query = $this->db->placehold($sql, $this->date_from, $this->date_to);
        $this->db->query($query);
        return (int)$this->db->result('total');
    }

    private function download(): void
    {
        $managers = $this->getManagers();
        $orders = $this->getResults();

        $header = [
            'Заявка' => 'string',
            'Дата размещения' => 'string',
            'НК/ПК' => 'string',
            'ФИО' => 'string',
            'Дата рождения' => 'string',
            'Причина отказа' => 'string',
            'Менеджер' => 'string',
        ];
        $writer = new XLSXWriter();
        $writer->writeSheetHeader('rejected_report', $header);

        foreach ($orders as $order) {
            $row_data = [
                $order->order_id,
                $order->date,
                $order->have_close_credits === '1' ? 'пк' : 'нк',
                $order->fio,
                $order->birth,
                $order->reason_admin_name,
                $managers[$order->manager_id],
            ];

            $writer->writeSheetRow('rejected_report', $row_data);
        }
        $filename = 'files/reports/rejected_report.xlsx';
        $writer->writeToFile($this->config->root_dir . $filename);
        header('Location:' . $filename);
        exit;
    }

    private function getManagers(): array
    {
        $query = $this->db->placehold(
            "SELECT DISTINCT o.manager_id, m.name_1c
                FROM s_orders o
                INNER JOIN s_managers m ON o.manager_id = m.id
                WHERE o.manager_id != 50 AND date >= ? AND date <= ?
                ORDER BY m.name_1c;",
            $this->date_from, $this->date_to,
        );
        $this->db->query($query);
        foreach ($this->db->results() as $manager) {
            $managers[$manager->manager_id] = $manager->name_1c;
        }
        return $managers ?? [];
    }
}
