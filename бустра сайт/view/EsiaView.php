<?php

require_once 'AuthServiceView.php';

/**
 * Модуль контроллер ГосУслуги
 */
class EsiaView extends AuthServiceView
{
    public function auth(): void
    {
        $this->validateState();
        $code = trim($this->request->get('code'));
        $response_token = $this->esia_service->get_token_action($code);

        if (empty($response_token['access_token'])) {
            throw new Exception("Bad request", 400);
        }

        $this->esia_service->setUserToken($response_token['access_token']);

        $amount = $_COOKIE['amount'] ?? 30000;
        $period = $_COOKIE['period'] ?? 16;
        $userInfo = $this->esia_service->getUserInfo();

        if (!empty($userInfo['info']['mobilePhone'])) {
            $phone_mobile = $this->users->clear_phone($userInfo['info']['mobilePhone']);
            // Авторизуем пользователя если телефон найден
            $this->authUserByPhoneOrRedirect($phone_mobile, EsiaService::ESIA_AUTH_OLD_USER_TYPE);
        }

        // Валидируем общие данные
        if(!$this->validateData($userInfo)){
            return;
        }

        $passport = $this->findPassport($userInfo);
        $passport_serial = $passport['series'] . ' ' . $passport['number'];

        // Проверим пользователя по паспортным данным в базе
        $this->findUserByPassportOrRedirect($passport_serial);

        // Проверим пользователя в 1С, и сделаем импорт его с авторизацией
        $this->findUserByPhone1CAndAuth($phone_mobile, EsiaService::ESIA_AUTH_OLD_USER_TYPE);

        $date = date( 'Y-m-d H:i:s' );
        $user = new StdClass();
        $user->firstname = $userInfo['info']['firstName'];
        $user->lastname = $userInfo['info']['lastName'];
        $user->patronymic = $userInfo['info']['middleName'];
        $user->phone_mobile = $phone_mobile;
        $user->birth = (new DateTime($userInfo['info']['birthDate']))->format('d.m.Y');
        $user->birth_place = $userInfo['info']['birthPlace'];
        $user->gender = $userInfo['info']['gender'] === 'M' ? 'male' : 'female';

        $user->first_loan_period = $period;
        $user->first_loan_amount = $amount;
        $user->first_loan = 1;

        $user->reg_ip = $_SERVER['REMOTE_ADDR'];
        $user->enabled = 1;
        $user->use_b2p = 1;

        $user->additional_data_added = 0;
        $user->accept_data_added = 0;
        $user->files_added = 0;
        $user->card_added = 0;
        $user->created = date('Y-m-d H:i:s');
        $user->missing_real_date   = date('Y-m-d H:i:s');

        $user->service_sms = 1;
        $user->service_insurance = 0;
        $user->sms = rand(1000, 9999);

        $user->utm_source = empty($_COOKIE["utm_source"]) ? 'Boostra' : strip_tags($_COOKIE["utm_source"]);
        $user->utm_medium = empty($_COOKIE["utm_medium"]) ? 'Site' : strip_tags($_COOKIE["utm_medium"]);
        $user->utm_campaign = empty($_COOKIE["utm_campaign"]) ? 'C1_main' : strip_tags($_COOKIE["utm_campaign"]);
        $user->utm_content = empty($_COOKIE["utm_content"]) ? '' : strip_tags($_COOKIE["utm_content"]);
        $user->utm_term = empty($_COOKIE["utm_term"]) ? '' : strip_tags($_COOKIE["utm_term"]);
        $user->webmaster_id = empty($_COOKIE["webmaster_id"]) ? '' : strip_tags($_COOKIE["webmaster_id"]);
        $user->click_hash = empty($_COOKIE["click_hash"]) ? '' : strip_tags($_COOKIE["click_hash"]);
        $user->inn = $userInfo['info']['inn'] ?? '';

        $user->personal_data_added = 1;
        $user->personal_data_added_date = $date;
        $user->passport_issued = $passport['issuedBy'];
        $user->passport_serial = $passport_serial;
        $user->subdivision_code = preg_replace('/(\d{3})(\d{3})/', '$1-$2', str_replace('-', '', $passport['issueId']));
        $user->passport_date = (new DateTime($passport['issueDate']))->format('d.m.Y');
        $user->address_data_added = 0;

        $address = $this->findRegistrationAddress($userInfo);
        if (!empty($address)) {
            $user->Regindex = $address['zipCode'] ?? '';
            $user->Regregion = $address['region'] ?? '';
            $user->Regcity = $address['city'] ?? $address['settlement'] ?? '';
            $user->Regstreet = $address['street'] ?? '';
            $user->Reghousing = $address['house'] ?? '';
            $user->Regbuilding = '';
            $user->Regroom = $address['flat'] ?? '';
            $user->Regregion_shorttype = '';
            $user->Regcity_shorttype = '';
            $user->Regstreet_shorttype = '';

            $factAddress = $this->findFactAddress($userInfo);
            if (!empty($factAddress)) {
                $user->Faktindex = $factAddress['zipCode'] ?? '';
                $user->Faktregion = $factAddress['region'] ?? '';
                $user->Faktcity = $factAddress['city'] ?? $factAddress['settlement'] ?? '';
                $user->Faktstreet = $factAddress['street'] ?? '';
                $user->Fakthousing = $factAddress['house'] ?? '';
                $user->Faktbuilding = '';
                $user->Faktroom = $factAddress['flat'] ?? '';
                $user->Faktregion_shorttype = '';
                $user->Faktcity_shorttype = '';
                $user->Faktstreet_shorttype = '';

                if ($this->defaultValidateFieldAddress($factAddress) && $this->defaultValidateFieldAddress($address)) {
                    $user->address_data_added = 1;
                    $user->address_data_added_date = $date;
                }
            }
        }

        if ($user_id = $this->users->add_user($user))
        {
            // Инициализация автоподписания для новых пользователей по правилам utm и настроек
            $this->users->initAutoConfirmNewUser($user_id, $user);
            $this->users->initAutoConfirm2NewUser($user_id, $user);

            $this->user_data->set($user_id, $this->user_data::IS_ESIA_NEW_USER, 1);

            if (!empty($user->address_data_added)) {
                $this->addAddressService($user_id, $user);
            }

            $scorings = array_merge(Scorings::getScoringListAfterPersonalData(), Scorings::getScoringListAfterNewOrder());
            foreach ($scorings as $scoring) {
                $this->scorings->add_scoring(
                    [
                        'user_id' => $user_id,
                        'type' => $scoring,
                    ]
                );
            }

            setcookie('amount', null);
            setcookie('period', null);

            $this->users->authUserById($user_id);
        }
    }

    public function validateData(array $data): bool
    {
        $errors = [];

        if (empty($data['info'])) {
            $errors['main'] = 'Основные данные';
        }

        if (empty($data['info']['lastName']) || empty($data['info']['firstName']) || empty($data['info']['middleName'])) {
            $errors['fio'] = "ФИО";
        }

        if (empty($data['info']['mobilePhone'])) {
            $errors['phone'] = "Телефон";
        }

        if (empty($data['info']['birthPlace'])) {
            $errors['birth_place'] = "Место рождения";
        }

        if (empty($data['info']['birthDate'])) {
            $errors['birth_date'] = "Дата рождения";
        }

        if (empty($data['info']['gender'])) {
            $errors['gender'] = "Пол";
        }

        $passport = $this->findPassport($data);
        if (empty($passport) || empty($passport['issuedBy']) || empty($passport['series']) || empty($passport['issueId']) || empty($passport['issueDate'])) {
            $errors['passport'] = "Паспорт";
        }

        $address = $this->findRegistrationAddress($data);
        if (!empty($address) && !$this->defaultValidateFieldAddress($address)) {
            $errors['registration_address'] = "Адрес";
        }

        if (!empty($errors)) {
            $this->service_auth_logs->addEsiaErrorLog($data['sub'], $errors);

            $required_filed_errors = array_filter($errors, function ($key) {
                return $key !== 'registration_address';
            }, ARRAY_FILTER_USE_KEY);

            if (!empty($required_filed_errors)) {
                $_SESSION['esia_id_error'] = "Данных из сервиса ESIA недостаточно для моментальной регистрации. Пожалуйста, попробуйте снова, отметив обязательные пункты (" . implode(',', $required_filed_errors) . ").";
                return false;
            }
        }

        return true;
    }


    /**
     * Ищем паспорт
     *
     * @param array $data
     * @return false|mixed
     */
    private function findPassport(array $data)
    {
        $passport = array_filter($data['info']['documents'] ?? [], function ($item) {
            return strtoupper($item['type']) === 'RF_PASSPORT';
        });

        if (!empty($passport)) {
            return current($passport);
        }

        return $passport;
    }

    /**
     * Поиск адреса
     *
     * @param array $data
     * @return array
     */
    private function findRegistrationAddress(array $data): array
    {
        $address = array_filter($data['info'] ?? [], function ($key) {
            return $key === 'registrationAddress';
        }, ARRAY_FILTER_USE_KEY);

        if (!empty($address)) {
            return current($address);
        }

        return $address;
    }

    /**
     * Адрес проживания
     *
     * @param array $data
     * @return array
     */
    private function findFactAddress(array $data): array
    {
        $address = array_filter($data['info'] ?? [], function ($key) {
            return $key === 'homeAddress';
        }, ARRAY_FILTER_USE_KEY);

        if (!empty($address)) {
            return current($address);
        }

        return $address;
    }

    /**
     * @return void
     */
    public function init()
    {
        if($url = $this->esia_service->get_auth_url_action())
        {
            $this->request->redirect($url);
        }

        $_SESSION['esia_id_error'] = "Произошла ошибка, повторите попытку.";
    }

    /**
     * @return void
     * @throws Exception
     */
    public function validateState()
    {
        $state = $this->request->get('state', 'string');
        if (!$this->esia_service->validateState($state)) {
            throw new Exception("Bad request", 400);
        }
    }
}
