<?php

declare(strict_types=1);

require_once 'View.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

/**
 * Class MultipolisReportView
 * Класс для отображения количества купленных мультиполисов с группировкой по дням/месяцам или без группировки
 */
class MultipolisReportView extends View
{
    /**
     * Adds a possibility to call download method
     */
    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');

        if ($action && method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        $filter_date_range = Helpers::getDataRange($this);
        $filters = [
            'filter_date_start' => $filter_date_range['filter_date_start'],
            'filter_date_end' => $filter_date_range['filter_date_end'] . ' 00:00:01',
            'filter_group_by' => $this->request->get('filter_group_by') ?: '',
            'filter_status' => Multipolis::STATUS_SUCCESS
        ];

        $multipolisList = $this->multipolis->getAllWithUsersData($filters);
        $unpaidPolisesCount = 0;
        $totalPolisesCount = 0;
        $totalSentCount = 0;

        if ($filters['filter_group_by']) {
            // Count all polises and those, that  were send
            foreach ($multipolisList as $item) {
                $totalPolisesCount += $item->polis_count;
                $totalSentCount += $item->sent_count;
            }

            // Get unpaid multipolises
            $unpaidPolises = $this->multipolis->selectAll(['filter_status' => 'NEW']);
            if ($unpaidPolises) {
                $result = [];
                foreach ($unpaidPolises as $polis) {
                    if ($polis->date_added >= $filters['filter_date_start'] && $polis->date_added <= $filters['filter_date_end']) {
                        $result[] = $polis;
                    }
                }

                $unpaidPolisesCount = count($result);
            }
        }

        return compact( 'filters', 'multipolisList', 'unpaidPolisesCount', 'totalPolisesCount', 'totalSentCount');
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {
        [$filters, $multipolisList, $unpaidPolisesCount, $totalPolisesCount, $totalSentCount] = array_values($this->getData());

        $this->design->assign('groupBy', $filters['filter_group_by']);
        $this->design->assign('dateStart', $filters['filter_date_start']);
        $this->design->assign('dateEnd', $filters['filter_date_end']);
        $this->design->assign('multipolisList', $multipolisList);
        $this->design->assign('unpaidPolisesCount', $unpaidPolisesCount);
        $this->design->assign('totalPolisesCount', $totalPolisesCount);
        $this->design->assign('totalPolisesCount', $totalPolisesCount);
        $this->design->assign('totalSentCount', $totalSentCount);
        $this->design->assign('report_uri', strtok($_SERVER['REQUEST_URI'], '?'));

        return $this->design->fetch('multipolis_report.tpl');
    }

    /**
     * Выгрузка данных в Excel
     *
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function download(): void
    {
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');

        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $filename = 'files/reports/multipolis_report.xls';
        $activeSheet->setTitle(date('Y_m_d_H_i_s'));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        [$filters, $multipolisList] = array_values($this->getData());

        $activeSheet->setCellValue('A1', 'Дата');
        $row = 2;
        if ($filters['filter_group_by']) {
            $activeSheet->setCellValue('B1', 'Полисы');
            $activeSheet->setCellValue('C1', 'Отправлена заявка');

            $cell = 'A';
            foreach ($multipolisList as $item) {
                $activeSheet->setCellValue('A' . $row, $item->date_filter);
                $activeSheet->setCellValue('B' . $row, $item->polis_count);
                $activeSheet->setCellValue('C' . $row, $item->sent_count);
                $row++;
            }
        } else {
            $activeSheet->setCellValue('B1', 'Полисы');
            $activeSheet->setCellValue('C1', 'ФИО клиента');
            $activeSheet->setCellValue('D1', 'Номер телефона клиента');
            $activeSheet->setCellValue('E1', 'Отправлена заявка');

            foreach ($multipolisList as $item) {
                $activeSheet->setCellValue('A' . $row, $item->date_filter);
                $activeSheet->setCellValue('B' . $row, $item->number);
                $activeSheet->setCellValue('C' . $row, $item->username);
                $activeSheet->setCellValue('D' . $row, $item->phone_mobile);
                $activeSheet->setCellValue('E' . $row, $item->is_sent == 1 ? "Да" : "Нет");
                $row++;
            }
        }

        for ($i = 'A'; $i <= 'J'; $i++) {
            $activeSheet->getColumnDimension($i)->setAutoSize(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename . '?v=' . time());
        exit;
    }
}
