<?php

require_once(__DIR__ . '/../Simpla.php');

/**
 * Класс для работы с API Финансового доктора
 */
class FinancialDoctorApi extends Simpla
{
    /**
     * Ключ для работы с API (продакшен)
     */
    const API_KEY = 'kxmyKRLRj9dfRNmRxSZgDvPr';

    /**
     * Ключ для работы с API (тестовый)
     */
    const API_KEY_TEST = '9frX3ZkLrYkJSWDTUwZinvWU';

    /**
     * Основной и резервные домены для API
     */
    private static $domains = [
        'https://api.finansdoctor.ru',
        'https://api.stageback.ru',
    ];

    /**
     * URL для авторизации на сайте с готовым ключом
     */
    const LOGIN_URL = 'https://finansdoctor.ru/login?license=%s';

    /**
     * Выбор ключа в зависимости от окружения
     * @return string
     */
    private static function getApiKey(): string
    {
        $simpla = new Simpla();
        $frontUrl = rtrim($simpla->config->front_url, '/');

        if ($frontUrl === 'https://boostra.ru') {
            return self::API_KEY;
        }

        return self::API_KEY_TEST;
    }

    /**
     * Выполнение API-запроса с fallback на резервный домен
     *
     * @param string $endpoint
     * @param array $options
     * @return array|false
     */
    private static function apiRequestWithFallback(string $endpoint, array $options = [])
    {
        $apiKey = self::getApiKey();
        $options['headers']['apikey'] = $apiKey;

        foreach (self::$domains as $domain) {
            $url = $domain . $endpoint;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, self::formatHeaders($options['headers'] ?? []));

            if (!empty($options['method']) && strtolower($options['method']) === 'post') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body'] ?? '');
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            $simpla = new Simpla();
            $simpla->logging(
                __METHOD__,
                $url,
                $options,
                [
                    'http_code' => $httpCode,
                    'response' => $response,
                    'error' => $error,
                ],
                'fetchGeneratedKey.txt'
            );

            if ($httpCode === 200 && $response !== false) {
                return json_decode($response, true);
            }
        }

        return false;
    }

    /**
     * Форматирует заголовки для CURL
     *
     * @param array $headers
     * @return array
     */
    private static function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = "$key: $value";
        }
        return $formatted;
    }

    /**
     * Генерация ключа и получение всех данных лицензии
     * @param array $data
     * @return array|false
     */
    public static function makeKey(array $data)
    {
        $response = self::apiRequestWithFallback(
            '/license/makeKey',
            [
                'method' => 'POST',
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($data),
            ]
        );

        if ($response && $response['ok'] && isset($response['result'])) {
            return $response['result'];
        }

        return false;
    }

}

