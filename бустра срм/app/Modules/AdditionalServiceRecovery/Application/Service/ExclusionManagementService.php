<?php

namespace App\Modules\AdditionalServiceRecovery\Application\Service;

use App\Modules\AdditionalServiceRecovery\Application\DTO\ExclusionRequest;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\ExclusionRepository;

/**
 * Сервис для управления списком исключений.
 */
class ExclusionManagementService
{
    private ExclusionRepository $exclusionRepository;

    public function __construct(ExclusionRepository $exclusionRepository)
    {
        $this->exclusionRepository = $exclusionRepository;
    }

    /**
     * Добавляет новую запись в список исключений.
     */
    public function addExclusion(ExclusionRequest $request): void
    {
        $this->exclusionRepository->add($request);
    }

    /**
     * Деактивирует (удаляет) запись из списка исключений по ее ID.
     */
    public function deactivateExclusion(int $exclusionId): void
    {
        $this->exclusionRepository->deactivate($exclusionId);
    }

    /**
     * Возвращает список всех активных исключений.
     */
    public function getActiveExclusions(): array
    {
        return $this->exclusionRepository->findAllActive();
    }
}
