<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

require_once __DIR__ . '/../../api/Simpla.php';

class SyncLoadOrdersPDN extends Simpla
{
    private const GET_ORDERS_TO_CALCULATE_PDN_URL = '/api/v1/loan-orders/sync-loan-orders/';

    private int $maxExecutionTime;

    private const flags = [
        PdnCalculation::DISABLE_INQUIRING_NEW_REPORTS,
        PdnCalculation::ONLY_DEBTS_DOCUMENT,
        PdnCalculation::IS_FORCED_CALCULATION,
        PdnCalculation::WITHOUT_CHECK_REPORTS_DATE,
        PdnCalculation::WITHOUT_CH_REPORT,
        PdnCalculation::ONLY_UPDATE_PDN,
        PdnCalculation::ONLY_CONVERT_CREDIT_HISTORY_REPORT_ENCODING,
        PdnCalculation::CALCULATE_PDN_BEFORE_ISSUANCE,
        PdnCalculation::WITHOUT_REPORTS
    ];

    private string $logFileName;

    private int $executionStartTime;

    public function __construct(
        string $logFileName,
        int $executionStartTime,
        int $maxExecutionTime = 3500
    ){
        parent::__construct();

        $this->logFileName = $logFileName;
        $this->maxExecutionTime = $maxExecutionTime;

        $this->executionStartTime = $executionStartTime;
    }

    public function runForLastWeek(string $taxpayer): void
    {
        $dateFrom = new DateTime();
        $dateFrom->sub(new DateInterval('P7D'));
        $dateTo = new DateTime(); // по сегодня, для избежания пересечения с десяти минутным кроном

        $this->runFromWork($taxpayer, $dateFrom, $dateTo);
    }

    public function runForQuarter(string $taxpayer): void
    {
        $dateFrom = $this->getStartOfCurrentQuarter();
        $dateTo = new DateTime(); // по сегодня, для избежания пересечения с десяти минутным кроном

        $this->runFromWork($taxpayer, $dateFrom, $dateTo);
    }

    public function runFromWork(string $taxpayer, ?DateTime $dateFrom = null, ?DateTime $dateTo = null): void
    {
        $this->log("", 'Начало работы крона: ' . date('Y-m-d H:i:s'));

        if (is_null($dateFrom)) {
            $dateFrom = new DateTime();
            $dateFrom->sub(new DateInterval('P1D'));
        }

        if (is_null($dateTo)) {
            $dateTo = new DateTime();
            $dateTo->add(new DateInterval('P1D')); // пока в 1с не настроят даты для запросов
        }

        $data = [
            'taxpayer_number' => $taxpayer,
            'term_start' => $dateFrom->format('Y-m-d'),
            'term_end' => $dateTo->format('Y-m-d'),
        ];

        $request = $this->makeRequest($data, $this->config->pdnHost . self::GET_ORDERS_TO_CALCULATE_PDN_URL);

        $orders = json_decode($request);

        $this->run($orders);

        $this->log('', 'Крон завершен: ' . date('Y-m-d H:i:s'));
    }

    public function runFromFile(string $fileName): void
    {
        $stream = $this->onceOpen($fileName);
        $success = true;

        if ($stream === false) {
            $this->log("", 'Не удалось открыть файл');
            return;
        }

        foreach ($this->getItems($stream, 500) as $items) {
            $success = $this->run($items);

            if ($success === false) {
                break;
            }
        }

        fclose($stream);

        if ($success) {
            unlink($fileName);
            $this->log("", 'Файл удален');
        }

        $this->log('', 'Крон завершен: ' . date('Y-m-d H:i:s'));
    }

    private function run($orders): bool
    {
        if (empty($orders)) {
            $this->log("", 'Заявки не найдены');
            return true;
        }

        $this->log("", ['orders' => $orders]);

        $orderNumbers = array_column($orders, 'order_number');

        $calculatedPdn = $this->getCalculatedPdn($orderNumbers);

        foreach ($orders as $order) {
            if ($this->executionTimeHasExpired()) {
                $this->log(
                    "",
                    'Достигнута максимальная продолжительность работы крон. Время ' . date('Y-m-d H:i:s')
                );

                return false;
            }

            try {
                $flags = $this->getFlags($order);

                if (!$this->needCalculateOrderPdn($order, $calculatedPdn, $flags)) {
                    continue;
                }

                // Если есть флаг только обновления значения ПДН
                if (!empty($flags[PdnCalculation::ONLY_UPDATE_PDN])) {
                    if (isset($order->pti_percent)) {
                        $this->pdnCalculation->onlyUpdatePdnValue($order, (float)$order->pti_percent);
                    } else {
                        $this->log('', 'Не найдено значение ПДН для заявки ' . $order->order_number, '');
                    }
                }

                // Если есть флаг только изменения кодировки локального файла КИ отчета
                elseif (!empty($flags[PdnCalculation::ONLY_CONVERT_CREDIT_HISTORY_REPORT_ENCODING])) {
                    $this->pdnCalculation->onlyConvertCreditHistoryEncoding($order);
                }

                // Если есть флаг только "Лист оценки платежеспособности заемщика" и есть успешный расчет ПДН
                elseif (
                    !empty($flags[PdnCalculation::ONLY_DEBTS_DOCUMENT]) &&
                    !empty($calculatedPdn[$order->order_number]->success)
                ) {
                    $pdnCalculationResult = json_decode($calculatedPdn[$order->order_number]->result);
                    if (!empty($pdnCalculationResult) && isset($pdnCalculationResult->pti_percent)) {
                        $this->pdnCalculation->onlyAddDebtsDocumentAction($order, $pdnCalculationResult);
                    }
                }

                // Обычный расчет ПДН по займу
                else {
                    $this->pdnCalculation->run($order->order_number, $flags);
                }

            } catch (Throwable $e) {
                $error = [
                    'Ошибка: ' . $e->getMessage(),
                    'Файл: ' . $e->getFile(),
                    'Строка: ' . $e->getLine(),
                    'Подробности: ' . $e->getTraceAsString()
                ];

                $this->log($order->order_number, ['result' => false, 'error' => $error]);
            }
        }

        return true;
    }

    private function needCalculateOrderPdn(stdClass $order, array $calculatedPdn, array $flags): bool
    {
        if (empty($order->order_number)) {
            return false;
        }

        $orderPdn = $calculatedPdn[$order->order_number];

        // Рассчитать ПДН, если его не рассчитывали ранее
        if (empty($orderPdn)) {
            return true;
        }

        // Разрешаем, если есть флаг только "Лист оценки платежеспособности заемщика" и ранее документ не был добавлен
        if (!empty($flags[PdnCalculation::ONLY_DEBTS_DOCUMENT]) && empty($orderPdn->debts_document_added)) {
            return true;
        }

        // Разрешаем, если есть флаг только обновления значения ПДН
        if (!empty($flags[PdnCalculation::ONLY_UPDATE_PDN])) {
            return true;
        }

        // Разрешаем, если есть флаг только изменения кодировки локального файла КИ отчета
        if (!empty($flags[PdnCalculation::ONLY_CONVERT_CREDIT_HISTORY_REPORT_ENCODING])) {
            return true;
        }

        // Для определенных результатов рассчитываем ошибочные ПДН повторно
        if ($orderPdn->auto_recalc) {
            return true;
        }

        // Для пересчета предварительных расчетов
        if (!empty($orderPdn->success) && strpos($orderPdn->request, 'loan_date') === false) {
            return true;
        }

        // НЕ рассчитываем ПДН, если он был ранее успешно рассчитан
        if (!empty($orderPdn->success)) {
            return false;
        }

        if (mb_strpos($orderPdn->result, 'Недопустимый первичный ключ') !== false) {
            return true;
        }

        return false;
    }

    private function getCalculatedPdn(array $orderNumbers): array
    {
        $query = $this->db->placehold(
            'SELECT order_uid, contract_number, request, result, success, auto_recalc FROM __pdn_calculation 
            WHERE order_uid IN (?@)
            ORDER BY id',
            $orderNumbers
        );

        $this->db->query($query);
        $pdn = $this->db->results();

        if (empty($pdn)) {
            return [];
        }

        return array_column($pdn, null, 'order_uid');
    }

    private function makeRequest($data, $url): ?string
    {
        $client = new Client([
            'http_errors' => false,
        ]);

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'form_params' => $data,
                'allow_redirects' => true,
            ]);

            $body = (string) $response->getBody();

            $this->log($data, ['guzzle_response' => $body]);

            return $body;

        } catch (RequestException|GuzzleException $e) {
            $errorMsg = $e->getMessage();

            $this->log($data, ['guzzle_error' => $errorMsg]);

            return null;
        }
    }

    private function getFlags(stdClass $order): array
    {
        $flags = [];

        foreach (self::flags as $flag) {
            $flags[$flag] = isset($order->{$flag}) && $order->{$flag} === true;
        }

        return $flags;
    }

    private function getStartOfCurrentQuarter(): DateTime
    {
        $year = date('Y');
        $month = (int)date('n');

        $quarter = (int)ceil($month / 3);
        $quarterStartMonth = ($quarter - 1) * 3 + 1;

        return new DateTime(sprintf('%d-%02d-01 00:00:00', $year, $quarterStartMonth));
    }

    private function log($request, $response, $url = self::GET_ORDERS_TO_CALCULATE_PDN_URL, $method = __METHOD__): void
    {
        $this->logging(
            $method,
            $url,
            $request,
            $response,
            $this->logFileName
        );
    }

    private function executionTimeHasExpired(): bool
    {
        return microtime(true) - $this->executionStartTime > $this->maxExecutionTime;
    }

    private function getItems($resource, int $chunk = 100): Generator
    {
        fseek($resource, 1);

        $buffer = '';
        $items = [];

        while (($char = fgetc($resource)) !== false) {
            if ($char === '{') {
                $buffer = $char;
            } elseif ($char === '}') {
                $buffer .= $char;

                try {
                    $items[] = json_decode($buffer, false, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {

                }

                $buffer = '';
                fgetc($resource);

                if (count($items) === $chunk) {
                    yield $items;

                    $items = [];
                }
            } elseif ($buffer !== '') {
                $buffer .= $char;
            }
        }

        yield $items;
    }

    private function onceOpen(string $fileName) {
        $fp = fopen($fileName, 'rb');

        if ($fp !== false && flock($fp, LOCK_EX | LOCK_NB)) {
            return $fp;
        }

        return false;
    }
}