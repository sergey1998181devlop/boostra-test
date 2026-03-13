<?php
// cron/sync_orders_status_1c.php

/**
 * Массовая синхронизация статусов заявок с 1С
 * Запуск: php cron/sync_orders_status_1c.php?date=2025-10-26
 * Сухой прогон: php cron/sync_orders_status_1c.php?date=2025-10-26&dry_run=1
 */

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);

chdir(dirname(__FILE__));

date_default_timezone_set('Europe/Moscow');

define('APP_ROOT', dirname(__FILE__) . '/..');

require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/api/Simpla.php';

use App\Service\OneC\OneCClient;

class SyncOrdersStatus1C extends Simpla
{
    private const BATCH_SIZE = 100;
    private const MAX_PAGES = 200;
    private const LOG_FILE = 'sync_orders_status_1c.txt';
    private const LOCK_FILE = APP_ROOT . '/files/cache/sync_orders_status_1c.lock';
    
    private $oneCClient;
    private $dryRun = false;
    
    public function __construct()
    {
        parent::__construct();
        $this->oneCClient = new OneCClient();
    }
    
    public function run(): void
    {
        echo "=== Синхронизация статусов заявок с 1С ===\n";
        
        if (!$this->acquireLock()) {
            echo "ОШИБКА: Синхронизация уже запущена! Дождитесь завершения предыдущего процесса.\n";
            echo "Если это ошибка, удалите файл: " . self::LOCK_FILE . "\n";
            exit(1);
        }
        
        $startTime = microtime(true);
        
        try {
            $this->dryRun = (bool)$this->request->get('dry_run', 'integer');
            if ($this->dryRun) {
                echo "РЕЖИМ СУХОГО ПРОГОНА (данные не будут обновлены)\n\n";
            }

            $date = $this->request->get('date', 'string');

            if (empty($date)) {
                $date = date('Y-m-d', strtotime('-1 day'));
            }
            
            if (!strtotime($date)) {
                echo "ОШИБКА: Неверный формат даты. Используйте Y-m-d (например: 2025-10-26)\n";
                exit(1);
            }
            
            echo "Обработка за: {$date}\n\n";

            $filter = [
                'date_from' => $date,
                'date_to' => $date,
                'limit' => self::BATCH_SIZE,
            ];
            
            $totalUpdated = 0;
            $totalErrors = 0;
            $totalProcessed = 0;
            $page = 1;
            
            while (true) {
                if ($page > self::MAX_PAGES) {
                    echo "\nДостигнут лимит страниц ({$page}). Остановка.\n";
                    $this->logging('warning', '', '', "Достигнут лимит страниц: {$page}", self::LOG_FILE);
                    break;
                }
                
                $filter['page'] = $page;
                $pageStart = microtime(true);
                
                echo "[" . date('H:i:s') . "] Страница {$page}... ";
                $orders = $this->orders->get_orders($filter);
                
                if (empty($orders)) {
                    echo "нет заявок. Завершение.\n";
                    break;
                }
                
                $ordersCount = count($orders);
                $totalProcessed += $ordersCount;
                echo "получено: {$ordersCount}, ";
                
                $result = $this->processBatch($orders);
                $totalUpdated += $result['updated'];
                $totalErrors += $result['errors'];
                
                $pageTime = round(microtime(true) - $pageStart, 2);
                echo "обновлено: {$result['updated']}, ошибок: {$result['errors']}, время: {$pageTime}с\n";
                
                if (count($orders) < self::BATCH_SIZE) {
                    echo "Последняя страница. Завершение.\n";
                    break;
                }
                
                $page++;
                sleep(1);
            }
            
            $totalTime = round(microtime(true) - $startTime, 2);
            echo "\n=== ИТОГО ===\n";
            echo "Обработано заявок: {$totalProcessed}\n";
            echo "Обновлено: {$totalUpdated}\n";
            echo "Ошибок: {$totalErrors}\n";
            echo "Время выполнения: {$totalTime}с\n";
            
            if ($this->dryRun) {
                echo "\nЭто был сухой прогон. Данные не были обновлены.\n";
            }
            
            $this->logging('info', '', '', "Обработано: {$totalProcessed}, обновлено: {$totalUpdated}, ошибок: {$totalErrors}, время: {$totalTime}с", self::LOG_FILE);
            
        } catch (\Exception $e) {
            $this->logging('error', '', '', "ОШИБКА: {$e->getMessage()}", self::LOG_FILE);
            echo "\nКРИТИЧЕСКАЯ ОШИБКА: {$e->getMessage()}\n";
            throw $e;
        } finally {
            $this->releaseLock();
        }
    }
    
    private function acquireLock(): bool
    {
        if (file_exists(self::LOCK_FILE)) {
            $lockData = @file_get_contents(self::LOCK_FILE);
            if ($lockData) {
                $lock = json_decode($lockData, true);
                $lockAge = time() - ($lock['timestamp'] ?? 0);

                if ($lockAge > 1800) {
                    @unlink(self::LOCK_FILE);
                    echo "Удален устаревший lock (возраст: {$lockAge}с)\n";
                } else {
                    return false;
                }
            }
        }

        $lockData = json_encode([
            'timestamp' => time(),
            'pid' => getmypid(),
            'started_at' => date('Y-m-d H:i:s'),
        ]);
        
        return @file_put_contents(self::LOCK_FILE, $lockData) !== false;
    }
    
    private function releaseLock(): void
    {
        if (file_exists(self::LOCK_FILE)) {
            @unlink(self::LOCK_FILE);
        }
    }
    
    private function processBatch(array $orders): array
    {
        $ordersToCheck = [];
        $orderMap = [];
        
        foreach ($orders as $order) {
            if (empty($order->id_1c) || empty($order->status_1c)) {
                continue;
            }
            
            $ordersToCheck[] = [
                'OrderNumber' => $order->id_1c,
                'Status' => $order->status_1c,
            ];
            
            $orderMap[$order->id_1c] = $order;
        }
        
        if (empty($ordersToCheck)) {
            return ['updated' => 0, 'errors' => 0];
        }

        $result = $this->oneCClient->checkOrderStatuses($ordersToCheck, 'batch');
        
        if ($result === null || !($result['Success'] ?? false)) {
            $error = $result['Error'] ?? 'Неизвестная ошибка';
            echo "Ошибка при обращении к 1С: {$error}\n";
            $this->logging('error', '', $ordersToCheck, $result, self::LOG_FILE);
            return ['updated' => 0, 'errors' => count($ordersToCheck)];
        }

        $updatedCount = 0;
        $ordersToUpdate = $result['Data'] ?? [];
        
        foreach ($ordersToUpdate as $orderData) {
            $oneCId = $orderData['OrderNumber'];
            $newStatus = $orderData['Status'];
            
            if (!isset($orderMap[$oneCId])) {
                continue;
            }
            
            if (empty($newStatus)) {
                echo "Заявка ({$oneCId}): пустой статус от 1С, пропускаем\n";
                continue;
            }
            
            $order = $orderMap[$oneCId];
            
            try {
                if (!$this->dryRun) {
                    $this->orders->update_order($order->order_id, [
                        '1c_status' => $newStatus,
                        'modified' => date('Y-m-d H:i:s'),
                    ]);
                }
                
                $prefix = $this->dryRun ? '[DRY] ' : '';
                echo "{$prefix}Заявка #{$order->order_id} ({$oneCId}): {$order->status_1c} → {$newStatus}\n";
                $updatedCount++;
            } catch (\Exception $e) {
                echo "Ошибка обновления заявки #{$order->order_id}: {$e->getMessage()}\n";
                $this->logging('error', '', $order, ['error' => $e->getMessage()], self::LOG_FILE);
            }
        }
        
        return ['updated' => $updatedCount, 'errors' => 0];
    }
}

$cron = new SyncOrdersStatus1C();
$cron->run();
