<?php

namespace App\Modules\AdditionalServiceRecovery\Application\DTO;

/**
 * DTO результата выполнения
 */
class RecoveryResult
{
    /** @var bool Успешность выполнения */
    public bool $success;

    /** @var string Сообщение о результате */
    public string $message;

    /** @var int Количество обработанных кандидатов */
    public int $processedCandidates;

    /** @var int Количество успешно восстановленных услуг */
    public int $reenabledCount;

    /** @var int|null ID записи в логе */
    public ?int $logId;

    /**
     * @param bool $success
     * @param string $message
     * @param int $processedCandidates
     * @param int $reenabledCount
     * @param int|null $logId
     */
    public function __construct(
        bool $success,
        string $message,
        int $processedCandidates = 0,
        int $reenabledCount = 0,
        ?int $logId = null
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->processedCandidates = $processedCandidates;
        $this->reenabledCount = $reenabledCount;
        $this->logId = $logId;
    }
}