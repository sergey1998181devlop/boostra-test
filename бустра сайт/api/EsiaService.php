<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once 'Simpla.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

class EsiaService extends Simpla
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $baseUrl;
    private $state;
    private $nonce;
    private $token;
    private Client $httpClient;

    /**
     * Тип входа ПК
     */
    public const ESIA_AUTH_OLD_USER_TYPE = 'esia_old_user';

    /**
     * Тригер, если пользователь есть в 1С, но не был в нашей базе
     */
    public const ESIA_AUTH_FROM_1C = 'esia_from_1c';

    public function __construct()
    {
        parent::__construct();
        $this->initLogs();

        $this->clientId = $this->config->esia['clientId'];
        $this->clientSecret = $this->config->esia['clientSecret'];
        $this->redirectUri = $this->config->root_url . '/esia/auth';
        $this->baseUrl = $this->config->esia['baseUrl'];
        $this->state = $_COOKIE['esia_id_state'] ?? $this->setState();
        $this->nonce = $this->generateUuid();

        $this->initHttpClient();
    }

    public function generateUuid()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * @return void
     */
    private function initLogs()
    {
        error_reporting(E_ERROR | E_NOTICE);
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('error_log', $this->config->root_dir . 'logs/esia_api_error.log');
    }

    /**
     * @return void
     */
    private function initHttpClient()
    {
        // Настройка логгера
        $logger = new Logger('guzzle');
        $logger->pushHandler(new StreamHandler($this->config->root_dir . '/logs/esia_logs.txt', Logger::INFO));

        // Создание стека обработчиков
        $stack = HandlerStack::create();
        $stack->push(Middleware::log($logger, new MessageFormatter('Request: {request} - Response: {response}')));

        $this->httpClient = new Client(
            [
                'base_uri' => $this->baseUrl,
                'handler' => $stack,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]
        );
    }

    /**
     * Получает токен
     * @param string $code
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get_token_action(string $code)
    {
        $response = $this->httpClient->post('auth/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Обработка кнопки
     *
     * https://www.garant.ru/products/ipo/prime/doc/71540608/?ysclid=mc3aaka2m6412624548 Описание scope
     *
     * https://digital.gov.ru/documents/sczenarii-ispolzovaniya-infrastruktury-czifrovogo-profilya-fizicheskogo-licza официальная документация
     *
     * - fullname — просмотр фамилии, имени и отчества;
     * - birthdate — просмотр даты рождения;
     * - gender — просмотр пола;
     * - snils — просмотр СНИЛС;
     * - inn — просмотр ИНН;
     * - id_doc — просмотр данных о документе, удостоверяющем личность;
     * - birthplace — просмотр места рождения;
     * - medical_doc — просмотр данных полиса обязательного медицинского страхования (ОМС);
     * - military_doc — просмотр данных военного билета;
     * - foreign_passport_doc — просмотр данных заграничного паспорта;
     * - drivers_licence_doc — просмотр данных водительского удостоверения;
     * - birth_cert_doc — просмотр данных свидетельства о рождении;
     * - residence_doc — просмотр данных вида на жительство;
     * - temporary_residence_doc — просмотр данных разрешения на временное проживание;
     * - vehicles — просмотр данных транспортных средств;
     * - email — просмотр адреса электронной почты;
     * - mobile — просмотр номера мобильного телефона;
     * - addresses — просмотр данных об адресах.
     *
     * @return string
     */
    public function get_auth_url_action(): string
    {
        $permissions = 'fullname electronic_workbook addresses id_doc birthdate birthplace email gender history_passport_doc inn mobile snils';

        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'provider' => 'cpg_oauth',
            'scope' => 'openid',
            'permissions' => $permissions,
            'redirect_uri' => $this->redirectUri,
            'state' => $this->state,
            'purposes' => 'CREDIT REG_QUESTIONNAIRE UPD_CUSTOMER_INF AGGREGATOR_CREDIT FINANCIAL_NONFIN_SERVICES',
            'actions' => 'ALL_ACTIONS_TO_DATA',
            'sysname' => 'CREDIT',
            'expire' => 5,
            'nonce' => $this->nonce,
        ];

        return $this->baseUrl . '/auth/authorize?' . http_build_query($params);
    }

    /**
     * Сохраним уникальный state в сессию для дальнейшей проверки
     * @return string
     */
    public function setState(): string
    {
        $token = $this->generateUuid();
        setcookie('esia_id_state', $token, time() + 3600, '/', $this->config->main_domain);
        return $token;
    }

    /**
     * Валидирует state
     *
     * @param string $receivedState
     * @return bool
     */
    public function validateState(string $receivedState): bool
    {
        return $_COOKIE['esia_id_state'] === $receivedState;
    }

    /**
     * Информация о пользователе
     * @return mixed
     * @throws GuzzleException
     */
    public function getUserInfo()
    {
        $response = $this->httpClient->get('auth/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @return void
     */
    public function setCookie()
    {
        $amount = $this->request->get('amount') ?: 30000;
        $period = $this->request->get('period') ?: 16;

        setcookie("amount", $amount, time() + 3600);
        setcookie("period", $period, time() + 3600);
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
}
