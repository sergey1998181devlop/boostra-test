<?php

namespace App\Modules\AdditionalServiceRecovery\Domain\Model;

use App\Modules\AdditionalServiceRecovery\Domain\Enum\ProcessStatus;
use App\Modules\AdditionalServiceRecovery\Domain\Enum\RunType;
use DateTimeImmutable;

/**
 * Доменная модель "Процесс восстановления".
 * Представляет один запуск (ручной или автоматический) и его результат.
 */
class RecoveryProcess
{
    /** @var int|null ID записи в логе */
    private ?int $id = null;

    /** @var RunType Тип запуска */
    private RunType $runType;

    /** @var ProcessStatus Текущий статус */
    private ProcessStatus $status;

    /** @var DateTimeImmutable Время начала */
    private DateTimeImmutable $startedAt;

    /** @var DateTimeImmutable|null Время окончания */
    private ?DateTimeImmutable $finishedAt = null;

    /** @var int|null ID менеджера-инициатора */
    private ?int $managerId;
    private ?int $ruleId;

    /** @var int Количество обработанных кандидатов */
    private int $processedCandidates = 0;

    /** @var int Количество восстановленных услуг */
    private int $reenabledCount = 0;

    /** @var string|null Сообщение о результате */
    private ?string $message = null;

    /** @var string|null Детали ошибки */
    private ?string $errorDetails = null;

    public function __construct(int $ruleId, RunType $runType, ?int $managerId)
    {
        $this->ruleId = $ruleId;
        $this->runType = $runType;
        $this->managerId = $managerId;
        $this->status = ProcessStatus::RUNNING();
        $this->startedAt = new DateTimeImmutable();
    }
    
    /**
     * Помечает процесс как успешно завершенный.
     *
     * @param int $reenabledCount
     */
    public function complete(int $reenabledCount): void
    {
        if ($this->status->equals(ProcessStatus::RUNNING())) {
            $this->status = ProcessStatus::COMPLETED();
            $this->reenabledCount = $reenabledCount;
            $this->finishedAt = new DateTimeImmutable();
        }
    }

    /**
     * Помечает процесс как проваленный.
     *
     * @param string $errorMessage
     */
    public function fail(string $errorMessage): void
    {
        if ($this->status->equals(ProcessStatus::RUNNING())) {
            $this->status = ProcessStatus::FAILED();
            $this->errorDetails = $errorMessage;
            $this->finishedAt = new DateTimeImmutable();
        }
    }

    public function setId(int $id): void
    {
        if ($this->id === null) {
            $this->id = $id;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRunType(): RunType
    {
        return $this->runType;
    }

    public function getStatus(): ProcessStatus
    {
        return $this->status;
    }

    public function getStartedAt(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function getManagerId(): ?int
    {
        return $this->managerId;
    }

    public function getRuleId(): ?int 
    { 
        return $this->ruleId;
    }

    public function getProcessedCandidates(): int
    {
        return $this->processedCandidates;
    }

    public function getReenabledCount(): int
    {
        return $this->reenabledCount;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getErrorDetails(): ?string
    {
        return $this->errorDetails;
    }

    public function setProcessedCandidates(int $count): void
    {
        $this->processedCandidates = $count;
    }
}