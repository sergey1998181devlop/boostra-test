<?php

namespace App\Modules\AdditionalServiceRecovery\Application\Service;

use App\Modules\AdditionalServiceRecovery\Application\DTO\RuleRequest;
use App\Modules\AdditionalServiceRecovery\Domain\Model\RecoveryRule;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\RuleRepository;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class RuleManagementService
{
    private RuleRepository $ruleRepository;

    public function __construct(RuleRepository $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @throws Exception
     */
    public function createRule(RuleRequest $request, int $managerId): RecoveryRule
    {
        if ($this->ruleRepository->existsByName($request->name)) {
            throw new InvalidArgumentException(sprintf('Правило с именем "%s" уже существует', $request->name));
        }

        $ruleData = $this->prepareDataForStorage($request);
        $ruleData['created_by'] = $managerId;
        $ruleData['updated_by'] = $managerId;

        $ruleId = $this->ruleRepository->save($ruleData);
        $newRule = $this->ruleRepository->findById($ruleId);

        if (!$newRule) {
            throw new RuntimeException('Failed to create or find the new rule.');
        }

        return $newRule;
    }

    /**
     * @throws Exception
     */
    public function updateRule(int $ruleId, RuleRequest $request, int $managerId): RecoveryRule
    {
        if (!$this->ruleRepository->findById($ruleId)) {
            throw new RuntimeException(sprintf('Правило #%d не найдено', $ruleId));
        }
        if ($this->ruleRepository->existsByName($request->name, $ruleId)) {
            throw new InvalidArgumentException(sprintf('Правило с именем "%s" уже существует', $request->name));
        }

        $ruleData = $this->prepareDataForStorage($request);
        $ruleData['updated_by'] = $managerId;

        $this->ruleRepository->update($ruleId, $ruleData);

        $updatedRule = $this->ruleRepository->findById($ruleId);

        if (!$updatedRule) {
            throw new RuntimeException('Failed to find the updated rule.');
        }

        return $updatedRule;
    }

    private function prepareDataForStorage(RuleRequest $request): array
    {
        return [
            'name' => $request->name,
            'is_active' => (int)$request->isActive,
            'priority' => $request->priority,
            'days_since_disable' => $request->daysSinceDisable,
            'disabled_from' => $request->disabledFrom ? $request->disabledFrom->format('Y-m-d H:i:s') : null,
            'disabled_to' => $request->disabledTo ? $request->disabledTo->format('Y-m-d H:i:s') : null,
            'min_loan_amount' => $request->minLoanAmount,
            'max_loan_amount' => $request->maxLoanAmount,
            'auto_run_enabled' => (int)$request->autoRunEnabled,
            'cron_schedule' => $request->cronSchedule,
            'manager_ids' => !empty($request->managerIds) ? implode(',', $request->managerIds) : null,
            'service_keys' => !empty($request->serviceKeys) ? implode(',', $request->serviceKeys) : null,
        ];
    }

    /**
     * @return RecoveryRule[]
     * @throws Exception
     */
    public function getRules(array $filters = []): array
    {
        return $this->ruleRepository->findAll($filters);
    }

    /**
     * @throws Exception
     */
    public function getRule(int $ruleId): ?RecoveryRule
    {
        return $this->ruleRepository->findById($ruleId);
    }
}