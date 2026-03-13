<?php

namespace App\Modules\AdditionalServiceRecovery\Domain\Model;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Доменная модель правила восстановления
 */
class RecoveryRule
{
    private ?int $id;
    private string $name;
    private bool $isActive;
    private int $priority;
    private bool $autoRunEnabled;
    private ?string $cronSchedule;
    private ?DateTimeImmutable $lastAutoRunAt;
    private int $daysSinceDisable;
    private ?DateTimeImmutable $disabledFrom;
    private ?DateTimeImmutable $disabledTo;
    private array $allowedManagerIds;
    private array $allowedManagerRoleIds;
    private array $allowedServiceKeys;
    private ?float $minLoanAmount;
    private ?float $maxLoanAmount;
    private string $repaymentStage;

    public function __construct(
        ?int $id,
        string $name,
        bool $isActive,
        int $priority,
        int $daysSinceDisable,
        ?DateTimeImmutable $disabledFrom,
        ?DateTimeImmutable $disabledTo,
        array $allowedManagerIds,
        array $allowedManagerRoleIds,
        array $allowedServiceKeys,
        ?float $minLoanAmount,
        ?float $maxLoanAmount,
        string $repaymentStage,
        bool $autoRunEnabled,
        ?string $cronSchedule,
        ?DateTimeImmutable $lastAutoRunAt
    ) {
        if ($minLoanAmount !== null && $maxLoanAmount !== null && $minLoanAmount > $maxLoanAmount) {
            throw new InvalidArgumentException('Min loan amount cannot be greater than max');
        }

        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
        $this->priority = $priority;
        $this->daysSinceDisable = $daysSinceDisable;
        $this->disabledFrom = $disabledFrom;
        $this->disabledTo = $disabledTo;
        $this->allowedManagerIds = $allowedManagerIds;
        $this->allowedManagerRoleIds = $allowedManagerRoleIds;
        $this->allowedServiceKeys = $allowedServiceKeys;
        $this->minLoanAmount = $minLoanAmount;
        $this->maxLoanAmount = $maxLoanAmount;
        $this->repaymentStage = $repaymentStage;
        $this->autoRunEnabled = $autoRunEnabled;
        $this->cronSchedule = $cronSchedule;
        $this->lastAutoRunAt = $lastAutoRunAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Проверяет, соответствует ли кандидат этому правилу
     */
    public function matches(ServiceCandidate $candidate): bool
    {
        if (!$this->isActive) {
            return false;
        }

        // Проверка по дням с момента отключения
        if ($candidate->getDaysSinceDisabled() < $this->daysSinceDisable) {
            return false;
        }

        // Проверка по ID менеджера
        if (!empty($this->allowedManagerIds) && !in_array($candidate->getManagerId(), $this->allowedManagerIds, true)) {
            return false;
        }

        // Проверка по ключу услуги
        if (!empty($this->allowedServiceKeys) && !in_array($candidate->getServiceKey(), $this->allowedServiceKeys, true)) {
            return false;
        }

        // Проверка по сумме займа
        $loanAmount = $candidate->getLoanAmount();
        if ($this->minLoanAmount !== null && $loanAmount < $this->minLoanAmount) {
            return false;
        }
        if ($this->maxLoanAmount !== null && $loanAmount > $this->maxLoanAmount) {
            return false;
        }

        return true;
    }

    /**
     * Может ли правило быть запущено автоматически
     */
    public function canRunAutomatically(): bool
    {
        if (!$this->isActive() || !$this->autoRunEnabled || empty($this->cronSchedule)) {
            return false;
        }
        
        return true;
    }

    /**
     * Преобразует объект в массив для API-ответов.
    */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->isActive,
            'priority' => $this->priority,
            'days_since_disable' => $this->daysSinceDisable,
            'disabled_from' => $this->disabledFrom ? $this->disabledFrom->format('Y-m-d H:i:s') : null,
            'disabled_to' => $this->disabledTo ? $this->disabledTo->format('Y-m-d H:i:s') : null,
            'repayment_stage' => $this->repaymentStage,
            'manager_ids' => $this->allowedManagerIds,
            'service_keys' => $this->allowedServiceKeys,
            'min_loan_amount' => $this->minLoanAmount,
            'max_loan_amount' => $this->maxLoanAmount,
            'auto_run_enabled' => $this->autoRunEnabled,
            'cron_schedule' => $this->cronSchedule,
            'last_auto_run_at' => $this->lastAutoRunAt ? $this->lastAutoRunAt->format('Y-m-d H:i:s') : null,
        ];
    }

    public function getDaysSinceDisable(): int
    {
        return $this->daysSinceDisable;
    }

    public function getMinLoanAmount(): ?float
    {
        return $this->minLoanAmount;   
    }

    public function getMaxLoanAmount(): ?float
    {
        return $this->maxLoanAmount;
    }

    public function getRepaymentStage(): string
    {
        return $this->repaymentStage;
    }

    public function getServiceKeys(): array
    {
        return $this->allowedServiceKeys;
    }

    public function getManagerIds(): array
    {
        return $this->allowedManagerIds;
    }

    public function getManagerRoleIds(): array
    {
        return $this->allowedManagerRoleIds;
    }

    public function getDisabledFrom(): ?DateTimeImmutable
    {
        return $this->disabledFrom;
    }

    public function getDisabledTo(): ?DateTimeImmutable
    {
        return $this->disabledTo;
    }
}