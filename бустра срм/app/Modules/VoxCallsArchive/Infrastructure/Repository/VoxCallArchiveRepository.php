<?php

namespace App\Modules\VoxCallsArchive\Infrastructure\Repository;

use App\Infrastructure\Database\DatabaseManager;
use App\Modules\VoxCallsArchive\Application\DTO\VoxCallDTO;
use App\Modules\VoxCallsArchive\Domain\Repository\VoxCallArchiveRepositoryInterface;
use Medoo\Medoo;

/**
 * Class VoxCallArchiveRepository
 * Репозиторий для работы с архивом звонков Voximplant
 */
class VoxCallArchiveRepository implements VoxCallArchiveRepositoryInterface
{
    private const ACTIVE_TABLE = 's_vox_calls';
    private const ARCHIVE_TABLE_PREFIX = 's_vox_calls_';

    /** @var Medoo */
    private $archiveDb;

    /** @var string */
    private $databaseName;

    public function __construct()
    {
        $this->archiveDb = DatabaseManager::singleton()->connection('archive');

        $pdo = $this->archiveDb->pdo;
        $stmt = $pdo->query("SELECT DATABASE() as db");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->databaseName = $result['db'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function save(VoxCallDTO $dto): ?int
    {
        // Проверяем, существует ли уже такой звонок
        if ($dto->voxCallId && $this->existsByVoxCallId($dto->voxCallId)) {
            return null;
        }

        $data = $dto->toDbArray();

        // Удаляем null значения
        $data = array_filter($data, function ($value) {
            return $value !== null;
        });

        $this->archiveDb->insert(self::ACTIVE_TABLE, $data);

        $lastId = $this->archiveDb->id();
        return $lastId ? (int)$lastId : null;
    }

    /**
     * {@inheritdoc}
     */
    public function updateByVoxCallId(int $voxCallId, array $data): bool
    {
        $rowsAffected = $this->archiveDb->update(
            self::ACTIVE_TABLE,
            $data,
            ['vox_call_id' => $voxCallId]
        );

        // Если не нашли в активной таблице, ищем в архивных
        if ($rowsAffected === 0) {
            $archiveTables = $this->getRecentArchiveTables(3);
            foreach ($archiveTables as $tableName) {
                $rowsAffected = $this->archiveDb->update(
                    $tableName,
                    $data,
                    ['vox_call_id' => $voxCallId]
                );
                if ($rowsAffected > 0) {
                    return true;
                }
            }
        }

        return $rowsAffected > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function existsByVoxCallId(int $voxCallId): bool
    {
        // Сначала проверяем активную таблицу
        if ($this->archiveDb->has(self::ACTIVE_TABLE, ['vox_call_id' => $voxCallId])) {
            return true;
        }

        // Проверяем последние 3 архивные таблицы
        $archiveTables = $this->getRecentArchiveTables(3);
        foreach ($archiveTables as $tableName) {
            if ($this->archiveDb->has($tableName, ['vox_call_id' => $voxCallId])) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalls(array $filter): array
    {
        $where = [];

        if (!empty($filter['user_id'])) {
            $userIds = is_array($filter['user_id']) ? $filter['user_id'] : [$filter['user_id']];
            $where['user_id'] = $userIds;
        }

        if (!empty($filter['date_from'])) {
            $where['created[>=]'] = $filter['date_from'];
        }

        if (!empty($filter['date_to'])) {
            $where['created[<=]'] = $filter['date_to'];
        }

        return $this->archiveDb->select(self::ACTIVE_TABLE, '*', $where);
    }

    /**
     * {@inheritdoc}
     */
    public function getCallsForPeriod(string $dateFrom, string $dateTo, array $additionalFilter = []): array
    {
        $yearMonthFrom = date('Y-m', strtotime($dateFrom));
        $yearMonthTo = date('Y-m', strtotime($dateTo));

        // Получаем список таблиц для запроса
        $tables = $this->getTablesForPeriod($yearMonthFrom, $yearMonthTo);

        $allResults = [];

        foreach ($tables as $tableName) {
            $where = [
                'AND' => [
                    'created[>=]' => $dateFrom,
                    'created[<=]' => $dateTo,
                ]
            ];

            // Добавляем дополнительные фильтры
            foreach ($additionalFilter as $key => $value) {
                if (is_array($value)) {
                    $where['AND'][$key] = $value;
                } else {
                    $where['AND'][$key] = $value;
                }
            }

            try {
                $results = $this->archiveDb->select($tableName, '*', $where);
                $allResults = array_merge($allResults, $results);
            } catch (\Exception $e) {
                // Таблица может не существовать, пропускаем
                continue;
            }
        }

        return $allResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveTableName(): string
    {
        return self::ACTIVE_TABLE;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchiveTablesForPeriod(string $yearMonthFrom, string $yearMonthTo): array
    {
        $tables = [];
        $current = $yearMonthFrom;

        while ($current <= $yearMonthTo) {
            $tableName = self::ARCHIVE_TABLE_PREFIX . str_replace('-', '_', $current);
            $tables[] = $tableName;
            $current = date('Y-m', strtotime($current . '-01 +1 month'));
        }

        return $tables;
    }

    /**
     * Получить таблицы для указанного периода (активная + архивные)
     *
     * @param string $yearMonthFrom
     * @param string $yearMonthTo
     * @return array
     */
    private function getTablesForPeriod(string $yearMonthFrom, string $yearMonthTo): array
    {
        $tables = [];
        $currentYearMonth = date('Y-m');

        // Если период включает текущий месяц, добавляем активную таблицу
        if ($currentYearMonth >= $yearMonthFrom && $currentYearMonth <= $yearMonthTo) {
            $tables[] = self::ACTIVE_TABLE;
        }

        // Добавляем архивные таблицы за прошлые месяцы
        $current = $yearMonthFrom;
        while ($current <= $yearMonthTo) {
            if ($current < $currentYearMonth) {
                $tableName = self::ARCHIVE_TABLE_PREFIX . str_replace('-', '_', $current);
                $tables[] = $tableName;
            }
            $current = date('Y-m', strtotime($current . '-01 +1 month'));
        }

        return $tables;
    }

    /**
     * Получить последние N архивных таблиц
     *
     * @param int $count
     * @return array
     */
    private function getRecentArchiveTables(int $count): array
    {
        $tables = [];
        $currentDate = new \DateTime();

        for ($i = 1; $i <= $count; $i++) {
            $currentDate->modify('-1 month');
            $yearMonth = $currentDate->format('Y_m');
            $tables[] = self::ARCHIVE_TABLE_PREFIX . $yearMonth;
        }

        return $tables;
    }

    /**
     * Проверить существование таблицы в архивной БД
     *
     * @param string $tableName
     * @return bool
     */
    public function tableExists(string $tableName): bool
    {
        try {
            $dbName = $this->databaseName;
            $result = $this->archiveDb->query(
                "SELECT 1 FROM information_schema.tables WHERE table_schema = '{$dbName}' AND table_name = '{$tableName}' LIMIT 1"
            );

            if ($result && $result->rowCount() > 0) {
                return true;
            }

            $result = $this->archiveDb->query("SHOW TABLES LIKE '{$tableName}'");
            $exists = $result && $result->rowCount() > 0;

            error_log('VoxCallArchiveRepository::tableExists - table: ' . $tableName . ', db: ' . $dbName . ', exists: ' . ($exists ? 'true' : 'false'));

            return $exists;
        } catch (\Exception $e) {
            error_log('VoxCallArchiveRepository::tableExists - exception for table ' . $tableName . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Выполнить произвольный SQL запрос
     *
     * @param string $sql
     * @return \PDOStatement|null
     */
    public function query(string $sql): ?\PDOStatement
    {
        return $this->archiveDb->query($sql);
    }

    /**
     * Получить PDO объект
     *
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->archiveDb->pdo;
    }
}
