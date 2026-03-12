<?php

require_once 'Simpla.php';

/**
 * Класс обертка для логирование фатальных ошибок в OpenSearch
 */
class ExceptionHandler extends Simpla
{
    // Отправлено ли исключение в логер
    private bool $exceptionHandled = false;

    /**
     * @var ErrorException
     */
    private $exception;

    /**
     * Id ошибки
     * @var string
     */
    private string $error_uuid = '';

    /**
     * Инициализируем фатальные ошибки
     * @return void
     */
    public function init()
    {
        // 1. Для перехвата исключений (Exceptions)
        set_exception_handler(function(Throwable $exception) {
            // Устанавливаем флаг, что исключение уже обработано
            $this->exceptionHandled = true;
            $this->error_uuid = uniqid();

            $this->open_search_logger->create($exception->getMessage(), [
                'error' => [
                    'type' => $this->getErrorType($exception->getMessage()),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ],
                'request_data' => $this->getRequestData(),
                'error_uuid' => $this->error_uuid,
            ],'default_exception', \OpenSearchLogger::LOG_LEVEL_ERROR, 'errors');

            $this->exception = $exception;

            $this->responseError();
        });

        // 2. Для перехвата фатальных ошибок (Fatal Errors)
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                // Проверяем, не было ли уже обработано исключение
                if (!$this->exceptionHandled) {
                    $this->error_uuid = uniqid();
                    $this->open_search_logger->create($error['message'], [
                        'error' => [
                            'type' => $this->getErrorType($error['message']),
                            'file' => $error['file'],
                            'line' => $error['line'],
                            'error_type' => $error['type'],
                        ],
                        'request_data' => $this->getRequestData(),
                        'error_uuid' => $this->error_uuid,
                    ],'default_php_error', \OpenSearchLogger::LOG_LEVEL_ERROR, 'errors');

                    // Преобразуем массив ошибки в исключение
                    $exception = new ErrorException(
                        $error['message'] ?? 'Unknow error',
                        0, // код
                        $error['type'], // severity
                        $error['file'] ?? null,
                        $error['line'] ?? null,
                    );

                    $this->exception = $exception;

                    $this->responseError();
                }
            }
        });
    }

    /**
     * Возвращает тип ошибки в дальнейшем расширяем
     * @param string $message
     * @return string
     */
    private function getErrorType(string $message): string
    {
        if (str_contains($message, 'Soap')) {
            return '1c';
        } else {
            return 'default_exception';
        }
    }

    /**
     * Возвращает данные для запроса
     * @return array
     */
    private function getRequestData(): array
    {
        // Если консольная команда данные о запросе не получаем
        if (str_contains(strtolower(php_sapi_name()), 'cli')) {
            return [];
        }

        return [
            'json' => $this->request->getAllJsonInput(),
            'post' => $_POST,
            'get' => $_GET,

            // Основные HTTP данные
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'full_url' => ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' .
                ($_SERVER['HTTP_HOST'] ?? '') .
                ($_SERVER['REQUEST_URI'] ?? ''),
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
            'query_string' => $_SERVER['QUERY_STRING'] ?? null,

            // Заголовки
            'headers' => getallheaders(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'referer' => $_SERVER['HTTP_REFERER'] ?? null,
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? null,

            // IP и сетевые данные
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            'real_ip' => $_SERVER['HTTP_X_REAL_IP'] ?? null,

            // Аутентификация
            'authorization_header' => !empty($_SERVER['HTTP_AUTHORIZATION']) ? '(present)' : null, // Не логируем сам токен!

            // Куки (можно ограничить по размеру или маскировать чувствительные)
            'cookies' => $this->sanitizeCookies($_COOKIE),

            // Файлы
            'files_count' => count($_FILES),
            'files_info' => array_map(function($file) {
                return [
                    'name' => $file['name'] ?? null,
                    'type' => $file['type'] ?? null,
                    'size' => $file['size'] ?? null,
                    'error' => $file['error'] ?? null,
                ];
            }, $_FILES),

            // Сессия (если используется)
            'session_id' => session_id() ?: null,
            'session_keys' => isset($_SESSION) ? array_keys($_SESSION) : [],

            // Сервер и окружение
            'server_name' => $_SERVER['SERVER_NAME'] ?? null,
            'server_port' => $_SERVER['SERVER_PORT'] ?? null,
            'https' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? null,

            // Время запроса
            'request_time' => $_SERVER['REQUEST_TIME_FLOAT'] ?? $_SERVER['REQUEST_TIME'] ?? null,
            'timestamp' => date('Y-m-d H:i:s'),

            // Пользовательские данные (если есть в системе)
            'user_id' => $this->getCurrentUserId(), // если в системе есть текущий пользователь
            'api_key' => $this->getApiKey(), // если используется API ключ

            // Дополнительные данные запроса
            'content_length' => $_SERVER['CONTENT_LENGTH'] ?? $_SERVER['HTTP_CONTENT_LENGTH'] ?? null,
            'accept' => $_SERVER['HTTP_ACCEPT'] ?? null,
            'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null,
            'accept_encoding' => $_SERVER['HTTP_ACCEPT_ENCODING'] ?? null,
        ];
    }

    /**
     * Берем куксы для лога
     * @param array $cookies
     * @return array
     */
    private function sanitizeCookies(array $cookies): array
    {
        $sensitiveKeys = ['session', 'token', 'auth', 'password', 'key', 'secret'];
        $sanitized = [];

        foreach ($cookies as $key => $value) {
            foreach ($sensitiveKeys as $sensitive) {
                if (stripos($key, $sensitive) !== false) {
                    $value = '***MASKED***';
                    break;
                }
            }
            $sanitized[$key] = is_string($value) ? substr($value, 0, 100) : $value;
        }

        return $sanitized;
    }

    /**
     * Берем авторизованного пользователя
     * @return null
     */
    private function getCurrentUserId()
    {
        // В зависимости от вашей системы
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * берем ключи апи
     * @return mixed|null
     */
    private function getApiKey()
    {
        // Получение API ключа, если используется
        return $_SERVER['HTTP_X_API_KEY'] ??
            $_GET['api_key'] ??
            $_POST['api_key'] ??
            null;
    }

    /**
     * Возвращает ошибки в HTTP Api
     * @return void
     */
    private function responseError()
    {
        if(strpos(strtolower(php_sapi_name()), 'cli') === false) {
            $r_headers = array_filter((array)getallheaders(), function($item) {
                return strtolower($item) == 'xmlhttprequest';
            });
            if(count($r_headers)) {
                http_response_code(500);
                $this->request->json_output(
                    [
                        'soap_fault' => true,
                        'error' => 'Сервер перегружен!',
                        'error_uuid' => $this->error_uuid,
                    ]
                );
            } else {
                $view = new IndexView();
                if ($view->is_developer || $this->config->show_errors) {
                    $this->design->assign('message', $this->exception->getMessage());
                }

                $_GET['page_url'] = '404';
                $_GET['module'] = 'ExceptionView';
                print $view->fetch();
                exit(1);
            }
        }
    }
}

(new ExceptionHandler())->init();

