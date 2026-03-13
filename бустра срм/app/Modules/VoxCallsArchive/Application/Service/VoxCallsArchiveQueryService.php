<?php

namespace App\Modules\VoxCallsArchive\Application\Service;

use App\Infrastructure\Database\DatabaseManager;
use App\Modules\VoxCallsArchive\Infrastructure\Repository\VoxCallArchiveRepository;
use Medoo\Medoo;

/**
 * Class VoxCallsArchiveQueryService
 * Сервис для чтения звонков из архивной базы данных
 */
class VoxCallsArchiveQueryService
{
    private const ACTIVE_TABLE = 's_vox_calls';
    private const ARCHIVE_TABLE_PREFIX = 's_vox_calls_';

    /** @var Medoo */
    private $archiveDb;

    /** @var VoxCallArchiveRepository */
    private $repository;

    public function __construct()
    {
        $this->archiveDb = DatabaseManager::singleton()->connection('archive');
        $this->repository = new VoxCallArchiveRepository();
    }

    /**
     * Получить звонки за период
     * Автоматически определяет, какие таблицы нужно запросить
     *
     * @param string $dateFrom Дата начала (Y-m-d или Y-m-d H:i:s)
     * @param string $dateTo Дата конца (Y-m-d или Y-m-d H:i:s)
     * @param array $where Дополнительные условия
     * @return array
     */
    public function getCallsForPeriod(string $dateFrom, string $dateTo, array $where = []): array
    {
        $tables = $this->getTablesForPeriod($dateFrom, $dateTo);
        $allResults = [];

        foreach ($tables as $tableName) {
            try {
                $whereClause = array_merge([
                    'datetime_start[>=]' => $dateFrom,
                    'datetime_start[<=]' => $dateTo,
                ], $where);

                $results = $this->archiveDb->select($tableName, '*', $whereClause);
                if ($results) {
                    $allResults = array_merge($allResults, $results);
                }
            } catch (\Exception $e) {
                // Таблица может не существовать, пропускаем
                continue;
            }
        }

        return $allResults;
    }

    /**
     * Получить агрегированные данные по звонкам пользователей за период
     * Используется для отчётов типа MissingService
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param array|null $userIds Фильтр по user_id (если null - все пользователи)
     * @return array Ассоциативный массив user_id => статистика
     */
    public function getCallsAggregatedByUser(string $dateFrom, string $dateTo, array $userIds = null): array
    {
        $tables = $this->getTablesForPeriod($dateFrom, $dateTo);
        $aggregated = [];

        foreach ($tables as $tableName) {
            try {
                $sql = "SELECT
                    user_id,
                    MAX(created) AS last_call,
                    COUNT(*) AS total_calls,
                    SUM(CASE WHEN duration > 0 THEN 1 ELSE 0 END) AS accepted_calls,
                    SUM(CASE WHEN duration = 0 THEN 1 ELSE 0 END) AS not_accepted_calls,
                    SUM(CASE WHEN duration > 0 THEN duration ELSE 0 END) AS total_duration_accepted_calls,
                    SUM(duration) AS total_duration_all_calls,
                    AVG(CASE WHEN duration > 0 THEN duration END) AS avg_accepted_duration_per_user
                FROM `{$tableName}`
                WHERE created BETWEEN :dateFrom AND :dateTo";

                $params = [
                    ':dateFrom' => $dateFrom,
                    ':dateTo' => $dateTo,
                ];

                if ($userIds !== null && !empty($userIds)) {
                    $placeholders = [];
                    foreach ($userIds as $i => $userId) {
                        $key = ':userId' . $i;
                        $placeholders[] = $key;
                        $params[$key] = $userId;
                    }
                    $sql .= " AND user_id IN (" . implode(',', $placeholders) . ")";
                }

                $sql .= " GROUP BY user_id";

                $pdo = $this->archiveDb->pdo;
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($results as $row) {
                    $userId = $row['user_id'];
                    if (!isset($aggregated[$userId])) {
                        $aggregated[$userId] = [
                            'user_id' => $userId,
                            'last_call' => $row['last_call'],
                            'total_calls' => 0,
                            'accepted_calls' => 0,
                            'not_accepted_calls' => 0,
                            'total_duration_accepted_calls' => 0,
                            'total_duration_all_calls' => 0,
                            'avg_accepted_duration_per_user' => 0,
                        ];
                    }

                    $agg = &$aggregated[$userId];

                    // Обновляем last_call если новее
                    if ($row['last_call'] > $agg['last_call']) {
                        $agg['last_call'] = $row['last_call'];
                    }

                    $agg['total_calls'] += (int)$row['total_calls'];
                    $agg['accepted_calls'] += (int)$row['accepted_calls'];
                    $agg['not_accepted_calls'] += (int)$row['not_accepted_calls'];
                    $agg['total_duration_accepted_calls'] += (int)$row['total_duration_accepted_calls'];
                    $agg['total_duration_all_calls'] += (int)$row['total_duration_all_calls'];

                    // Пересчитываем среднее
                    if ($agg['accepted_calls'] > 0) {
                        $agg['avg_accepted_duration_per_user'] = $agg['total_duration_accepted_calls'] / $agg['accepted_calls'];
                    }
                }
            } catch (\Exception $e) {
                // Таблица может не существовать, пропускаем
                continue;
            }
        }

        return $aggregated;
    }

    /**
     * Получить данные для отчёта VoxCallsReportView
     * Агрегация по операторам (vox_user_id)
     * НЕ включает имена операторов - их нужно получить из основной БД
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param array $queueIds Фильтр по ID очередей (если пустой - все)
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getReportData(
        string $dateFrom,
        string $dateTo,
        array $queueIds = [],
        int $limit = null,
        int $offset = null
    ): array {
        $tables = $this->getTablesForPeriod($dateFrom, $dateTo);

        // Строим UNION ALL запрос для всех таблиц
        $unionParts = [];
        foreach ($tables as $tableName) {
            if (!$this->repository->tableExists($tableName)) {
                continue;
            }
            $unionParts[] = "SELECT * FROM `{$tableName}` WHERE DATE(datetime_start) BETWEEN :dateFrom AND :dateTo";
        }

        if (empty($unionParts)) {
            return [];
        }

        $unionQuery = implode(' UNION ALL ', $unionParts);

        $queueFilter = '';
        if (!empty($queueIds)) {
            $queueFilter = ' AND vcr.queue_id IN (' . implode(',', array_map('intval', $queueIds)) . ')';
        }

        $sql = "SELECT
            vcr.vox_user_id,
            SUM(CASE WHEN vcr.assessment = 1 THEN 1 ELSE 0 END) AS assessment_1,
            SUM(CASE WHEN vcr.assessment = 2 THEN 1 ELSE 0 END) AS assessment_2,
            SUM(CASE WHEN vcr.assessment = 3 THEN 1 ELSE 0 END) AS assessment_3,
            SUM(CASE WHEN vcr.assessment = 4 THEN 1 ELSE 0 END) AS assessment_4,
            SUM(CASE WHEN vcr.assessment = 5 THEN 1 ELSE 0 END) AS assessment_5,
            SUM(CASE WHEN vcr.assessment IS NOT NULL THEN 1 ELSE 0 END) AS total_rated,
            COUNT(*) AS total,
            ROUND(AVG(vcr.assessment), 2) AS avg_assessment,
            ROUND(SUM(CASE WHEN vcr.assessment IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS assessment_percent
        FROM ({$unionQuery}) vcr
        WHERE vcr.vox_user_id IS NOT NULL
          AND vcr.record_url IS NOT NULL
          {$queueFilter}
        GROUP BY vcr.vox_user_id
        ORDER BY vcr.vox_user_id ASC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }

        $pdo = $this->archiveDb->pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':dateFrom', $dateFrom);
        $stmt->bindValue(':dateTo', $dateTo);
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Получить количество уникальных операторов для отчёта
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param array $queueIds Фильтр по ID очередей (если пустой - все)
     * @return int
     */
    public function getReportTotalCount(string $dateFrom, string $dateTo, array $queueIds = []): int
    {
        $tables = $this->getTablesForPeriod($dateFrom, $dateTo);

        $queueFilter = '';
        if (!empty($queueIds)) {
            $queueFilter = ' AND queue_id IN (' . implode(',', array_map('intval', $queueIds)) . ')';
        }

        $unionParts = [];
        foreach ($tables as $tableName) {
            if (!$this->repository->tableExists($tableName)) {
                continue;
            }
            $unionParts[] = "SELECT vox_user_id FROM `{$tableName}` WHERE DATE(datetime_start) BETWEEN :dateFrom AND :dateTo AND vox_user_id IS NOT NULL AND record_url IS NOT NULL{$queueFilter}";
        }

        if (empty($unionParts)) {
            return 0;
        }

        $unionQuery = implode(' UNION ALL ', $unionParts);

        $sql = "SELECT COUNT(DISTINCT vox_user_id) AS total FROM ({$unionQuery}) vcr";

        $pdo = $this->archiveDb->pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':dateFrom', $dateFrom);
        $stmt->bindValue(':dateTo', $dateTo);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Выполнить произвольный SQL-запрос к архивной БД
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function query(string $sql, array $params = []): array
    {
        $pdo = $this->archiveDb->pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Получить список таблиц для указанного периода
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    private function getTablesForPeriod(string $dateFrom, string $dateTo): array
    {
        $tables = [];
        $currentYearMonth = date('Y-m');

        $fromYearMonth = date('Y-m', strtotime($dateFrom));
        $toYearMonth = date('Y-m', strtotime($dateTo));

        // Перебираем все месяцы в периоде
        $current = $fromYearMonth;
        while ($current <= $toYearMonth) {
            if ($current === $currentYearMonth) {
                // Текущий месяц - используем активную таблицу
                $tables[] = self::ACTIVE_TABLE;
            } else {
                // Прошлые месяцы - используем архивные таблицы
                $archiveTable = self::ARCHIVE_TABLE_PREFIX . str_replace('-', '_', $current);
                $tables[] = $archiveTable;
            }

            $current = date('Y-m', strtotime($current . '-01 +1 month'));
        }

        return array_unique($tables);
    }

    /**
     * Получить PDO объект архивной БД
     *
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->archiveDb->pdo;
    }

    /**
     * Получить репозиторий
     *
     * @return VoxCallArchiveRepository
     */
    public function getRepository(): VoxCallArchiveRepository
    {
        return $this->repository;
    }

    /**
     * Получить уникальные vox_user_id за период (для фильтра операторов)
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param array $queueIds Фильтр по ID очередей (если пустой - все)
     * @return array Массив vox_user_id
     */
    public function getDistinctVoxUserIds(string $dateFrom, string $dateTo, array $queueIds = []): array
    {
        $tables = $this->getTablesForPeriod($dateFrom, $dateTo);

        $queueFilter = '';
        if (!empty($queueIds)) {
            $queueFilter = ' AND queue_id IN (' . implode(',', array_map('intval', $queueIds)) . ')';
        }

        $unionParts = [];
        foreach ($tables as $tableName) {
            if (!$this->repository->tableExists($tableName)) {
                continue;
            }
            $unionParts[] = "SELECT DISTINCT vox_user_id FROM `{$tableName}` WHERE DATE(datetime_start) BETWEEN :dateFrom AND :dateTo AND vox_user_id IS NOT NULL{$queueFilter}";
        }

        if (empty($unionParts)) {
            return [];
        }

        $unionQuery = implode(' UNION ', $unionParts);

        $sql = "SELECT DISTINCT vox_user_id FROM ({$unionQuery}) sub ORDER BY vox_user_id ASC";

        $pdo = $this->archiveDb->pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':dateFrom', $dateFrom);
        $stmt->bindValue(':dateTo', $dateTo);
        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        return array_map('intval', $result);
    }

    /**
     * Получить детализацию звонков для конкретного оператора
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $voxUserId
     * @param int|null $assessment Фильтр по оценке (null = все, 'rated' обрабатывается снаружи)
     * @param bool $hasAssessment Если true - только с оценкой
     * @param int $limit
     * @return array
     */
    public function getCallDetails(
        string $dateFrom,
        string $dateTo,
        int $voxUserId,
        int $assessment = null,
        bool $hasAssessment = false,
        int $limit = 500
    ): array {
        $tables = $this->getTablesForPeriod($dateFrom, $dateTo);

        $unionParts = [];
        foreach ($tables as $tableName) {
            if (!$this->repository->tableExists($tableName)) {
                continue;
            }
            $unionParts[] = "SELECT * FROM `{$tableName}` WHERE DATE(datetime_start) BETWEEN :dateFrom AND :dateTo";
        }

        if (empty($unionParts)) {
            return [];
        }

        $unionQuery = implode(' UNION ALL ', $unionParts);

        $assessmentFilter = '';
        if ($hasAssessment) {
            $assessmentFilter = ' AND vcr.assessment IS NOT NULL';
        } elseif ($assessment !== null) {
            $assessmentFilter = ' AND vcr.assessment = :assessment';
        }

        $sql = "SELECT
            vcr.datetime_start,
            vcr.is_incoming,
            vcr.phone_a,
            vcr.phone_b,
            vcr.tags,
            vcr.assessment,
            vcr.record_url,
            vcr.user_id,
            vcr.queue_id,
            vcr.vox_user_id
        FROM ({$unionQuery}) vcr
        WHERE vcr.vox_user_id = :voxUserId
          AND vcr.record_url IS NOT NULL
          {$assessmentFilter}
        ORDER BY vcr.datetime_start DESC
        LIMIT :limit";

        $pdo = $this->archiveDb->pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':dateFrom', $dateFrom);
        $stmt->bindValue(':dateTo', $dateTo);
        $stmt->bindValue(':voxUserId', $voxUserId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);

        if ($assessment !== null && !$hasAssessment) {
            $stmt->bindValue(':assessment', $assessment, \PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}
