<?php

ini_set('max_execution_time', '1200');
date_default_timezone_set('Europe/Moscow');

use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__FILE__) . '/../api/Simpla.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';

/**
* Send an email with orders reports
 */
class SendOrdersReport extends Simpla
{
    private const SMTP_HOST = 'smtp.yandex.ru';
    private const SMTP_MAIL = 'sv@boostra.ru';
    private const SMTP_PASSWORD = 'SVB163(hj9';
    private const RECIPIENTS_EMAILS = [
        'ek.zhilina@boostra.ru',
        'ol.romanovapz@gmail.com',
        'popova.a@boostra.ru',
        'ol.gerdt@boostra.ru',
        'gar63@list.ru',
        'yurovskiy@boostra.ru'
    ];
    private const PK_USER_TYPE = 'ПК';
    private const NK_USER_TYPE = 'НК';
    private string $firstDayOfMonth;
    private string $yesterday;
    private int $yesterday_timestamp;
    private string $filePath;

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws PHPExcel_Reader_Exception
     */
    public function run()
    {
        $this->yesterday = date('Y-m-d', strtotime('-1 days'));
        $this->yesterday_timestamp = strtotime($this->yesterday . ' 23:59:59');
        $this->firstDayOfMonth = date('Y-m-01', strtotime($this->yesterday));
        $fileName = 'files/reports/Отчёт по заявкам за ' . $this->yesterday . ' из CRM.xls';
        $this->filePath = $this->config->root_dir . $fileName;

        $orders = $this->getOrders();
        $confirmedOrders = $this->getConfirmedOrders();

        $this->createExcelReport($orders, $confirmedOrders);
        $this->sendEmail();

        // Deletes a report form the server
        unlink($this->filePath);
    }

    /**
     * Create and save excel report
     *
     * @param array $orders
     * @param array $confirmedOrders
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function createExcelReport(array $orders, array $confirmedOrders): void
    {
        $currentMonthReport = [];
        $yesterdayReport = [];
        foreach ($orders as $order) {
            $orderType = $order->have_close_credits == 1 ? self::PK_USER_TYPE : self::NK_USER_TYPE;
            if (date('Y-m-d', strtotime($order->date)) === $this->yesterday) {
                $this->getOrdersReport($order, $yesterdayReport, $orderType);
            }

            $this->getOrdersReport($order, $currentMonthReport, $orderType);
        }

        foreach ($confirmedOrders as $confirmedOrder) {
            if ($confirmedOrder->filter_date === $this->yesterday) {
                if ((int)$confirmedOrder->have_close_credits === 1) {
                    $yesterdayReport['pkOrders']['total_sum_confirmed_day'] = (int)$confirmedOrder->orders_sum;
                } else {
                    $yesterdayReport['nkOrders']['total_sum_confirmed_day'] = (int)$confirmedOrder->orders_sum;
                }
            }

            if ((int)$confirmedOrder->have_close_credits === 1) {
                $currentMonthReport['pkOrders']['total_sum_confirmed_day'] += $confirmedOrder->orders_sum;
            } else {
                $currentMonthReport['nkOrders']['total_sum_confirmed_day'] += $confirmedOrder->orders_sum;
            }
        }

        ini_set('memory_limit', '280M');
        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();

        $activeSheet->setTitle('MissingsReport');
        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $rowNumber = 1;
        $activeSheet->mergeCells("A{$rowNumber}:G{$rowNumber}");
        $activeSheet->setCellValue("A{$rowNumber}", "Ежедневный отчет по заявкам")
            ->getStyle()->getFont()->setBold();

        $rowNumber += 2;
        $activeSheet->mergeCells("A{$rowNumber}:G{$rowNumber}");
        $activeSheet->setCellValue("A{$rowNumber}", "Итого за день")->getStyle()->getFont()->setBold();
        $rowNumber++;
        $this->fillTotalReport($activeSheet, $yesterdayReport, $rowNumber);
        $this->fillSourceReport($activeSheet, $yesterdayReport, $rowNumber, self::NK_USER_TYPE);
        $this->fillSourceReport($activeSheet, $yesterdayReport, $rowNumber, self::PK_USER_TYPE);

        $rowNumber += 2;
        $activeSheet->mergeCells("A{$rowNumber}:G{$rowNumber}");
        $activeSheet->setCellValue("A{$rowNumber}", "Итого нарастающим итогом с 1 числа месяца")
            ->getStyle()->getFont()->setBold();
        $rowNumber++;
        $this->fillTotalReport($activeSheet, $currentMonthReport, $rowNumber);
        $this->fillSourceReport($activeSheet, $currentMonthReport, $rowNumber, self::NK_USER_TYPE);
        $this->fillSourceReport($activeSheet, $currentMonthReport, $rowNumber, self::PK_USER_TYPE);

        $activeSheet->getColumnDimension()->setAutoSize(true);
        $rowCount = $activeSheet->getHighestRow();
        $activeSheet->getStyle("A1:M" . $rowCount)->getAlignment()->setWrapText(true);

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->filePath);
    }

    /**
     * Sena an email with generated report
     *
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendEmail(): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = self::SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = self::SMTP_MAIL;
        $mail->Password = self::SMTP_PASSWORD;
        $mail->SMTPSecure = 'TLS';
        $mail->Port = 587;

        $mail->setFrom(self::SMTP_MAIL, 'Ежедневная отчётность');
        foreach (self::RECIPIENTS_EMAILS as $email) {
            $mail->addAddress($email);
        }

        $mail->addAttachment($this->filePath);
        $mail->isHTML(true);
        $mail->Subject = "Отчетность за день по заявкам Boostra - CRM. " . date('d.m.Y', strtotime($this->yesterday));
        $mail->Body = '<b>Ежедневный отчет по заявкам</b>';
        $mail->send();
    }

    /**
     * Create orders report
     *
     * @param StdClass $order
     * @param array $report
     * @param string $type
     * @return void
     */
    private function getOrdersReport(StdClass $order, array &$report, string $type): void
    {
        $reportType = $type === self::PK_USER_TYPE ? 'pkOrders' : 'nkOrders';
        if (!isset($report[$reportType])) {
            $report[$reportType]['total'] = 0;
            $report[$reportType]['total_approved'] = 0;
            $report[$reportType]['total_confirmed'] = 0;
            $report[$reportType]['total_sum_day'] = 0;
            $report[$reportType]['total_sum_confirmed_day'] = 0;
            $report[$reportType]['total_sum_with_cd_day'] = 0;
        }

        if (!isset($report[$reportType]['utm_sources'][$order->utm_source])) {
            $report[$reportType]['utm_sources'][$order->utm_source]['name'] = $order->utm_source;
            $report[$reportType]['utm_sources'][$order->utm_source]['total'] = 0;
            $report[$reportType]['utm_sources'][$order->utm_source]['total_approved'] = 0;
            $report[$reportType]['utm_sources'][$order->utm_source]['total_confirmed'] = 0;
        }

        $report[$reportType]['total']++;
        $report[$reportType]['utm_sources'][$order->utm_source]['total']++;
        if ($order->approve_date) {
            $report[$reportType]['total_approved']++;
            $report[$reportType]['utm_sources'][$order->utm_source]['total_approved']++;
        }

        $creditDoctorAmounts = $this->getCreditDoctorAmount($order->id);
        $creditDoctorTotal = 0;
        foreach ($creditDoctorAmounts as $amountData) {
            $creditDoctorTotal += $amountData->amount;
        }

        if ($order->confirm_date && $order->status == 10) {
            $confirm_date = strtotime($order->confirm_date);
            if ($confirm_date <= $this->yesterday_timestamp) {
                $report[$reportType]['total_sum_day'] += $order->amount;
                $report[$reportType]['total_sum_with_cd_day'] += $order->amount + $creditDoctorTotal;
                $report[$reportType]['total_confirmed']++;
                $report[$reportType]['utm_sources'][$order->utm_source]['total_confirmed']++;
            }
        }
    }

    /**
     * Fill report with totals in the Excel file
     *
    * @param $activeSheet
    * @param $report
    * @param int $rowNumber
    * @return void
     */
    private function fillTotalReport($activeSheet, $report, int &$rowNumber): void
    {
        $rowNumber++;

        $startRowNumber = $rowNumber;
        $activeSheet->setCellValue("B" . $rowNumber, "ПК");
        $activeSheet->setCellValue("C" . $rowNumber, "НК");
        $activeSheet->setCellValue("D" . $rowNumber, "Всего");

        $total_pk_confirmed = $report['pkOrders']['total_confirmed'];
        $total_nk_confirmed = $report['nkOrders']['total_confirmed'];
        $total_confirmed = $total_nk_confirmed + $total_pk_confirmed;

        $total_pk_approved = $report['pkOrders']['total_approved'];
        $total_nk_approved = $report['nkOrders']['total_approved'];
        $total_approved = $total_nk_approved + $total_pk_approved;

        $total_pk_orders = $report['pkOrders']['total'];
        $total_nk_orders = $report['nkOrders']['total'];
        $total_orders = $total_nk_orders + $total_pk_orders;

        $rowNumber++;
        $activeSheet->setCellValue("A" . $rowNumber, "Заявки, шт.");
        $activeSheet->setCellValue("B" . $rowNumber, $total_pk_orders);
        $activeSheet->setCellValue("C" . $rowNumber, $total_nk_orders);
        $activeSheet->setCellValue("D" . $rowNumber, $total_orders);

        $rowNumber++;
        $activeSheet->setCellValue("A" . $rowNumber, "Одобрение, шт.");
        $activeSheet->setCellValue("B" . $rowNumber, $total_pk_approved);
        $activeSheet->setCellValue("C" . $rowNumber, $total_nk_approved);
        $activeSheet->setCellValue("D" . $rowNumber, $total_approved);

        $rowNumber++;
        $activeSheet->setCellValue("A" . $rowNumber, "AR,%");
        $activeSheet->setCellValue("B" . $rowNumber, round($total_pk_orders ? $total_pk_approved * 100 / $total_pk_orders : 0, 2));
        $activeSheet->setCellValue("C" . $rowNumber, round($total_nk_orders ? $total_nk_approved * 100 / $total_nk_orders : 0, 2));
        $activeSheet->setCellValue("D" . $rowNumber, round($total_orders ? $total_approved * 100 / $total_orders : 0, 2));

        $rowNumber++;
        $activeSheet->setCellValue("A" . $rowNumber, "Выдано, шт.");
        $activeSheet->setCellValue("B" . $rowNumber, $total_pk_confirmed);
        $activeSheet->setCellValue("C" . $rowNumber, $total_nk_confirmed);
        $activeSheet->setCellValue("D" . $rowNumber, $total_confirmed);

        $rowNumber++;
        $activeSheet->setCellValue("A" . $rowNumber, "TR,%");
        $activeSheet->setCellValue("B" . $rowNumber, round($total_pk_approved ? $total_pk_confirmed * 100 / $total_pk_approved : 0, 2));
        $activeSheet->setCellValue("C" . $rowNumber, round($total_nk_approved ? $total_nk_confirmed * 100 / $total_nk_approved : 0, 2));
        $activeSheet->setCellValue("D" . $rowNumber, round($total_approved ? $total_confirmed * 100 / $total_approved : 0, 2));

        $rowNumber++;
        $activeSheet->setCellValue("A" . $rowNumber, "CV, %");
        $activeSheet->setCellValue("B" . $rowNumber, round($total_pk_orders ? $total_pk_confirmed * 100 / $total_pk_orders : 0, 2));
        $activeSheet->setCellValue("C" . $rowNumber, round($total_nk_orders ? $total_nk_confirmed * 100 / $total_nk_orders : 0, 2));
        $activeSheet->setCellValue("D" . $rowNumber, round($total_orders ? $total_confirmed * 100 / $total_orders : 0, 2));

        $rowNumber++;
        $activeSheet->setCellValue("A" . $rowNumber, "Выдано, руб.");
        $activeSheet->setCellValue("B" . $rowNumber, $report['pkOrders']['total_sum_day']);
        $activeSheet->setCellValue("C" . $rowNumber, $report['nkOrders']['total_sum_day']);
        $activeSheet->setCellValue("D" . $rowNumber, $report['pkOrders']['total_sum_day'] + $report['nkOrders']['total_sum_day']);

        $rowNumber++;
        $activeSheet->setCellValue("A" . $rowNumber, "Выдано всего, руб.");
        $activeSheet->setCellValue("B" . $rowNumber, $report['pkOrders']['total_sum_confirmed_day']);
        $activeSheet->setCellValue("C" . $rowNumber, $report['nkOrders']['total_sum_confirmed_day']);
        $activeSheet->setCellValue("D" . $rowNumber, $report['pkOrders']['total_sum_confirmed_day'] + $report['nkOrders']['total_sum_confirmed_day']);

        $rowNumber++;
        $activeSheet->setCellValue("A" . $rowNumber, "Выдано всего с КД, руб.");
        $activeSheet->setCellValue("B" . $rowNumber, $report['pkOrders']['total_sum_with_cd_day']);
        $activeSheet->setCellValue("C" . $rowNumber, $report['nkOrders']['total_sum_with_cd_day']);
        $activeSheet->setCellValue("D" . $rowNumber, $report['pkOrders']['total_sum_with_cd_day'] + $report['nkOrders']['total_sum_with_cd_day']);


        $cellsWithBorders = "A{$startRowNumber}:D{$rowNumber}";
        $this->setTableBorders($activeSheet, $cellsWithBorders);
    }

    /**
     * Fill report with NK/PK orders in the Excel file filtered by utm_source
     *
    * @param PHPExcel_Worksheet $activeSheet
    * @param array $report
    * @param int $rowNumber
    * @param string $type
    * @return void
     */
    private function fillSourceReport(
        PHPExcel_Worksheet $activeSheet,
        array $report,
        int &$rowNumber,
        string $type
    ): void {
        $rowNumber += 2;

        $startRowNumber = $rowNumber;
        $activeSheet->setCellValue("B" . $rowNumber, "Заявки $type, шт");
        $activeSheet->setCellValue("C" . $rowNumber, "Одобрено, шт.");
        $activeSheet->setCellValue("D" . $rowNumber, "AR,%");
        $activeSheet->setCellValue("E" . $rowNumber, "Выдано $type, шт");
        $activeSheet->setCellValue("F" . $rowNumber, "TR,%");
        $activeSheet->setCellValue("G" . $rowNumber, "CV в выдачу, %");

        $reportType = $type === self::PK_USER_TYPE ? 'pkOrders' : 'nkOrders';

        ksort($report[$reportType]['utm_sources'],  SORT_FLAG_CASE |  SORT_STRING);

        foreach ($report[$reportType]['utm_sources'] as $source) {
            $ar = 0;
            $tr = 0;
            $cv = 0;
            $rowNumber++;
            $activeSheet->setCellValue("A" . $rowNumber, $source['name']);
            $activeSheet->setCellValue("B" . $rowNumber, $source['total']);
            $activeSheet->setCellValue("C" . $rowNumber, $source['total_approved']);

            if ($source['total_approved'] && $source['total']) {
                $ar = round((float)($source['total_approved'] * 100 / $source['total']), 2);
            }

            if ($source['total_confirmed'] && $source['total_approved']) {
                $tr = round((float)($source['total_confirmed'] * 100 / $source['total_approved']), 2);
            }

            if ($source['total_confirmed'] && $source['total']) {
                $cv = round((float)($source['total_confirmed'] * 100 / $source['total']), 2);
            }

            $activeSheet->setCellValue("D" . $rowNumber, $ar);
            $activeSheet->setCellValue("E" . $rowNumber, $source['total_confirmed']);
            $activeSheet->setCellValue("F" . $rowNumber, $tr);
            $activeSheet->setCellValue("G" . $rowNumber, $cv);
        }

        $cellsWithBorders = "A{$startRowNumber}:G{$rowNumber}";
        $this->setTableBorders($activeSheet, $cellsWithBorders);
    }

    /**
     * Get orders by date
     *
     * @return array
     */
    private function getOrders(): array
    {
        $sql = $this->db->placehold(
            "SELECT s_orders.id, confirm_date, approve_date, amount, have_close_credits, s_orders.utm_source, date, status
                FROM s_orders
                LEFT JOIN s_users AS u
                ON u.id = s_orders.user_id
                WHERE date >= ? AND date <= ? 
                AND additional_data_added = 1",
            $this->firstDayOfMonth . ' 00:00:00',
            $this->yesterday . ' 23:59:59'
        );
        $this->db->query($sql);

        return $this->db->results() ?? [];
    }

    /**
     * Get confirmed orders
     *
     * @return array
     */
    private function getConfirmedOrders(): array
    {
        $sql = $this->db->placehold(
            "SELECT DATE_FORMAT(confirm_date , '%Y-%m-%d') as filter_date, SUM(amount) as orders_sum, have_close_credits
            FROM s_orders 
            WHERE confirm_date >= ? AND confirm_date <= ?  
            AND status = 10
            GROUP BY filter_date, have_close_credits",
            $this->firstDayOfMonth . ' 00:00:00',
            $this->yesterday . ' 23:59:59'
        );
        $this->db->query($sql);

        return $this->db->results() ?? [];
    }

    /**
     * @param int $orderId
     * @return array
     */
    private function getCreditDoctorAmount(int $orderId): array
    {
        $query = $this->db->placehold("
        SELECT order_id, amount
        FROM s_credit_doctor_to_user
        WHERE order_id = ? AND status = 'SUCCESS'
    ", $orderId);

        $this->db->query($query);

        return $this->db->results() ?? [];
    }

    private function setTableBorders($activeSheet, string $cells): void
    {
        $activeSheet
            ->getStyle($cells)
            ->applyFromArray(
                [
                    'borders' => [
                        'allborders' => [
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        ]
                    ]
                ]
            );
    }
}

(new SendOrdersReport())->run();
