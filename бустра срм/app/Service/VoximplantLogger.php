<?php

namespace App\Service;

/**
 * Централизованный логгер для всех операций с Voximplant API
 * 
 * Обеспечивает структурированное логирование всех запросов, ответов и ошибок
 * с контекстом для каждого типа операции
 */
class VoximplantLogger
{
    /**
     * Логирование запроса к Voximplant API
     * 
     * @param string $service Название сервиса/канала логирования
     * @param string $method Название метода
     * @param array $requestData Данные запроса
     * @param array $context Дополнительный контекст (organization_id, manager_id, campaign_id и т.д.)
     * @return void
     */
    public function logRequest(string $service, string $method, array $requestData, array $context = []): void
    {
        $logData = [
            'method' => $method,
            'request' => $requestData,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        logger($service)->info("Request: {$method}", $logData);
    }

    /**
     * Логирование ответа от Voximplant API
     * 
     * @param string $service Название сервиса/канала логирования
     * @param string $method Название метода
     * @param array|string $responseData Данные ответа
     * @param float $duration Время выполнения в секундах
     * @param array $context Дополнительный контекст
     * @return void
     */
    public function logResponse(string $service, string $method, $responseData, float $duration, array $context = []): void
    {
        // Преобразуем строку ответа в массив если нужно
        if (is_string($responseData)) {
            $decoded = json_decode($responseData, true);
            $responseData = $decoded !== null ? $decoded : ['raw_response' => $responseData];
        }

        $logData = [
            'method' => $method,
            'response' => $responseData,
            'duration_ms' => round($duration * 1000, 2),
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'success' => $this->isSuccessResponse($responseData),
        ];

        $level = $logData['success'] ? 'info' : 'warning';
        logger($service)->{$level}("Response: {$method}", $logData);
    }

    /**
     * Логирование успешной операции
     * 
     * @param string $service Название сервиса/канала логирования
     * @param string $method Название метода
     * @param array $data Данные операции
     * @param float $duration Время выполнения в секундах
     * @param array $context Дополнительный контекст
     * @return void
     */
    public function logSuccess(string $service, string $method, array $data, float $duration, array $context = []): void
    {
        $logData = [
            'method' => $method,
            'data' => $data,
            'duration_ms' => round($duration * 1000, 2),
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'success' => true,
        ];

        logger($service)->info("Success: {$method}", $logData);
    }

    /**
     * Логирование ошибки
     * 
     * @param string $service Название сервиса/канала логирования
     * @param string $method Название метода
     * @param \Throwable $error Исключение
     * @param array $context Дополнительный контекст
     * @return void
     */
    public function logError(string $service, string $method, \Throwable $error, array $context = []): void
    {
        $logData = [
            'method' => $method,
            'error' => $error->getMessage(),
            'exception' => get_class($error),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'success' => false,
        ];

        logger($service)->error("Error: {$method}", $logData);
    }

    /**
     * Определяет, является ли ответ успешным
     * 
     * @param array|string $response Данные ответа
     * @return bool
     */
    private function isSuccessResponse($response): bool
    {
        if (is_string($response)) {
            $decoded = json_decode($response, true);
            if ($decoded === null) {
                return false;
            }
            $response = $decoded;
        }

        if (!is_array($response)) {
            return false;
        }

        // Проверяем различные форматы успешных ответов Voximplant API
        return isset($response['result']) 
            || isset($response['success']) 
            || (isset($response['error']) && empty($response['error']))
            || (isset($response['error_id']) && $response['error_id'] === null);
    }
}


