<?php

namespace App\Modules\AdditionalServiceRecovery\Application\DTO;

use DateTime;
use InvalidArgumentException;

/**
 * Единый DTO для запросов на создание и обновление правил.
 */
final class RuleRequest
{
    public string $name;
    public bool $isActive;
    public int $priority;
    public int $daysSinceDisable;
    public ?DateTime $disabledFrom;
    public ?DateTime $disabledTo;
    public array $managerIds;
    public array $serviceKeys;
    public ?float $minLoanAmount;
    public ?float $maxLoanAmount;
    public bool $autoRunEnabled;
    public ?string $cronSchedule;
    public ?int $createdBy;

    /**
     * @throws \Exception
     */
    public function __construct(array $data)
    {
        $this->name = (string)($data['name'] ?? '');
        $this->isActive = (bool)($data['is_active'] ?? true);
        $this->priority = (int)($data['priority'] ?? 100);
        $this->daysSinceDisable = (int)($data['days_since_disable'] ?? 0);
        $this->disabledFrom = !empty($data['disabled_from']) ? new DateTime($data['disabled_from']) : null;
        $this->disabledTo = !empty($data['disabled_to']) ? new DateTime($data['disabled_to']) : null;
        $this->managerIds = $this->parseIds($data['manager_ids'] ?? []);
        $this->serviceKeys = $this->parseKeys($data['service_keys'] ?? []);
        $this->minLoanAmount = isset($data['min_loan_amount']) ? (float)$data['min_loan_amount'] : null;
        $this->maxLoanAmount = isset($data['max_loan_amount']) ? (float)$data['max_loan_amount'] : null;
        $this->autoRunEnabled = (bool)($data['auto_run_enabled'] ?? false);
        $this->cronSchedule = isset($data['cron_schedule']) ? (string)$data['cron_schedule'] : null;
        $this->createdBy = isset($data['created_by']) ? (int)$data['created_by'] : null;

        $this->validate();
    }

    private function parseIds($ids): array
    {
        if (is_string($ids) && !empty($ids)) {
            return array_filter(array_map('intval', explode(',', $ids)));
        }
        return is_array($ids) ? array_map('intval', array_filter($ids)) : [];
    }

    private function parseKeys($keys): array
    {
        if (is_string($keys) && !empty($keys)) {
            return array_filter(array_map('trim', explode(',', $keys)));
        }
        return is_array($keys) ? array_filter(array_map('trim', $keys)) : [];
    }

    private function validate(): void
    {
        if (empty($this->name)) {
            throw new InvalidArgumentException('Название правила обязательно');
        }
        if ($this->priority < 0) {
            throw new InvalidArgumentException('Приоритет не может быть отрицательным');
        }
        if ($this->daysSinceDisable < 0) {
            throw new InvalidArgumentException('Количество дней не может быть отрицательным');
        }
        if ($this->minLoanAmount !== null && $this->maxLoanAmount !== null && $this->minLoanAmount > $this->maxLoanAmount) {
            throw new InvalidArgumentException('Минимальная сумма не может быть больше максимальной');
        }
    }
}