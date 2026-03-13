<?php

namespace App\Service;

use App\Enums\MindboxConstants;
use App\Repositories\MindboxDbRepository;
use App\Service\MindboxApiClient;
use App\Service\CsvGenerator;
use Exception;
class MindboxExportService
{
    private MindboxDbRepository $dbRepo;
    private MindboxApiClient $apiClient;
    private CsvGenerator $csvGenerator;
    private int $batchSize = 50000;

    public function __construct(
        MindboxDbRepository $dbRepo,
        MindboxApiClient $apiClient,
        CsvGenerator $csvGenerator
    ) {
        $this->dbRepo = $dbRepo;
        $this->apiClient = $apiClient;
        $this->csvGenerator = $csvGenerator;
        $this->batchSize = MindboxConstants::DEFAULT_BATCH_SIZE;
    }


    /**
     * Экспорт пользователей в Mindbox
     * @param string $startDate
     * @return array
     * @throws \Random\RandomException
     */
    public function exportUsersToMindbox(string $startDate): array
    {
        $totalClients = $this->dbRepo->countUserDataForExport($startDate);

        $this->logMindbox('exportUsersToMindbox: start', [
            'start_date' => $startDate,
            'total_clients' => $totalClients,
        ]);

        if ($totalClients === 0) {
            $this->logMindbox('exportUsersToMindbox: skip — no clients');
            return [
                'status' => 'error',
                'message' => 'Нет клиентов для экспорта'
            ];
        }

        $transactionId = $this->generateGUID();
        $operationName = 'DirectCrm.Customers.Import';

        $this->logMindbox('exportUsersToMindbox: sending', [
            'transaction_id' => $transactionId,
            'operation' => $operationName,
            'batch_size' => $this->batchSize,
        ]);

        try {
            $csvContent = $this->getUsersCsv($startDate);
            $response = $this->apiClient->sendBatchStream(
                $csvContent,
                $transactionId,
                $operationName
            );

            $this->logMindbox('exportUsersToMindbox: success', [
                'transaction_id' => $transactionId,
                'http_code' => $response['http_code'] ?? null,
                'response' => $response['response'] ?? null,
            ]);

            return [
                'total_clients' => $totalClients,
                'start_date' => $startDate,
                'result' => [
                    'users_count' => $totalClients,
                    'transaction_id' => $transactionId,
                    'status' => 'success',
                    'response' => $response
                ]
            ];
        } catch (Exception $e) {
            $this->logMindbox('exportUsersToMindbox: error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ], 'error');

            return [
                'total_clients' => $totalClients,
                'start_date' => $startDate,
                'result' => [
                    'users_count' => $totalClients,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'transaction_id' => $transactionId,
                    'message' => strpos($e->getMessage(), '429') !== false
                        ? 'Превышен лимит запросов. Повторите попытку позже.'
                        : null
                ]
            ];
        }
    }


    /**
     * Экспорт заказов в Mindbox
     * @param string $startDate
     * @return array
     * @throws \Random\RandomException
     */
    public function exportOrdersToMindbox(string $startDate): array
    {
        $totalOrders = $this->dbRepo->countOrdersDataForExport($startDate);

        if ($totalOrders === 0) {
            return [
                'status' => 'error',
                'message' => 'Нет заказов для экспорта'
            ];
        }

        $transactionId = $this->generateGUID();

        try {
            $csvContent = $this->getOrdersCsv($startDate);
            $response = $this->apiClient->sendBatchStream(
                $csvContent,
                $transactionId,
                'RetailOrder.Import'
            );

            return [
                'total_orders' => $totalOrders,
                'start_date' => $startDate,
                'result' => [
                    'orders_count' => $totalOrders,
                    'transaction_id' => $transactionId,
                    'status' => 'success',
                    'response' => $response
                ]
            ];
        } catch (Exception $e) {
            return [
                'total_orders' => $totalOrders,
                'start_date' => $startDate,
                'result' => [
                    'orders_count' => $totalOrders,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'transaction_id' => $transactionId,
                    'message' => strpos($e->getMessage(), '429') !== false
                        ? 'Превышен лимит запросов. Повторите попытку позже.'
                        : null
                ]
            ];
        }
    }


    /**
     * Стриминг CSV пользователей для скачивания
     * @param string $startDate
     * @return void
     */
    public function streamUsersCsvToDownload(string $startDate): void
    {
        $this->csvGenerator->streamToDownload(
            $this->getUsersCsv($startDate),
            'mindbox_users_export_' . date('Y-m-d_His') . '.csv'
        );
    }


    /**
     * Стриминг CSV заказов для скачивания
     * @param string $startDate
     * @return void
     */
    public function streamOrdersCsvToDownload(string $startDate): void
    {
        $this->csvGenerator->streamToDownload(
            $this->getOrdersCsv($startDate),
            'mindbox_orders_export_' . date('Y-m-d_His') . '.csv'
        );
    }


    /**
     * Формирование CSV пользователей
     * @param string $startDate
     * @return string
     * @throws Exception
     */
    private function getUsersCsv(string $startDate): string
    {
        $result = $this->csvGenerator->getUsersHeaders() . "\n";

        $totalClients = $this->dbRepo->countUserDataForExport($startDate);
        $offset = 0;

        while ($offset < $totalClients) {
            $clients = $this->dbRepo->getUserDataForExport($startDate, $offset, $this->batchSize);

            if (empty($clients)) {
                break;
            }

            foreach ($clients as $row) {
                $result .= $this->csvGenerator->formatUserRow($row) . "\n";
            }

            $offset += $this->batchSize;
            unset($clients);
            gc_collect_cycles();
        }

        return $result;
    }


    /**
     * Формирование CSV заказов (без yield, через накопление строки)
     * @param string $startDate
     * @return string
     */
    private function getOrdersCsv(string $startDate): string
    {
        $result = $this->csvGenerator->getOrdersHeaders() . "\n";

        $totalOrders = $this->dbRepo->countOrdersDataForExport($startDate);
        $offset = 0;

        while ($offset < $totalOrders) {
            $ordersData = $this->dbRepo->getOrdersDataForExport($startDate, $offset, $this->batchSize);

            if (empty($ordersData)) {
                break;
            }

            $orderIds = array_keys($ordersData);
            $addonsData = $this->dbRepo->getOrderAddonsBatch($orderIds);

            foreach ($ordersData as $order) {
                $orderId = $order->id;
                $lines = $this->buildOrderLines($order, $addonsData[$orderId] ?? []);

                foreach ($lines as $line) {
                    $result .= $this->csvGenerator->formatOrderLine($order, $line, !empty($addonsData[$orderId])) . "\n";
                }
            }

            $offset += $this->batchSize;
            unset($ordersData, $orderIds, $addonsData);
            gc_collect_cycles();
        }

        return $result;
    }


    /**
     * Построение линий заказа
     * @param object $order
     * @param array $addons
     * @return array
     */
    private function buildOrderLines(object $order, array $addons): array
    {
        $loanStatus = MindboxConstants::getOrderStatus($order->{'1c_status'});
        $lines = [];

        // Основная линия займа
        $lines[] = [
            'line_id' => 1,
            'product_id' => MindboxConstants::ORDER_LINE_MAP['loan_body'],
            'status' => $loanStatus,
            'price' => (int)$order->body_sum,
            'license_key' => '',
            'return_amount' => 0,
            'enddate' => $order->date,
            'startdate' => $order->date,
        ];

        // Дополнительные услуги
        foreach ($addons as $index => $addon) {
            $status = MindboxConstants::getAddonStatus($addon->status, $loanStatus);

            $lines[] = [
                'line_id' => $index + 2,
                'product_id' => MindboxConstants::ORDER_LINE_MAP[$addon->service_type],
                'status' => $status,
                'price' => (int)$addon->amount,
                'license_key' => $addon->license_key,
                'return_amount' => $addon->return_amount,
                'enddate' => $addon->return_date,
                'startdate' => $addon->date_added,
            ];
        }

        return $lines;
    }

    /**
     * Генерация GUID v4
     * @return string
     * @throws \Random\RandomException
     */
    private function generateGUID(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Логирование импорта Mindbox (работает без Application)
     * @param string $message
     * @param array $context
     * @param string $level
     */
    private function logMindbox(string $message, array $context = [], string $level = 'info'): void
    {
        $line = '[MindboxExport] ' . $message;
        if (!empty($context)) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        error_log($line);
        if (function_exists('logger')) {
            try {
                $logger = logger('mindbox');
                $logger->{$level}($message, $context);
            } catch (\Throwable $e) {
                // ignore if Application not bootstrapped
            }
        }
    }
}