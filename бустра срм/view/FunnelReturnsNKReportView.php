<?php

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once 'View.php';

/**
 * Class FunnelReturnsNKReportView
 * Класс для работы с отчётом Воронка по возвратам НК
 */
class FunnelReturnsNKReportView extends View
{
    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');

        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    /**
     * Генератор данных
     * @return array
     */
    private function getData(): array
    {
        $filter_data = [
            'date_range' => $this->getDataRange(),
            'is_funnel_returns' => true,
            'days_from' => $this->request->get('days_from', 'integer'),
            'days_to' => $this->request->get('days_to', 'integer'),
            'log_new_status' => '6.Закрыт',
            'have_close_credits' => 0,
        ];
        $results = $this->orders->getOrdersByStatusLog($filter_data);

        $totals = [
            'visit_lk_after_closed_order' => array_sum(array_map(fn($item) => $item->visit_lk_after_closed_order, $results)),
            'has_order_after_closed' => array_sum(array_map(fn($item) => $item->has_order_after_closed, $results)),
            'has_approve_order_after_closed' => array_sum(array_map(fn($item) => $item->has_approve_order_after_closed, $results)),
            'has_confirm_order_after_closed' => array_sum(array_map(fn($item) => $item->has_confirm_order_after_closed, $results)),
        ];

        return compact('results', 'totals');
    }

    public function fetch()
    {
        if ($this->request->get('ajax')) {
            $data = $this->getData();

            $this->design->assign('results', $data['results']);
            $this->design->assign('totals', $data['totals']);
        }

        return $this->design->fetch('funnel_returns_nk_report_view.tpl');
    }

    /**
     * Получение дат для фильтра
     * @return array
     */
    public function getDataRange(): array
    {
        $filter_data = [];

        $filter_date_start = date('Y-m-d');
        $filter_date_end = date('Y-m-d');

        $filter_date_range = $this->request->get('date_range') ?? '';

        if (!empty($filter_date_range)) {
            $filter_date_array = array_map('trim', explode('-', $filter_date_range));
            $filter_date_start = str_replace('.', '-', $filter_date_array[0]);
            $filter_date_end = str_replace('.', '-', $filter_date_array[1]);
        }

        $filter_data['filter_date_start'] = $filter_date_start;
        $filter_data['filter_date_end'] = $filter_date_end;

        return $filter_data;
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

        $filename = 'files/reports/download_funnel_returns.xls';

        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();

        $filter_data = $this->getDataRange();

        $active_sheet->setTitle(
            " " . $filter_data['filter_date_start'] . "_" . $filter_data['filter_date_end']
        );

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $active_sheet->getColumnDimension('A')->setWidth(10);
        $active_sheet->getColumnDimension('B')->setWidth(20);
        $active_sheet->getColumnDimension('C')->setWidth(20);
        $active_sheet->getColumnDimension('D')->setWidth(20);
        $active_sheet->getColumnDimension('E')->setWidth(20);
        $active_sheet->getColumnDimension('F')->setWidth(20);

        $active_sheet->setCellValue('A1', 'N');
        $active_sheet->setCellValue('B1', 'Номера телефона клиентов, закрывших свой первый договоро в указанный период (НК)');
        $active_sheet->setCellValue('C1', 'Кто из них заходил в ЛК после закрытия займа в интервале дат включительно');
        $active_sheet->setCellValue('D1', 'Кто из них подал заявку на заём после закрытия займа в интервале дат включительно');
        $active_sheet->setCellValue('E1', 'Кто из них получил Одобрение после закрытия займа в интервале дат включительно');
        $active_sheet->setCellValue('F1', 'Кто из них получил заём после закрытия займа в интервале дат включительно');

        $data = $this->getData();

        // преобразуем каждый элемент в массив
        $excell_data = array_map(fn($item) => (array)$item,$data['results']);
        $excell_data[] = array_merge(['Итого', ''], array_values($data['totals']));

        $active_sheet->fromArray($excell_data, null, 'A2');

        //$active_sheet->getColumnDimension()->setAutoSize(true);
        // перенос слов
        $row_count = $active_sheet->getHighestRow();
        $active_sheet->getStyle("A1:F" . $row_count)->getAlignment()->setWrapText(true);

        // покрасим последнюю строку
        $active_sheet->getStyle('A'. $row_count.':'.'F'.$row_count)->applyFromArray(
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
