<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

ini_set('max_execution_time', -1);
ini_set('memory_limit', '3G');

require_once dirname(__DIR__) . '/api/Simpla.php';
require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload для Composer

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

final class ParseBankIssued extends Simpla
{
    private int $last_id = 0;
    private int $limit = 1000;
    private int $offset = 0;
    private string $lock_file;
    private string $state_file;
    private string $bank_file;
    private array $existing_banks = [];
    private float $start_time;
    private Logger $logger;

    public function __construct()
    {
        parent::__construct();

        $this->start_time = microtime(true);

        $logs_dir = $this->config->root_dir . 'logs/';
        $this->lock_file = $logs_dir . 'parse_bank_issued.pid';
        $this->state_file = $logs_dir . 'parse_bank_issued.state';
        $this->bank_file = $logs_dir . 'bank_list.txt';

        // Инициализируем Monolog
        $this->initLogger($logs_dir);

        $this->loadLastId();
        $this->loadExistingBanks();
    }

    /**
     * Инициализация Monolog
     */
    private function initLogger(string $logs_dir): void
    {
        // Создаем логгер
        $this->logger = new Logger('ParseBankIssued');

        // Форматтер для красивого вывода
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s',
            true,
            true
        );

        // Основной handler с ротацией (хранит 7 дней)
        $mainHandler = new RotatingFileHandler(
            $logs_dir . 'parse_bank_issued.log',
            7,
            Logger::INFO
        );
        $mainHandler->setFormatter($formatter);

        // Handler для ошибок (без ротации, всегда в один файл)
        $errorHandler = new StreamHandler(
            $logs_dir . 'parse_bank_issued_errors.log',
            Logger::ERROR
        );
        $errorHandler->setFormatter($formatter);

        // Handler для дебага (если нужно)
        $debugHandler = new StreamHandler(
            $logs_dir . 'parse_bank_issued_debug.log',
            Logger::DEBUG
        );
        $debugHandler->setFormatter($formatter);

        // Добавляем handlers
        $this->logger->pushHandler($mainHandler);
        $this->logger->pushHandler($errorHandler);

        // Добавляем debug handler только в development
        if ($this->isDevelopment()) {
            $this->logger->pushHandler($debugHandler);
        }

        // Добавляем дополнительную информацию
        $this->logger->pushProcessor(function ($record) {
            $record['extra']['pid'] = getmypid();
            $record['extra']['memory_usage'] = round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB';
            return $record;
        });
    }

    /**
     * Проверяем development окружение
     */
    private function isDevelopment(): bool
    {
        return $_SERVER['APP_ENV'] === 'dev' ||
            $_SERVER['APP_ENV'] === 'local' ||
            $_SERVER['APP_DEBUG'] === 'true';
    }

    public function run()
    {
        $this->logger->info('Script started');

        // Проверяем мьютекс по PID
        if (!$this->acquireLock()) {
            $this->logger->warning('Script is already running');
            return;
        }

        try {
            $total = $this->getTotal();
            $processed = 0;
            $added_banks = 0;

            $this->logger->info("Start parsing from ID: {$this->last_id}, Total records: {$total}");

            do {
                $batch_start_time = microtime(true);
                $items = $this->getItems();

                if (empty($items)) {
                    break;
                }

                $batch_size = count($items);
                $batch_banks_added = 0;

                foreach ($items as $item) {
                    $this->last_id = max($this->last_id, $item->id);

                    if (!empty($item->callback_response)) {
                        $banks_added = $this->parseItem($item);
                        $added_banks += $banks_added;
                        $batch_banks_added += $banks_added;
                    }

                    $processed++;

                    // Логируем прогресс каждые 100 записей
                    if ($processed % 100 === 0) {
                        $current_time = microtime(true);
                        $elapsed = $current_time - $this->start_time;
                        $items_per_second = $processed / $elapsed;

                        $this->logger->info("Progress update", [
                            'processed' => $processed,
                            'total' => $total,
                            'added_banks' => $added_banks,
                            'speed_items_per_second' => round($items_per_second, 2),
                            'elapsed_seconds' => round($elapsed, 2)
                        ]);
                    }
                }

                $batch_time = round(microtime(true) - $batch_start_time, 3);
                $this->offset += $this->limit;
                $this->saveLastId();

                // Логируем производительность пачки
                if ($batch_size > 0) {
                    $batch_speed = round($batch_size / $batch_time, 2);
                    $this->logger->debug("Batch processed", [
                        'batch_size' => $batch_size,
                        'batch_banks_added' => $batch_banks_added,
                        'batch_time_seconds' => $batch_time,
                        'batch_speed_items_per_second' => $batch_speed
                    ]);
                }

            } while (!empty($items));

            $total_time = round(microtime(true) - $this->start_time, 3);
            $average_speed = round($processed / $total_time, 2);

            $this->logger->info("Script completed successfully", [
                'total_processed' => $processed,
                'new_banks_added' => $added_banks,
                'last_processed_id' => $this->last_id,
                'total_time_seconds' => $total_time,
                'average_speed_items_per_second' => $average_speed,
                'memory_peak_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB'
            ]);

        } catch (\Throwable $e) {
            $this->logger->error("Script failed with error", [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        } finally {
            // Всегда освобождаем lock
            $this->releaseLock();
        }
    }

    /**
     * Получаем блокировку через PID
     */
    private function acquireLock(): bool
    {
        if (file_exists($this->lock_file)) {
            $pid = (int)file_get_contents($this->lock_file);

            if ($this->isProcessRunning($pid)) {
                $this->logger->warning("Process with PID {$pid} is still running");
                return false;
            } else {
                $this->logger->info("Process with PID {$pid} not found, removing stale lock");
                unlink($this->lock_file);
            }
        }

        $current_pid = getmypid();
        $result = file_put_contents($this->lock_file, $current_pid) !== false;

        if ($result) {
            $this->logger->debug("Lock acquired", ['pid' => $current_pid]);
        } else {
            $this->logger->error("Failed to acquire lock");
        }

        return $result;
    }

    /**
     * Проверяем running process по PID
     */
    private function isProcessRunning(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        if (function_exists('posix_kill')) {
            return posix_kill($pid, 0);
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = [];
            exec("tasklist /FI \"PID eq {$pid}\" 2>NUL", $output);
            return count($output) > 1;
        }

        $output = [];
        $result = null;
        exec("ps -p {$pid} 2>/dev/null", $output, $result);
        return $result === 0 && count($output) > 1;
    }

    /**
     * Освобождаем блокировку
     */
    private function releaseLock(): void
    {
        if (file_exists($this->lock_file)) {
            $current_pid = getmypid();
            $file_pid = (int)file_get_contents($this->lock_file);

            if ($file_pid === $current_pid) {
                unlink($this->lock_file);
                $this->logger->debug("Lock released", ['pid' => $current_pid]);
            } else {
                $this->logger->warning("Lock file PID mismatch", [
                    'file_pid' => $file_pid,
                    'current_pid' => $current_pid
                ]);
            }
        }
    }

    /**
     * Загружаем последний обработанный ID
     */
    private function loadLastId(): void
    {
        if (file_exists($this->state_file)) {
            $content = file_get_contents($this->state_file);
            if ($content !== false && is_numeric($content)) {
                $this->last_id = (int)$content;
                $this->logger->debug("Last ID loaded", ['last_id' => $this->last_id]);
            }
        }
    }

    /**
     * Сохраняем последний обработанный ID
     */
    private function saveLastId(): void
    {
        file_put_contents($this->state_file, $this->last_id);
        $this->logger->debug("Last ID saved", ['last_id' => $this->last_id]);
    }

    /**
     * Загружаем существующие банки из файла
     */
    private function loadExistingBanks(): void
    {
        if (file_exists($this->bank_file)) {
            $content = file_get_contents($this->bank_file);
            if ($content !== false) {
                $banks = explode(PHP_EOL, trim($content));
                $this->existing_banks = array_flip($banks);
                $this->logger->info("Existing banks loaded", [
                    'banks_count' => count($this->existing_banks)
                ]);
            }
        } else {
            $this->logger->info("Bank file not found, starting fresh");
        }
    }

    /**
     * @return array|false
     */
    private function getItems()
    {
        $sql = $this->db->placehold("SELECT id, callback_response FROM b2p_transactions WHERE id > ? ORDER BY id ASC LIMIT ?", $this->last_id, $this->limit);
        $this->db->query($sql);

        $items = $this->db->results();
        $this->logger->debug("Fetched items from database", [
            'offset' => $this->offset,
            'limit' => $this->limit,
            'items_count' => is_array($items) ? count($items) : 0
        ]);

        return $items;
    }

    /**
     * @return false|int
     */
    private function getTotal()
    {
        $sql = $this->db->placehold("SELECT COUNT(*) as total FROM b2p_transactions", [$this->last_id]);
        $this->db->query($sql);

        $total = $this->db->result('total');
        $this->logger->debug("Total records calculated", ['total' => $total]);

        return $total;
    }

    /**
     * @param object $item
     * @return int Количество добавленных банков
     */
    private function parseItem(object $item): int
    {
        $added = 0;
        try {
            $data = simplexml_load_string($item->callback_response);
            if (!empty($data->bin_issuer)) {
                $bank_name = trim((string)$data->bin_issuer);
                if (!empty($bank_name) && $this->addBankToFile($bank_name)) {
                    $added = 1;
                    $this->logger->debug("Bank added", [
                        'item_id' => $item->id,
                        'bank_name' => $bank_name
                    ]);
                }
            }
        } catch (Exception $e) {
            $this->logger->error("Error parsing XML", [
                'item_id' => $item->id,
                'error' => $e->getMessage()
            ]);
        }

        return $added;
    }

    /**
     * @param string $bank_name
     * @return bool true если банк добавлен, false если уже существует
     */
    private function addBankToFile(string $bank_name): bool
    {
        if (isset($this->existing_banks[$bank_name])) {
            $this->logger->debug("Bank already exists", ['bank_name' => $bank_name]);
            return false;
        }

        file_put_contents($this->bank_file, $bank_name . PHP_EOL, FILE_APPEND);
        $this->existing_banks[$bank_name] = true;

        return true;
    }

    public function __destruct()
    {
        $this->releaseLock();
        $this->logger->debug("Script destructor called");
    }
}

// Запуск скрипта
try {
    (new ParseBankIssued())->run();
} catch (Exception $e) {
    // Monolog уже залогировал ошибку, просто выходим
    exit(1);
}