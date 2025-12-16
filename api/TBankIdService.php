<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use GuzzleHttp\Psr7\Request;

require_once 'Simpla.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

class TBankIdService extends Simpla
{
    private $url_auth_token;
    private $url_userinfo;
    private $url_inn;
    private $url_documents_passport;
    private $url_address;
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $token;
    private $auth_url;
    private $state = null;
    private Client $httpClient;

    /**
     * Храним id запросов, для логов
     * @var array
     */
    private $XRequestIds = [];

    /**
     * Идентификатор для логов, который связывает несколько request_id
     * @var null
     */
    private $session_uid = null;

    public function __construct()
    {
        parent::__construct();

        $this->initLogs();
        $this->initHttpClient();

        $this->url_auth_token = 'https://id.tbank.ru/auth/token';
        $this->url_userinfo = 'https://id.tbank.ru/userinfo/userinfo';
        $this->url_inn = 'https://business.tbank.ru/openapi/api/v1/individual/documents/inn';
        $this->url_documents_passport = 'https://business.tbank.ru/openapi/api/v1/individual/documents/passport';
        $this->url_address = 'https://business.tbank.ru/openapi/api/v1/individual/addresses';
        $this->auth_url = 'https://id.tbank.ru/auth/authorize';

        $this->client_id = $this->config->TBankId['clientId'];
        $this->client_secret = $this->config->TBankId['clientSecret'];
        $this->redirect_uri = $this->config->root_url . '/t-bank-id/auth';

        $this->session_uid = uniqid();
    }

    /**
     * @return void
     */
    private function initLogs()
    {
        error_reporting(E_ERROR | E_NOTICE);
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('error_log', $this->config->root_dir . 'logs/t_id_api_error.log');
    }

    /**
     * Генерация UID state
     * @return string
     */
    private function generateUuid(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Сохраним уникальный state в сессию для дальнейшей проверки
     * @return string
     */
    public function setState(): string
    {
        $token = $this->generateUuid();
        $this->state = $token;
        setcookie('t_id_state', $token, time() + 3600, '/', $this->config->main_domain);
        return $token;
    }

    /**
     * Получает активное значение state, если его нет генерирует новое
     *
     * @return string
     */
    public function getState(): string
    {
        return $this->state ?: $_COOKIE['t_id_state'] ?? $this->setState();
    }

    /**
     * Валидирует state
     *
     * @param string $receivedState
     * @return bool
     */
    public function validateState(string $receivedState): bool
    {
        return $_COOKIE['t_id_state'] === $receivedState;
    }

    /**
     * @return void
     */
    private function initHttpClient()
    {
        // Настройка логгера
        $logger = new Logger('guzzle');
        $logger->pushHandler(new StreamHandler($this->config->root_dir . '/logs/t_bank_id_logs.txt', Logger::INFO));

        // Создание стека обработчиков
        $stack = HandlerStack::create();
        $stack->push(Middleware::log($logger, new MessageFormatter('Request: {request} - Response: {response}')));

        // Настройка повторений при сбросе
        $stack->push(Middleware::retry(
            function ($retries, $request, $response, $exception) {
                // Повторяем только при таймауте (max 3 попытки)
                return $exception instanceof \GuzzleHttp\Exception\ConnectException && $retries < 3;
            },
            function ($retries) {
                return 1000 * $retries; // Задержка между попытками (1s, 2s, 3s)
            }
        ));

        // Установка задержек
        $stack->push(Middleware::mapRequest(function (Request $request) {
            $delay = 1000;         // Милисекунды
            usleep($delay * 1000); // микросекунды (1 000 000 = 1 сек)
            return $request;
        }));

        $this->httpClient = new Client(
            [
                'handler' => $stack,
                'timeout' => 15, // 10 минут (в секундах)
                'connect_timeout' => 10, // таймаут соединения
            ]
        );
    }


    /**
     * Получает токен пользователя для запросов в АПИ
     * @param $code
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken($code)
    {
        $response = $this->httpClient->post($this->url_auth_token, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->client_secret),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirect_uri,
            ],
        ]);

        // Декодируем JSON-ответ (если API возвращает JSON)
        return json_decode($response->getBody(), true); // $token['access_token']
    }

    /**
     * Устанавливает токен для пользователя
     * @param string $token
     * @return void
     */
    public function setUserToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @return void
     */
    public function setCookie()
    {
        $amount = $this->request->get('amount');
        $period = $this->request->get('period');

        setcookie("amount", $amount, time() + 3600);
        setcookie("period", $period, time() + 3600);
    }

    /**
     * Получает ФИО, телефон пользователя
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMainData()
    {

        $response = $this->httpClient->post($this->url_userinfo, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
            ],
        ]);

        $this->setRequestId('main', $response->getHeaderLine('x-request-id'));

        return json_decode($response->getBody(), true); // $response['access_token']
    }

    /**
     * Получает ИНН пользователя
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getInn()
    {
        $response = $this->httpClient->get($this->url_inn, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true); // $response['inn']
    }

    /**
     * Получаем адрес пользователя
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAddresses()
    {
        $response = $this->httpClient->get($this->url_address, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->setRequestId('registration_address', $response->getHeaderLine('x-request-id'));

        return json_decode($response->getBody(), true);
    }

    /**
     * Паспортные данные
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPassportData()
    {
        $response = $this->httpClient->get($this->url_documents_passport, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->setRequestId('passport', $response->getHeaderLine('x-request-id'));

        return json_decode($response->getBody(), true);
    }

    /**
     * Получает ссылку для кнопки сайта, ссылка нужна чтобы можно было кастомизировать кнопку без виджета
     * Это первый этап для пользователя, клик по кнопке и переход по этой ссылке на страницу Т Банка
     *
     * @return string
     */
    public function getAuthUrl(): string
    {
        $params = http_build_query(
            [
                'client_id' => $this->client_id,
                'redirect_uri' => $this->redirect_uri,
                'response_type' => 'code',
                'state' => $this->getState(),
            ]
        );

        return $this->auth_url . '?' . $params;
    }

    /**
     * Устанавливаем id запроса
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    private function setRequestId(string $key, string $value)
    {
        $this->XRequestIds[$key] = $value;
    }

    /**
     * Получаем id запроса
     *
     * @param string $key
     * @return mixed
     */
    public function getRequestId(string $key)
    {
        return $this->XRequestIds[$key];
    }

    /**
     * @return string|null
     */
    public function getSessionId(): ?string
    {
        return $this->session_uid;
    }
}
