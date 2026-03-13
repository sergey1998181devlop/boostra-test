<?php
error_reporting(0);
ini_set('display_errors', 'Off');
require_once 'View.php';

class LeadgidReportView extends View
{
    public function fetch()
    {
        if ($daterange = $this->request->get('daterange'))
        {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

            $leadgen = $this->request->get('leadgen');

            if (empty($leadgen)) {
                $leadgen = 'leadgid';
            }

            $this->design->assign('leadgen', $leadgen);

            $query = $this->db->placehold("
                SELECT 
                    o.webmaster_id,
                    o.utm_source,
                    o.click_hash,
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
                    IF(p.id IS NOT NULL, 1, NULL) as postback_hold,
                    (
                        SELECT scorista_ball 
                        FROM __scorings AS s
                        WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                        AND status = ".$this->scorings::STATUS_COMPLETED."
                        AND s.order_id = o.id
                        ORDER BY s.id DESC
                        LIMIT 1
                    ) AS scorista_ball
                FROM __orders AS o
                LEFT JOIN __postback as p ON p.order_id = o.id AND p.type = 'hold'
                LEFT JOIN __reasons as r ON r.id = o.reason_id
                LEFT JOIN __users AS u
                ON u.id = o.user_id
                WHERE o.utm_source = ?
                AND o.have_close_credits = 0
                AND DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND u.card_added = 1
                AND u.additional_data_added = 1
                ORDER BY o.date
            ", $leadgen, $date_from, $date_to);
            $this->db->query($query);

            $report = $this->db->results();

            $this->design->assign('report', $report);

            $totals = [
                'orders' => count($report),
                'pays' => 0,
                'leadgen_postback' => 0,
                'postback_hold' => 0,
            ];

            foreach ($report as $item) {
                if (!empty($item->leadgen_postback) && $item->status == 2) {
                    $item->payout_grade = $this->orders->getPayoutGrade($item);
                }

                if ($item->leadgen_postback) {
                    $item->leadgen_postback = 1;
                }

                $totals['pays'] += $item->payout_grade ?? 0;
                $totals['leadgen_postback'] += (int)$item->leadgen_postback;
                $totals['postback_hold'] += (int)$item->postback_hold;
            }

            $this->design->assign('totals', $totals);

            if ($this->request->get('download') == 'excel')
            {
                $filename = 'files/reports/'.$leadgen.'.xls';
                require $this->config->root_dir.'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle(" ".$from."-".$to);

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

                $i = 2;
                foreach ($report as $item)
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

                    $i++;
                }

                $objWriter = PHPExcel_IOFactory::createWriter($excel,'Excel5');

                $objWriter->save($this->config->root_dir.$filename);
                
                header('Location:'.$this->config->root_url.'/'.$filename);
                exit;
            }            
            
        }            
        return $this->design->fetch('leadgid_report.tpl');
    }
    
}
