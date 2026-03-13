<?php
// ajax/tinkoff_id.php
ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');

//ini_set('display_errors', 'On');
//ini_set('display_startup_errors', 'On');
error_reporting(E_ALL);

chdir('..');
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once 'api/Simpla.php';
require_once 'api/Helpers.php';
require_once 'api/TinkoffId.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class TinkoffAjax extends Simpla
{
    private $tinkoffId;

    public function __construct()
    {
        parent::__construct();

        try {
            $this->tinkoffId = new TinkoffId();
            $this->logging('INFO', 'TinkoffId initialized successfully', '', null, 'tinkoff_ajax_log.txt');
        } catch (Exception $e) {
            $this->logging('ERROR', 'Error initializing TinkoffId', '', ['error' => $e->getMessage()], 'tinkoff_ajax_log.txt');
            $this->sendErrorResponse('Error initializing TinkoffId', 500);
            exit;
        }
    }

    /**
     * Анализирует запрошенное действие и вызывает соответствующий метод
     */
    public function analyzeActions()
    {
        $action = $this->request->get('action', 'auth');

        $this->logging('INFO', 'Analyzing action request', '', [
            'action' => $action,
            'get' => $_GET,
            'post' => $_POST
        ], 'tinkoff_ajax_log.txt');

        try {
            if ($action === 'auth') {
                $this->logging('INFO', 'Handling auth action', '', null, 'tinkoff_ajax_log.txt');
                $this->tinkoffId->handleAuth();
            } elseif ($action === 'complete') {
                $this->logging('INFO', 'Handling complete action', '', null, 'tinkoff_ajax_log.txt');
                $userInfo = $this->handleComplete();
                $userPhone = $_SESSION['user_info']['phone_number'] ?? '';
                if (empty($userPhone)) {
                    $this->logging('INFO', 'User not found or phone number missing. Redirecting to registration.', '', null, 'tinkoff_ajax_log.txt');
                    $this->tinkoffId->redirectLogin();
                }

//                $phone = $this->users->clear_phone($userPhone);
//                if (Helpers::isBlockedUserBy1C($this, $phone)) {
//                    // Пользователь заблокирован в 1С
//                    $user = $this->users->get_user($phone);
//                    if ($user) {
//                        $this->users->update_user($user->id, ['blocked' => 1]);
//                    }
//                    $this->logging('INFO', 'User is blocked', '', ['phone' => $phone], 'tinkoff_ajax_log.txt');
//                    unset($_SESSION['state']);
//                    unset($_SESSION['code']);
//                    unset($_SESSION['user_info']);
//
//                    $this->tinkoffId->redirectLogin();
//                }

                $userId = (int)$this->users->get_phone_user($userPhone);
                if ($userId) {
                    $_SESSION['user_id'] = $userId;

                    //setcookie('user_id', $userId, time() + 86400 * 365, '/');
                    //setcookie('user_id', (string)$user->id, time() + 86400 * 365, '/', '', isset($_SERVER['HTTPS']), true);

                    $this->logging('INFO', 'User logged in', '', ['user_id' => $userId], 'tinkoff_ajax_log.txt');
                    header('Location: ' . $this->config->root_url . '/user');
                    exit;
                } else {
                    $this->logging('INFO', 'User registration with tinkoff id', '', ['userInfo' => $userInfo], 'tinkoff_ajax_log.txt');
                    $this->tinkoffId->redirectLogin();
                }
            } else {
                $this->logging('ERROR', 'Action not found', '', ['action' => $action], 'tinkoff_ajax_log.txt');
                $this->sendErrorResponse('Action not found', 404);
            }
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Обрабатывает завершение авторизации через Tinkoff ID
     */
    private function handleComplete()
    {
        try {
            $this->logging('INFO', 'Starting handleComplete', '', [
                'session' => $_SESSION,
                'get' => $_GET,
                'post' => $_POST
            ], 'tinkoff_ajax_log.txt');

            $userInfo = $this->tinkoffId->handleAuthComplete();

            if (!isset($userInfo['sub'])) {
                $this->logging('ERROR', 'Invalid user information received', '', ['received_user_info' => $userInfo], 'tinkoff_ajax_log.txt');
                throw new Exception('Invalid user information received');
            }

            $_SESSION['user_info'] = $userInfo;

            $this->logging('INFO', 'User information validated', '', ['user' => $userInfo], 'tinkoff_ajax_log.txt');
            //$this->sendSuccessResponse(['user' => $userInfo]);
            return $userInfo;
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Отправляет успешный ответ в формате JSON
     *
     * @param mixed $data Данные для отправки в ответе
     */
    private function sendSuccessResponse($data)
    {
        if (!headers_sent()) {
            header("Content-Type: application/json; charset=UTF-8");
        }
        $this->logging('INFO', 'Success response sent', '', ['response' => $data], 'tinkoff_ajax_log.txt');
        echo json_encode($data);
    }

    /**
     * Отправляет ответ с ошибкой в формате JSON
     *
     * @param string $message Сообщение об ошибке
     * @param int $code       Код ошибки
     */
    private function sendErrorResponse($message, $code = 400)
    {
        if (!headers_sent()) {
            http_response_code($code);
            header("Content-Type: application/json; charset=UTF-8");
        }
        $this->logging('ERROR', 'Error response sent', '', ['message' => $message, 'code' => $code], 'tinkoff_ajax_log.txt');
        //echo json_encode(['error' => $message]);
        $this->tinkoffId->redirectLogin();
    }

    /**
     * Обрабатывает исключение и отправляет ответ с ошибкой
     *
     * @param Exception $e Объект исключения
     */
    private function handleError(Exception $e)
    {
        $this->logging('ERROR', 'Exception caught', '', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 'tinkoff_ajax_log.txt');
        $this->sendErrorResponse('An error occurred during processing. Please try again later.', 500);
    }
}

$tinkoffAjax = new TinkoffAjax();
$tinkoffAjax->analyzeActions();
