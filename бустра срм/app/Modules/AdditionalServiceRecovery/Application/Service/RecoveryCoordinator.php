<?php

namespace App\Modules\AdditionalServiceRecovery\Application\Service;

use App\Modules\AdditionalServiceRecovery\Application\DTO\RecoveryResult;
use App\Modules\AdditionalServiceRecovery\Domain\Enum\RunType;
use App\Modules\AdditionalServiceRecovery\Domain\Model\RecoveryProcess;
use App\Modules\AdditionalServiceRecovery\Domain\Model\RecoveryRule;
use App\Modules\AdditionalServiceRecovery\Domain\Service\ServiceEnabler;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\CandidateRepository;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\ProcessLogRepository;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\RuleRepository;
use Exception;
use InvalidArgumentException;

/**
 * Class RecoveryCoordinator
 * Оркестрирует весь процесс восстановления дополнительных услуг.
 */
class RecoveryCoordinator
{
    private RuleRepository $ruleRepository;
    private CandidateRepository $candidateRepository;
    private ServiceEnabler $serviceEnabler;
    private ProcessLogRepository $processLogRepository;

    public function __construct(
        RuleRepository $ruleRepository,
        CandidateRepository $candidateRepository,
        ServiceEnabler $serviceEnabler,
        ProcessLogRepository $processLogRepository
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->candidateRepository = $candidateRepository;
        $this->serviceEnabler = $serviceEnabler;
        $this->processLogRepository = $processLogRepository;
    }

    /**
     * Запускает выполнение одного конкретного правила по его ID.
     * @throws Exception
     */
    public function runSingleRule(int $ruleId, ?int $managerId): RecoveryResult
    {
        if (empty($ruleId)) {
            throw new InvalidArgumentException('Не указан ID правила для запуска.');
        }

        $rule = $this->ruleRepository->findById($ruleId);
        if (!$rule) {
            throw new InvalidArgumentException("Правило с ID {$ruleId} не найдено.");
        }

        return $this->processRule($rule, RunType::MANUAL(), $managerId);
    }

    /**
     * Запускает все активные правила для CRON.
     */
    public function runAllScheduledRules(): void
    {
        $rules = $this->ruleRepository->findActiveForAutoRun();

        foreach ($rules as $rule) {
            try {
                $this->processRule($rule, RunType::AUTO(), null);
            } catch (Exception $e) {
                error_log("Failed to process rule #{$rule->getId()}: " . $e->getMessage());
            }
        }
    }

    /**
     * Основная логика обработки одного правила.
     * @throws Exception
     */
    private function processRule(RecoveryRule $rule, RunType $runType, ?int $managerId): RecoveryResult
    {
        $process = new RecoveryProcess($rule->getId(), $runType, $managerId);
        $this->processLogRepository->save($process);

        try {
            $candidates = $this->candidateRepository->findCandidatesForRule($rule);
            $process->setProcessedCandidates(count($candidates));

            $reenabledCount = 0;
            foreach ($candidates as $candidate) {
                try {
                    $initiatorId = $managerId ?? 0;
                    $this->serviceEnabler->reenableService($candidate, $initiatorId, $process->getId(), $rule->getId());
                    $reenabledCount++;
                } catch (Exception $e) {
                    error_log("Failed to re-enable service for order #{$candidate->getOrderId()}: " . $e->getMessage());
                }
            }

            $process->complete($reenabledCount);

        } catch (Exception $e) {
            $process->fail($e->getMessage());
            $this->processLogRepository->save($process);
            throw $e;
        }

        $this->processLogRepository->save($process);

        $message = "Обработано кандидатов: {$process->getProcessedCandidates()}. Восстановлено услуг: {$process->getReenabledCount()}.";
        return new RecoveryResult(
            true,
            $message,
            $process->getProcessedCandidates(),
            $process->getReenabledCount(),
            $process->getId()
        );
    }
}