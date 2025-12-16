<?php

namespace RestApi;

use PhonePartnerModel;
use Simpla;

require dirname(__DIR__) . '/api/Simpla.php';

/**
 * Интеграция партнеров
 */
final class PhonePartnerApi extends Simpla
{
    /**
     * Возвращаемые данные JSON
     * @var array
     */
    private array $response_data = [];

    /**
     * Идентификатор запроса
     * @var string
     */
    private string $request_uid;

    /**
     * Телефон для обработки
     * @var string
     */
    private string $phone;


    /**
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->request_uid = uniqid();
        $this->initLogs();
    }

    /**
     * @return void
     */
    private function initLogs()
    {
        error_reporting(E_ERROR | E_WARNING | E_NOTICE);
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('error_log', $this->config->root_dir . 'logs/partner_phone_api_error.log');
    }

    /**
     * Основная точка входа
     * @return void
     * @throws \Exception
     */
    public function init()
    {
        try {
            $this->validateAuthToken();
            $raw_phone = $this->request->getJsonInput('phone');
            $this->open_search_logger->create("Request", [
                'post' => $_POST,
                'get' => $_GET,
                'json' => $this->request->getAllJsonInput(),
                'ip' => $_SERVER['REMOTE_ADDR'],
                'request_uid' => $this->request_uid,
            ], 'phone_partner_api');

            $this->phone = $this->users->clear_phone($raw_phone, '7');
            $this->processingPhone();

        } catch (\Exception $e) {
            error_log("Exception:" . $e->getMessage());

            $this->open_search_logger->create("Fatal Exception", [
                'error' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace(),
                ],
                'request_uid' => $this->request_uid,
            ], 'phone_partner_api', \OpenSearchLogger::LOG_LEVEL_ERROR);

            http_response_code(500);
            $this->response_data['error'] = 'Server Internal Error';
            $this->response(); // ← Отправляем ответ и завершаем
        }

        $this->response();
    }

    private function processingPhone()
    {
        $this->validatePhone();

        if (!empty($this->response_data['error'])) {
            return;
        }

        // Проверим пользователя в нашей базе
        $user = $this->users->get_user($this->phone);

        // Выполним действие в зависимости от того, есть ли пользователь в базе данных
        if (!empty($user)) {
            $this->actionOldClient();
        } else {
            $this->actionNewClient();
        }
    }

    /**
     * Действие для пользователей которые есть в базе данных
     * @return void
     */
    private function actionOldClient()
    {
        $this->phone_partner_model->addItem(
            [
                'phone' => $this->phone,
                'client_type' => PhonePartnerModel::CLIENT_TYPE_OLD,
                'cron_status' => PhonePartnerModel::CRON_STATUS_NEW
            ],
        );

        $this->response_data['result'] = 'old_client';
    }

    /**
     * Действие для новых пользователей
     * @return void
     */
    private function actionNewClient()
    {
        $this->phone_partner_model->addItem(
            [
                'phone' => $this->phone,
                'client_type' => PhonePartnerModel::CLIENT_TYPE_NEW,
                'cron_status' => PhonePartnerModel::CRON_STATUS_NEW
            ],
        );

        $this->response_data['result'] = 'new_client';
    }

    /**
     * Возвращает ответ партнеру
     * @return void
     */
    private function response()
    {
        $this->open_search_logger->create("Response", array_merge($this->response_data, ['request_uid' => $this->request_uid]), 'phone_partner_api');
        $this->request->json_output($this->response_data);
    }

    /**
     * Проверка телефона
     */
    private function validatePhone()
    {
        // Проверим телефон в базе
        $has_phone = $this->phone_partner_model->hasPhone($this->phone);

        if ($has_phone) {
            http_response_code(403);
            $this->response_data['error'] = 'Phone already exists';
        }
    }

    /**
     * Получаем токен из заголовка
     */
    private function validateAuthToken()
    {
        $headers = getallheaders();

        // Проверяем заголовок X-Api-Key
        if (isset($headers['X-Api-Key'])) {
            $apiKey = $headers['X-Api-Key'];
            if ($apiKey === $this->config->partner_phone_api_key) {
                return; // Авторизация успешна
            }
        }

        // Если дошли сюда - авторизация не пройдена
        http_response_code(401);
        $this->response_data['error'] = 'Not authorized';
        $this->response();
        exit; // ← Важно: завершаем выполнение скрипта
    }
}

(new PhonePartnerApi())->init();
