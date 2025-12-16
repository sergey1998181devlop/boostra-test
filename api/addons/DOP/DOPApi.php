<?php

namespace ADDONS\DOP;

use Simpla;

abstract class DOPApi extends Simpla
{
    protected const LOG_FILENAME = 'dop_api.txt';
    protected const TIMEOUT = 10;

    /**
     * Возвращает список доменов API
     *
     * @return string[]
     */
    abstract protected function domains(): array;

    /**
     * Возвращает API ключ
     *
     * @return string
     */
    abstract protected function apiKey(): string;

    /**
     * Генерация ключа и получение всех данных лицензии
     * 
     * @param array $data Данные для генерации ключа
     * @return array Массив с данными лицензии или response в случае ошибки
     */
    public function makeKey(array $data): array
    {
        $response = $this->executeRequest(
            '/license/makeKey',
            [
                'method' => 'POST',
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($data),
            ]
        );

        if (isset($response['error']) && $response['error']) {
            return $response;
        }

        if (isset($response['result'], $response['ok']) && $response['ok']) {
            return $response['result'];
        }

        return [
            'error' => true,
            'message' => 'Invalid API response format',
            'response' => $response
        ];
    }

    /**
     * Выполняет HTTP запрос к API с поддержкой резервных доменов
     *
     * @param string $endpoint Конечная точка API
     * @param array $options Параметры запроса
     * @return array|false
     */
    protected function executeRequest(string $endpoint, array $options = [])
    {
        $options['headers']['apikey'] = $this->apiKey();
        $lastError = null;

        foreach ($this->domains() as $domain) {
            $url = $domain . $endpoint;
            $response = $this->sendRequest($url, $options);

            // Если это не ошибка, возвращаем успешный ответ
            if (!isset($response['error']) || !$response['error']) {
                return $response;
            }

            // Сохраняем последнюю ошибку
            $lastError = $response;
        }

        // Если все домены вернули ошибки, возвращаем последнюю
        return $lastError ?: [
            'error' => true,
            'message' => 'All API domains failed',
            'domains_tried' => $this->domains()
        ];
    }

    /**
     * Отправляет единичный HTTP запрос
     * 
     * @param string $url URL для запроса
     * @param array $options Параметры запроса
     * @return array|false
     */
    private function sendRequest(string $url, array $options)
    {
        $ch = $this->initCurl($url, $options);
        
        if ($ch === false) {
            return [
                'error' => true,
                'message' => 'Failed to initialize cURL',
                'http_code' => null,
                'url' => $url
            ];
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        $this->logging(
            __METHOD__,
            $url,
            $options,
            [
                'http_code' => $httpCode,
                'response' => $response,
                'error' => $error,
            ],
            static::LOG_FILENAME
        );

        if ($httpCode === 200 && $response !== false) {
            $decoded = json_decode($response, true);
            if ($decoded) {
                return $decoded;
            }
            return [
                'error' => true,
                'message' => 'Failed to decode JSON response',
                'http_code' => $httpCode,
                'raw_response' => $response,
                'url' => $url
            ];
        }

        return [
            'error' => true,
            'message' => $error ?: 'HTTP request failed',
            'http_code' => $httpCode,
            'response' => $response,
            'url' => $url
        ];
    }

    /**
     * Инициализирует CURL с общими параметрами
     * 
     * @param string $url
     * @param array $options
     * @return resource|false
     */
    private function initCurl(string $url, array $options)
    {
        $ch = curl_init($url);
        
        if ($ch === false) {
            return false;
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders($options['headers'] ?? []));

        if (!empty($options['method']) && strtolower($options['method']) === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body'] ?? '');
        }

        return $ch;
    }

    /**
     * Форматирует заголовки для CURL
     *
     * @param array $headers
     * @return string[]
     */
    private function formatHeaders(array $headers): array
    {
        return array_map(
            static fn($key, $value) => "$key: $value",
            array_keys($headers),
            $headers
        );
    }
}
