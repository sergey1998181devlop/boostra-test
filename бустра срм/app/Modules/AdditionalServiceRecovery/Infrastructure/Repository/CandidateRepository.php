<?php

namespace App\Modules\AdditionalServiceRecovery\Infrastructure\Repository;

use App\Modules\AdditionalServiceRecovery\Domain\Model\RecoveryRule;
use App\Modules\AdditionalServiceRecovery\Domain\Model\ServiceCandidate;
use App\Modules\Manager\Repository\ManagerRepository;
use App\Modules\Shared\AdditionalServices\Enum\AdditionalServiceKey;
use Database;
use DateTime;
use DateTimeImmutable;

/**
 * Class CandidateRepository
 * Отвечает за поиск кандидатов на восстановление услуг на основе правил.
 */
class CandidateRepository
{
    private Database $db;
    private ManagerRepository $managerRepository;

    public function __construct(
        Database $db,
        ManagerRepository $managerRepository
    ) {
        $this->db = $db;
        $this->managerRepository = $managerRepository;
    }

    /**
     * Находит всех кандидатов (заявки/услуги), которые подходят под заданное правило.
     *
     * @param RecoveryRule $rule
     * @return ServiceCandidate[]
     * @throws \Exception
     */
    public function findCandidatesForRule(RecoveryRule $rule): array
    {
        $targetServiceKeys = [];
        $targetStatuses1c = [];

        $stageFromRule = $rule->getRepaymentStage();
        $keysFromRule = $rule->getServiceKeys();
        $allServicesByStage = AdditionalServiceKey::getServicesByStage();

        if (!empty($stageFromRule)) {
            // Если в правиле задан этап, берем статусы и услуги для него
            $targetStatuses1c = $this->get1cStatusesForStage($stageFromRule);
            $servicesForThisStage = $allServicesByStage[$stageFromRule] ?? [];

            // Если в правиле указаны еще и ключи, находим их пересечение
            $targetServiceKeys = !empty($keysFromRule)
                ? array_intersect($keysFromRule, $servicesForThisStage)
                : $servicesForThisStage;

        } elseif (!empty($keysFromRule)) {
            // Если заданы только ключи, но не этап
            $targetServiceKeys = $keysFromRule;
            $stagesForTheseKeys = [];
            // Находим все возможные этапы для указанных ключей
            foreach ($targetServiceKeys as $key) {
                foreach ($allServicesByStage as $stage => $services) {
                    if (in_array($key, $services)) {
                        $stagesForTheseKeys[$stage] = $stage;
                    }
                }
            }
            // Собираем все возможные статусы для найденных этапов
            foreach ($stagesForTheseKeys as $stage) {
                $targetStatuses1c = array_merge($targetStatuses1c, $this->get1cStatusesForStage($stage));
            }
            $targetStatuses1c = array_unique($targetStatuses1c);
        } else {
            // Если в правиле не указаны ни этап, ни ключи, берем все "открытые" статусы
            foreach (array_keys($allServicesByStage) as $stage) {
                $targetStatuses1c = array_merge($targetStatuses1c, $this->get1cStatusesForStage($stage));
            }
            $targetStatuses1c = array_unique($targetStatuses1c);
        }

        if ((!empty($stageFromRule) || !empty($keysFromRule)) && empty($targetServiceKeys)) {
            return [];
        }

        // Подготавливаем условия для фильтрации дат в CTE
        $dateConditions = [];
        $dateParams = [];

        $disabledFrom = $rule->getDisabledFrom();
        $disabledTo = $rule->getDisabledTo();

        if ($disabledFrom || $disabledTo) {
            if ($disabledFrom) {
                $dateConditions[] = "cl.created >= ?";
                $dateParams[] = $disabledFrom->format('Y-m-d 00:00:00');
            }
            if ($disabledTo) {
                $dateConditions[] = "cl.created < ?";
                $dateParams[] = $disabledTo->modify('+1 day')->format('Y-m-d 00:00:00');
            }
        } else {
            $upperBoundDate = (new DateTime())->modify("-{$rule->getDaysSinceDisable()} days")->format('Y-m-d H:i:s');
            $dateConditions[] = "cl.created <= ?";
            $dateParams[] = $upperBoundDate;

            $lowerBoundDate = (new DateTime())->modify("-30 days")->format('Y-m-d H:i:s');
            $dateConditions[] = "cl.created >= ?";
            $dateParams[] = $lowerBoundDate;
        }

        $dateConditionsSql = !empty($dateConditions) ? 'AND ' . implode(' AND ', $dateConditions) : '';

        $sql = "
            WITH RankedChangelogs AS (
                SELECT
                    cl.order_id,
                    cl.user_id,
                    cl.manager_id,
                    cl.created AS disabled_at,
                    cl.type AS service_key,
                    ROW_NUMBER() OVER(PARTITION BY cl.order_id, cl.type ORDER BY cl.created DESC) as rn
                FROM s_changelogs AS cl
                WHERE cl.new_values = 'Выключение'
                  {$dateConditionsSql}
            )
            SELECT
                rc.order_id,
                rc.user_id,
                rc.manager_id,
                rc.disabled_at,
                rc.service_key,
                o.amount AS loan_amount
            FROM RankedChangelogs AS rc
                INNER JOIN s_orders AS o ON o.id = rc.order_id
            WHERE rc.rn = 1
              AND NOT EXISTS (
                SELECT 1 FROM s_order_data sd 
                WHERE sd.order_id = rc.order_id 
                  AND sd.key = rc.service_key 
                  AND sd.value = '0'
              )
              AND NOT EXISTS (
                SELECT 1 FROM s_service_recovery_exclusions ex
                WHERE ex.deleted_at IS NULL
                  AND (ex.expires_at IS NULL OR ex.expires_at > NOW())
                  AND ex.user_id = rc.user_id
                  AND (ex.order_id = rc.order_id OR ex.order_id = 0)
                  AND (ex.service_key = rc.service_key OR ex.service_key IS NULL)
              )
        ";

        $params = $dateParams;

        // Фильтр по сумме займа
        if ($rule->getMinLoanAmount() !== null) {
            $sql .= " AND o.amount >= ?";
            $params[] = $rule->getMinLoanAmount();
        }
        if ($rule->getMaxLoanAmount() !== null) {
            $sql .= " AND o.amount <= ?";
            $params[] = $rule->getMaxLoanAmount();
        }

        // Фильтр по ключам услуг (используем подготовленный массив targetServiceKeys)
        if (!empty($targetServiceKeys)) {
            $inPlaceholders = implode(',', array_fill(0, count($targetServiceKeys), '?'));
            $sql .= " AND rc.service_key IN ({$inPlaceholders})";
            $params = array_merge($params, $targetServiceKeys);
        }

        // Фильтр по менеджерам или ролям
        $managerIds = $rule->getManagerIds();
        if (!empty($rule->getManagerRoleIds())) {
            $managerIdsFromRoles = $this->managerRepository->findManagerIdsByRoleIds($rule->getManagerRoleIds());
            $managerIds = array_unique(array_merge($managerIds, $managerIdsFromRoles));
        }

        if (!empty($managerIds)) {
            $inPlaceholders = implode(',', array_fill(0, count($managerIds), '?'));
            $sql .= " AND rc.manager_id IN ({$inPlaceholders})";
            $params = array_merge($params, $managerIds);
        }

        // Фильтр по статусу 1С (используем подготовленный массив targetStatuses1c)
        if (!empty($targetStatuses1c)) {
            $inPlaceholders = implode(',', array_fill(0, count($targetStatuses1c), '?'));
            $sql .= " AND o.1c_status IN ({$inPlaceholders})";
            $params = array_merge($params, $targetStatuses1c);
        }

        $this->db->query($sql, ...$params);
        $results = $this->db->results();

        if (empty($results)) {
            return [];
        }

        $finalCandidates = [];
        foreach ($results as $candidateData) {
            $finalCandidates[] = new ServiceCandidate(
                (int)$candidateData->order_id,
                (int)$candidateData->user_id,
                $candidateData->manager_id,
                $candidateData->service_key,
                new DateTimeImmutable($candidateData->disabled_at),
                (float)$candidateData->loan_amount
            );
        }

        return $finalCandidates;
    }

    /**
     * Преобразует ключ этапа из правила в реальные статусы 1С.
     * @param string $stageKey
     * @return string[]
     */
    private function get1cStatusesForStage(string $stageKey): array
    {
        $map = [
            'issue'             => ['Новая', '1.Рассматривается', '3.Одобрено'],
            'prolongation'      => ['5.Выдан'],
            'partial_repayment' => ['5.Выдан'],
            'full_repayment'    => ['5.Выдан'],
        ];

        return $map[$stageKey] ?? [];
    }
}