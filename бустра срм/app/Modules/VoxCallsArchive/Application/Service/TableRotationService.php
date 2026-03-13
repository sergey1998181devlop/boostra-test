<?php

namespace App\Modules\VoxCallsArchive\Application\Service;

use App\Infrastructure\Database\DatabaseManager;
use App\Modules\VoxCallsArchive\Infrastructure\Repository\VoxCallArchiveRepository;
use Medoo\Medoo;

/**
 * Class TableRotationService
 * Сервис для ротации таблиц архива звонков
 */
class TableRotationService
{
    private const ACTIVE_TABLE = 's_vox_calls';
    private const ARCHIVE_TABLE_PREFIX = 's_vox_calls_';
    private const META_TABLE = 's_vox_calls_archive_meta';
    private const RETENTION_YEARS = 3;

    /** @var Medoo */
    private $archiveDb;

    /** @var VoxCallArchiveRepository */
    private $repository;

    /** @var bool */
    private $dryRun = false;

    public function __construct()
    {
        $this->archiveDb = DatabaseManager::singleton()->connection('archive');
        $this->repository = new VoxCallArchiveRepository();
    }

    /**
     * Установить режим dry-run (только вывод, без изменений)
     *
     * @param bool $dryRun
     * @return self
     */
    public function setDryRun(bool $dryRun): self
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    /**
     * Выполнить ротацию таблиц (вызывается 1 числа каждого месяца)
     *
     * @return array Результат операции
     */
    public function rotate(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'old_table' => null,
            'new_table' => self::ACTIVE_TABLE,
            'records_count' => 0,
            'errors' => [],
        ];

        try {
            // Определяем имя архивной таблицы для прошлого месяца
            $lastMonth = new \DateTime('first day of last month');
            $archiveTableName = self::ARCHIVE_TABLE_PREFIX . $lastMonth->format('Y_m');
            $yearMonth = $lastMonth->format('Y-m');

            $result['old_table'] = $archiveTableName;

            // Проверяем, не была ли ротация уже выполнена
            if ($this->repository->tableExists($archiveTableName)) {
                $result['message'] = "Rotation already performed for {$yearMonth}. Table {$archiveTableName} already exists.";
                $result['success'] = true;
                return $result;
            }

            // Проверяем существование активной таблицы
            if (!$this->repository->tableExists(self::ACTIVE_TABLE)) {
                throw new \RuntimeException("Active table " . self::ACTIVE_TABLE . " does not exist in archive database");
            }

            // Получаем статистику перед ротацией
            $stats = $this->getTableStats(self::ACTIVE_TABLE);
            $result['records_count'] = $stats['count'];

            if ($this->dryRun) {
                $result['success'] = true;
                $result['message'] = "[DRY-RUN] Would rename " . self::ACTIVE_TABLE . " to {$archiveTableName} ({$stats['count']} records)";
                return $result;
            }

            // 1. Переименовываем активную таблицу в архивную
            $this->renameTable(self::ACTIVE_TABLE, $archiveTableName);

            // 2. Создаём новую пустую активную таблицу
            $this->createActiveTable();

            // 3. Записываем метаданные в основную БД
            $this->saveRotationMeta($archiveTableName, $yearMonth, $stats);

            $result['success'] = true;
            $result['message'] = "Successfully rotated table to {$archiveTableName} ({$stats['count']} records)";

        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            $result['message'] = "Rotation failed: " . $e->getMessage();
        }

        return $result;
    }

    /**
     * Очистка старых архивных таблиц (старше RETENTION_YEARS лет)
     *
     * @return array Результат операции
     */
    public function cleanup(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'deleted_tables' => [],
            'errors' => [],
        ];

        try {
            // Вычисляем дату отсечения (3 года назад)
            $cutoffDate = new \DateTime();
            $cutoffDate->modify('-' . self::RETENTION_YEARS . ' years');
            $cutoffYearMonth = $cutoffDate->format('Y-m');

            // Получаем список таблиц для удаления из метаданных
            $tablesToDelete = $this->getExpiredTables($cutoffYearMonth);

            if (empty($tablesToDelete)) {
                $result['success'] = true;
                $result['message'] = "No tables to cleanup (cutoff: {$cutoffYearMonth})";
                return $result;
            }

            foreach ($tablesToDelete as $tableInfo) {
                $tableName = $tableInfo['table_name'];

                if ($this->dryRun) {
                    $result['deleted_tables'][] = "[DRY-RUN] Would delete: {$tableName}";
                    continue;
                }

                try {
                    // Удаляем таблицу из архивной БД
                    if ($this->repository->tableExists($tableName)) {
                        $this->dropTable($tableName);
                    }

                    // Обновляем метаданные
                    $this->markTableAsDeleted($tableInfo['id']);

                    $result['deleted_tables'][] = $tableName;

                } catch (\Exception $e) {
                    $result['errors'][] = "Failed to delete {$tableName}: " . $e->getMessage();
                }
            }

            $count = count($result['deleted_tables']);
            $result['success'] = empty($result['errors']);
            $result['message'] = $this->dryRun
                ? "[DRY-RUN] Would delete {$count} tables"
                : "Deleted {$count} tables";

        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            $result['message'] = "Cleanup failed: " . $e->getMessage();
        }

        return $result;
    }

    /**
     * Получить статистику таблицы
     *
     * @param string $tableName
     * @return array
     */
    private function getTableStats(string $tableName): array
    {
        $count = $this->archiveDb->count($tableName);

        $minMax = $this->archiveDb->get($tableName, [
            'min_datetime' => \Medoo\Medoo::raw('MIN(datetime_start)'),
            'max_datetime' => \Medoo\Medoo::raw('MAX(datetime_start)'),
        ]);

        return [
            'count' => $count,
            'min_datetime' => $minMax['min_datetime'] ?? null,
            'max_datetime' => $minMax['max_datetime'] ?? null,
        ];
    }

    /**
     * Переименовать таблицу
     *
     * @param string $from
     * @param string $to
     */
    private function renameTable(string $from, string $to): void
    {
        $sql = "RENAME TABLE `{$from}` TO `{$to}`";
        $this->archiveDb->query($sql);
    }

    /**
     * Создать новую активную таблицу
     */
    private function createActiveTable(): void
    {
        $sql = "CREATE TABLE `" . self::ACTIVE_TABLE . "` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `cost` DECIMAL(10,4) DEFAULT NULL,
            `call_result_code` VARCHAR(50) DEFAULT NULL,
            `datetime_start` DATETIME DEFAULT NULL,
            `duration` INT DEFAULT NULL,
            `vox_call_id` BIGINT DEFAULT NULL,
            `is_incoming` TINYINT(1) DEFAULT NULL,
            `phone_a` VARCHAR(20) DEFAULT NULL,
            `phone_b` VARCHAR(20) DEFAULT NULL,
            `scenario_id` INT DEFAULT NULL,
            `tags` TEXT DEFAULT NULL,
            `created` DATETIME DEFAULT NULL,
            `user_id` INT DEFAULT NULL,
            `queue_id` BIGINT UNSIGNED DEFAULT NULL,
            `vox_user_id` BIGINT UNSIGNED DEFAULT NULL,
            `record_url` VARCHAR(1024) DEFAULT NULL,
            `assessment` TINYINT UNSIGNED DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `ux_vox_call_id` (`vox_call_id`),
            KEY `ix_user_id` (`user_id`),
            KEY `ix_created` (`created`),
            KEY `ix_datetime_start` (`datetime_start`),
            KEY `ix_vox_user_id_datetime` (`vox_user_id`, `datetime_start`),
            KEY `ix_assessment_user_dt` (`assessment`, `vox_user_id`, `datetime_start`),
            KEY `ix_queue_dt` (`queue_id`, `datetime_start`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->archiveDb->query($sql);
    }

    /**
     * Удалить таблицу
     *
     * @param string $tableName
     */
    private function dropTable(string $tableName): void
    {
        $sql = "DROP TABLE IF EXISTS `{$tableName}`";
        $this->archiveDb->query($sql);
    }

    /**
     * Сохранить метаданные о ротации в архивную БД
     *
     * @param string $tableName
     * @param string $yearMonth
     * @param array $stats
     */
    private function saveRotationMeta(string $tableName, string $yearMonth, array $stats): void
    {
        $expiresAt = new \DateTime();
        $expiresAt->modify('+' . self::RETENTION_YEARS . ' years');

        $this->archiveDb->insert(self::META_TABLE, [
            'table_name' => $tableName,
            'year_month' => $yearMonth,
            'records_count' => $stats['count'],
            'min_datetime' => $stats['min_datetime'],
            'max_datetime' => $stats['max_datetime'],
            'rotated_at' => Medoo::raw('NOW()'),
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Получить список истёкших таблиц
     *
     * @param string $cutoffYearMonth
     * @return array
     */
    private function getExpiredTables(string $cutoffYearMonth): array
    {
        return $this->archiveDb->select(self::META_TABLE, [
            'id',
            'table_name',
            'year_month',
        ], [
            'year_month[<]' => $cutoffYearMonth,
            'is_deleted' => 0,
            'ORDER' => ['year_month' => 'ASC'],
        ]);
    }

    /**
     * Пометить таблицу как удалённую
     *
     * @param int $id
     */
    private function markTableAsDeleted(int $id): void
    {
        $this->archiveDb->update(self::META_TABLE, [
            'is_deleted' => 1,
            'deleted_at' => Medoo::raw('NOW()'),
        ], [
            'id' => $id,
        ]);
    }

    /**
     * Проверить и создать активную таблицу, если она не существует
     *
     * @return bool True если таблица была создана
     */
    public function ensureActiveTableExists(): bool
    {
        if ($this->repository->tableExists(self::ACTIVE_TABLE)) {
            return false;
        }

        if ($this->dryRun) {
            return false;
        }

        $this->createActiveTable();
        return true;
    }

    /**
     * Получить информацию о состоянии архива
     *
     * @return array
     */
    public function getArchiveInfo(): array
    {
        $info = [
            'active_table' => [
                'name' => self::ACTIVE_TABLE,
                'exists' => $this->repository->tableExists(self::ACTIVE_TABLE),
                'records' => 0,
            ],
            'archive_tables' => [],
        ];

        if ($info['active_table']['exists']) {
            $info['active_table']['records'] = $this->archiveDb->count(self::ACTIVE_TABLE);
        }

        // Получаем информацию об архивных таблицах из метаданных
        $rows = $this->archiveDb->select(self::META_TABLE, '*', [
            'is_deleted' => 0,
            'ORDER' => ['year_month' => 'DESC'],
        ]);

        foreach ($rows as $row) {
            $info['archive_tables'][] = [
                'table_name' => $row['table_name'],
                'year_month' => $row['year_month'],
                'records_count' => $row['records_count'],
                'rotated_at' => $row['rotated_at'],
                'expires_at' => $row['expires_at'],
            ];
        }

        return $info;
    }
}
