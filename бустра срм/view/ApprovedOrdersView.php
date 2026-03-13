<?php

require_once 'View.php';

class ApprovedOrdersView extends View
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
        $conds  = [
            'NK' => 'AND nk_pk.confirmed IS NULL',
            'PK' => 'AND nk_pk.confirmed IS NOT NULL',
        ];
        $source = $this->request->get('client_type');
        $date_limit  = (new Datetime())->sub(new DateInterval('P7D'))->format('Y-m-d');
        $source_cond = $source ? $conds[$source] : '';

        $utm_source = $this->request->get('utm_source');
        if (!empty($utm_source)) {
            $source_cond .= $this->db->placehold(" AND o.utm_source = ?", $utm_source);
        }

        #$date_limit = (new Datetime($this->request->get('datelimit')))->format('Y-m-d');
        $queryAll = $this->db->placehold("SELECT
                                            CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) fullname,
                                            u.phone_mobile,
                                            o.utm_source,
                                            DATE_ADD(o.approve_date, INTERVAL 7 DAY) fin
                                        FROM s_orders o
                                        JOIN s_users u
                                            ON u.id = o.user_id
                                        JOIN (SELECT 
                                                    o.user_id,
                                                    GROUP_CONCAT(o.confirm_date) confirmed
                                                FROM s_orders o
                                                GROUP BY o.user_id) nk_pk
                                            ON nk_pk.user_id = o.user_id
                                        WHERE o.approve_date >= ?
                                            AND o.reject_date IS NULL
                                            AND o.confirm_date IS NULL
                                            AND o.1c_status = '3.Одобрено'
                                            $source_cond", $date_limit);
        $this->db->query('SET group_concat_max_len = 1000000;');
        $this->db->query($queryAll);
        
        $report = $this->db->results();
        $client_types = [
            ['type' => 'NK', 'title' => 'НК'],
            ['type' => 'PK', 'title' => 'ПК'],
        ];

        $utm_sources = $this->getUtmSources();

        return compact('report', 'client_types', 'utm_sources', 'utm_source', 'date_limit', 'source');
    }

    private function getUtmSources()
    {
        $query = $this->db->placehold("SELECT utm_source
            FROM s_orders
            WHERE reject_date IS NULL AND confirm_date IS NULL AND 1c_status = '3.Одобрено' GROUP BY utm_source");
        $this->db->query($query);
        return $this->db->results();
    }

    public function fetch()
    {
        [$report, $client_types, $utm_sources, $utm_source, $date_limit, $source] = array_values($this->getData());

        $this->design->assign('client_types', $client_types);
        $this->design->assign('utm_sources', $utm_sources);
        $this->design->assign('utm_sources', $utm_sources);
        $this->design->assign('filter_utm_source', $utm_source);
        $this->design->assign('filter_source', $source);
        $this->design->assign('report', $report);
        $this->design->assign('report_uri', strtok($_SERVER['REQUEST_URI'], '?'));

        return $this->design->fetch('approved_orders.tpl');
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

        $filename = 'files/reports/approved_orders.xls';
        $excel    = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        
        [$report, $client_types, $date_limit, $source] = array_values($this->getData());

        $active_sheet->setTitle('ApprovedOrders' . $source);

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $active_sheet->getColumnDimension('A')->setWidth(33);
        $active_sheet->getColumnDimension('B')->setWidth(20);
        $active_sheet->getColumnDimension('C')->setWidth(20);
        $active_sheet->getColumnDimension('D')->setWidth(20);

        $active_sheet->setCellValue('A1', 'ФИО');
        $active_sheet->setCellValue('B1', 'Телефон');
        $active_sheet->setCellValue('C1', 'Источник данных');
        $active_sheet->setCellValue('D1', 'Дата окончания');

        $active_sheet->fromArray(array_map(fn($item) => (array)$item, $report), null, 'A2');

        $active_sheet->getColumnDimension()->setAutoSize(true);
        // перенос слов
        $row_count = $active_sheet->getHighestRow();
        $active_sheet->getStyle("A1:C" . $row_count)->getAlignment()->setWrapText(true);

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename);
        exit;
    }
}