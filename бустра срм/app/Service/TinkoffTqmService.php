<?php

namespace App\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class TinkoffTqmService
{
    private string $apiUrl;
    private string $apiKey;
    private string $apiVersion;
    private Client $client;

    public function __construct()
    {
        $this->apiUrl = rtrim((string) config('services.tinkoff_tqm.api_url'), '/');
        $this->apiKey = (string) config('services.tinkoff_tqm.api_key');
        $this->apiVersion = (string) (config('services.tinkoff_tqm.api_version') ?? '1.0');

        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 5,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function sendCall(array $data): bool
    {
        try {
            if (empty($this->apiUrl) || empty($this->apiKey)) {
                return false;
            }

            if (!isset($data['apiVersion'])) {
                $data['apiVersion'] = $this->apiVersion;
            }

            logger('tqm')->info('Отправка звонка в Tinkoff TQM', [
                'endpoint' => '/v2/integration/import/call',
                'data' => $data,
            ]);

            $response = $this->client->post(
                $this->apiUrl . '/v2/integration/import/call',
                [
                    RequestOptions::HEADERS => [
                        'Authorization' => 'Basic ' . $this->apiKey,
                    ],
                    RequestOptions::QUERY => $data,
                ]
            );

            return $response->getStatusCode() === 200;
        } catch (\Throwable $e) {
            logger('tqm')->error('Ошибка отправки звонка в Tinkoff TQM', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return false;
        }
    }

    /**
     * Экспортирует пользователя в Tinkoff TQM
     *
     * @param array $data Данные пользователя:
     *                     - id (string): ID пользователя (обязательно)
     *                     - lastName (string): Фамилия (обязательно)
     *                     - firstName (string): Имя (опционально)
     *                     - middleName (string): Отчество (опционально)
     *                     - email (string): Email (обязательно)
     *                     - roleId (string): ID роли (обязательно, 2-агент, 3-супервайзер и т.д.)
     *                     - organizationalUnitId (string): ID орг. юнита (опционально)
     *                     - apiVersion (string): Версия API (опционально)
     * @return bool
     */
    public function exportUserToTqm(array $data): bool
    {
        try {
            if (empty($this->apiUrl) || empty($this->apiKey)) {
                return false;
            }

            // Валидация обязательных полей
            if (empty($data['id']) || empty($data['lastName']) || empty($data['email']) || empty($data['roleId'])) {
                logger('tqm')->error('Ошибка валидации данных пользователя для TQM', [
                    'required_fields' => ['id', 'lastName', 'email', 'roleId'],
                    'provided_data' => $data,
                ]);
                return false;
            }

            if (!isset($data['apiVersion'])) {
                $data['apiVersion'] = $this->apiVersion;
            }

            logger('tqm')->info('Экспорт пользователя в Tinkoff TQM', [
                'endpoint' => '/integration/import/user',
                'data' => $data,
            ]);

            $response = $this->client->post(
                $this->apiUrl . '/integration/import/user',
                [
                    RequestOptions::HEADERS => [
                        'Authorization' => 'Basic ' . $this->apiKey,
                    ],
                    RequestOptions::QUERY => $data,
                ]
            );

            $statusCode = $response->getStatusCode();
            $body = (string)$response->getBody();

            if ($statusCode === 200) {
                logger('tqm')->info('Пользователь успешно импортирован в TQM', [
                    'user_id' => $data['id'],
                    'response' => $body,
                ]);
                return true;
            }

            logger('tqm')->error('Ошибка импорта пользователя в TQM', [
                'status_code' => $statusCode,
                'body' => $body,
                'data' => $data,
            ]);

            return false;
        } catch (\Throwable $e) {
            logger('tqm')->error('Ошибка экспорта пользователя в Tinkoff TQM', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return false;
        }
    }
}
