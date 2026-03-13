<?php

use App\Core\Application\Application;
use Psr\Log\LoggerInterface;

if (!function_exists('logger')) {
    /**
     * Получить основной логгер приложения
     *
     * @param string|null $channel
     * @return LoggerInterface
     */
    function logger(string $channel = null): LoggerInterface
    {
        $app = Application::getInstance();
        
        if ($channel) {
            return $app->make("logger.{$channel}");
        }
        
        return $app->make(LoggerInterface::class);
    }
}

if (!function_exists('log_emergency')) {
    /**
     * Логирование критических ошибок системы
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function log_emergency(string $message, array $context = []): void
    {
        logger()->emergency($message, $context);
    }
}

if (!function_exists('log_alert')) {
    /**
     * Логирование предупреждений требующих немедленного внимания
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function log_alert(string $message, array $context = []): void
    {
        logger()->alert($message, $context);
    }
}

if (!function_exists('log_critical')) {
    /**
     * Логирование критических ошибок
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function log_critical(string $message, array $context = []): void
    {
        logger()->critical($message, $context);
    }
}

if (!function_exists('log_error')) {
    /**
     * Логирование ошибок
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function log_error(string $message, array $context = []): void
    {
        logger()->error($message, $context);
    }
}

if (!function_exists('log_warning')) {
    /**
     * Логирование предупреждений
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function log_warning(string $message, array $context = []): void
    {
        logger()->warning($message, $context);
    }
}

if (!function_exists('log_notice')) {
    /**
     * Логирование уведомлений
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function log_notice(string $message, array $context = []): void
    {
        logger()->notice($message, $context);
    }
}

if (!function_exists('log_info')) {
    /**
     * Логирование информационных сообщений
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function log_info(string $message, array $context = []): void
    {
        logger()->info($message, $context);
    }
}

if (!function_exists('log_debug')) {
    /**
     * Логирование отладочных сообщений
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function log_debug(string $message, array $context = []): void
    {
        logger()->debug($message, $context);
    }
}

if (!function_exists('log_exception')) {
    /**
     * Логирование исключений с полным контекстом
     *
     * @param \Throwable $exception
     * @param string|null $message
     * @param array $context
     * @return void
     */
    function log_exception(\Throwable $exception, string $message = null, array $context = []): void
    {
        $context = array_merge($context, [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $logMessage = $message ?: $exception->getMessage();
        logger()->error($logMessage, $context);
    }
}

if (!function_exists('log_api_request')) {
    /**
     * Логирование API запросов
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @param int $statusCode
     * @param float $duration
     * @return void
     */
    function log_api_request(string $method, string $url, array $data = [], int $statusCode = 200, float $duration = 0): void
    {
        $context = [
            'method' => $method,
            'url' => $url,
            'status_code' => $statusCode,
            'duration_ms' => round($duration * 1000, 2),
            'data' => $data,
        ];

        logger('api')->info('API Request', $context);
    }
}

if (!function_exists('log_sql_query')) {
    /**
     * Логирование SQL запросов
     *
     * @param string $query
     * @param array $bindings
     * @param float $duration
     * @return void
     */
    function log_sql_query(string $query, array $bindings = [], float $duration = 0): void
    {
        $context = [
            'query' => $query,
            'bindings' => $bindings,
            'duration_ms' => round($duration * 1000, 2),
        ];

        logger('sql')->debug('SQL Query', $context);
    }
}
