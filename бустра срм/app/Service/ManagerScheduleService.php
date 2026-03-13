<?php

namespace App\Service;

use Managers;

/**
 * Сервис для управления расписанием менеджеров
 * 
 * Обеспечивает добавление расписания и получение менеджеров по расписанию
 * с логированием критичных операций
 */
class ManagerScheduleService
{
    private Managers $managers;
    private OrganizationService $organizationService;
    private VoximplantLogger $logger;

    public function __construct(
        Managers $managers,
        OrganizationService $organizationService,
        VoximplantLogger $logger
    ) {
        $this->managers = $managers;
        $this->organizationService = $organizationService;
        $this->logger = $logger;
    }

    /**
     * Добавление расписания для менеджеров
     * 
     * @param string $date Дата в формате Y-m-d
     * @param array $managerIds Массив ID менеджеров
     * @param bool $plus Флаг plus
     * @param int|null $organizationId ID организации
     * @return bool Результат операции
     */
    public function addSchedule(string $date, array $managerIds, bool $plus, ?int $organizationId = null): bool
    {
        $startTime = microtime(true);
        $method = 'addSchedule';

        // Резолвим organization ID если передан
        if ($organizationId !== null) {
            $organizationId = $this->organizationService->resolveOrganizationId($organizationId);
        }

        $context = [
            'date' => $date,
            'managers_count' => count($managerIds),
            'plus' => $plus,
            'organization_id' => $organizationId,
        ];

        try {
            $this->logger->logRequest('manager_schedule', $method, [
                'date' => $date,
                'managers_count' => count($managerIds),
            ], $context);

            $this->managers->add_schedule($date, $managerIds, $plus, $organizationId);

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess('manager_schedule', $method, [
                'date' => $date,
                'managers_count' => count($managerIds),
            ], $duration, $context);

            return true;

        } catch (\Throwable $e) {
            $this->logger->logError('manager_schedule', $method, $e, $context);
            return false;
        }
    }

    /**
     * Получение менеджеров по расписанию на дату
     * 
     * @param string $date Дата в формате Y-m-d
     * @param bool $plus Флаг plus
     * @param int|null $organizationId ID организации
     * @return array Массив менеджеров
     */
    public function getScheduledManagers(string $date, bool $plus, ?int $organizationId = null): array
    {
        // Резолвим organization ID если передан
        if ($organizationId !== null) {
            $organizationId = $this->organizationService->resolveOrganizationId($organizationId);
        }

        return $this->managers->getScheduledManagers($date, $plus, $organizationId);
    }

    /**
     * Получение менеджеров компании
     * 
     * @param bool $plus Флаг plus
     * @param string|bool $minus Флаг minus
     * @param int|null $organizationId ID организации
     * @return array Массив менеджеров
     */
    public function getCompanyManagers(bool $plus, $minus, ?int $organizationId = null): array
    {
        // Резолвим organization ID если передан
        if ($organizationId !== null) {
            $organizationId = $this->organizationService->resolveOrganizationId($organizationId);
        }

        return $this->managers->getCompanyManagers($plus, $minus, $organizationId);
    }

    /**
     * Получение менеджеров (общий метод)
     * 
     * @param bool $plus Флаг plus
     * @param string|bool $minus Флаг minus
     * @param int|null $organizationId ID организации
     * @return array Массив менеджеров
     */
    public function getManagers(bool $plus, $minus, ?int $organizationId = null): array
    {
        // Резолвим organization ID если передан
        if ($organizationId !== null) {
            $organizationId = $this->organizationService->resolveOrganizationId($organizationId);
        }

        return $this->managers->getManagers($plus, $minus, $organizationId);
    }
}

