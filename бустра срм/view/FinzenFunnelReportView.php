<?php

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

require_once 'View.php';

/**
 * Class FinzenFunnelReportView
 * Отчёт по воронке продаж ШКД (Кредитный Доктор) для ФинДзен
 */
class FinzenFunnelReportView extends View
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
     * Отображение отчёта
     * @return string
     * @throws Exception
     */
    public function fetch()
    {
        if ($this->request->get('ajax')) {
            $results = $this->getResults();
            $this->design->assign('results', $results);
        }

        return $this->design->fetch('finzen_funnel_report.tpl');
    }

    /**
     * Получение дат для фильтрации
     * @return array
     */
    private function getFilterData(): array
    {
        return Helpers::getDataRange($this);
    }

    /**
     * Генерация данных отчёта
     * @return array
     */
    private function getResults(): array
    {
        $filter = $this->getFilterData();

        $overdue9Data = $this->getOverdue9DataFrom1C($filter);
        $metric1 = $overdue9Data['overdue_clients_count'];
        $metric2 = $overdue9Data['credit_doctor_charges_count'];
        $finzenData = $this->fetchFromFinzenApi($filter);

        return [
            'overdue_9plus_clients' => $metric1,
            'finzen_charges' => $metric2,
            'bot_clicks' => $finzenData['bot_usage_count'] ?? 0,
            'active_users' => $finzenData['active_users_count'] ?? 0,
        ];
    }

    /**
     * Метрики 1-2 из 1С: клиенты с просрочкой 9+ и начисления ШКД
     * @param array $filter
     * @return array ['overdue_clients_count' => int, 'credit_doctor_charges_count' => int]
     */
    private function getOverdue9DataFrom1C(array $filter): array
    {
        $default = [
            'overdue_clients_count' => 0,
            'credit_doctor_charges_count' => 0,
        ];

        $result = $this->soap->getOverdue9Clients(
            $filter['filter_date_start'],
            $filter['filter_date_end']
        );

        if (isset($result['errors']) || !isset($result['response'])) {
            return $default;
        }

        $response = $result['response'];

        if (is_array($response) && ($response['Result'] ?? '') !== 'ERROR') {
            return [
                'overdue_clients_count' => (int) ($response['OverdueClientsQuantity'] ?? 0),
                'credit_doctor_charges_count' => (int) ($response['AccrualQuantity'] ?? 0),
            ];
        }

        return $default;
    }

    /**
     * Получение метрик из Finzen API
     * @param array $filter
     * @return array
     */
    private function fetchFromFinzenApi(array $filter): array
    {
        $url = $this->config->finzen_api_url ?? '';
        $key = $this->config->finzen_api_key ?? '';

        if ($url === '' || $key === '') {
            return [
                'bot_usage_count' => 0,
                'active_users_count' => 0,
            ];
        }

        $url = $this->config->finzen_api_url . '?' . http_build_query([
            'date_from' => $filter['filter_date_start'],
            'date_to' => $filter['filter_date_end'],
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'X-API-KEY: ' . $this->config->finzen_api_key,
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return [
                'bot_usage_count' => 0,
                'active_users_count' => 0,
            ];
        }

        $data = json_decode($response, true);

        if (!isset($data['success']) || !$data['success']) {
            return [
                'bot_usage_count' => 0,
                'active_users_count' => 0,
            ];
        }

        return $data['data'] ?? [];
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
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');

        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        $filename = 'files/reports/download_finzen_funnel.xls';

        $filter = $this->getFilterData();
        $active_sheet->setTitle($filter['filter_date_start'] . ' - ' . $filter['filter_date_end']);

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        // Заголовки
        $active_sheet->setCellValue('A1', 'Этап воронки');
        $active_sheet->setCellValue('B1', 'Количество');
        $active_sheet->setCellValue('C1', 'Конверсия');
        $active_sheet->setCellValue('D1', 'Описание');

        // Стиль заголовков
        $active_sheet->getStyle('A1:D1')->applyFromArray([
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '272c33'],
            ],
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
                'bold' => true,
            ],
        ]);

        $results = $this->getResults();

        // Данные
        $active_sheet->setCellValue('A2', '1. Клиенты с просрочкой 9+');
        $active_sheet->setCellValue('B2', $results['overdue_9plus_clients']);
        $active_sheet->setCellValue('C2', '100%');
        $active_sheet->setCellValue('D2', 'База клиентов с просрочкой 9+ дней за период');

        $active_sheet->setCellValue('A3', '2. Начисления ШКД');
        $active_sheet->setCellValue('B3', $results['finzen_charges']);
        $cv2 = $results['overdue_9plus_clients'] > 0
            ? round($results['finzen_charges'] / $results['overdue_9plus_clients'] * 100, 1) . '%'
            : '0%';
        $active_sheet->setCellValue('C3', $cv2);
        $active_sheet->setCellValue('D3', 'Переход на сайт ФинДзен (оплата КД)');

        $active_sheet->setCellValue('A4', '3. Использования бота');
        $active_sheet->setCellValue('B4', $results['bot_clicks']);
        $cv3 = $results['finzen_charges'] > 0
            ? round($results['bot_clicks'] / $results['finzen_charges'] * 100, 1) . '%'
            : '0%';
        $active_sheet->setCellValue('C4', $cv3);
        $active_sheet->setCellValue('D4', 'Клики/создание ID в Telegram боте');

        $active_sheet->setCellValue('A5', '4. Активные пользователи');
        $active_sheet->setCellValue('B5', $results['active_users']);
        $cv4 = $results['bot_clicks'] > 0
            ? round($results['active_users'] / $results['bot_clicks'] * 100, 1) . '%'
            : '0%';
        $active_sheet->setCellValue('C5', $cv4);
        $active_sheet->setCellValue('D5', 'Уникальные клиенты, написавшие боту 1+ сообщений');

        // Стиль последней строки (итог)
        $active_sheet->getStyle('A5:D5')->applyFromArray([
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '8FD14F'],
            ],
        ]);

        // Автоширина колонок
        foreach (range('A', 'D') as $col) {
            $active_sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename . '?v=' . time());
        exit;
    }
}
