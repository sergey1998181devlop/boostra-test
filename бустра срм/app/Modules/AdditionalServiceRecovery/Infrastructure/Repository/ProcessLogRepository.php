<?php

namespace App\Modules\AdditionalServiceRecovery\Infrastructure\Repository;

use App\Modules\AdditionalServiceRecovery\Domain\Model\RecoveryProcess;
use Database;

/**
 * Репозиторий для логов процессов восстановления
 */
class ProcessLogRepository
{
    private const TABLE_NAME = 's_service_recovery_process_logs';

    /** @var Database */
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Сохраняет состояние процесса (создает или обновляет).
     *
     * @param RecoveryProcess $process
     * @return void
     */
    public function save(RecoveryProcess $process): void
    {
        if ($process->getId() === null) {
            $this->insert($process);
        } else {
            $this->update($process);
        }
    }

    /**
     * Создает новую запись в логе.
     *
     * @param RecoveryProcess $process
     */
    private function insert(RecoveryProcess $process): void
    {
        $logData = [
            'started_at' => $process->getStartedAt()->format('Y-m-d H:i:s'),
            'run_type' => $process->getRunType()->getValue(),
            'manager_id' => $process->getManagerId(),
            'rule_id' => $process->getRuleId(),
            'status' => $process->getStatus()->getValue(),
        ];

        $query = $this->db->placehold('INSERT INTO '.self::TABLE_NAME.' SET ?%', $logData);
        $this->db->query($query);

        $id = $this->db->insert_id();
        $process->setId($id);
    }

    /**
     * Обновляет существующую запись в логе.
     *
     * @param RecoveryProcess $process
     */
    private function update(RecoveryProcess $process): void
    {
        $logData = [
            'finished_at' => $process->getFinishedAt() ? $process->getFinishedAt()->format('Y-m-d H:i:s') : null,
            'processed_candidates' => $process->getProcessedCandidates(),
            'reenabled_count' => $process->getReenabledCount(),
            'message' => $process->getMessage(),
            'status' => $process->getStatus()->getValue(),
            'error_details' => $process->getErrorDetails(),
        ];

        $filteredData = array_filter($logData, fn($value) => $value !== null);

        $query = $this->db->placehold('UPDATE '.self::TABLE_NAME.' SET ?% WHERE id = ?', $filteredData, $process->getId());
        $this->db->query($query);
    }

    /**
     * Находит последние запуски, связанные с конкретным правилом.
     * Также включает общие автоматические запуски.
     *
     * @param int $ruleId
     * @param int $limit
     * @return array
     */
    public function findRunsForRule(int $ruleId, int $limit = 15): array
    {
        $query = $this->db->placehold(
            'SELECT id, started_at, run_type, status, processed_candidates, reenabled_count 
             FROM '.self::TABLE_NAME.' 
             WHERE rule_id = ? OR rule_id IS NULL
             ORDER BY started_at DESC 
             LIMIT ?',
            $ruleId,
            $limit
        );
        $this->db->query($query);
        return $this->db->results();
    }
}