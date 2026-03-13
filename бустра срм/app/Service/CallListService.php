<?php

namespace App\Service;

use Tasks;
use Orders;
use TvMedical;

/**
 * Сервис для генерации отчетов по списку звонков
 * 
 * Обеспечивает генерацию Excel отчетов по звонкам с логированием операций
 */
class CallListService
{
    private Tasks $tasks;
    private Orders $orders;
    private TvMedical $tvMedical;
    private VoximplantLogger $logger;
    private string $rootDir;
    private string $rootUrl;

    public function __construct(
        Tasks $tasks,
        Orders $orders,
        TvMedical $tvMedical,
        VoximplantLogger $logger,
        string $rootDir,
        string $rootUrl
    ) {
        $this->tasks = $tasks;
        $this->orders = $orders;
        $this->tvMedical = $tvMedical;
        $this->logger = $logger;
        $this->rootDir = $rootDir;
        $this->rootUrl = $rootUrl;
    }

    /**
     * Генерация отчета по списку звонков
     * 
     * @param array $params Параметры запроса:
     *   - dateRange: string Диапазон дат в формате "YYYY.MM.DD - YYYY.MM.DD"
     *   - managerId: array|int ID менеджера(ов)
     *   - plus: bool Флаг plus
     *   - organizationId: int|null ID организации (МКК) для фильтрации
     * @return array Результат операции с URL файла
     */
    public function generateCallListReport(array $params): array
    {
        $startTime = microtime(true);
        $method = 'generateCallListReport';

        $dateRange = $params['dateRange'] ?? '';
        $managerId = $params['managerId'] ?? null;
        $plus = $params['plus'] ?? false;
        $organizationId = isset($params['organizationId']) ? (int) $params['organizationId'] : null;

        $context = [
            'date_range' => $dateRange,
            'manager_id' => $managerId,
            'plus' => $plus,
            'organization_id' => $organizationId,
        ];

        try {
            // Валидация параметров
            $validationResult = $this->validateRequest($dateRange, $managerId);
            if (!$validationResult['success']) {
                $this->logger->logError('call_list', $method, 
                    new \Exception($validationResult['error']), $context);
                return $validationResult;
            }

            $this->logger->logRequest('call_list', $method, [
                'date_range' => $dateRange,
                'manager_id' => $managerId,
                'organization_id' => $organizationId,
            ], $context);

            // Парсим диапазон дат
            $dateRangeArray = array_map('trim', explode('-', $dateRange));
            $dateFrom = str_replace('.', '-', $dateRangeArray[0]);
            $dateTo = str_replace('.', '-', $dateRangeArray[1]);

            // Определяем период
            $period = $plus ? 'period_one_two' : 'zero';

            // Нормализуем managerId
            if (is_array($managerId)) {
                $managerId = !empty($managerId) ? $managerId[0] : null;
            }

            // Получаем задачи
            $taskFilter = [
                'manager_id' => $managerId,
                'task_date_from' => $dateFrom,
                'task_date_to' => $dateTo,
                'period' => $period,
            ];
            if ($organizationId !== null && $organizationId > 0) {
                $taskFilter['organization_id'] = $organizationId;
            }
            $tasks = $this->tasks->get_tasks($taskFilter);

            // Форматируем задачи
            $formattedTasks = $this->formatTasksForReport($tasks);

            // Генерируем Excel файл
            $url = $this->generateExcelFile($formattedTasks);

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess('call_list', $method, [
                'tasks_count' => count($formattedTasks),
                'file_url' => $url,
            ], $duration, $context);

            return [
                'success' => true,
                'message' => $url
            ];

        } catch (\Throwable $e) {
            $this->logger->logError('call_list', $method, $e, $context);
            
            return [
                'success' => false,
                'message' => 'Ошибка при генерации отчета: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Валидация параметров запроса
     * 
     * @param string $dateRange Диапазон дат
     * @param array|int|null $managerId ID менеджера
     * @return array Результат валидации
     */
    private function validateRequest(string $dateRange, $managerId): array
    {
        if (empty($dateRange)) {
            return ['success' => false, 'error' => 'Не указан диапазон дат'];
        }

        $dateRangeArray = array_map('trim', explode('-', $dateRange));
        if (count($dateRangeArray) < 2) {
            return ['success' => false, 'error' => 'Неверный формат диапазона дат'];
        }

        $dateFrom = str_replace('.', '-', $dateRangeArray[0]);
        $dateTo = str_replace('.', '-', $dateRangeArray[1]);
        $currentDate = date('Y-m-d');

        if ($dateFrom !== $dateTo || $currentDate < $dateFrom) {
            return [
                'success' => false,
                'error' => 'Выберите правильную дату'
            ];
        }

        if (is_array($managerId) && count($managerId) > 1) {
            return [
                'success' => false,
                'error' => 'Выберите только одного менеджера'
            ];
        }

        if (empty($managerId)) {
            return [
                'success' => false,
                'error' => 'Выберите менеджера'
            ];
        }

        return ['success' => true];
    }

    /**
     * Форматирование задач для отчета
     * 
     * @param array $tasks Массив задач
     * @return array Отформатированные данные
     */
    public function formatTasksForReport(array $tasks): array
    {
        $data = [];
        $tvMedicalTariffs = $this->tvMedical->getAllTariffs();
        $tvMedicalPrice = $tvMedicalTariffs[0]->price ?? 0;

        foreach ($tasks as $task) {
            if (empty($task->zayavka)) {
                continue;
            }

            $orderId = $this->orders->get_order_1cid($task->zayavka);
            if (!$orderId) {
                continue;
            }

            $zaimDate = new \DateTime($task->zaim_date);
            $zaimDate = $zaimDate->format('d.m.Y');
            
            $paymentDate = new \DateTime($task->payment_date);
            $paymentDate = $paymentDate->format('d.m.Y');

            $sum = floor($task->ostatok_od + $task->ostatok_percents + $task->ostatok_peni);

            $data[] = [
                'ID' => $orderId,
                'phone_mobile' => $task->phone_mobile,
                'timezone' => $task->timezone,
                'lastname' => $task->lastname,
                'firstname' => $task->firstname,
                'patronymic' => $task->patronymic,
                'work_phone' => '88003333073',
                'sum' => $sum,
                'prolongation_amount' => floor($task->prolongation_amount + $tvMedicalPrice),
                'zaim_date' => $zaimDate,
                'payment_date' => $paymentDate,
            ];
        }

        return $data;
    }

    /**
     * Генерация Excel файла
     * 
     * @param array $data Данные для записи
     * @return string URL файла
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function generateExcelFile(array $data): string
    {
        require_once $this->rootDir . 'PHPExcel/Classes/PHPExcel.php';
        error_reporting(0);
        ini_set('display_errors', 'Off');

        $filename = 'files/reports/call_list.xls';
        $excel = new \PHPExcel();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();

        $activeSheet->setTitle('Call List');

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        
        // Установка ширины колонок
        $activeSheet->getColumnDimension('A')->setWidth(33);
        $activeSheet->getColumnDimension('B')->setWidth(20);
        $activeSheet->getColumnDimension('C')->setWidth(5);
        $activeSheet->getColumnDimension('D')->setWidth(20);
        $activeSheet->getColumnDimension('E')->setWidth(20);
        $activeSheet->getColumnDimension('F')->setWidth(20);
        $activeSheet->getColumnDimension('G')->setWidth(20);
        $activeSheet->getColumnDimension('H')->setWidth(15);
        $activeSheet->getColumnDimension('I')->setWidth(15);
        $activeSheet->getColumnDimension('J')->setWidth(15);
        $activeSheet->getColumnDimension('K')->setWidth(15);

        // Заголовки
        $activeSheet->setCellValue('A1', 'ID');
        $activeSheet->setCellValue('B1', 'Телефон');
        $activeSheet->setCellValue('C1', 'UTC');
        $activeSheet->setCellValue('D1', 'Фамилия');
        $activeSheet->setCellValue('E1', 'Имя');
        $activeSheet->setCellValue('F1', 'Отчество');
        $activeSheet->setCellValue('G1', 'Номер телефона компании');
        $activeSheet->setCellValue('H1', 'Сумма задолженности');
        $activeSheet->setCellValue('I1', 'Минимальная сумма');
        $activeSheet->setCellValue('J1', 'Дата выдачи займа');
        $activeSheet->setCellValue('K1', 'Дата платежа');

        // Запись данных
        $activeSheet->fromArray(array_map(fn($item) => (array)$item, $data), null, 'A2');

        $activeSheet->getColumnDimension()->setAutoSize(true);
        $rowCount = $activeSheet->getHighestRow();
        $activeSheet->getStyle("A1:K" . $rowCount)->getAlignment()->setWrapText(true);

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->rootDir . $filename);
        
        return $this->rootUrl . '/' . $filename;
    }
}

