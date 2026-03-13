<?php

declare(strict_types=1);

ini_set( 'max_execution_time', '0' );
ini_set( 'memory_limit', '-1' );

require_once 'lib/autoloader.php';
require_once dirname( __DIR__ ) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname( __DIR__ ) . '/api/Helpers.php';
require_once 'View.php';

class VerificationsGroupReportView extends View
{
    private $date_from;

    private $date_to;

    public function __construct()
    {
        parent::__construct();

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
        $this->design->assign('can_see_manager_url', in_array('verificators', $this->manager->permissions));
    }

    public function fetch()
    {
        if ($this->request->get('action') === 'download') {
            $this->download();
        } else {
            $this->design->assign('managersAvgSpeed', $this->getManagersAvgSpeed());
            $this->design->assign('verificationsGroupUri', strtok($_SERVER['REQUEST_URI'], '?' ));
            return $this->design->fetch('verifications_group_report.tpl');
        }
    }

    /**
     * Генерация данных
     */
    private function getResults(): array
    {
        // фильтр
        $andWhere = $this->buildSqlWhere();

        // Получаем информацию о займах
        $query = $this->db->placehold("SELECT
                o.id as order_id,
                o.manager_id,
                m.name_1c
            FROM s_orders o
            INNER JOIN s_managers AS m ON m.id = o.manager_id
            WHERE " . implode(' AND ', $andWhere) . "
            ORDER BY date ASC");
        $this->db->query($query);

        $orders = [];
        foreach ($this->db->results() as $order) {
            $orders[$order->order_id] = $order;
        }
        return $this->calcItems($orders);
    }

    /**
     * Расчет промежуточных данных
     */
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

    /**
     * Формирование SQL WHERE
     */
    private function buildSqlWhere(): array
    {
        return [
            "`manager_id` != 50",
            $this->db->placehold("date >= ?", $this->date_from),
            $this->db->placehold("date <= ?", $this->date_to),
        ];
    }

    private function download(): void
    {
        $header = [
            'Менеджер' => 'string',
            'Количество заявок' => 'string',
            'Средняя скорость обработки менеджером (сек)' => 'string',
        ];
        $writer = new XLSXWriter();
        $writer->writeSheetHeader('verifications_group_report', $header);

        $managersAvgSpeed = $this->getManagersAvgSpeed();
        foreach ($managersAvgSpeed as $managerAvgSpeed) {
            $row_data = [
                $managerAvgSpeed['name_1c'],
                $managerAvgSpeed['count'],
                $managerAvgSpeed['avg'],
            ];
            $writer->writeSheetRow('verifications_group_report', $row_data);
        }
        $filename = 'files/reports/verifications_group_report.xlsx';
        $writer->writeToFile($this->config->root_dir . $filename);
        header('Location:' . $filename);
        exit;
    }

    /**
     * Расчет средней скорости менеджера
     */
    private function getManagersAvgSpeed(): array
    {
        $orders = $this->getResults();
        foreach($orders as $order) {
            if (!isset($order->speedManager)) {
                continue;
            }
            if (!isset($managersAvgSum[$order->manager_id])) {
                $managersAvgSum[$order->manager_id] = 0;
            }
            if (!isset($managersAvgCount[$order->manager_id])) {
                $managersAvgCount[$order->manager_id] = 0;
            }
            $managersAvgSum[$order->manager_id] += $order->speedManager;
            $managersAvgCount[$order->manager_id]++ ;

            $managers[$order->manager_id] = $order->name_1c ;
        }

        if (!empty($managersAvgSum) && !empty($managersAvgCount)) {
            foreach ($managersAvgSum as $managerId => $sum) {
                $result[$managerId] = [
                    'managerId' => $managerId,
                    'name_1c' => $managers[$managerId] ?? '',
                    'count' => $managersAvgCount[$managerId] ?? 0,
                    'avg' => ceil($sum / $managersAvgCount[$managerId]),
                ];
            }
        }
        uasort($result, function($a, $b) {
            if ($a['avg'] === $b['avg']) {
                return 0;
            }
            return $a['avg'] < $b['avg'] ? 1 : -1;
        });
        return $result ?? [];
    }
}
