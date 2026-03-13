<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

/**
 * Единый клиент для работы с Voximplant API
 * 
 * Заменяет прямые cURL вызовы на Guzzle HTTP клиент
 * Обеспечивает единый интерфейс для всех API операций
 */
class VoximplantApiClient
{
    private OrganizationService $organizationService;
    private VoximplantLogger $logger;
    private Client $httpClient;
    private string $apiUrlV3;

    public function __construct(
        OrganizationService $organizationService,
        VoximplantLogger $logger
    ) {
        $this->organizationService = $organizationService;
        $this->logger = $logger;
        $this->apiUrlV3 = $this->organizationService->getApiUrlV3();

        $this->httpClient = new Client([
            'timeout' => 60,
            'connect_timeout' => 10,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'PHP-MCAPI/2.0',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);
    }

    /**
     * Добавление контактов в PDS кампанию
     * 
     * @param array $users Массив пользователей для отправки
     * @param int|string $campaignId ID кампании
     * @param int|null $organizationId ID организации
     * @return array Результат операции
     */
    public function appendContactsToCampaign(array $users, $campaignId, ?int $organizationId = null): array
    {
        $startTime = microtime(true);
        $method = 'appendContactsToCampaign';
        
        // Резолвим credentials
        if ($organizationId !== null) {
            $credentials = $this->organizationService->getVoxCredentials($organizationId);
        } else {
            $resolvedOrgId = $this->organizationService->resolveOrganizationIdByCampaign((string) $campaignId);
            if ($resolvedOrgId !== null) {
                $credentials = $this->organizationService->getVoxCredentials($resolvedOrgId);
            } else {
                $credentials = $this->organizationService->getDefaultVoxCredentials();
            }
        }

        $domain = $credentials['domain'];
        $token = $credentials['token'];

        $data = [
            'rows' => json_encode($users, JSON_UNESCAPED_UNICODE),
            'campaign_id' => (string) $campaignId,
        ];

        $context = [
            'campaign_id' => (string) $campaignId,
            'organization_id' => $organizationId,
            'domain' => $domain,
            'users_count' => count($users),
        ];

        try {
            $this->logger->logRequest('voximplant_api', $method, $data, $context);

            $url = rtrim($this->apiUrlV3, '/') . "/agentCampaigns/appendContacts";
            $response = $this->httpClient->post($url, [
                RequestOptions::QUERY => [
                    'access_token' => $token,
                    'domain' => $domain,
                ],
                RequestOptions::FORM_PARAMS => $data,
            ]);

            $responseBody = $response->getBody()->getContents();
            $decodedResponse = json_decode($responseBody, true) ?? ['raw_response' => $responseBody];
            
            $duration = microtime(true) - $startTime;
            $this->logger->logResponse('voximplant_api', $method, $decodedResponse, $duration, $context);

            $httpCode = $response->getStatusCode();
            if ($httpCode === 200 && (isset($decodedResponse['result']) || isset($decodedResponse['success']))) {
                return ['success' => true, 'data' => $decodedResponse];
            }

            return ['success' => false, 'error' => $responseBody, 'data' => $decodedResponse];

        } catch (GuzzleException $e) {
            $this->logger->logError('voximplant_api', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'http_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ];
        }
    }

    /**
     * Добавление контактов в робокомпанию (outbound campaign)
     * 
     * @param array $data Данные для отправки (campaign_id, rows)
     * @param int|null $organizationId ID организации
     * @param int|null $managerId ID менеджера
     * @return array Результат операции
     */
    public function appendToCampaign(array $data, ?int $organizationId = null, ?int $managerId = null): array
    {
        $startTime = microtime(true);
        $method = 'appendToCampaign';
        $campaignId = $data['campaign_id'] ?? null;

        // Резолвим credentials
        if ($organizationId !== null) {
            $credentials = $this->organizationService->getVoxCredentials($organizationId);
        } elseif ($campaignId !== null) {
            $resolvedOrgId = $this->organizationService->resolveOrganizationIdByCampaign((string) $campaignId);
            if ($resolvedOrgId !== null) {
                $credentials = $this->organizationService->getVoxCredentials($resolvedOrgId);
            } else {
                $credentials = $this->organizationService->getDefaultVoxCredentials();
            }
        } elseif ($managerId !== null) {
            $resolvedOrgId = $this->organizationService->resolveOrganizationIdByManager($managerId);
            if ($resolvedOrgId !== null) {
                $credentials = $this->organizationService->getVoxCredentials($resolvedOrgId);
            } else {
                $credentials = $this->organizationService->getDefaultVoxCredentials();
            }
        } else {
            $credentials = $this->organizationService->getDefaultVoxCredentials();
        }

        $domain = $credentials['domain'];
        $token = $credentials['token'];

        $requestData = array_merge($data, [
            'domain' => $domain,
            'access_token' => $token,
        ]);

        $context = [
            'campaign_id' => (string) ($campaignId ?? 'unknown'),
            'organization_id' => $organizationId,
            'manager_id' => $managerId,
            'domain' => $domain,
        ];

        try {
            $this->logger->logRequest('voximplant_api', $method, $requestData, $context);

            $url = rtrim($this->apiUrlV3, '/') . "/outbound/appendToCampaign";
            $response = $this->httpClient->post($url, [
                RequestOptions::FORM_PARAMS => $requestData,
            ]);

            $responseBody = $response->getBody()->getContents();
            $decodedResponse = json_decode($responseBody, true) ?? ['raw_response' => $responseBody];
            
            $duration = microtime(true) - $startTime;
            $this->logger->logResponse('voximplant_api', $method, $decodedResponse, $duration, $context);

            $httpCode = $response->getStatusCode();
            if ($httpCode === 200 && (isset($decodedResponse['result']) || isset($decodedResponse['success']))) {
                return ['success' => true, 'data' => $decodedResponse];
            }

            return ['success' => false, 'error' => $responseBody, 'data' => $decodedResponse];

        } catch (GuzzleException $e) {
            $this->logger->logError('voximplant_api', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'http_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ];
        }
    }

    /**
     * Добавление номеров в DNC-лист
     * 
     * @param array $contacts Массив номеров телефонов
     * @param int $dncListId ID DNC-листа
     * @param string $comment Комментарий
     * @param int|string|null $campaignId ID кампании для резолвинга организации
     * @param int|null $organizationId ID организации
     * @return array Результат операции
     */
    public function addDncContacts(array $contacts, int $dncListId, string $comment = '', $campaignId = null, ?int $organizationId = null): array
    {
        $startTime = microtime(true);
        $method = 'addDncContacts';

        // Резолвим credentials
        if ($organizationId !== null) {
            $credentials = $this->organizationService->getVoxCredentials($organizationId);
        } elseif ($campaignId !== null) {
            $resolvedOrgId = $this->organizationService->resolveOrganizationIdByCampaign((string) $campaignId);
            if ($resolvedOrgId !== null) {
                $credentials = $this->organizationService->getVoxCredentials($resolvedOrgId);
            } else {
                $credentials = $this->organizationService->getDefaultVoxCredentials();
            }
        } else {
            $credentials = $this->organizationService->getDefaultVoxCredentials();
        }

        $domain = $credentials['domain'];
        $token = $credentials['token'];

        $data = [
            'id' => $dncListId,
            'contacts' => json_encode($contacts, JSON_UNESCAPED_UNICODE),
            'comment' => $comment,
        ];

        $context = [
            'dnc_list_id' => $dncListId,
            'campaign_id' => $campaignId ? (string) $campaignId : null,
            'organization_id' => $organizationId,
            'domain' => $domain,
            'contacts_count' => count($contacts),
        ];

        try {
            $this->logger->logRequest('voximplant_api', $method, $data, $context);

            $url = rtrim($this->apiUrlV3, '/') . "/dnc/addDncContacts";
            $response = $this->httpClient->post($url, [
                RequestOptions::QUERY => [
                    'access_token' => $token,
                    'domain' => $domain,
                ],
                RequestOptions::FORM_PARAMS => $data,
            ]);

            $responseBody = $response->getBody()->getContents();
            $decodedResponse = json_decode($responseBody, true) ?? ['raw_response' => $responseBody];
            
            $duration = microtime(true) - $startTime;
            $this->logger->logResponse('voximplant_api', $method, $decodedResponse, $duration, $context);

            return ['success' => true, 'data' => $decodedResponse];

        } catch (GuzzleException $e) {
            $this->logger->logError('voximplant_api', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'http_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ];
        }
    }

    /**
     * Удаление контакта из DNC-листа
     * 
     * @param int $dncListId ID DNC-листа
     * @param string $phoneNumber Номер телефона
     * @param int|string|null $campaignId ID кампании для резолвинга организации
     * @param int|null $organizationId ID организации
     * @return array Результат операции
     */
    public function deleteDncContact(int $dncListId, string $phoneNumber, $campaignId = null, ?int $organizationId = null): array
    {
        $startTime = microtime(true);
        $method = 'deleteDncContact';

        // Резолвим credentials
        if ($organizationId !== null) {
            $credentials = $this->organizationService->getVoxCredentials($organizationId);
        } elseif ($campaignId !== null) {
            $resolvedOrgId = $this->organizationService->resolveOrganizationIdByCampaign((string) $campaignId);
            if ($resolvedOrgId !== null) {
                $credentials = $this->organizationService->getVoxCredentials($resolvedOrgId);
            } else {
                $credentials = $this->organizationService->getDefaultVoxCredentials();
            }
        } else {
            $credentials = $this->organizationService->getDefaultVoxCredentials();
        }

        $domain = $credentials['domain'];
        $token = $credentials['token'];

        $data = [
            'list_id' => $dncListId,
            'number' => $phoneNumber,
        ];

        $context = [
            'dnc_list_id' => $dncListId,
            'phone_number' => $phoneNumber,
            'campaign_id' => $campaignId ? (string) $campaignId : null,
            'organization_id' => $organizationId,
            'domain' => $domain,
        ];

        try {
            $this->logger->logRequest('voximplant_api', $method, $data, $context);

            $url = rtrim($this->apiUrlV3, '/') . "/dnc/deleteDncContact";
            $response = $this->httpClient->post($url, [
                RequestOptions::QUERY => [
                    'access_token' => $token,
                    'domain' => $domain,
                ],
                RequestOptions::FORM_PARAMS => $data,
            ]);

            $responseBody = $response->getBody()->getContents();
            $decodedResponse = json_decode($responseBody, true) ?? ['raw_response' => $responseBody];
            
            $duration = microtime(true) - $startTime;
            $this->logger->logResponse('voximplant_api', $method, $decodedResponse, $duration, $context);

            return ['success' => true, 'data' => $decodedResponse];

        } catch (GuzzleException $e) {
            $this->logger->logError('voximplant_api', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'http_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ];
        }
    }

    /**
     * Поиск контактов в кампании
     * 
     * @param int|string $campaignId ID кампании
     * @param array $filters Фильтры поиска (status, from, to, page, per-page)
     * @param int|null $organizationId ID организации
     * @return array Результат поиска
     */
    public function searchContacts($campaignId, array $filters = [], ?int $organizationId = null): array
    {
        $startTime = microtime(true);
        $method = 'searchContacts';

        // Резолвим credentials
        if ($organizationId !== null) {
            $credentials = $this->organizationService->getVoxCredentials($organizationId);
        } else {
            $resolvedOrgId = $this->organizationService->resolveOrganizationIdByCampaign((string) $campaignId);
            if ($resolvedOrgId !== null) {
                $credentials = $this->organizationService->getVoxCredentials($resolvedOrgId);
            } else {
                $credentials = $this->organizationService->getDefaultVoxCredentials();
            }
        }

        $domain = $credentials['domain'];
        $token = $credentials['token'];

        $data = array_merge([
            'campaign_id' => (string) $campaignId,
            'access_token' => $token,
            'per-page' => '50',
            'page' => 1,
        ], $filters);

        $context = [
            'campaign_id' => (string) $campaignId,
            'organization_id' => $organizationId,
            'domain' => $domain,
            'filters' => $filters,
        ];

        try {
            $this->logger->logRequest('voximplant_api', $method, $data, $context);

            $url = rtrim($this->apiUrlV3, '/') . "/agentCampaigns/searchContacts";
            $response = $this->httpClient->post($url, [
                RequestOptions::QUERY => [
                    'domain' => $domain,
                ],
                RequestOptions::FORM_PARAMS => $data,
            ]);

            $responseBody = $response->getBody()->getContents();
            $decodedResponse = json_decode($responseBody, true);
            
            // Если ответ не JSON, возвращаем как есть
            if ($decodedResponse === null) {
                $decodedResponse = ['raw_response' => $responseBody];
            }
            
            $duration = microtime(true) - $startTime;
            $this->logger->logResponse('voximplant_api', $method, $decodedResponse, $duration, $context);

            return ['success' => true, 'data' => $decodedResponse];

        } catch (GuzzleException $e) {
            $this->logger->logError('voximplant_api', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'http_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ];
        }
    }
}


