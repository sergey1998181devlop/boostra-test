<?php

ini_set('memory_limit', '1024M');

require_once 'lib/autoloader.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

require_once 'View.php';

/**
 * Class AdditionalOrdersReportView
 */
class AdditionalOrdersReportView extends View
{
    private const SERVICE_PAYMENT_METHOD = 'B2P';

    private string $dateFrom;

    private string $dateTo;

    private array $sortedItems = [];

    private const SERVICE_TITLE = [
        "s_credit_doctor_to_user" => 'Кредитный доктор',
        "s_multipolis" => 'Консьерж сервис',
        "s_tv_medical_payments" => 'Вита-мед',
    ];

    public function __construct()
    {
        parent::__construct();

        list($this->dateFrom, $this->dateTo) = $this->getDateRange();

        $action = $this->request->get('action');
        $action = explode('&', $action);
        $methodName = reset($action);
        if (method_exists(static::class, $methodName)) {
            $this->{$methodName}();
        }
    }

    private function getDateRange(): array
    {
        if ($dateRange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $dateRange);
            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));
        } else {
            $date_from = date('Y-m-d', strtotime('-1 day'));
            $date_to = date('Y-m-d');
        }

        $date_from .= " 00:00:00";
        $date_to .= " 23:59:59";

        return [
            $date_from,
            $date_to
        ];
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {
        $items = $this->sortResults($this->getResults());

        $this->design->assign('items', $items);
        $this->design->assign('reportUri', strtok($_SERVER['REQUEST_URI'], '?'));

        $this->design->assign('from', date('d.m.Y', strtotime($this->dateFrom)));
        $this->design->assign('to', date('d.m.Y', strtotime($this->dateTo)));

        return $this->design->fetch('additional_orders_report_view.tpl');
    }

    private function sortResultsStructure(): array
    {
        $services = [];
        foreach (array_keys(static::SERVICE_TITLE) as $service) {
            $services[$service] = [0, 0];
        }

        $type = [];
        foreach (['issuance', 'prolongation', 'closing'] as $item) {
            $type[$item] = $services;
        }

        $calculations = [];
        foreach (['sold', 'returned', 'returned_percent', 'returned_percent_total'] as $calc) {
            $calculations[$calc] = $type;
        }

        $return = [];
        foreach (['count', 'sum'] as $category) {
            $return[$category] = $calculations;
        }

        return $return;
    }

    private function setDataByReturnDate(object $item, string $type, bool $returnDate): void
    {
        if (!$returnDate) {
            $this->setServiceData($item, $type, 'sold');
        } else {
            $this->setServiceData($item, $type, 'returned');
        }
    }

    private function setServiceData(object $item, string $type, string $col): void
    {
        switch ($type) {
            case static::SERVICE_TITLE['s_credit_doctor_to_user']:
                $this->sortedItems['count'][$col]['issuance']['s_credit_doctor_to_user'][($item->have_close_credits) ? 1 : 0]++;
                $this->sortedItems['sum'][$col]['issuance']['s_credit_doctor_to_user'][($item->have_close_credits) ? 1 : 0] += $item->amount;
                break;
            case static::SERVICE_TITLE['s_multipolis']:
                if ($item->prolongation) {
                    $this->sortedItems['count'][$col]['prolongation']['s_multipolis'][($item->have_close_credits) ? 1 : 0]++;
                    $this->sortedItems['sum'][$col]['prolongation']['s_multipolis'][($item->have_close_credits) ? 1 : 0] += $item->amount;
                }
                break;
            case static::SERVICE_TITLE['s_tv_medical_payments']:
                if ($item->prolongation) {
                    $this->sortedItems['count'][$col]['prolongation']['s_tv_medical_payments'][($item->have_close_credits) ? 1 : 0]++;
                    $this->sortedItems['sum'][$col]['prolongation']['s_tv_medical_payments'][($item->have_close_credits) ? 1 : 0] += $item->amount;
                } else {
                    $this->sortedItems['count'][$col]['closing']['s_tv_medical_payments'][($item->have_close_credits) ? 1 : 0]++;
                    $this->sortedItems['sum'][$col]['closing']['s_tv_medical_payments'][($item->have_close_credits) ? 1 : 0] += $item->amount;
                }
                break;
        }
    }

    private function calcReturnPercents(string $col)
    {
        foreach (['issuance', 'prolongation', 'closing'] as $item) {
            foreach (array_keys(static::SERVICE_TITLE) as $service) {
                $this->sortedItems[$col]['returned_percent'][$item][$service] = [
                    ($this->sortedItems[$col]['sold'][$item][$service][0]) ?
                        round(($this->sortedItems[$col]['returned'][$item][$service][0] / $this->sortedItems[$col]['sold'][$item][$service][0]) * 100, 2) :
                        0,
                    ($this->sortedItems[$col]['sold'][$item][$service][1]) ?
                        round(($this->sortedItems[$col]['returned'][$item][$service][1] / $this->sortedItems[$col]['sold'][$item][$service][1]) * 100, 2) :
                        0,
                ];

                $this->sortedItems[$col]['returned_percent_total'][$item][$service] = [
                    ($this->sortedItems[$col]['sold'][$item][$service][0] + $this->sortedItems[$col]['sold'][$item][$service][1]) ?
                        round((($this->sortedItems[$col]['returned'][$item][$service][0] + $this->sortedItems[$col]['returned'][$item][$service][1]) / ($this->sortedItems[$col]['sold'][$item][$service][0] + $this->sortedItems[$col]['sold'][$item][$service][1])) * 100, 2) :
                        0
                ];
            }
        }
    }

    private function sortResults(array $items): array
    {
        $this->sortedItems = $this->sortResultsStructure();
        foreach ($items as $item) {
            $this->setDataByReturnDate($item, $item->type, (bool)$item->return_date);
        }

        foreach (['count', 'sum'] as $col) {
            $this->calcReturnPercents($col);
        }

        return $this->sortedItems;
    }

    /**
     * Генерация данных
     * @return array
     */
    private function getResults(): array
    {
        $sql = "
                SELECT * FROM
                (SELECT
                    o.have_close_credits,
                    b2p.prolongation,
                    sc.date_added,
                    sc.return_date,
                    sc.amount,
                    ? as type
                FROM s_credit_doctor_to_user sc
                LEFT JOIN s_orders o ON o.id = sc.order_id
                LEFT JOIN b2p_payments b2p ON o.id = b2p.order_id
                WHERE sc.date_added BETWEEN ? and ? 
                  AND sc.payment_method = ?
                  AND sc.status = ?
                    UNION ALL
                SELECT
                    o.have_close_credits,
                    b2p.prolongation,
                    stv.date_added,
                    stv.return_date,
                    stv.amount,
                    ? as type
                FROM s_tv_medical_payments stv
                LEFT JOIN s_orders o ON o.id = stv.order_id
                LEFT JOIN b2p_payments b2p ON o.id = b2p.order_id
                WHERE stv.date_added BETWEEN ? and ? 
                  AND stv.payment_method = ?
                  AND stv.status = ?
                    UNION ALL
                SELECT
                    o.have_close_credits,
                    b2p.prolongation,
                    sm.date_added,
                    sm.return_date,
                    sm.amount,
                    ? as type
                FROM s_multipolis sm
                LEFT JOIN s_orders o ON o.id = sm.order_id
                LEFT JOIN b2p_payments b2p ON o.id = b2p.order_id
                WHERE sm.date_added BETWEEN ? and ? 
                  AND sm.payment_method = ?
                  AND sm.status = ?) as r
                ORDER BY r.date_added DESC
        ";

        $query = $this->db->placehold(
            $sql,
            static::SERVICE_TITLE['s_credit_doctor_to_user'],
            $this->dateFrom,
            $this->dateTo,
            static::SERVICE_PAYMENT_METHOD,
            $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS,
            static::SERVICE_TITLE['s_tv_medical_payments'],
            $this->dateFrom,
            $this->dateTo,
            static::SERVICE_PAYMENT_METHOD,
            $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS,
            static::SERVICE_TITLE['s_multipolis'],
            $this->dateFrom,
            $this->dateTo,
            static::SERVICE_PAYMENT_METHOD,
            $this->multipolis::STATUS_SUCCESS
        );

        $this->db->query($query);

        return $this->db->results() ?: [];
    }

    /**
     * Выгрузка данных в Excel
     *
     * @return void
     */
    private function download(): void
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');
        require dirname(__DIR__) . '/vendor/autoload.php';

        list($this->dateFrom, $this->dateTo) = $this->getDateRange();

        $writer = new XLSXWriter();
        $sheet_name = 'additional_orders_report';

        $writer->markMergedCell($sheet_name, 0, 1, 0, 6);
        $writer->markMergedCell($sheet_name, 0, 7, 0, 12);
        $row1 = ['', 'Количество', '', '', '', '', '', '', 'Сумма',];
        $writer->writeSheetRow($sheet_name, $row1);

        $writer->markMergedCell($sheet_name, 1, 1, 1, 2);
        $writer->markMergedCell($sheet_name, 1, 3, 1, 4);
        $writer->markMergedCell($sheet_name, 1, 5, 1, 6);
        $writer->markMergedCell($sheet_name, 1, 7, 1, 8);
        $writer->markMergedCell($sheet_name, 1, 9, 1, 10);
        $writer->markMergedCell($sheet_name, 1, 11, 1, 12);
        $row2 = [$this->dateFrom . ' - ' . $this->dateTo, 'Продано', '', 'Возвращено', '', '% возврата', '', '% возврата общий', 'Продано', '', 'Возвращено', '', '% возврата', '', '% возврата общий'];
        $writer->writeSheetRow($sheet_name, $row2);

        $items = $this->sortResults($this->getResults());

        foreach ($this->getDataRows($items, 'issuance', 'Выдача') as $issuanceDataRow) {
            $writer->writeSheetRow($sheet_name, $issuanceDataRow);
        }

        foreach ($this->getDataRows($items, 'prolongation', 'Пролонгация') as $prolongationDataRow) {
            $writer->writeSheetRow($sheet_name, $prolongationDataRow);
        }

        foreach ($this->getDataRows($items, 'closing', 'Закрытие') as $closingDataRow) {
            $writer->writeSheetRow($sheet_name, $closingDataRow);
        }

        $filename = 'files/reports/additional_orders_report.xlsx';
        $writer->writeToFile($this->config->root_dir . $filename);
        header('Location:' . $filename);
        exit;
    }

    private function getDataRows(array $items, string $type, string $title): array
    {
        $rows = [];
        $rows[] = [$title, 'НК', 'ПК', 'НК', 'ПК', 'НК', 'ПК', '', 'НК', 'ПК', 'НК', 'ПК', 'НК', 'ПК', ''];
        foreach (array_keys(static::SERVICE_TITLE) as $service) {
            if (
                ($type == 'issuance' && $service != array_keys(static::SERVICE_TITLE)[0]) ||
                ($type == 'prolongation' && $service == array_keys(static::SERVICE_TITLE)[0]) ||
                ($type == 'closing' && $service != array_keys(static::SERVICE_TITLE)[2])
            ) {
                continue;
            }

            $rows[] = [
                static::SERVICE_TITLE[$service],
                $items['count']['sold'][$type][$service][0],
                $items['count']['sold'][$type][$service][1],
                $items['count']['returned'][$type][$service][0],
                $items['count']['returned'][$type][$service][1],
                $items['count']['returned_percent'][$type][$service][0],
                $items['count']['returned_percent'][$type][$service][1],
                $items['count']['returned_percent_total'][$type][$service][0],
                $items['sum']['sold'][$type][$service][0],
                $items['sum']['sold'][$type][$service][1],
                $items['sum']['returned'][$type][$service][0],
                $items['sum']['returned'][$type][$service][1],
                $items['sum']['returned_percent'][$type][$service][0],
                $items['sum']['returned_percent'][$type][$service][1],
                $items['sum']['returned_percent_total'][$type][$service][0],
            ];
        }

        return $rows;
    }
}
