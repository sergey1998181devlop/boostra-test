<?php

namespace App\Service;

use Managers;

/**
 * Сервис для отправки договоров в кампании Voximplant
 * 
 * Обеспечивает отправку в PDS кампании и робокомпании
 * с полным логированием всех операций
 */
class VoximplantCampaignService
{
    private VoximplantApiClient $apiClient;
    private VoximplantLogger $logger;
    private OrganizationService $organizationService;
    private Managers $managers;

    public function __construct(
        VoximplantApiClient $apiClient,
        VoximplantLogger $logger,
        OrganizationService $organizationService
    ) {
        $this->apiClient = $apiClient;
        $this->logger = $logger;
        $this->organizationService = $organizationService;
        $this->managers = new Managers();
    }

    /**
     * Форматирование пользователей для отправки в Voximplant
     * 
     * @param array $users Массив пользователей
     * @return array Отформатированные пользователи
     */
    public function formatUsers(array $users): array
    {
        foreach ($users as $user) {
            if (is_object($user) && isset($user->loan_type) && $user->loan_type === 'IL') {
                $user->prolongation_amount = 0;
                $user->zaim_summ = $user->overdue_debt_od_IL
                    + $user->overdue_debt_percent_IL
                    + $user->next_payment_od
                    + $user->next_payment_percent;
            }
        }

        return $users;
    }

    /**
     * Отправка договоров в PDS кампанию
     * 
     * @param array $users Массив пользователей для отправки
     * @param int|null $managerId ID менеджера
     * @param int|string|null $companyNumber ID кампании (опционально)
     * @param int|null $organizationId ID организации
     * @return array Результат операции
     */
    public function sendToPdsCampaign(
        array $users,
        ?int $managerId = null,
        $companyNumber = null,
        ?int $organizationId = null
    ): array {
        $startTime = microtime(true);
        $method = 'sendToPdsCampaign';

        // Получаем company ID если не передан
        if ($companyNumber === null && $managerId !== null) {
            $company = $this->managers->getCompany($managerId, $organizationId);
            if (is_object($company) && isset($company->company)) {
                $companyNumber = $company->company;
            } elseif (!empty($company)) {
                $companyNumber = $company;
            }
        }

        if (empty($companyNumber)) {
            $error = "Company ID not found for manager: {$managerId}";
            $this->logger->logError('voximplant_campaign', $method, new \Exception($error), [
                'manager_id' => $managerId,
                'organization_id' => $organizationId,
            ]);
            return ['success' => false, 'error' => $error];
        }

        $campaignId = (string) $companyNumber;

        // Резолвим organization ID если не передан
        if ($organizationId === null) {
            if ($managerId !== null) {
                $organizationId = $this->organizationService->resolveOrganizationIdByManager($managerId);
            }
            if ($organizationId === null) {
                $organizationId = $this->organizationService->resolveOrganizationIdByCampaign($campaignId);
            }
        }

        // Форматируем пользователей
        $formattedUsers = $this->formatUsers($users);

        $context = [
            'manager_id' => $managerId,
            'campaign_id' => $campaignId,
            'organization_id' => $organizationId,
            'users_count' => count($formattedUsers),
        ];

        try {
            $this->logger->logRequest('voximplant_campaign', $method, [
                'campaign_id' => $campaignId,
                'users_count' => count($formattedUsers),
            ], $context);

            $result = $this->apiClient->appendContactsToCampaign(
                $formattedUsers,
                $campaignId,
                $organizationId
            );

            $duration = microtime(true) - $startTime;
            
            if ($result['success']) {
                $this->logger->logSuccess('voximplant_campaign', $method, [
                    'campaign_id' => $campaignId,
                    'users_count' => count($formattedUsers),
                ], $duration, $context);
            } else {
                $this->logger->logError('voximplant_campaign', $method, 
                    new \Exception($result['error'] ?? 'Unknown error'), $context);
            }

            return $result;

        } catch (\Throwable $e) {
            $this->logger->logError('voximplant_campaign', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Отправка договоров в робокомпанию (outbound campaign)
     * 
     * @param array $users Массив пользователей для отправки
     * @param int $managerId ID менеджера
     * @param int|null $organizationId ID организации
     * @return array Результат операции
     */
    public function sendToRoboCampaign(array $users, int $managerId, ?int $organizationId = null): array
    {
        $startTime = microtime(true);
        $method = 'sendToRoboCampaign';

        // Получаем company ID
        $company = $this->managers->getCompany($managerId, $organizationId);
        if (empty($company)) {
            $error = "Company ID not found for manager: {$managerId}";
            $this->logger->logError('voximplant_campaign', $method, new \Exception($error), [
                'manager_id' => $managerId,
                'organization_id' => $organizationId,
            ]);
            return ['success' => false, 'error' => $error];
        }

        if (is_object($company) && isset($company->company)) {
            $company = $company->company;
        }

        $campaignId = (string) $company;

        // Резолвим organization ID если не передан
        if ($organizationId === null) {
            $organizationId = $this->organizationService->resolveOrganizationIdByManager($managerId);
            if ($organizationId === null) {
                $organizationId = $this->organizationService->resolveOrganizationIdByCampaign($campaignId);
            }
        }

        // Форматируем пользователей
        $formattedUsers = $this->formatUsers($users);

        $data = [
            'campaign_id' => $campaignId,
            'rows' => json_encode($formattedUsers, JSON_UNESCAPED_UNICODE),
        ];

        $context = [
            'manager_id' => $managerId,
            'campaign_id' => $campaignId,
            'organization_id' => $organizationId,
            'users_count' => count($formattedUsers),
        ];

        try {
            $this->logger->logRequest('voximplant_campaign', $method, [
                'campaign_id' => $campaignId,
                'users_count' => count($formattedUsers),
            ], $context);

            $result = $this->apiClient->appendToCampaign($data, $organizationId, $managerId);

            $duration = microtime(true) - $startTime;
            
            if ($result['success']) {
                $this->logger->logSuccess('voximplant_campaign', $method, [
                    'campaign_id' => $campaignId,
                    'users_count' => count($formattedUsers),
                ], $duration, $context);
            } else {
                $this->logger->logError('voximplant_campaign', $method, 
                    new \Exception($result['error'] ?? 'Unknown error'), $context);
            }

            return $result;

        } catch (\Throwable $e) {
            $this->logger->logError('voximplant_campaign', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Отправка договоров в Vox для конкретной организации
     * 
     * @param int $managerId ID менеджера
     * @param array $users Массив пользователей
     * @param int $organizationId ID организации
     * @param string|null $role Роль менеджера (определяет тип кампании)
     * @return array Результат операции
     */
    public function sendForOrganization(
        int $managerId,
        array $users,
        int $organizationId,
        ?string $role = null
    ): array {
        $startTime = microtime(true);
        $method = 'sendForOrganization';

        $context = [
            'manager_id' => $managerId,
            'organization_id' => $organizationId,
            'role' => $role,
            'users_count' => count($users),
        ];

        try {
            // Определяем тип кампании по роли
            $isRoboCampaign = in_array($role, [
                'contact_center_robo',
                'contact_center_new_robo',
                'robot_minus'
            ], true);

            if ($isRoboCampaign) {
                $result = $this->sendToRoboCampaign($users, $managerId, $organizationId);
            } else {
                $result = $this->sendToPdsCampaign($users, $managerId, null, $organizationId);
            }

            $duration = microtime(true) - $startTime;
            
            if ($result['success']) {
                $this->logger->logSuccess('voximplant_campaign', $method, [
                    'users_count' => count($users),
                    'campaign_type' => $isRoboCampaign ? 'robo' : 'pds',
                ], $duration, $context);
            }

            return $result;

        } catch (\Throwable $e) {
            $this->logger->logError('voximplant_campaign', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Отправка в PDS по ID для конкретной организации
     * 
     * @param array $users Массив пользователей
     * @param int $pdsId ID PDS кампании
     * @param int $organizationId ID организации
     * @return array Результат операции
     */
    public function sendToPdsById(array $users, int $pdsId, int $organizationId): array
    {
        return $this->sendToPdsCampaign($users, null, $pdsId, $organizationId);
    }
}


