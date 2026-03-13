<?php

namespace App\Modules\AdditionalServiceRecovery\Infrastructure\Repository;

use App\Modules\AdditionalServiceRecovery\Domain\Model\RecoveryRule;
use Database;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

/**
 * Репозиторий для работы с правилами.
 */
class RuleRepository
{
    private const TABLE_NAME = 's_service_recovery_rules';

    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Создает объект RecoveryRule из массива данных БД.
     * @throws Exception
     */
    private function hydrate(array $data): RecoveryRule
    {
        if (empty($data['id'])) {
            throw new InvalidArgumentException('Rule ID is required for hydration');
        }
        
        $managerIds = [];
        if (!empty($data['manager_ids']) && is_string($data['manager_ids'])) {
            $managerIds = array_filter(array_map('intval', explode(',', $data['manager_ids'])));
        }

        $managerRoleIds = [];
        if (!empty($data['manager_role_ids']) && is_string($data['manager_role_ids'])) {
            $managerRoleIds = array_filter(array_map('intval', explode(',', $data['manager_role_ids'])));
        }
        
        $serviceKeys = [];
        if (!empty($data['service_keys']) && is_string($data['service_keys'])) {
            $serviceKeys = array_filter(array_map('trim', explode(',', $data['service_keys'])));
        }

        return new RecoveryRule(
            (int)$data['id'],
            (string)$data['name'],
            (bool)$data['is_active'],
            (int)$data['priority'],
            (int)($data['days_since_disable'] ?? 0),
            !empty($data['disabled_from']) ? new DateTimeImmutable($data['disabled_from']) : null,
            !empty($data['disabled_to']) ? new DateTimeImmutable($data['disabled_to']) : null,
            $managerIds,
            $managerRoleIds,
            $serviceKeys,
            isset($data['min_loan_amount']) ? (float)$data['min_loan_amount'] : null,
            isset($data['max_loan_amount']) ? (float)$data['max_loan_amount'] : null,
            $data['repayment_stage'] ?? '',
            (bool)($data['auto_run_enabled'] ?? false),
            $data['cron_schedule'] ?? null,
            !empty($data['last_auto_run_at']) ? new DateTimeImmutable($data['last_auto_run_at']) : null
        );
    }

    /**
     * Находит правило по ID и возвращает объект RecoveryRule.
     * @throws Exception
     */
    public function findById(int $id): ?RecoveryRule
    {
        $query = $this->db->placehold(
            "SELECT * FROM ".self::TABLE_NAME." WHERE id = ? LIMIT 1",
            $id
        );
        $this->db->query($query);

        $resultObject = $this->db->result();
        if (!$resultObject) {
            return null;
        }

        return $this->hydrate((array)$resultObject);
    }

    /**
     * Получает все правила и возвращает массив объектов RecoveryRule.
     * @throws Exception
     */
    public function findAll(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (isset($filters['is_active'])) {
            $where[] = 'is_active = ?';
            $params[] = (int)$filters['is_active'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = $this->db->placehold(
            "SELECT * FROM ".self::TABLE_NAME." $whereClause ORDER BY priority ASC, id ASC",
            ...$params
        );
        $this->db->query($query);

        $resultObjects = $this->db->results();
        if (empty($resultObjects)) {
            return [];
        }

        $rules = [];
        foreach ($resultObjects as $row) {
            try {
                $rules[] = $this->hydrate((array)$row);
            } catch (Exception $e) {
                error_log(sprintf(
                    'RuleRepository: Failed to hydrate rule from data for rule ID %d. Error: %s',
                    $row->id ?? 0,
                    $e->getMessage()
                ));
            }
        }
        return $rules;
    }

    /**
     * Сохраняет новое правило.
     * @param array $data Ассоциативный массив полей для вставки.
     * @return int ID созданного правила.
     */
    public function save(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $query = $this->db->placehold("INSERT INTO ".self::TABLE_NAME." SET ?%", $data);
        $this->db->query($query);
        return (int)$this->db->insert_id();
    }

    /**
     * Обновляет существующее правило.
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            return true;
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $query = $this->db->placehold("UPDATE ".self::TABLE_NAME." SET ?% WHERE id = ?", $data, $id);
        return (bool)$this->db->query($query);
    }

    /**
     * Проверяет существование правила с таким именем (кроме текущего ID).
     */
    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $excludeClause = '';
        $params = [$name];
        if ($excludeId !== null) {
            $excludeClause = 'AND id != ?';
            $params[] = $excludeId;
        }

        $query = $this->db->placehold(
            "SELECT COUNT(*) as cnt FROM ".self::TABLE_NAME." WHERE name = ? $excludeClause",
            ...$params
        );

        $this->db->query($query);
        return ((int)$this->db->result('cnt')) > 0;
    }
    
    public function rollback(): void
    {
        $this->db->query("ROLLBACK");
    }

    public function findActiveForAutoRun(): array
    {
        return [];
    }
}