<?php

declare(strict_types=1);

ini_set( 'max_execution_time', '0' );
ini_set( 'memory_limit', '-1' );

require_once 'lib/autoloader.php';
require_once dirname( __DIR__ ) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname( __DIR__ ) . '/api/Helpers.php';
require_once dirname( __DIR__ ) . '/api/Scorings.php';
require_once 'View.php';

class RejectReasonReportView extends View
{
    public const PAGE_CAPACITY = 15;
    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;
    private $date_from;
    private $date_to;

    /** @var int Id системного менеджера */
    private int $sysMngrId;

    public function __construct()
    {
        parent::__construct();

        $this->sysMngrId = $this->managers::MANAGER_SYSTEM_ID;

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

            $this->design->assign('current_page_num', $this->currentPage);
            $this->design->assign('total_pages_num', $this->pagesNum);
            $this->design->assign('total_items', $this->totalItems);
            $this->design->assign('rejectedUri', strtok($_SERVER['REQUEST_URI'], '?' ));

            return $this->design->fetch('reject_reason_report.tpl');
        }
    }

    private const SCORING_TYPES = [
        'crm' => [
            Scorings::TYPE_BLACKLIST,
            Scorings::TYPE_EFRSB,
            Scorings::TYPE_FMS,
            Scorings::TYPE_FNS,
            Scorings::TYPE_FSSP,
            Scorings::TYPE_JUICESCORE,
            // Scorings::TYPE_LOCAL_TIME
            Scorings::TYPE_LOCATION,
            Scorings::TYPE_AGE,
            Scorings::TYPE_SVO,
            // Scorings::TYPE_DBRAIN_PASSPORT,
            // Scorings::TYPE_DBRAIN_CARD,
        ],
        'scorista' => [Scorings::TYPE_SCORISTA],
        'axi' => [Scorings::TYPE_AXILINK, Scorings::TYPE_AXILINK_2]
    ];

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

        $query = $this->db->placehold("
            SELECT o.id AS order_id, o.`date` AS order_date, o.user_id, 
                   sc.id AS scoring_id, sc.`type`, scb.body, sc.string_result, sc.end_date AS scoring_end,
                   r.admin_name, o.manager_id
            FROM (
                SELECT DISTINCT o.id
                FROM s_orders o
                WHERE " . implode(' AND ', $andWhere) . "
                ORDER BY o.`date` DESC
                " . $limitSQl . "
            ) AS limited_orders
            JOIN s_orders o ON limited_orders.id = o.id
            LEFT JOIN s_scorings sc ON o.id = sc.order_id AND sc.`status` IN (?, ?) AND sc.success = 0
            LEFT JOIN s_reasons r ON o.reason_id = r.id
            LEFT JOIN s_scoring_body scb ON sc.id = scb.scoring_id
            ORDER BY o.`date` DESC, scoring_end;
        ", Scorings::STATUS_COMPLETED, Scorings::STATUS_STOPPED);
        $this->db->query($query);

        // Получаем "Сырые" данные
        $orders = [];
        foreach ($this->db->results() as $order) {
            $orders[$order->order_id][] = $order;
        }

        // Обработка данных для отображения в отчёте
        $results = [];
        foreach ($orders as $order_id => $data) {
            // Стандартные поля для каждой причины отказа
            $results[$order_id] = [
                'order_id' => $order_id,
                'manager_id' => $this->sysMngrId
            ];
            $result = &$results[$order_id]; // Просто для удобства

            foreach ($data as $row) {
                $result['user_id'] = $row->user_id;
                $result['order_date'] = $row->order_date;

                $row->type = (int)$row->type;
                // Отказ CRM
                if (in_array($row->type, self::SCORING_TYPES['crm'])) {
                    $result['crm_reason'] = $row->string_result ?? $row->body;
                }

                // Отказ Скористы
                if (in_array($row->type, self::SCORING_TYPES['scorista'])) {
                    $body = json_decode($row->body);
                    if (empty($body))
                        continue;

                    if ($ball = $body->additional->summary->score)
                        $result['scorista_ball'] = $ball;

                    if (!empty($body->stopFactors)) {
                        $factors = [];
                        foreach ($this->orders::SCORISTA_CAN_REJECT_FACTORS as $factor_name) {
                            if (!empty($body->stopFactors->$factor_name)) {
                                $factor = $body->stopFactors->$factor_name;
                                if ($factor->result == 1)
                                    $factors[] = $factor->description;
                            }
                        }
                        $result['scorista_factors'] = $factors;
                    }
                }

                // Отказ Акси
                if (in_array($row->type, self::SCORING_TYPES['axi'])) {
                    $body = json_decode($row->body);
                    $reason = $body->message ?? 'Отказ';
                    if ($row->type == $this->scorings::TYPE_AXILINK) {
                        $result['axi_reason'] = 'АксиЛинк: ' . $reason;
                    }
                    else {
                        $result['axi_reason'] = 'АксиНбки: ' . $reason;
                    }
                }

                // Отказ верификатора
                if ($row->manager_id != $this->sysMngrId) {
                    $result['manager_id'] = $row->manager_id;
                    $result['manager_reason'] = $row->admin_name;
                }
            }

            // Порядковый номер
            $this->db->query(
                $this->db->placehold(
                    "SELECT COUNT(id) AS n FROM s_orders WHERE user_id = ? AND id <= ?",
                    $result['user_id'], $result['order_id'])
            );
            $result['order_number'] = $this->db->result('n');

            $result = (object)$result;
        }

        return $results;
    }

    private function buildSqlWhere(): array
    {
        return [
            $this->db->placehold("o.date >= ?", $this->date_from),
            $this->db->placehold("o.date <= ?", $this->date_to),
            $this->db->placehold("o.status = 3"),
        ];
    }

    /**
     * Общее количество
     */
    private function getTotals(): int
    {
        $andWhere = $this->buildSqlWhere();
        $sql = "SELECT COUNT(o.id) AS total FROM s_orders o WHERE " . implode(' AND ', $andWhere);
        $query = $this->db->placehold($sql, $this->date_from, $this->date_to);
        $this->db->query($query);
        return (int)$this->db->result('total');
    }

    private function download(): void
    {
        $rows = $this->getResults();

        $header = [
            'Id заявки' => 'string',
            'Дата заявки' => 'string',
            'Порядковый номер' => 'string',
            'Отказ CRM' => 'string',
            'Отказ скористы' => 'string',
            'Отказ акси' => 'string',
            'Отказ андеррайтинга' => 'string',
        ];
        $writer = new XLSXWriter();
        $writer->writeSheetHeader('reject_reason_report', $header);

        foreach ($rows as $row) {
            $scorista_reason = '';

            if ($row->scorista_ball)
                $scorista_reason .= 'Балл: ' . $row->scorista_ball . '\n';

            if ($row->scorista_factors) {
                $scorista_reason .= 'Стоп-факторы:';
                foreach ($row->scorista_factors as $factor)
                    $scorista_reason .= '\n' . $factor;
            }

            $row_data = [
                $row->order_id,
                $row->order_date,
                $row->order_number,
                $row->crm_reason,
                $scorista_reason,
                $row->axi_reason,
                $row->manager_reason,
            ];

            $writer->writeSheetRow('reject_reason_report', $row_data);
        }
        $filename = 'files/reports/reject_reason_report.xlsx';
        $writer->writeToFile($this->config->root_dir . $filename);
        header('Location:' . $filename);
        exit;
    }
}
