<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', '600');

require_once 'View.php';
require_once dirname(__DIR__) . '/api/Helpers.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 *
 *
 */
class ReportWeekNKView extends View
{
    // use api\traits\FunnelLoansReportTrait;
    public function fetch()
    {
        if ($this->manager->role !== 'discharge'){
            die('access denied');
        }
        if ($this->request->get('ajax', 'boolean')) {

            if ($this->request->get('download', 'boolean')) {
                $this->download();
            }
        }
        return $this->design->fetch('report_week.tpl');
    }

    private function download()
    {
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');


        $sheet_name = 'test';

        $writer = new XLSXWriter();
        $header = ['user_id'=>'number','fio' => 'string', 'phone' => "string", 'amount' => 'number'];
        $writer->writeSheetHeader($sheet_name, $header);
        $type_report = $this->request->get('type_report');
        $date_filter = Helpers::getDataRange($this);
        switch ($type_report) {
            case 'NK':
                $filter = "AND have_close_credits = 0
                AND (o.1c_status IN ('5.Выдан', '6.Закрыт') OR (o.status = 10 AND o.1c_status != '6.Закрыт'))";
                break;
            case 'PK':
                $filter = "AND have_close_credits = 1
                AND (o.1c_status IN ('5.Выдан', '6.Закрыт') OR (o.status = 10 AND o.1c_status != '6.Закрыт'))";
                break;
            case 'reject':
                $filter = "AND o.status = 3";
                break;
        }
        $filename = "files/reports/{$date_filter['filter_date_start']}_{$date_filter['filter_date_end']}_{$type_report}_week.xls";
        $sql = "SELECT
           DISTINCT u.id,
                     CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) as fio,
                     u.phone_mobile,
                     o.amount
        FROM s_orders o
                 LEFT JOIN s_users u ON u.id = o.user_id
        WHERE DATE(o.date) >= ? AND DATE(o.date) <= ?
            $filter";
        $query = $this->db->placehold($sql, $date_filter['filter_date_start'], $date_filter['filter_date_end']);
        //var_dump([$query, $filter, $type_report]); die();
        $this->db->query($query);
        $items = $this->db->results();
        foreach ($items as $item) {
            $writer->writeSheetRow($sheet_name, (array)$item);
        }
        $writer->writeToFile($this->config->root_dir . '/' . $filename);
        header('Location:' . $this->config->root_url . '/' . $filename);
        exit;
    }
}
