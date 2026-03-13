<?php

require_once 'View.php';

class Ping3OrdersReportView extends View
{
    private array $filters = [];

    private $items = [];

    private $totals;

    public $limit = 100;

    public function fetch()
    {
        $this->setFilters();

        if ($this->request->get('download') == 'excel')
        {
            $this->download();
        } else {
            $this->getTotals();
            $this->initPagination();
            $this->getData();
        }

        return $this->design->fetch('ping3_orders_report.tpl');
    }

    private function initPagination()
    {
        $pages_num = ceil($this->totals->total_orders / $this->limit);
        $this->design->assign('total_pages_num', $pages_num);

        $current_page = $this->request->get('page', 'integer');
        $current_page = max(1, $current_page);
        $this->design->assign('current_page_num', $current_page);
    }

    /**
     * Инициализируем фильтры
     *
     * @return void
     */
    private function setFilters()
    {
        $this->filters = [
            'date_from' => date('Y-m-d'),
            'date_to' => date('Y-m-d H:i:s'),
        ];

        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d 00:00:00', strtotime($from));
            $date_to = date('Y-m-d 23:59:59', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

            $this->filters['date_from'] = $date_from;
            $this->filters['date_to'] = $date_to;
        }

        if ($utm_source = $this->request->get('utm_source')) {
            $this->filters['utm_source'] = $utm_source;
            $this->design->assign('utm_source', $utm_source);
        }

        if ($status = $this->request->get('status')) {
            $this->filters['status'] = $status;
            $this->design->assign('status', $status);
        }

        if ($client_type = $this->request->get('client_type')) {
            $this->filters['client_type'] = $client_type;
            $this->design->assign('client_type', $client_type);
        }

        if ($order_type = $this->request->get('order_type')) {
            $this->filters['order_type'] = $order_type;
            $this->design->assign('order_type', $order_type);
        }

        if ($date_type = $this->request->get('date_type')) {
            $this->filters['date_type'] = $date_type;
            $this->design->assign('date_type', $date_type);
        }
    }

    /**
     * Получаем данные
     * @return void
     */
    private function getData(?int $offset = null, int $limit = 0)
    {
        $where = [];

        if (is_null($offset)) {
            $current_page = $this->request->get('page', 'integer');
            $offset = $this->limit * (max(1, $current_page) - 1);
        }

        $query = $this->db->placehold("
                SELECT 
                    o.webmaster_id,
                    o.utm_source,
                    o.click_hash,
                    o.loan_type,
                    o.id AS order_id,
                    o.1c_id,
                    o.date AS order_date,
                    o.status,
                    r.admin_name as reason,
                    o.1c_status AS status_1c,
                    o.confirm_date,
                    o.leadgid_postback_date,
                    o.leadgen_postback,
                    o.payout_grade,
                    o.amount,
                    sod.value as switch_parent_order_id,
                    IF(p.id IS NOT NULL, 1, NULL) as postback_hold,
                    o.scorista_ball
                FROM __orders AS o
                LEFT JOIN __postback as p ON p.order_id = o.id AND p.type = 'hold'
                LEFT JOIN __reasons as r ON r.id = o.reason_id
                LEFT JOIN __users u ON u.id = o.user_id
                LEFT JOIN __order_data sod ON sod.order_id = o.id AND sod.key = 'order_org_switch_parent_order_id'
                WHERE 1=1 
                -- {{where}}
                ORDER BY o.id ASC
                LIMIT ?, ?
        ", $offset, $limit ?: $this->limit);

        $where[] = $this->getDateFilter();

        if (!empty($this->filters['status'])) {
            $where[] = $this->db->placehold("o.status = ?", $this->filters['status']);
        }

        if (!empty($this->filters['utm_source'])) {
            $where['utm_source'] = $this->db->placehold("o.utm_source = ?", $this->filters['utm_source']);
        } else {
            $where[] = $this->db->placehold("EXISTS (SELECT * FROM s_order_data od1 WHERE od1.order_id = o.id AND od1.key = ?)", $this->ping3_data::ORDER_FROM_PARTNER);
        }

        if (!empty($this->filters['client_type'])) {
            $where[] = $this->db->placehold("EXISTS (SELECT * FROM s_order_data od2 WHERE od2.order_id = o.id AND od2.key = ? AND od2.value = ?)", Ping3Data::PING3_USER_STATUS, $this->filters['client_type']);
        }

        if (!empty($this->filters['order_type'])) {
            $where[] = $this->db->placehold("EXISTS (SELECT * FROM s_order_data od3 WHERE od3.order_id = o.id AND od3.key = ? AND od3.value = 1)", $this->filters['order_type']);

            if (!empty($this->filters['utm_source'])) {
                $where['utm_source'] = $this->db->placehold("EXISTS (SELECT * FROM s_order_data od4 WHERE od4.order_id = o.id AND od4.key = ? AND od4.value = ?)", $this->ping3_data::ORDER_FROM_PARTNER, $this->filters['utm_source']);
            }
        }

        $query = strtr($query, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);
        $report = $this->db->results();

        if (!empty($report)) {
            array_walk($report, function (&$item) {
                if ($item->switch_parent_order_id) {
                    $parentOrder = $this->orders->get_order($item->switch_parent_order_id);

                    $item->webmaster_id = $parentOrder->webmaster_id;
                    $item->order_id = $parentOrder->order_id;
                    $item->id_1c = $parentOrder->id_1c;
                    $item->click_hash = $parentOrder->click_hash;
                    $item->date = $parentOrder->date;
                    $item->scorista_ball = $parentOrder->scorista_ball;
                }
            });
        }

        $this->design->assign('items', $report);
        $this->items = $report;
    }

    private function getDateFilter()
    {
        if (!empty($this->filters['date_type'])) {
            return $this->db->placehold("o." . $this->filters['date_type'] . " >= ? AND o." . $this->filters['date_type'] . " <= ?", $this->filters['date_from'], $this->filters['date_to']);
        } else {
            return $this->db->placehold("o.date >= ? AND o.date <= ?", $this->filters['date_from'], $this->filters['date_to']);
        }
    }

    private function getTotals()
    {
        $where = [];

        $query = $this->db->placehold("
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(o.amount) as total_amount,
                    SUM(o.payout_grade) as total_payout_grade,
                    SUM(IF(o.leadgen_postback IS NOT NULL AND o.leadgen_postback != '', 1, 0)) as total_leadgen_postback
                FROM __orders AS o
                WHERE 1 = 1
                -- {{where}}
        ");

        $where[] = $this->getDateFilter();

        if (!empty($this->filters['status'])) {
            $where[] = $this->db->placehold("o.status = ?", $this->filters['status']);
        }

        if (!empty($this->filters['utm_source'])) {
            $where['utm_source'] = $this->db->placehold("o.utm_source = ?", $this->filters['utm_source']);
        } else {
            $where[] = $this->db->placehold("EXISTS (SELECT * FROM s_order_data od1 WHERE od1.order_id = o.id AND od1.key = ?)", $this->ping3_data::ORDER_FROM_PARTNER);
        }

        if (!empty($this->filters['client_type'])) {
            $where[] = $this->db->placehold("EXISTS (SELECT * FROM s_order_data od2 WHERE od2.order_id = o.id AND od2.key = ? AND od2.value = ?)", Ping3Data::PING3_USER_STATUS, $this->filters['client_type']);
        }

        if (!empty($this->filters['order_type'])) {
            $where[] = $this->db->placehold("EXISTS (SELECT * FROM s_order_data od3 WHERE od3.order_id = o.id AND od3.key = ? AND od3.value = 1)", $this->filters['order_type']);

            if (!empty($this->filters['utm_source'])) {
                $where['utm_source'] = $this->db->placehold("EXISTS (SELECT * FROM s_order_data od4 WHERE od4.order_id = o.id AND od4.key = ? AND od4.value = ?)", $this->ping3_data::ORDER_FROM_PARTNER, $this->filters['utm_source']);
            }
        }

        $query = strtr($query, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);

        $this->totals = $this->db->result();
        $this->design->assign('totals', $this->totals);
    }

    private function download()
    {
        $filename = 'files/reports/' . ($this->filters['utm_source'] ?? 'all_leads') . '.xls';
        require_once $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();

        $active_sheet->setTitle(" " . date('Y-m-d', strtotime($this->filters['date_from'])) . "_" . date('Y-m-d', strtotime($this->filters['date_to'])));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $active_sheet->getColumnDimension('A')->setWidth(15);
        $active_sheet->getColumnDimension('B')->setWidth(15);
        $active_sheet->getColumnDimension('C')->setWidth(15);
        $active_sheet->getColumnDimension('D')->setWidth(20);
        $active_sheet->getColumnDimension('E')->setWidth(20);
        $active_sheet->getColumnDimension('F')->setWidth(15);
        $active_sheet->getColumnDimension('G')->setWidth(20);
        $active_sheet->getColumnDimension('H')->setWidth(10);
        $active_sheet->getColumnDimension('I')->setWidth(10);
        $active_sheet->getColumnDimension('J')->setWidth(25);
        $active_sheet->getColumnDimension('K')->setWidth(25);
        $active_sheet->getColumnDimension('L')->setWidth(25);
        $active_sheet->getColumnDimension('M')->setWidth(25);
        $active_sheet->getColumnDimension('N')->setWidth(25);
        $active_sheet->getColumnDimension('O')->setWidth(25);

        $active_sheet->setCellValue('A1', 'ID вебмастера');
        $active_sheet->setCellValue('B1', 'Источник');
        $active_sheet->setCellValue('C1', 'кликхеш');
        $active_sheet->setCellValue('D1', 'ID заявки');
        $active_sheet->setCellValue('E1', '1C id заявки');
        $active_sheet->setCellValue('F1', 'Дата заявки');
        $active_sheet->setCellValue('G1', 'Статус');
        $active_sheet->setCellValue('H1', 'Дата выдачи');
        $active_sheet->setCellValue('I1', 'Скориста');
        $active_sheet->setCellValue('J1', 'Выдача');
        $active_sheet->setCellValue('K1', 'Постбэк о выдачи НК');
        $active_sheet->setCellValue('L1', 'Постбэк Заявка НК');
        $active_sheet->setCellValue('M1', 'Причина отказа');
        $active_sheet->setCellValue('N1', 'Тип заявки');
        $active_sheet->setCellValue('O1', 'Сумма заявки');

        $i = 2;

        $step = 0;
        $limit = 1000;
        $offset = 0;
        $this->getData($offset, $limit);

        do {
            foreach ($this->items as $item)
            {
                $col = 'A';
                if ($item->status == 1)
                    $status = 'Новая';
                elseif ($item->status == 2)
                    $status = 'Одобрена';
                elseif ($item->status == 3)
                    $status = 'Отказ';

                $active_sheet->setCellValue($col++.$i, $item->webmaster_id);
                $active_sheet->setCellValue($col++.$i, $item->utm_source);
                $active_sheet->setCellValue($col++.$i, $item->click_hash);
                $active_sheet->setCellValue($col++.$i, $item->order_id);
                $active_sheet->setCellValue($col++.$i, $item->{'1c_id'});
                $active_sheet->setCellValue($col++.$i, date('d.m.Y H:i:s', strtotime($item->order_date)));
                $active_sheet->setCellValue($col++.$i, $status);
                $active_sheet->setCellValue($col++.$i, empty($item->confirm_date) ? '' : date('d.m.Y H:i:s', strtotime($item->confirm_date)));
                $active_sheet->setCellValue($col++.$i, $item->scorista_ball);
                $active_sheet->setCellValue($col++.$i, $item->payout_grade);
                $active_sheet->setCellValue($col++.$i, $item->leadgen_postback);
                $active_sheet->setCellValue($col++.$i, $item->postback_hold);
                $active_sheet->setCellValue($col++.$i, $item->reason);
                $active_sheet->setCellValue($col++.$i, $item->loan_type);
                $active_sheet->setCellValue($col++.$i, $item->amount);

                $i++;
            }
            $step++;
            $offset = $limit * $step;
            $this->getData($offset, $limit);
        } while (!empty($this->items));

        $objWriter = PHPExcel_IOFactory::createWriter($excel,'Excel5');
        //$objWriter->save($this->config->root_dir . $filename);

        // Отправляем заголовки
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Выводим файл напрямую в вывод
        $objWriter->save('php://output');
        exit;
    }
}
