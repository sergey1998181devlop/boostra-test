<?php

declare(strict_types=1);

ini_set('max_execution_time', '0');
ini_set('memory_limit', '-1');

require_once 'lib/autoloader.php';
require_once dirname( __DIR__ ) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname( __DIR__ ) . '/api/Helpers.php';
require_once 'View.php';

class StoristaReportView extends View
{
    private $date_from;

    private $date_to;

    public function __construct()
    {
        parent::__construct();

        $daterange = $this->request->get('daterange');
        if (empty($daterange)) {
            $daterange = date('d.m.Y', strtotime('- 1 day')) . ' - ' . date('d.m.Y');
        }
        list($from, $to) = explode('-', $daterange);
        $this->date_from = date('Y-m-d', strtotime($from));
        $this->date_to = date('Y-m-d', strtotime($to));

        $this->design->assign('date_from', $this->date_from);
        $this->design->assign('date_to', $this->date_to);
        $this->design->assign('from', $from);
        $this->design->assign('to', $to);
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {
        if ($this->request->get('action') === 'download') {
            $this->download();
        } else {

            $this->design->assign('data', $this->getResults());
            $this->design->assign('storistaUri', strtok($_SERVER['REQUEST_URI'], '?' ));

            $this->design->assign('filterStatus', $this->request->get('filterStatus'));
            $this->design->assign('filterSource', $this->request->get('filterSource'));

            $this->design->assign('can_see_manager_url', in_array('verificators', $this->manager->permissions));
            $this->design->assign('can_see_client_url', in_array('clients', $this->manager->permissions));

            return $this->design->fetch('storista_report.tpl');
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
        $query = $this->db->placehold("
            SELECT 
                o.user_id,
                o.id AS order_id,
                o.1c_id as id_1c,
                o.date,
                o.status,
                o.utm_source,
                o.approve_date,
                m.id as manager_id,
                m.name_1c,
                CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) AS fio,
                u.birth,
                c.issuance_date
            FROM s_orders AS o
            LEFT JOIN s_users AS u ON u.id = o.user_id
            LEFT JOIN s_managers AS m ON m.id = o.manager_id
            LEFT JOIN s_contracts c ON c.order_id = o.id
            WHERE " . implode(' AND ', $andWhere));
        $this->db->query($query);

        foreach ($this->db->results() as $i => $item) {
            $item = $this->createScoring($item);
            if (
                isset($item->success) && $item->success === '0'
                && !empty($item->pdn)/* && $item->pdn <= 75*/
                && isset($item->scoristaAndAxilinkIsset) && $item->scoristaAndAxilinkIsset === true
            ) {
                $result['items'][] = $item;
                if (!isset($result['managers'][$item->name_1c][$item->status])) {
                    $result['managers'][$item->name_1c][$item->status] = 0;
                }
                $result['managers'][$item->name_1c][$item->status]++;
            }
        }
        $result['statuses'] = (new Orders())->get_statuses();
            $result['sources'] = $this->getSources();

        return $result;
    }

    private function buildSqlWhere(): array
    {
        $andWhere = [
            "`manager_id` != 50",
            $this->db->placehold("date >= ?", $this->date_from),
            $this->db->placehold("date <= ?", $this->date_to),
        ];

        if (is_numeric($this->request->get('filterStatus'))) {
            $andWhere[] = $this->db->placehold("o.status = ?", $this->request->get('filterStatus'), 'integer');
        }
        if (!empty($this->request->get('filterSource'))) {
            $andWhere[] = $this->db->placehold("o.utm_source = ?", $this->request->get('filterSource'));
        }

        return $andWhere;
    }

    private function createScoring($item)
    {
        $this->db->query("
            SELECT scorista_id, body, type, success
            FROM s_scorings
            WHERE type IN (?, ?)
            AND order_id = ?
            AND status = ?
            ORDER BY created",
            $this->scorings::TYPE_SCORISTA, $this->scorings::TYPE_AXILINK,
            $this->scorings::STATUS_COMPLETED
            $item->order_id
        );
        $scorings = $this->db->results();

        $scoristaIsset = false;
        $axilinkIsset = false;
        foreach($scorings as $scoring) {
            if ($scoring->type === $this->scorings::TYPE_SCORISTA) {
                $scoristaIsset = true;
            } elseif ($scoring->type === $this->scorings::TYPE_AXILINK) {
                $axilinkIsset = true;
            }
        }
        if ($scoristaIsset === true && $axilinkIsset === true) {
            $item->scoristaAndAxilinkIsset = true;
        }

        if (!empty($scoring)) {
            $item->agrid = $scoring->scorista_id;
            $scoring_body = json_decode($scoring->body);

            if ($scoring->type === $this->scorings::TYPE_SCORISTA) {
                $item->success = (string)$scoring->body->decision->decisionBinnar;
            } elseif ($scoring->type === $this->scorings::TYPE_AXILINK) {
                $item->success = $scoring->success;
            }

            if ($scoring->type === $this->scorings::TYPE_SCORISTA) {
                $item->pdn = $scoring_body->additional->pti_RosStat->pti->result * 100;
            } else {
                $item->pdn = isset($scoring_body->pdn) ? $scoring_body->pdn * 100 : 0;
            }
        }
        return $item;
    }

    private function getSources(): array
    {
        $query = $this->db->placehold(
            "SELECT DISTINCT o.utm_source
                FROM s_orders o
                WHERE o.manager_id != 50 AND o.date >= ? AND o.date <= ?
                ORDER BY o.utm_source;",
            $this->date_from, $this->date_to,
        );
        $this->db->query($query);
        foreach ($this->db->results() as $source) {
            $sources[] = $source->utm_source;
        }
        return $sources ?? [];
    }

    private function download(): void
    {
        $orders = $this->getResults();

        $header = [
            'UserID' => 'string',
            'Клиент' => 'string',
            'ДР' => 'string',
            'Дата заявки' => 'string',
            'Заявка' => 'string',
            'Заявка 1С' => 'string',
            'Статус' => 'string',
            'Дата одобрения' => 'string',
            'Дата выдачи' => 'string',
            'Менеджер' => 'string',
            'pdn' => 'string',
            'utm_source' => 'string',
        ];
        $writer = new XLSXWriter();
        $writer->writeSheetHeader('storista_report', $header);

        if (!empty($orders['items'])) {
            foreach ($orders['items'] as $order) {
                $row_data = [
                    $order->user_id,
                    $order->fio,
                    $order->birth,
                    $order->date,
                    $order->order_id,
                    $order->id_1c,
                    $orders['statuses'][$order->status],
                    $order->approve_date,
                    $order->issuance_date,
                    $order->name_1c,
                    $order->pdn,
                    $order->utm_source,
                ];
                $writer->writeSheetRow('storista_report', $row_data);
            }
        }

        $filename = 'files/reports/storista_report.xlsx';
        $writer->writeToFile($this->config->root_dir . $filename);
        header('Location:' . $filename);
        exit;
    }
}
