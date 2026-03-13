<?php

declare(strict_types=1);

ini_set( 'max_execution_time', '0' );
ini_set( 'memory_limit', '-1' );

require_once 'lib/autoloader.php';
require_once dirname( __DIR__ ) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname( __DIR__ ) . '/api/Helpers.php';
require_once 'View.php';

class VerificationsReportView extends View
{
    /**
     * Лимит
     */
    public const PAGE_CAPACITY = 15;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var int
     */
    private int $totalItems;

    /**
     * @var int
     */
    private $pagesNum;

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

        $this->design->assign('order_id', $this->request->get('order_id'));
        $this->design->assign('date', $this->request->get('date'));
        $this->design->assign('have_close_credits', $this->request->get('have_close_credits'));
        $this->design->assign('last_name', $this->request->get('last_name'));
        $this->design->assign('first_name', $this->request->get('first_name'));
        $this->design->assign('patronymic', $this->request->get('patronymic'));
        $this->design->assign('manager_id', $this->request->get('manager_id', 'integer'));
        $this->design->assign('status_id', $this->request->get('status_id'));

        $this->totalItems = $this->getTotals();
        $this->pagesNum = ceil($this->totalItems / self::PAGE_CAPACITY);
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

            $this->design->assign('orderStatuses', (new Orders())->get_statuses());
            $this->design->assign('managers', $this->getManagers());
            $this->design->assign('current_page_num', $this->currentPage);
            $this->design->assign('total_pages_num', $this->pagesNum);
            $this->design->assign('total_items', $this->totalItems);
            $this->design->assign('verificationsUri', strtok($_SERVER['REQUEST_URI'], '?' ));

            return $this->design->fetch('verifications_report.tpl');
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
                concat(u.lastname, ' ', u.firstname, ' ', u.patronymic) as fio,
                status,
                approve_date,
                manager_id
            FROM s_orders
            INNER JOIN s_users AS u ON u.id = s_orders.user_id
            WHERE " . implode(' AND ', $andWhere) . "
            ORDER BY `date` ASC
            " . $limitSQl);
        $this->db->query($query);

        $orders = [];
        foreach ($this->db->results() as $order) {
            $orders[$order->order_id] = $order;
        }
        return $this->calcItems($orders);
    }

    private function calcItems(array $orders): array
    {
        if (!empty($orders)) {
            // Получаем информацию об изменении статусов заказов
            $orderIds = array_column($orders, 'order_id');
            $sql = $this->db->placehold("SELECT *
                FROM s_changelogs
                WHERE order_id IN (" . implode(',', $orderIds) . ") AND type IN ('status', 'images')
                ORDER BY order_id ASC, created ASC");
            $this->db->query($sql);
            $statuses = $this->db->results() ?? [];

            foreach ($statuses as $status) {
                try {
                    $newValues = unserialize($status->new_values);
                } catch (Exception $e) {
                    continue;
                }
                if ($status->type === 'status' && ($newValues['manager_id'] ?? false)) {
                    if (!($orders[$status->order_id]->date_set_manager ?? false)) {
                        $orders[$status->order_id]->date_set_manager = $status->created;
                    }
                }
                if ($status->type === 'status' && ($newValues['status'] ?? false)) {
                    if (!($orders[$status->order_id]->date_set_first_status ?? false)) {
                        $orders[$status->order_id]->date_set_first_status = $status->created;
                        $orders[$status->order_id]->first_status = $newValues['status'];
                    }
                }
                if (($status->type === 'images') && ($newValues['status'] ?? false) && ($newValues['status'] === '3') ) {
                    if (!($orders[$status->order_id]->date_set_first_status ?? false)) {
                        $orders[$status->order_id]->date_set_first_status = $status->created;
                        $orders[$status->order_id]->first_status = 5; // На исправлении
                    }
                }

            }

            foreach ($orders as $orderId => $order) {
                if (($order->date_set_manager ?? '') && ($order->date_set_first_status ?? '')) {
                    $date1 = strtotime($order->date_set_manager);
                    $date2 = strtotime($order->date_set_first_status);
                    $orders[$orderId]->speedManager = $date2 - $date1;
                    if ($orders[$orderId]->speedManager < 0) {
                        unset($orders[$orderId]);
                    }
                }
            }
        }
        return $orders;
    }

    private function buildSqlWhere(): array
    {
        $andWhere = [
            "`manager_id` != 50",
            $this->db->placehold("date >= ?", $this->date_from),
            $this->db->placehold("date <= ?", $this->date_to),
        ];
        if (!empty($this->request->get('order_id'))) {
            $andWhere[] = $this->db->placehold("s_orders.id = ?", $this->request->get('order_id'));
        }
        if (!empty($this->request->get('date'))) {
            $andWhere[] = $this->db->placehold("s_orders.date = ?", $this->request->get('date'));
        }
        if (is_numeric($this->request->get('have_close_credits'))) {
            $andWhere[] = $this->db->placehold("s_orders.have_close_credits = ?", $this->request->get('have_close_credits'), 'integer');
        }
        if (!empty($this->request->get('manager_id'))) {
            $andWhere[] = $this->db->placehold("s_orders.manager_id = ?", $this->request->get('manager_id'));
        }
        if (is_numeric($this->request->get('status_id'))) {
            $andWhere[] = $this->db->placehold("s_orders.status = ?", $this->request->get('status_id'), 'integer');
        }
        return $andWhere;
    }

    /**
     * Получаем итого
     * @return int
     */
    private function getTotals(): int
    {
        $andWhere = $this->buildSqlWhere();
        $query = $this->db->placehold("SELECT COUNT(*) as total FROM s_orders WHERE " . implode(" AND ", $andWhere),
            $this->date_from,
            $this->date_to
        );
        $this->db->query($query);
        return (int)$this->db->result('total');
    }

    private function download(): void
    {
        $orders = $this->getResults();

        $header = [
            'Заявка' => 'string',
            'Дата размещения' => 'string',
            'НК/ПК' => 'string',
            'ФИО' => 'string',
            'Менеджер' => 'string',
            'Статус (текущий)' => 'string',
            'Дата взятия менеджером' => 'string',
            'Статус(первый)' => 'string',
            'Дата первого статуса' => 'string',
            'Скорость обработки (сек)' => 'string',
        ];
        $writer = new XLSXWriter();
        $writer->writeSheetHeader('verifications_report', $header);

        foreach ($orders as $order) {
            $row_data = [
                $order->order_id,
                $order->date,
                $order->have_close_credits === '1' ? 'пк' : 'нк',
                $order->fio,
                $this->getManagers()[$order->manager_id],
                (new Orders())->get_statuses()[$order->status],
                $order->date_set_manager,
                (new Orders())->get_statuses()[$order->first_status],
                $order->date_set_first_status,
                $order->speedManager,
            ];

            $writer->writeSheetRow('verifications_report', $row_data);
        }
        $filename = 'files/reports/verifications_report.xlsx';
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
