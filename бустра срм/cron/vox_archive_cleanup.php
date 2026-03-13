<?php

/**
 * Очистка старых таблиц архива звонков
 *
 * Выполняется 1 числа каждого месяца в 02:00
 * Cron: 0 2 1 * * php /var/www/crm/cron/vox_archive_cleanup.php
 *
 * Действия:
 * 1. Находит таблицы старше 3 лет
 * 2. Удаляет их из архивной БД
 * 3. Обновляет метаданные
 */

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../api/Simpla.php';

use App\Modules\VoxCallsArchive\Application\Service\TableRotationService;

class VoxArchiveCleanupCron extends Simpla
{
    private const LOG_FILE = 'vox_archive_cleanup.txt';

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
        $this->logging(__METHOD__, '', '', 'Начало очистки старых таблиц архива звонков', self::LOG_FILE);

        if ($this->dryRun) {
            $this->logging(__METHOD__, '', '', '[DRY-RUN] Режим тестирования - изменения не будут применены', self::LOG_FILE);
        }

        try {
            $rotationService = new TableRotationService();
            $rotationService->setDryRun($this->dryRun);

            // Выполняем очистку
            $result = $rotationService->cleanup();

            $logLevel = $result['success'] ? '' : 'error';

            $this->logging(
                __METHOD__,
                '',
                $logLevel,
                sprintf(
                    'Результат очистки: %s. Удалено таблиц: %d',
                    $result['message'],
                    count($result['deleted_tables'] ?? [])
                ),
                self::LOG_FILE
            );

            // Логируем удалённые таблицы
            if (!empty($result['deleted_tables'])) {
                foreach ($result['deleted_tables'] as $table) {
                    $this->logging(__METHOD__, '', '', 'Удалена таблица: ' . $table, self::LOG_FILE);
                }
            }

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
                'Критическая ошибка очистки: ' . $e->getMessage(),
                self::LOG_FILE
            );

            echo json_encode([
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

            exit(1);
        }

        $this->logging(__METHOD__, '', '', 'Завершение очистки старых таблиц архива звонков', self::LOG_FILE);
    }

    /**
     * Показать информацию о состоянии архива
     */
    public function info(): void
    {
        try {
            $rotationService = new TableRotationService();

            $info = $rotationService->getArchiveInfo();

            echo "=== Состояние архива звонков ===" . PHP_EOL . PHP_EOL;

            echo "Активная таблица:" . PHP_EOL;
            echo "  Имя: " . $info['active_table']['name'] . PHP_EOL;
            echo "  Существует: " . ($info['active_table']['exists'] ? 'Да' : 'Нет') . PHP_EOL;
            echo "  Записей: " . number_format($info['active_table']['records']) . PHP_EOL;
            echo PHP_EOL;

            echo "Архивные таблицы (" . count($info['archive_tables']) . "):" . PHP_EOL;
            foreach ($info['archive_tables'] as $table) {
                echo sprintf(
                    "  %s | %s | %s записей | Ротация: %s | Истекает: %s",
                    $table['table_name'],
                    $table['year_month'],
                    number_format($table['records_count']),
                    $table['rotated_at'],
                    $table['expires_at']
                ) . PHP_EOL;
            }

        } catch (\Exception $e) {
            echo "Ошибка: " . $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }
}

// Определяем команду
$action = 'run';
global $argv;
if (isset($argv[1]) && $argv[1] === '--info') {
    $action = 'info';
}

// Запуск
$cron = new VoxArchiveCleanupCron();

if ($action === 'info') {
    $cron->info();
} else {
    $cron->run();
}
