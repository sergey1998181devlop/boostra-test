<?php

require_once 'View.php';

class NKtoPKReportView extends View
{
    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');

        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    public function getData()
    {
        $report = [];
        $totals = [];
        $from  = '';
        $to    = '';
        $date_from  = '';
        $date_to    = '';
        $datelimit  = '';
        $date_limit = '';
        $source     = $this->request->get('utm_source');
        if (($daterange = $this->request->get('daterange'))
            && ($datelimit = $this->request->get('datelimit')))
        {
            list($from, $to) = explode('-', $daterange);
            
        	$date_from  = (new Datetime($from))->format('Y-m-d 00:00:00');
        	$date_to    = date('Y-m-d', strtotime($to)) . ' 23:59:59';
        	$date_limit = date('Y-m-d', strtotime($datelimit)) . ' 23:59:59';

            $max_depth = 4;
            $source_cond = $source ? 'AND o.utm_source = ?' : '';
            $utm_field   = $source ? 'o.webmaster_id' : 'o.utm_source';
            $queryAll    = $this->db->placehold("SELECT
                                                    u.id
                                                    , u.loan_history
                                                    , GROUP_CONCAT(v.created) visits
                                                    , GROUP_CONCAT(CONCAT(IFNULL(o.date, ''), CHAR(0),
                                                                          IFNULL(o.reject_date, ''), CHAR(0),
                                                                          IFNULL(o.confirm_date, ''), CHAR(0),
                                                                          IFNULL($utm_field, '')) SEPARATOR '\n') o_data
                                                FROM s_users u
                                                JOIN s_orders o
                                                    ON o.user_id = u.id
                                                LEFT JOIN s_visitors v
                                                    ON v.user_id = u.id
                                                WHERE
                                                    u.loan_history IS NOT NULL AND
                                                    u.loan_history <> '[]' AND
                                                    STR_TO_DATE(JSON_EXTRACT(u.loan_history, '$[0].close_date'),
                                                                '\"%Y-%m-%dT%T\"') BETWEEN ? AND ?
                                                    $source_cond
                                                GROUP BY u.id", $date_from, $date_to, $source);
            $this->db->query('SET group_concat_max_len = 1000000;');
            $this->db->query($queryAll);
            
            $results = $this->db->results();
            foreach ($results as $result) {
                $user_loans  = json_decode($result->loan_history, true);
                $user_loans  = array_slice($user_loans, 0, $max_depth);
                $user_orders = explode("\n", $result->o_data);
                $user_orders = array_map(function($item) { return explode(chr(0), $item); }, $user_orders);
                $user_visits = array_map(function($item) { return (new Datetime($item))->format('Y-m-d H:i:s'); },
                                         explode(",", $result->visits));
                $utm_source  = null;
                foreach($user_loans as $depth => $loan) {
                    $last_order = [];
                    $loan_date  = (new Datetime($loan['date']))->format('Y-m-d H:i:s');
                    $loan_close = $loan['close_date'] ? (new Datetime($loan['close_date']))->format('Y-m-d H:i:s') : '';
                    foreach($user_orders as $index => $order) {
                        $order_date = (new Datetime($order[0]))->format('Y-m-d H:i:s');
                        if($order_date > $loan_date) {
                            break;
                        }
                        $last_order = $order;
                    }
                    $utm_source = ($utm_source ?? end($last_order)) ?: 'Нет источника';
                    $report[$utm_source] = $report[$utm_source] ?? [
                                                                        'source' => $utm_source,
                                                                        'nk' => 0,
                                                                        'visits1' => 0,
                                                                        'orders1' => 0,
                                                                        'pk1' => 0,
                                                                        'visits2' => 0,
                                                                        'orders2' => 0,
                                                                        'pk2' => 0,
                                                                        'visits3' => 0,
                                                                        'orders3' => 0,
                                                                        'pk3' => 0,
                                                                    ];
                    if($depth == 0) {
                        $report[$utm_source]['nk']++;
                    } elseif($loan_date <= $date_limit) {
                        $report[$utm_source]["pk{$depth}"]++;
                    }
                    $report[$utm_source]['orders' . ($depth + 1)] += ($index < count($user_orders) - 1);
                    if($loan_close && $loan_close <= $date_limit) {
                        $after_visits = array_filter($user_visits, function($item) use ($loan_close) {return $item > $loan_close;});
                        $report[$utm_source]['visits' . ($depth + 1)] += !empty($after_visits);
                    }
                }
            }
            $totals = array_reduce($report, function($carry, $item) {
                foreach(array_keys($carry) as $key) {
                    $carry[$key] += $item[$key];
                }
                return $carry;
            }, [
                'nk' => 0,
                'visits1' => 0,
                'orders1' => 0,
                'pk1' => 0,
                'visits2' => 0,
                'orders2' => 0,
                'pk2' => 0,
                'visits3' => 0,
                'orders3' => 0,
                'pk3' => 0,
            ]);
        }
        $querySources = $this->db->placehold("SELECT utm_source AS source FROM s_orders GROUP BY utm_source");
        $this->db->query($querySources);
            
        $sources = $this->db->results();

        return compact('report', 'totals', 'date_from', 'date_to', 'date_limit',
                        'from', 'to', 'datelimit', 'sources', 'source');
    }

    public function fetch()
    {
        [$report, $totals, $date_from, $date_to, $date_limit, 
            $from, $to, $datelimit, $sources, $source] = array_values($this->getData());

        $this->design->assign('date_from', $date_from);
        $this->design->assign('date_to', $date_to);
        $this->design->assign('from', $from);
        $this->design->assign('to', $to);
        $this->design->assign('datelimit', $datelimit);
        $this->design->assign('sources', $sources);
        $this->design->assign('filter_source', $source);
        $this->design->assign('report', $report);
        $this->design->assign('totals', $totals);
        $this->design->assign('report_uri', strtok($_SERVER['REQUEST_URI'], '?'));

        return $this->design->fetch('nk_to_pk_report.tpl');
    }

    /**
     * Выгрузка данных в Excel
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function download()
    {
        require_once $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';
        error_reporting(0);
        ini_set('display_errors', 'Off');

        $filename = 'files/reports/download_nk_to_pk_report.xls';
        $excel    = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        
        [$report, $totals, $date_from, $date_to, $date_limit, 
            $from, $to, $datelimit, $sources, $source] = $this->getData();

        $active_sheet->setTitle(" $from_$to_$datelimit");

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $active_sheet->getColumnDimension('A')->setWidth(10);
        $active_sheet->getColumnDimension('B')->setWidth(20);
        $active_sheet->getColumnDimension('C')->setWidth(20);
        $active_sheet->getColumnDimension('D')->setWidth(20);
        $active_sheet->getColumnDimension('E')->setWidth(20);
        $active_sheet->getColumnDimension('F')->setWidth(20);

        $active_sheet->setCellValue('A1', 'Источник/web-id' . ($source ? "($source)" : ''));
        $active_sheet->setCellValue('B1', 'НК закрылись');
        $active_sheet->setCellValue('C1', 'Заходил в лк после закрытия 1-го договора');
        $active_sheet->setCellValue('D1', 'Заявки ПК1');
        $active_sheet->setCellValue('E1', 'ПК1');
        $active_sheet->setCellValue('F1', 'Заходил в лк после закрытия 2-го договора');
        $active_sheet->setCellValue('G1', 'Заявки ПК2');
        $active_sheet->setCellValue('H1', 'ПК2');
        $active_sheet->setCellValue('I1', 'Заходил в лк после закрытия 3-го договора');
        $active_sheet->setCellValue('J1', 'Заявки ПК3');
        $active_sheet->setCellValue('K1', 'ПК3');

        // преобразуем каждый элемент в массив
        $report_columns = [
            'source' => '',
            'nk' => 0,
            'visits1' => 0,
            'orders1' => 0,
            'pk1' => 0,
            'visits2' => 0,
            'orders2' => 0,
            'pk2' => 0,
            'visits3' => 0,
            'orders3' => 0,
            'pk3' => 0,
        ];
        $excell_data   = array_map(fn($item) => array_intersect_key($item, $report_columns), $report);
        $excell_data[] = array_merge(['Итого'], array_intersect_key($totals, $report_columns));

        $active_sheet->fromArray($excell_data, null, 'A2');

        //$active_sheet->getColumnDimension()->setAutoSize(true);
        // перенос слов
        $row_count = $active_sheet->getHighestRow();
        $active_sheet->getStyle("A1:K" . $row_count)->getAlignment()->setWrapText(true);

        // покрасим последнюю строку
        $active_sheet->getStyle('A'. $row_count.':'.'K'.$row_count)->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFDD00')
                )
            )
        );

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename);
        exit;
    }
}