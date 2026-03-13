<?php

namespace App\Service;

use Managers;

/**
 * Сервис для работы с DNC-листами Voximplant
 * 
 * Обеспечивает добавление, удаление и получение номеров из DNC-листов
 * с полным логированием всех операций
 */
class VoximplantDncService
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
     * Добавление номеров в DNC-лист
     * 
     * @param int|string|object $company ID кампании или объект с company
     * @param array $phoneNumbers Массив номеров телефонов
     * @param string $comment Комментарий
     * @param int|null $organizationId ID организации
     * @return array Результат операции
     */
    public function addToDnc($company, array $phoneNumbers, string $comment = '', ?int $organizationId = null): array
    {
        $startTime = microtime(true);
        $method = 'addToDnc';

        // Нормализуем company ID
        if (is_object($company) && isset($company->company)) {
            $company = $company->company;
        }
        $campaignId = (string) $company;

        // Получаем DNC ID из базы данных
        $dncListId = $this->managers->getDNC(['company' => (int) $campaignId]);
        if (empty($dncListId)) {
            $error = "DNC list ID not found for campaign: {$campaignId}";
            $this->logger->logError('voximplant_dnc', $method, new \Exception($error), [
                'campaign_id' => $campaignId,
                'organization_id' => $organizationId,
            ]);
            return ['success' => false, 'error' => $error];
        }

        $context = [
            'campaign_id' => $campaignId,
            'dnc_list_id' => $dncListId,
            'organization_id' => $organizationId,
            'phones_count' => count($phoneNumbers),
        ];

        try {
            $this->logger->logRequest('voximplant_dnc', $method, [
                'dnc_list_id' => $dncListId,
                'phones_count' => count($phoneNumbers),
            ], $context);

            $result = $this->apiClient->addDncContacts(
                $phoneNumbers,
                (int) $dncListId,
                $comment ?: 'тест',
                $campaignId,
                $organizationId
            );

            $duration = microtime(true) - $startTime;
            
            if ($result['success']) {
                $this->logger->logSuccess('voximplant_dnc', $method, [
                    'dnc_list_id' => $dncListId,
                    'phones_count' => count($phoneNumbers),
                ], $duration, $context);
            } else {
                $this->logger->logError('voximplant_dnc', $method, 
                    new \Exception($result['error'] ?? 'Unknown error'), $context);
            }

            return $result;

        } catch (\Throwable $e) {
            $this->logger->logError('voximplant_dnc', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Удаление номера из DNC-листа
     * 
     * @param int|null $managerId ID менеджера
     * @param string $phoneNumber Номер телефона
     * @param int|string|object|null $company ID кампании (опционально)
     * @return array Результат операции
     */
    public function removeFromDnc(?int $managerId, string $phoneNumber, $company = null): array
    {
        $startTime = microtime(true);
        $method = 'removeFromDnc';

        // Получаем company ID если не передан
        if ($company === null && $managerId !== null) {
            $company = $this->managers->getCompany($managerId);
        }

        // Нормализуем company ID
        if (is_object($company) && isset($company->company)) {
            $company = $company->company;
        }

        if (empty($company)) {
            $error = "Company ID not found for manager: {$managerId}";
            $this->logger->logError('voximplant_dnc', $method, new \Exception($error), [
                'manager_id' => $managerId,
                'phone_number' => $phoneNumber,
            ]);
            return ['success' => false, 'error' => $error];
        }

        $campaignId = (string) $company;

        // Получаем DNC ID из базы данных
        $dncListId = $this->managers->getDNC(['company' => (int) $campaignId]);
        if (empty($dncListId)) {
            $error = "DNC list ID not found for campaign: {$campaignId}";
            $this->logger->logError('voximplant_dnc', $method, new \Exception($error), [
                'campaign_id' => $campaignId,
                'phone_number' => $phoneNumber,
            ]);
            return ['success' => false, 'error' => $error];
        }

        // Резолвим organization ID
        $organizationId = null;
        if ($managerId !== null) {
            $organizationId = $this->organizationService->resolveOrganizationIdByManager($managerId);
        }
        if ($organizationId === null) {
            $organizationId = $this->organizationService->resolveOrganizationIdByCampaign($campaignId);
        }

        $context = [
            'manager_id' => $managerId,
            'campaign_id' => $campaignId,
            'dnc_list_id' => $dncListId,
            'phone_number' => $phoneNumber,
            'organization_id' => $organizationId,
        ];

        try {
            $this->logger->logRequest('voximplant_dnc', $method, [
                'dnc_list_id' => $dncListId,
                'phone_number' => $phoneNumber,
            ], $context);

            $result = $this->apiClient->deleteDncContact(
                (int) $dncListId,
                $phoneNumber,
                $campaignId,
                $organizationId
            );

            $duration = microtime(true) - $startTime;
            
            if ($result['success']) {
                $this->logger->logSuccess('voximplant_dnc', $method, [
                    'dnc_list_id' => $dncListId,
                    'phone_number' => $phoneNumber,
                ], $duration, $context);
            } else {
                $this->logger->logError('voximplant_dnc', $method, 
                    new \Exception($result['error'] ?? 'Unknown error'), $context);
            }

            return $result;

        } catch (\Throwable $e) {
            $this->logger->logError('voximplant_dnc', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Получение номеров из DNC по статусу и фильтрам
     * 
     * @param string|null $status Статус контактов (например, 'ongoing')
     * @param string $action Действие для определения логики обработки
     * @param int|null $managerId ID менеджера
     * @param string|null $from Дата начала (Y-m-d)
     * @param string|null $to Дата окончания (Y-m-d)
     * @return array Массив номеров телефонов или структурированный результат
     */
    public function getDncNumbers(
        ?string $status,
        string $action,
        ?int $managerId = null,
        ?string $from = null,
        ?string $to = null
    ): array {
        $startTime = microtime(true);
        $method = 'getDncNumbers';

        // Определяем даты по умолчанию
        $day = $to ?: date('Y-m-d');
        if ($from === null) {
            $from = date('Y-m-d', strtotime('-3 days'));
        }

        if ($action === 'deleteDnc') {
            $day = date('Y-m-d', strtotime('-1 days'));
            $from = date('Y-m-d', strtotime('-10 days'));
        }

        // Получаем список кампаний для менеджеров
        $campaigns = [];
        if ($managerId !== null) {
            $company = $this->managers->getCompany($managerId);
            if (is_object($company) && isset($company->company)) {
                $campaigns[(string) $managerId] = (string) $company->company;
            } elseif (!empty($company)) {
                $campaigns[(string) $managerId] = (string) $company;
            }
        } else {
            // Получаем всех выбранных менеджеров
            $selectedManagers = $this->managers->getSelectedManagers($day);
            $managerIds = [];
            foreach ($selectedManagers as $manager) {
                $managerIds[] = $manager->manager_id;
            }
            
            if (!empty($managerIds)) {
                $m = implode(',', $managerIds);
                $company = $this->managers->getCompany($m);
                
                if (is_object($company)) {
                    if (isset($company->companies)) {
                        $companies = explode(',', (string) $company->companies);
                        $companyManagers = explode(',', (string) $company->managers);
                        foreach ($companyManagers as $i => $manager) {
                            $campaigns[$manager] = $companies[$i] ?? null;
                        }
                    } elseif (isset($company->company)) {
                        // Один менеджер - одна кампания
                        foreach ($managerIds as $managerId) {
                            $campaigns[(string) $managerId] = (string) $company->company;
                        }
                    }
                }
            }
        }

        if (empty($campaigns)) {
            $this->logger->logRequest('voximplant_dnc', $method, [
                'message' => 'No campaigns found',
            ], [
                'manager_id' => $managerId,
                'action' => $action,
            ]);
            return [];
        }

        $result = [];
        $allPhones = [];

        foreach ($campaigns as $key => $campaignId) {
            if ($campaignId === null || $campaignId === '') {
                continue;
            }

            // Резолвим organization ID
            $organizationId = $this->organizationService->resolveOrganizationIdByCampaign($campaignId);

            $context = [
                'campaign_id' => $campaignId,
                'manager_id' => is_numeric($key) ? (int) $key : null,
                'organization_id' => $organizationId,
                'status' => $status,
                'action' => $action,
            ];

            try {
                $this->logger->logRequest('voximplant_dnc', $method, [
                    'campaign_id' => $campaignId,
                    'status' => $status,
                    'from' => $from,
                    'to' => $day,
                ], $context);

                // Получаем первую страницу для определения количества страниц
                $filters = [
                    'status' => $status ? '["' . $status . '"]' : null,
                    'from' => $from . ' 00:00:00',
                    'to' => $day . ' 23:59:59',
                    'per-page' => '50',
                    'page' => 1,
                ];

                if ($action === 'deleteDnc') {
                    unset($filters['status']);
                }

                $firstPageResult = $this->apiClient->searchContacts($campaignId, $filters, $organizationId);
                
                if (!$firstPageResult['success']) {
                    continue;
                }

                $responseData = $firstPageResult['data'];
                $phones = [];

                // Проверяем формат ответа
                if (is_object($responseData) && isset($responseData->_meta)) {
                    $pagesCount = (int) ($responseData->_meta->pageCount ?? 1);
                    
                    // Обрабатываем первую страницу
                    if (isset($responseData->result)) {
                        foreach ($responseData->result as $res) {
                            if (isset($res->phone)) {
                                $phones[] = $res->phone;
                            }
                        }
                    }

                    // Обрабатываем остальные страницы
                    for ($i = 2; $i <= $pagesCount; $i++) {
                        $pageFilters = $filters;
                        $pageFilters['page'] = $i;
                        
                        $pageResult = $this->apiClient->searchContacts($campaignId, $pageFilters, $organizationId);
                        
                        if ($pageResult['success'] && isset($pageResult['data']->result)) {
                            foreach ($pageResult['data']->result as $res) {
                                if (isset($res->phone)) {
                                    $phones[] = $res->phone;
                                }
                            }
                        }
                    }
                } elseif (is_array($responseData) && isset($responseData['_meta'])) {
                    // Обработка массива формата
                    $pagesCount = (int) ($responseData['_meta']['pageCount'] ?? 1);
                    
                    if (isset($responseData['result'])) {
                        foreach ($responseData['result'] as $res) {
                            if (isset($res['phone'])) {
                                $phones[] = $res['phone'];
                            }
                        }
                    }

                    for ($i = 2; $i <= $pagesCount; $i++) {
                        $pageFilters = $filters;
                        $pageFilters['page'] = $i;
                        
                        $pageResult = $this->apiClient->searchContacts($campaignId, $pageFilters, $organizationId);
                        
                        if ($pageResult['success'] && isset($pageResult['data']['result'])) {
                            foreach ($pageResult['data']['result'] as $res) {
                                if (isset($res['phone'])) {
                                    $phones[] = $res['phone'];
                                }
                            }
                        }
                    }
                }

                $duration = microtime(true) - $startTime;
                $this->logger->logSuccess('voximplant_dnc', $method, [
                    'campaign_id' => $campaignId,
                    'phones_count' => count($phones),
                ], $duration, $context);

                // Обрабатываем результат в зависимости от action
                switch ($action) {
                    case 'checkRecall':
                    case 'getOngoing':
                    case 'deleteManager':
                        $allPhones = array_merge($allPhones, $phones);
                        break;
                    case 'recall':
                        $result[$key] = $phones;
                        break;
                    case 'dialings':
                        // Для dialings нужно добавить в DNC
                        if (!empty($phones)) {
                            $chunks = array_chunk($phones, 50);
                            foreach ($chunks as $chunk) {
                                $this->addToDnc($campaignId, $chunk, '', $organizationId);
                            }
                        }
                        break;
                    case 'deleteDnc':
                        // Удаляем каждый номер из DNC
                        foreach ($phones as $phone) {
                            $this->removeFromDnc(null, $phone, $campaignId);
                        }
                        break;
                }

            } catch (\Throwable $e) {
                $this->logger->logError('voximplant_dnc', $method, $e, $context);
            }
        }

        // Возвращаем результат в зависимости от action
        if ($action === 'recall') {
            return $result;
        }

        return $allPhones;
    }
}

