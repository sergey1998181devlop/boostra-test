<?php

namespace App\Modules\TicketAssignment\Services;

use App\Modules\TicketAssignment\Contracts\CompetencyServiceInterface;
use App\Modules\TicketAssignment\Repositories\ManagerCompetencyRepository;
use App\Modules\TicketAssignment\Enums\CompetencyLevel;

class CompetencyService implements CompetencyServiceInterface
{
    /** @var ManagerCompetencyRepository */
    private $repository;

    /** @var array Кэш для SLA менеджеров */
    private $slaManagersCache = [];

    public function __construct(ManagerCompetencyRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function getManagerCompetency(int $managerId, string $type): ?string
    {
        return $this->repository->get($managerId, $type);
    }

    /**
     * @inheritDoc
     */
    public function getManagersByLevel(string $type, string $level): array
    {
        if (!CompetencyLevel::isValid($level)) {
            return [];
        }

        return $this->repository->getByLevel($type, $level);
    }

    /**
     * @inheritDoc
     */
    public function setManagerCompetency(int $managerId, string $type, string $level): bool
    {
        if (!CompetencyLevel::isValid($level)) {
            return false;
        }

        $result = $this->repository->set($managerId, $type, $level);
        if ($result) {
            $this->clearCache();
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function removeManagerCompetency(int $managerId, string $type): bool
    {
        $result = $this->repository->remove($managerId, $type);
        if ($result) {
            $this->clearCache();
        }
        return $result;
    }

    /**
     * Получить все компетенции менеджера
     *
     * @param int $managerId
     * @return array Массив в формате ['тип' => 'уровень']
     */
    public function getAllManagerCompetencies(int $managerId): array
    {
        return $this->repository->getAllForManager($managerId);
    }

    /**
     * Получить менеджеров для SLA эскалации с кэшированием
     *
     * @param string $type Тип тикета ('collection' или 'additional_services')
     * @param int $level Уровень SLA (1 или 2)
     * @return array Массив ID менеджеров
     */
    public function getSLAEscalationManagers(string $type, int $level): array
    {
        $cacheKey = "{$type}_{$level}";
        
        if (!isset($this->slaManagersCache[$cacheKey])) {
            $this->slaManagersCache[$cacheKey] = $this->repository->getSLAEscalationManagers($type, $level);
        }
        
        return $this->slaManagersCache[$cacheKey];
    }

    /**
     * Установить SLA уровень для менеджера
     *
     * @param int $managerId ID менеджера
     * @param string $type Тип тикета
     * @param int $level Уровень SLA (1 или 2)
     * @return bool
     */
    public function setSLAEscalationLevel(int $managerId, string $type, int $level): bool
    {
        $result = $this->repository->setSLAEscalationLevel($managerId, $type, $level);
        if ($result) {
            $this->clearCache();
        }
        return $result;
    }

    /**
     * Удалить SLA уровень для менеджера
     *
     * @param int $managerId ID менеджера
     * @param string $type Тип тикета
     * @return bool
     */
    public function removeSLAEscalationLevel(int $managerId, string $type): bool
    {
        $result = $this->repository->removeSLAEscalationLevel($managerId, $type);
        if ($result) {
            $this->clearCache();
        }
        return $result;
    }

    /**
     * Получить SLA уровни менеджера
     *
     * @param int $managerId ID менеджера
     * @return array Массив в формате ['тип' => 'уровень_sla']
     */
    public function getManagerSLAEscalationLevels(int $managerId): array
    {
        return $this->repository->getManagerSLAEscalationLevels($managerId);
    }

    /**
     * Получить полную информацию о компетенциях менеджера (включая SLA)
     *
     * @param int $managerId ID менеджера
     * @return array Массив с полной информацией
     */
    public function getFullManagerCompetencies(int $managerId): array
    {
        return $this->repository->getFullManagerCompetencies($managerId);
    }

    /**
     * Очистить кэш при изменении компетенций или SLA уровней
     */
    public function clearCache(): void
    {
        $this->slaManagersCache = [];
    }
}

