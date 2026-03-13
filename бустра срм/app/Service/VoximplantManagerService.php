<?php

namespace App\Service;

use Managers;

/**
 * Сервис для работы с настройками Voximplant для менеджеров
 * 
 * Обеспечивает добавление и обновление настроек Vox (PDS ID, DNC ID) для менеджеров
 * с полным логированием всех операций
 */
class VoximplantManagerService
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
     * Добавление или обновление настроек Vox для менеджера
     * 
     * @param int $managerId ID менеджера
     * @param int $pdsId ID PDS кампании
     * @param int $dncId ID DNC листа
     * @param int|null $organizationId ID организации
     * @param bool $plus Флаг plus
     * @param string|bool $minus Флаг minus
     * @return array Результат операции с данными менеджера
     */
    public function addOrUpdateVoxManager(
        int $managerId,
        int $pdsId,
        int $dncId,
        ?int $organizationId = null,
        bool $plus = false,
        $minus = 'false'
    ): array {
        $startTime = microtime(true);
        $method = 'addOrUpdateVoxManager';

        // Резолвим organization ID если передан
        if ($organizationId !== null) {
            $organizationId = $this->organizationService->resolveOrganizationId($organizationId);
        }

        $context = [
            'manager_id' => $managerId,
            'pds_id' => $pdsId,
            'dnc_id' => $dncId,
            'organization_id' => $organizationId,
            'plus' => $plus,
            'minus' => $minus,
        ];

        try {
            // Валидация входных данных
            $validationResult = $this->validateInput($managerId, $pdsId, $dncId);
            if (!$validationResult['success']) {
                $this->logger->logError('voximplant_manager', $method, 
                    new \Exception($validationResult['error']), $context);
                return $validationResult;
            }

            // Получаем информацию о менеджере
            $manager = $this->managers->get_manager($managerId);
            if (!$manager) {
                $error = "Менеджер с ID {$managerId} не найден";
                $this->logger->logError('voximplant_manager', $method, 
                    new \Exception($error), $context);
                return ['success' => false, 'error' => $error];
            }

            $this->logger->logRequest('voximplant_manager', $method, [
                'manager_id' => $managerId,
                'pds_id' => $pdsId,
                'dnc_id' => $dncId,
            ], $context);

            // Проверяем, существует ли уже запись
            $existsVox = $this->managers->getVoxManager($managerId, $organizationId);

            if (empty($existsVox)) {
                // Добавляем новую запись
                $this->managers->insertVoxData([
                    'manager_id' => $managerId,
                    'company' => $pdsId,
                    'dnc_number' => $dncId,
                    'plus' => $plus,
                    'minus' => $minus
                ], $organizationId);

                $action = 'inserted';
            } else {
                // Обновляем существующую запись
                $this->managers->updateVoxData($managerId, [
                    'company' => $pdsId,
                    'dnc_number' => $dncId,
                    'plus' => $plus,
                    'minus' => $minus
                ], $organizationId);

                $action = 'updated';
            }

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess('voximplant_manager', $method, [
                'manager_id' => $managerId,
                'action' => $action,
                'pds_id' => $pdsId,
                'dnc_id' => $dncId,
            ], $duration, $context);

            return [
                'success' => true,
                'name' => $manager->name,
                'action' => $action,
            ];

        } catch (\Throwable $e) {
            $this->logger->logError('voximplant_manager', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Получение настроек Vox для менеджера
     * 
     * @param int $managerId ID менеджера
     * @param int|null $organizationId ID организации
     * @return object|null Объект с настройками или null
     */
    public function getVoxManager(int $managerId, ?int $organizationId = null): ?object
    {
        $startTime = microtime(true);
        $method = 'getVoxManager';

        // Резолвим organization ID если передан
        if ($organizationId !== null) {
            $organizationId = $this->organizationService->resolveOrganizationId($organizationId);
        }

        $context = [
            'manager_id' => $managerId,
            'organization_id' => $organizationId,
        ];

        try {
            $this->logger->logRequest('voximplant_manager', $method, [
                'manager_id' => $managerId,
            ], $context);

            $result = $this->managers->getVoxManager($managerId, $organizationId);

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess('voximplant_manager', $method, [
                'manager_id' => $managerId,
                'found' => $result !== null,
            ], $duration, $context);

            return $result;

        } catch (\Throwable $e) {
            $this->logger->logError('voximplant_manager', $method, $e, $context);
            return null;
        }
    }

    /**
     * Валидация входных данных
     * 
     * @param int $managerId ID менеджера
     * @param int $pdsId ID PDS кампании
     * @param int $dncId ID DNC листа
     * @return array Результат валидации
     */
    private function validateInput(int $managerId, int $pdsId, int $dncId): array
    {
        if ($managerId <= 0) {
            return ['success' => false, 'error' => 'Неверный ID менеджера'];
        }

        if ($pdsId <= 0) {
            return ['success' => false, 'error' => 'Неверный ID PDS кампании'];
        }

        if ($dncId <= 0) {
            return ['success' => false, 'error' => 'Неверный ID DNC листа'];
        }

        return ['success' => true];
    }
}

