<?php

namespace RestApi;

use api\helpers\JWTHelper;
use RestApi\Interfaces\PartnerServiceInterface;
use RestApi\Services\DefaultService;
use Simpla;
use Throwable;

require dirname(__DIR__) . '/api/Simpla.php';

/**
 * Интеграция партнеров
 */
final class PartnerApi extends Simpla
{
    /**
     * Список экшенов
     */
    public const ACTION_LIST = [
        'token' => 'getToken',
        'check-double' => 'checkDouble',
        'application-for-decisions' => 'applicationForDecisions',
        'check-decisions' => 'checkDecisions',
    ];

    /**
     * Возвращаемые данные JSON
     * @var array
     */
    private array $response = [];

    /**
     * Идентификатор запроса
     * @var string
     */
    public static string $request_uid = '';

    /**
     * Массив ошибок
     * @var array
     */
    private array $errors = [];

    /**
     * Наш сервис, в зависимости от партнера
     *
     * @var PartnerServiceInterface
     */
    private PartnerServiceInterface $service;

    /**
     * Тег для логов
     */
    private const LOG_TAG = 'rest_partner_api_request';

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        self::$request_uid = uniqid();
        $this->initLogs();
        $this->initService();
    }

    /**
     * @return void
     */
    private function initLogs()
    {
        error_reporting(E_ERROR | E_WARNING | E_NOTICE);
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('error_log', $this->config->root_dir . 'logs/partner_api_error.log');

        // 1. Для перехвата исключений (Exceptions)
        set_exception_handler(function(Throwable $exception) {
            //error_log("Uncaught Exception: " . $exception->getMessage(), 3, $this->config->root_dir . 'logs/partner_api_error.log');

            $this->open_search_logger->create('Uncaught Exception ping3', [
                'error' => [
                    'type' => 'exception',
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ],
                'request_data' => [
                    'json' => $this->request->getAllJsonInput(),
                    'post' => $_POST,
                    'get' => $_GET,
                ],
                'request_uid' => self::$request_uid,
            ],'ping3', \OpenSearchLogger::LOG_LEVEL_ERROR, 'ping3');

            $this->addError('Server internal error');
            $this->responseError();
        });

        // 2. Для перехвата фатальных ошибок (Fatal Errors)
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                //error_log("Fatal Error: " . $error['message'], 3, $this->config->root_dir . 'logs/partner_api_error.log');

                $this->open_search_logger->create('Fatal Error ping3', [
                    'error' => [
                        'type' => 'fatal_error',
                        'message' => $error['message'],
                        'file' => $error['file'],
                        'line' => $error['line'],
                        'error_type' => $error['type'],
                    ],
                    'request_data' => [
                        'json' => $this->request->getAllJsonInput(),
                        'post' => $_POST,
                        'get' => $_GET,
                    ],
                    'request_uid' => self::$request_uid,
                ],'ping3', \OpenSearchLogger::LOG_LEVEL_ERROR, 'ping3');

                $this->addError('Server internal error');
                $this->responseError();
            }
        });
    }

    /**
     * Основная точка входа
     * @return void
     * @throws \Exception
     */
    public function init()
    {
        $action = $this->request->get('action', 'string');

        $this->addLogRequest();

        // Проверяем, существует ли действие в списке доступных
        if (!array_key_exists($action, self::ACTION_LIST)) {
            $this->addError('Action not found');
            $this->responseError(404);
        }

        // Получаем имя метода по ключу
        $methodName = self::ACTION_LIST[$action];

        // Проверяем, существует ли метод в текущем классе
        if (!method_exists($this, $methodName)) {
            $this->addError('Method not found');
            $this->responseError(404);
        }

        // Если метод защищенный, проверим токен
        if ($methodName !== 'getToken') {
            $this->validateToken();
        }

        // Вызываем соответствующий метод
        /**
         * @uses self::checkDouble()
         * @uses self::getToken()
         * @uses self::applicationForDecisions()
         * @uses self::checkDecisions()
         */
        $this->$methodName();

        $this->responseSuccess();
    }

    /**
     * Получаем сервис по партнеру
     *
     * Ид партнера передается в GET параметре
     * $partner = $this->request->get('partner', 'string')
     *
     * @return void
     * @throws \Exception
     */
    private function initService(): void
    {
        $this->service = new DefaultService();
    }

    /**
     * Возвращает ошибки
     * @param int $code
     * @return void
     */
    private function responseError(int $code = 500)
    {
        $data = [
            'data' => [
                'errors' => $this->errors,
            ],
        ];

        $this->open_search_logger->create('Ответ по запросу с ошибкой', [
            'response' => $data,
            'code' => $code,
            'request_uid' => self::$request_uid,
        ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

        http_response_code($code);
        $this->request->json_output($data);
    }

    /**
     * Добавляет ошибку в массив
     * @param string $message
     * @param string $field
     * @param int|null $code
     * @return void
     */
    private function addError(string $message, string $field = '', ?int $code = null)
    {
        $this->errors[] = [
            'message' => $message,
            'code' => $code,
            'field' => $field,
        ];
    }

    /**
     * Возвращает стандартизированный ответ
     * @return void
     */
    private function responseSuccess()
    {
        $data = [
            'data' => $this->response,
        ];

        $this->open_search_logger->create('Ответ по запросу без ошибки', [
            'response' => $data,
            'request_uid' => self::$request_uid,
        ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

        $this->request->json_output($data);
    }

    /**
     * Проверка токена
     *
     * @return void
     */
    private function validateToken(): void
    {
        if (!$this->validateBearerToken()) {
            $this->addError('Unauthorized', 'Token is invalid', 401);
            $this->responseError(401);
        }
    }

    /**
     * Валидируем токен
     * @return bool
     */
    private function validateBearerToken(): bool
    {
        $token = $this->getBearerToken();
        $secret_key = $this->config->jwt['partner_key'];

        $token_data = JWTHelper::decodeToken($token, $secret_key);
        return !empty($token_data);
    }

    /**
     * Получаем токен из заголовка
     * @return string
     */
    private function getBearerToken(): string
    {
        $headers = getallheaders();

        // Проверяем заголовок Authorization
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];

            // Проверяем, что это Bearer token
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return trim($matches[1]);
            }
        }

        return '';
    }

    /**
     * Генерирует токен доступа
     * @return void
     */
    public function getToken() : void
    {
        $token = $this->service->getToken();

        if ($token) {
            $this->response = $token;
        } else {
            $this->addError('Unauthorized', 'Password is invalid', 401);
            $this->responseError(401);
        }
    }

    /**
     * Отправить запрос на проверку повтоного клиента, а также проверки черных списков и проверки клиента на готовность выдачи займа
     * @return void
     */
    public function checkDouble(): void
    {
        $this->response = $this->service->checkDouble();
    }

    /**
     * Отправить анкету клиента для получения решения по займуслушай у
     * @return void
     */
    public function applicationForDecisions(): void
    {
        $this->response = $this->service->applicationForDecisions();
    }

    /**
     * Отправка запроса для получения решения по займу
     * @return void
     */
    public function checkDecisions(): void
    {
        $this->response = $this->service->checkDecisions();
    }

    private function addLogRequest(): void
    {
        $action = $this->request->get('action', 'string');

        $this->open_search_logger->create('Инициализация запроса', [
            'json' => $this->request->getAllJsonInput(),
            'post' => $_POST,
            'get' => $_GET,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'request_uid' => self::$request_uid,
        ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');


        $logData = [
            'action' => $action,
            'request_uid' => self::$request_uid,
            'partner' => $this->service->getPartner(),
        ];

        // Ищем в запросе номер телефона
        if ($action === 'check-double') {
            $phone_mobile = $this->users->clear_phone($this->request->getJsonInput('phone'));
            if (!empty($phone_mobile)) {
                $logData['phone_mobile'] = $phone_mobile;
            }
        }

        if ($action === 'application-for-decisions') {
            $json = $this->request->getAllJsonInput();
            $phone_mobile = $this->users->clear_phone($json['contact']['phone']);
            if (!empty($phone_mobile)) {
                $logData['phone_mobile'] = $phone_mobile;
            }
        }

        // Ищем в запросе номер заявки
        if ($action === 'check-decisions') {
            $order_id = $this->request->get('id', 'integer');
            if (!empty($order_id)) {
                $logData['order_id'] = $order_id;
            }
        }

        $this->rest_api_partner->addLog($logData);
    }
}

(new PartnerApi())->init();
