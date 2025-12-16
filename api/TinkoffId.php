<?php
// api/TinkoffId.php

use lib\TinkoffId\TinkoffLib;

class TinkoffId extends Simpla
{
    private $tinkoffLib;

    public function __construct()
    {
        parent::__construct();

        $tinkoffConfig = $this->getTinkoffConfig();
        $this->tinkoffLib = new TinkoffLib(
            $tinkoffConfig['client_id'],
            $tinkoffConfig['client_secret'],
            $tinkoffConfig['redirect_uri']
        );
    }

    /**
     * Возвращает конфигурацию для Tinkoff ID
     *
     * @return array Конфигурация Tinkoff ID
     */
    public function getTinkoffConfig(): array
    {
        // return [
        //     'client_id' => getenv('TINKOFF_CLIENT_ID'),
        //     'client_secret' => getenv('TINKOFF_CLIENT_SECRET'),
        //     'redirect_uri' => $this->config->root_url.'/auth/complete',
        // ];
        return [
            'client_id' => 'tid_aquarius-di',
            'client_secret' => 'GFPrGExHm1fFXngRQjx05rv8hLQVpc',
            'redirect_uri' => $this->config->root_url.'/auth/complete',
        ];
    }

    /**
     * Обрабатывает запрос на авторизацию через Tinkoff ID
     * @throws Exception
     */
    public function handleAuth()
    {
        $state = $this->tinkoffLib->stateGenerate();
        $_SESSION['state'] = $state;
        $authUrl = $this->tinkoffLib->authUrlGenerate($state);

        if (empty($authUrl)) {
            throw new Exception('URL для авторизации не сгенерирован!');
        }
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Обрабатывает запрос на завершение авторизации через Tinkoff ID
     * @throws Exception
     */
    public function handleAuthComplete(): array
    {
        try {
            // Получение и проверка параметров code и state
            //$code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);
            //$state = filter_input(INPUT_GET, 'state', FILTER_SANITIZE_STRING);

            $code  = $this->request->get('code') ?? '';
            $state = $this->request->get('state') ?? '';

            if (!$code || !$state) {
                throw new Exception('Некорректные параметры для авторизации. Код или состояние отсутствуют.');
            }

            if (!isset($_SESSION['state']) || $state !== $_SESSION['state']) {
                throw new Exception('Неверное состояние авторизации. Состояние сессии не совпадает с полученным состоянием.');
            }

            $tokenResponse = $this->tinkoffLib->requestToken($code);

            if (!isset($tokenResponse['access_token'])) {
                $this->logging('ERROR', 'Access token missing', __FILE__, [
                    'tokenResponse' => $tokenResponse,
                    'code' => $code,
                ], 'tinkoffid.txt');

                throw new Exception('Не удалось получить токен доступа. Пожалуйста, повторите попытку позже.');
            }

            $accessToken = $tokenResponse['access_token'];
            $userInfo = $this->requestUserInfoAll($accessToken);

            if (empty($userInfo)) {
                $this->redirectLogin();
                //throw new Exception('Не удалось получить информацию о пользователе.');
            }

            //$_SESSION['user_info'] = $userInfo;

            // Удаление кода авторизации и состояния после успешного получения токена и информации о пользователе
            unset($_SESSION['state']);
            //unset($_SESSION['code']);

            return $userInfo;
        } catch (Exception $e) {
            $this->logging('ERROR', 'Auth complete error', $e->getFile(), [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'code' => $code ?? 'N/A',
                'state' => $state ?? 'N/A',
                'session_state' => $_SESSION['state'] ?? 'N/A',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            ], 'tinkoffid.txt');

            throw $e;
        }
    }

//    /**
//     * @throws Exception
//     */
//    public function requestUserInfo(string $accessToken): array
//    {
//        try {
//            $userInfo = $this->tinkoffLib->requestUserInfo($accessToken);
//            if (isset($userInfo['error'])) {
//                throw new Exception("Ошибка получения информации о пользователе: {$userInfo['error']}");
//            }
//            if (empty($userInfo)) {
//                throw new Exception('Отсутствует информация о пользователе.');
//            }
//
//            $passportData = $this->tinkoffLib->requestPassportData($accessToken);
//            if (isset($passportData['error'])) {
//                throw new Exception("Ошибка получения информации о паспорте пользователя: {$passportData['error']}");
//            }
//            if (empty($passportData)) {
//                throw new Exception('Отсутствует информация о паспорте пользователя.');
//            }
//            $userInfo['passport_data'] = $passportData;
//
//            $addresses = $this->tinkoffLib->requestAddresses($accessToken);
//            if (isset($addresses['error'])) {
//                throw new Exception("Ошибка получения информации об адресе пользователе: {$addresses['error']}");
//            }
//            if (empty($addresses)) {
//                throw new Exception('Отсутствует информация об адресе пользователе.');
//            }
//            $userInfo['addresses'] = $addresses['addresses'] ?? [];
//
//            return $userInfo;
//        } catch (Exception $e) {
//            $this->logging('ERROR', 'UserInfo request error', $e->getFile(), [
//                'error' => $e->getMessage(),
//                'line' => $e->getLine(),
//                'access_token' => $accessToken,
//            ], 'tinkoffid.txt');
//            throw $e;
//        }
//    }

    /**
     * @throws Exception
     */
    public function requestUserInfoAll(string $accessToken): array
    {
        try {
            $userInfoPromise = $this->tinkoffLib->requestUserInfoAsync($accessToken);
            $passportDataPromise = $this->tinkoffLib->requestPassportDataAsync($accessToken);
            $addressesPromise = $this->tinkoffLib->requestAddressesAsync($accessToken);

            $promises = [
                'userInfo' => $userInfoPromise,
                'passportData' => $passportDataPromise,
                'addresses' => $addressesPromise,
            ];

            // Ждём выполнения всех промисов
            $results = \GuzzleHttp\Promise\Utils::settle($promises)->wait();

            $userInfo = [];
            if ($results['userInfo']['state'] === 'fulfilled') {
                $userInfo = $results['userInfo']['value'];
            } else {
                $reason = $results['userInfo']['reason'];
                throw new Exception('Ошибка запроса userInfo: ' . $reason->getMessage());
            }

            if ($results['passportData']['state'] === 'fulfilled') {
                $userInfo['passport_data'] = $results['passportData']['value'];
            } else {
                $reason = $results['passportData']['reason'];
                throw new Exception('Ошибка запроса passportData: ' . $reason->getMessage());
            }

            if ($results['addresses']['state'] === 'fulfilled') {
                $userInfo['addresses'] = $results['addresses']['value']['addresses'] ?? [];
            } else {
                $reason = $results['addresses']['reason'];
                throw new Exception('Ошибка запроса addresses: ' . $reason->getMessage());
            }

            return $userInfo;

        } catch (Exception $e) {
            $this->logging('ERROR', 'UserInfo request error', __FILE__, [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'access_token' => $accessToken,
            ], 'tinkoffid.txt');
            throw $e;
        }
    }

    /**
     * Функция для перенаправления пользователя в зависимости от наличия параметров amount и period.
     */
    public function redirectLogin()
    {
        $url = '/user/login';
        $currentPage = $_SESSION['current_page'] ?? null;

        if (!empty($currentPage)) {
            parse_str($currentPage, $params);
            if (!empty($params['/init_user?amount']) && !empty($params['period'])) {
                $url = '/init_user?' . http_build_query([
                        'amount' => $params['/init_user?amount'],
                        'period' => $params['period'],
                    ]);
            }
        }

        header('Location: ' . $url);
        exit;
    }
}
