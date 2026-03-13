<?php

/**
 * Ротация таблиц архива звонков
 *
 * Выполняется 1 числа каждого месяца в 00:05
 * Cron: 5 0 1 * * php /var/www/crm/cron/vox_archive_table_rotation.php
 *
 * Действия:
 * 1. Переименовывает активную таблицу s_vox_calls в s_vox_calls_YYYY_MM
 * 2. Создаёт новую пустую таблицу s_vox_calls
 * 3. Записывает метаданные о ротации
 */

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '300');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../api/Simpla.php';

use App\Modules\VoxCallsArchive\Application\Service\TableRotationService;

class VoxArchiveTableRotationCron extends Simpla
{
    private const LOG_FILE = 'vox_archive_rotation.txt';

    /** @var bool */
    private $dryRun = false;

    public function __construct()
    {
        parent::__construct();

        // Проверяем флаг --dry-run
        global $argv;
        if (isset($argv) && in_array('--dry-run', $argv)) {
            $this->dryRun = true;
        }
    }

    public function run(): void
    {
        $this->logging(__METHOD__, '', '', 'Начало ротации таблиц архива звонков', self::LOG_FILE);

        if ($this->dryRun) {
            $this->logging(__METHOD__, '', '', '[DRY-RUN] Режим тестирования - изменения не будут применены', self::LOG_FILE);
        }

        try {
            $rotationService = new TableRotationService();
            $rotationService->setDryRun($this->dryRun);

            // Выполняем ротацию
            $result = $rotationService->rotate();

            $logLevel = $result['success'] ? '' : 'error';

            $this->logging(
                __METHOD__,
                '',
                $logLevel,
                sprintf(
                    'Результат ротации: %s. Таблица: %s, Записей: %d',
                    $result['message'],
                    $result['old_table'] ?? 'N/A',
                    $result['records_count'] ?? 0
                ),
                self::LOG_FILE
            );

            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    $this->logging(__METHOD__, '', 'error', 'Ошибка: ' . $error, self::LOG_FILE);
                }
            }

            // Выводим результат в stdout для CLI
            echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

        } catch (\Exception $e) {
            $this->logging(
                __METHOD__,
                '',
                'error',
                'Критическая ошибка ротации: ' . $e->getMessage(),
                self::LOG_FILE
            );

            echo json_encode([
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

            exit(1);
        }

        $this->logging(__METHOD__, '', '', 'Завершение ротации таблиц архива звонков', self::LOG_FILE);
    }
}

// Запуск
$cron = new VoxArchiveTableRotationCron();
$cron->run();
