<?php

use boostra\services\UsersAddressService;

require_once 'View.php';

/**
 * Class AuthServiceView
 *
 * Работа с сервисами авторизации ГосУслуги, Т-Ид
 */
abstract class AuthServiceView extends View
{
    /**
     * Валидация данных
     * @param array $data
     * @return bool
     */
    abstract function validateData(array $data): bool;

    /**
     * Основной метод, где происходит авторизация
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    abstract function auth(): void;

    /**
     * Валидация переменной, для приема запроса от сервиса
     *
     * @return void
     * @throws Exception
     */
    abstract function validateState();

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws SoapFault
     */
    public function fetch()
    {
        $action = trim($this->request->get('action', 'string'));

        /**
         * @uses auth
         */
        if (method_exists($this, $action)) {
            $this->{$action}();
        }

        $this->request->redirect($this->getInitUrl());
    }

    /**
     * @return string
     */
    protected function getInitUrl(): string
    {
        $amount = $_COOKIE['amount'] ?? 30000;
        $period = $_COOKIE['period'] ?? 16;

        $params = http_build_query(compact('amount', 'period'));
        return $this->config->root_url . '/init_user?' . $params;
    }

    /**
     * Добавляет адрес в дополнительную таблицу
     *
     * @param int $user_id
     * @param object $user
     * @return void
     */
    protected function addAddressService(int $user_id, object $user)
    {
        $usersAddressService = new UsersAddressService();

        $registrationAddress = $usersAddressService->getRegistrationAddressFromUser((array)$user);
        $factualAddress = $usersAddressService->getFactualAddressFromUser((array)$user);

        $registration_address_id = $usersAddressService->saveNewAddress($registrationAddress);
        $factual_address_id = $usersAddressService->saveNewAddress($factualAddress);

        $this->users->update_user($user_id, compact('registration_address_id', 'factual_address_id'));

        $usersAddressService->saveOktmo($user_id, $registrationAddress);
    }

    /**
     * Проверяет обязательные поля адреса
     *
     * @param array $address
     * @return bool
     */
    protected function defaultValidateFieldAddress(array $address): bool
    {
        return !empty($address['zipCode']) && !empty($address['region']) && (!empty($address['city']) || !empty($address['settlement']));
    }

    /**
     * Проверка пользователя по базе данных, по паспорту или редирект на страницу входа в случае нахождения
     *
     * @param string $passport_serial
     * @return void
     */
    protected function findUserByPassportOrRedirect(string $passport_serial)
    {
        $passport_user_id = (int)$this->users->get_passport_user($passport_serial);

        if (empty($passport_user_id)) {
            return;
        }

        // Получаем маску телефона для фронта
        $existing_user = $this->users->get_user($passport_user_id);
        $phone_mobile_obfuscated = preg_replace(
            '@(\d{3})\d*(\d{3})@',
            '+$1****$2',
            $existing_user->phone_mobile
        );

        $_SESSION['flash_error'] = "Клиент с такими паспортными данными зарегистрирован по номеру телефона $phone_mobile_obfuscated";

        $this->request->redirect($this->config->root_url . '/user/login');
    }

    /**
     * Поиск пользователя по номеру телефона и его авторизация, в случае ошибки редирект
     *
     * @param string $phone
     * @param string $auth_type
     * @return void
     */
    protected function authUserByPhoneOrRedirect(string $phone, string $auth_type = '')
    {
        if ($user_id = $this->users->get_phone_user($phone)) {
            $isBlocked = Helpers::isBlockedUserBy1C($this, $phone);

            if (!$isBlocked) {
                if ($auth_type) {
                    $this->users->addAuthUser($user_id, $auth_type);
                }
                $this->users->authUserById($user_id);
            } else {
                $_SESSION['flash_error'] = 'Ваш аккаунт заблокирован!';
                $this->request->redirect('/user/login');
            }
        }
    }

    /**
     * Поиск пользователя в 1С, и его импорт при нахождении там
     *
     * @param $phone
     * @param string $auth_type
     * @return void
     * @throws SoapFault
     */
    protected function findUserByPhone1CAndAuth($phone, string $auth_type = '')
    {
        // Авторизация и создание пользователя, если он найден в 1с, но нет в нашей Базе
        $soap = $this->soap->get_uid_by_phone($phone);

        if (!empty($soap->result) && !empty($soap->uid))
        {
            $expl = explode(' ', $soap->client);
            $lastname = isset($expl[0]) ? mb_convert_case($expl[0], MB_CASE_TITLE) : '';
            $firstname = isset($expl[1]) ? mb_convert_case($expl[1], MB_CASE_TITLE) : '';
            $patronymic = isset($expl[2]) ? mb_convert_case($expl[2], MB_CASE_TITLE) : '';

            $user_id = $this->users->add_user(
                [
                    'UID' => $soap->uid,
                    'UID_status' => "ok",
                    'phone_mobile' => $phone,
                    'lastname' => $lastname,
                    'firstname' => $firstname,
                    'patronymic' => $patronymic,
                    'utm_source' => empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"],
                    'utm_medium' => empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"],
                    'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"],
                    'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
                    'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
                    'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
                    'click_hash' => empty($_COOKIE["click_hash"]) ? '' : $_COOKIE["click_hash"],
                    'enabled' => 1,
                    'last_ip' => $_SERVER['REMOTE_ADDR'],
                    'service_sms' => 1,
                    'service_insurance' => 1,
                    'use_b2p' => 1,
                ]
            );

            if ($auth_type) {
                $this->user_data->set($user_id, Users::AUTH_FROM_1C_TYPES[$auth_type] , 1);
            }

            // Делаем полный импорт из 1С
            $details = $this->soap->get_client_details($soap->uid);
            $this->import1c->import_user($user_id, $details);

            $this->users->authUserById($user_id);
        } elseif ($soap->error == 'Множество совпадений') {
            $this->soap->send_doubling_phone($phone);
        }
    }
}
