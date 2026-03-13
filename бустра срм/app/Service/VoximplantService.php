<?php

namespace App\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class VoximplantService
{
    private const SEARCH_USERS_URI = 'user/searchUsers';
    private const ADD_DNC_CONTACTS_URI = 'dnc/addDncContacts';
    private const SEARCH_DNC_CONTACTS_URI = 'dnc/searchDncContacts';
    private const DELETE_DNC_CONTACT_URI = 'dnc/deleteDncContact';
    private const SEARCH_CALLS_URI = 'history/searchCalls';

    public const OUTGOING_CALLS_DNC_LIST_ID = 897;

    private string $domain;
    private string $token;
    private string $apiUrlV3;
    private Client $client;

    /**
     * @param array|null $overrides Ключи: domain, token, api_url — подставляются вместо config
     */
    public function __construct(?array $overrides = null)
    {
        $this->apiUrlV3 = ($overrides['api_url'] ?? null) ?: config('services.voximplant.api_url_v3');
        $this->domain = ($overrides['domain'] ?? null) ?: config('services.voximplant.domain');
        $this->token = ($overrides['token'] ?? null) ?: config('services.voximplant.token');

        $this->client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'headers' => [
                'Accept' => 'application/json',
            ],
            'verify' => false,
        ]);
    }

    /**
     * Создать экземпляр с кредами из строки s_vox_site_dnc (для отключения звонков по заявке).
     *
     * @param object $row Объект из VoxSiteDncRepository (vox_domain, vox_token, api_url, outgoing_calls_dnc_list_id)
     * @return self
     */
    public static function fromVoxSiteDncRow(object $row): self
    {
        $overrides = [
            'domain' => $row->vox_domain ?? '',
            'token' => $row->vox_token ?? '',
            'api_url' => $row->api_url ?? '',
        ];
        return new self($overrides);
    }

    public function searchCalls(int $callId): array
    {
        $params = ['domain' => $this->domain];
        $data = [
            'access_token' => $this->token,
            'id' => $callId,
            'with_scenarios' => true,
            'with_calls' => true,
            'limit' => 1,
        ];

        $response = $this->post($this->apiUrlV3, self::SEARCH_CALLS_URI, $params, $data);

        return $response['result'] ?? [];
    }

    /**
     * Постраничный поиск звонков за период (v3 API)
     *
     * @param string $from Начало периода (Y-m-d H:i:s)
     * @param string $to Конец периода (Y-m-d H:i:s)
     * @param int $page Номер страницы
     * @param int $perPage Количество на страницу
     * @param array $extraParams Доп. параметры (queue_ids, with_tags и т.д.)
     * @return array Ответ API с ключами success, result, _meta
     */
    public function searchCallsPaginated(string $from, string $to, int $page = 1, int $perPage = 50, array $extraParams = []): array
    {
        $params = ['domain' => $this->domain];
        $data = array_merge([
            'access_token' => $this->token,
            'from' => $from,
            'to' => $to,
            'page' => $page,
            'per-page' => $perPage,
        ], $extraParams);

        return $this->post($this->apiUrlV3, self::SEARCH_CALLS_URI, $params, $data);
    }

    public function searchUsers(int $userId): array
    {
        $params = ['domain' => $this->domain];
        $data = ['access_token' => $this->token, 'id' => $userId];

        return $this->post($this->apiUrlV3, self::SEARCH_USERS_URI, $params, $data)['result'] ?? [];
    }
    
    /**
     * Добавляет номера телефонов в DNC-лист
     *
     * @param array $contacts Массив номеров телефонов
     * @param int $dncListId ID DNC-листа
     * @param string $comment Комментарий
     * @return array Результат операции
     */
    public function addDncContacts(array $contacts, int $dncListId, string $comment = ''): array
    {
        $params = ['domain' => $this->domain];
        $data = [
            'access_token' => $this->token,
            'id' => $dncListId,
            'contacts' => json_encode($contacts),
            'comment' => $comment
        ];

        return $this->post($this->apiUrlV3, self::ADD_DNC_CONTACTS_URI, $params, $data);
    }
    
    /**
     * Поиск контактов в DNC-листе
     *
     * @param string $contact Номер телефона
     * @param int $dncListId ID DNC-листа
     * @return array Результат поиска
     */
    public function searchDncContacts(string $contact, int $dncListId): array
    {
        $params = ['domain' => $this->domain];
        $data = [
            'access_token' => $this->token,
            'list_id' => $dncListId,
            'number' => $contact
        ];

        return $this->post($this->apiUrlV3, self::SEARCH_DNC_CONTACTS_URI, $params, $data);
    }
    
    /**
     * Удаляет контакт из DNC-листа
     *
     * @param int $dncContactId ID контакта в DNC-листе
     * @return array Результат операции
     */
    public function deleteDncContact(int $dncContactId): array
    {
        $params = ['domain' => $this->domain];
        $data = [
            'access_token' => $this->token,
            'id' => $dncContactId
        ];

        return $this->post($this->apiUrlV3, self::DELETE_DNC_CONTACT_URI, $params, $data);
    }

    private function post(string $apiUrl, string $endpoint, array $params = [], array $data = []): array
    {
        try {
            $response = $this->client->post(
                $apiUrl . '/' . $endpoint,
                [
                    RequestOptions::QUERY => $params,
                    RequestOptions::FORM_PARAMS => $data
                ]
            );

            $result = json_decode($response->getBody()->getContents(), true);

            return $result ?? [];
        } catch (Exception|GuzzleException $e) {
            logger('voximplant_api')->error('Voximplant API error', [
                'api_url'  => $apiUrl,
                'endpoint' => $endpoint,
                'query'    => $params,
                'data'     => $data,
                'message'  => $e->getMessage(),
            ]);
            return [];
        }
    }
}