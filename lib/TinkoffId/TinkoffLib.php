<?php
// lib/TinkoffId/TinkoffLib.php

namespace lib\TinkoffId;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Simpla;
use GuzzleHttp\Client;

class TinkoffLib
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $simpla;
    private $httpClient;

    /**
     * Конструктор класса TinkoffLib
     *
     * @param string $clientId     Идентификатор клиента
     * @param string $clientSecret Секретный ключ клиента
     * @param string $redirectUri  URI перенаправления после авторизации
     */
    public function __construct(string $clientId, string $clientSecret, string $redirectUri)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->simpla = new Simpla();
        $this->httpClient = new Client();
    }

    /**
     * Генерирует случайное значение состояния (state) для защиты от CSRF атак
     *
     * @return string Сгенерированное значение состояния
     * @throws RandomException
     */
    public function stateGenerate(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Генерирует URL для авторизации в Tinkoff ID
     *
     * @param string $state Значение состояния (state) для защиты от CSRF атак
     * @return string URL для авторизации
     */
    public function authUrlGenerate(string $state): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'state' => $state,
        ]);

        $authUrl = 'https://id.tinkoff.ru/auth/authorize?' . $query;

        $this->simpla->logging('INFO', 'Generated authorization URL', '', ['auth_url' => $authUrl], 'tinkoffid.txt');

        return $authUrl;
    }

    /**
     * Запрашивает токен доступа у Tinkoff ID
     *
     * @param string $code Код авторизации, полученный после успешной авторизации пользователя
     * @return array Ответ от сервера Tinkoff ID с токеном доступа или ошибкой
     * @throws Exception
     */
    public function requestToken(string $code): array
    {
        $postData = http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ]);

        $authHeader = 'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret);

        $response = $this->makeHttpRequest('https://id.tinkoff.ru/auth/token', $postData, true, [$authHeader]);

        $this->simpla->logging('INFO', 'Token response', '', $response, 'tinkoffid.txt');

        return $response;
    }

    /**
     * Запрашивает информацию о пользователе у Tinkoff ID
     *
     * @param string $accessToken Токен доступа, полученный после успешной авторизации
     * @return array Информация о пользователе или ошибка
     * @throws Exception
     */
//    public function requestUserInfo(string $accessToken): array
//    {
//        $authHeader = 'Authorization: Bearer ' . $accessToken;
//
//        $postData = http_build_query([
//            'client_id' => $this->clientId,
//            'client_secret' => $this->clientSecret,
//        ]);
//
//        $headers = [
//            $authHeader,
//            'Content-Type: application/x-www-form-urlencoded',
//        ];
//
//        $response = $this->makeHttpRequest('https://id.tinkoff.ru/userinfo/userinfo', $postData, true, $headers);
//
//        $this->simpla->logging('INFO', 'User info response', '', $response, 'tinkoffid.txt');
//
//        if (isset($response['error'])) {
//            throw new Exception('UserInfo error: ' . $response['error']);
//        }
//
//        return $response;
//    }
    public function requestUserInfoAsync(string $accessToken): PromiseInterface
    {
        $url = 'https://id.tinkoff.ru/userinfo/userinfo';
        $postData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $options = [
            'headers' => $headers,
            'form_params' => $postData,
        ];

        return $this->makeHttpRequestAsync('POST', $url, $options);
    }

    /**
     * Запрашивает паспортные данные пользователя у Tinkoff ID
     *
     * @param string $accessToken Токен доступа, полученный после успешной авторизации
     * @return array Паспортные данные пользователя или ошибка
     * @throws Exception
     */
//    public function requestPassportData(string $accessToken): array
//    {
//        $headers = [
//            'Accept: application/json',
//            'Authorization: Bearer ' . $accessToken,
//        ];
//
//        $response = $this->makeHttpRequest('https://business.tbank.ru/openapi/api/v1/individual/documents/passport', '', false, $headers);
//
//        $this->simpla->logging('INFO', 'Passport data response', '', $response, 'tinkoffid.txt');
//
//        if (isset($response['error'])) {
//            throw new Exception('Passport data error: ' . $response['error']);
//        }
//
//        return $response;
//    }
    public function requestPassportDataAsync(string $accessToken): PromiseInterface
    {
        $url = 'https://business.tbank.ru/openapi/api/v1/individual/documents/passport';
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $options = [
            'headers' => $headers,
        ];

        return $this->makeHttpRequestAsync('GET', $url, $options);
    }

    /**
     * Запрашивает адреса пользователя у Tinkoff ID
     *
     * @param string $accessToken Токен доступа, полученный после успешной авторизации
     * @return array Адреса пользователя или ошибка
     * @throws Exception
     */
//    public function requestAddresses(string $accessToken): array
//    {
//        $headers = [
//            'Accept: application/json',
//            'Authorization: Bearer ' . $accessToken,
//        ];
//
//        $response = $this->makeHttpRequest('https://business.tbank.ru/openapi/api/v1/individual/addresses', '', false, $headers);
//
//        $this->simpla->logging('INFO', 'Addresses response', '', $response, 'tinkoffid.txt');
//
//        if (isset($response['error'])) {
//            throw new Exception('Addresses error: ' . $response['error']);
//        }
//
//        return $response;
//    }
    public function requestAddressesAsync(string $accessToken): PromiseInterface
    {
        $url = 'https://business.tbank.ru/openapi/api/v1/individual/addresses';
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $options = [
            'headers' => $headers,
        ];

        return $this->makeHttpRequestAsync('GET', $url, $options);
    }

    /**
     * Выполняет HTTP запрос
     *
     * @param string $url     URL для запроса
     * @param string $postData Данные для POST запроса
     * @param bool   $isPost    Флаг, указывающий на то, является ли запрос POST запросом
     * @param array  $headers Дополнительные заголовки для запроса
     * @return array Ответ от сервера
     * @throws Exception
     */
    private function makeHttpRequest(string $url, string $postData = '', bool $isPost = false, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        //curl_setopt($ch, CURLOPT_FAILONERROR, true);

        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

            $defaultHeaders = ['Content-Type: application/x-www-form-urlencoded'];
            $allHeaders = array_merge($defaultHeaders, $headers);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        } else {
            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            $this->simpla->logging('ERROR', 'cURL error', '', [
                'error' => $error,
                'http_code' => $httpCode,
            ], 'tinkoffid.txt');
            return ['error' => $error, 'http_code' => $httpCode];
        }

        if ($httpCode >= 400) {
            $decodedResponse = json_decode($response, true);
            $errorMessage = $decodedResponse['error_description'] ?? 'Unknown error';
            return ['error' => $errorMessage, 'http_code' => $httpCode];
        }

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonError = json_last_error_msg();
            $this->simpla->logging('ERROR', 'JSON decode error', '', [
                'json_error' => $jsonError,
                'raw_response' => $response,
                'http_code' => $httpCode,
            ], 'tinkoffid.txt');
            return ['error' => 'JSON decode error: ' . $jsonError, 'raw_response' => $response, 'http_code' => $httpCode];
        }

        $this->simpla->logging('INFO', 'HTTP response', '', [
            'response' => $decodedResponse,
            'http_code' => $httpCode,
        ], 'tinkoffid.txt');

        return $decodedResponse;
    }

    /**
     * @throws Exception
     */
    private function makeHttpRequestAsync(string $method, string $url, array $options = []): PromiseInterface
    {
        $this->simpla->logging('INFO', 'HTTP request initiated', '', [
            'method' => $method,
            'url' => $url,
            'options' => $options,
        ], 'tinkoffid.txt');

        return $this->httpClient->requestAsync($method, $url, $options)
            ->then(
                function (ResponseInterface $response) {
                    $statusCode = $response->getStatusCode();
                    $body = $response->getBody()->getContents();
                    $decodedResponse = json_decode($body, true);

                    if ($statusCode >= 400) {
                        $errorMessage = $decodedResponse['error_description'] ?? 'Unknown error';
                        throw new Exception($errorMessage, $statusCode);
                    }

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $jsonError = json_last_error_msg();
                        $this->simpla->logging('ERROR', 'JSON decode error', '', [
                            'json_error' => $jsonError,
                            'raw_response' => $body,
                            'http_code' => $statusCode,
                        ], 'tinkoffid.txt');
                        throw new Exception('JSON decode error: ' . $jsonError);
                    }

                    $this->simpla->logging('INFO', 'HTTP response received', '', [
                        'response' => $decodedResponse,
                        'http_code' => $statusCode,
                    ], 'tinkoffid.txt');

                    return $decodedResponse;
                },
                function (\Exception $e) {
                    $this->simpla->logging('ERROR', 'HTTP request error', '', [
                        'error' => $e->getMessage(),
                    ], 'tinkoffid.txt');
                    throw $e;
                }
            );
    }
}
