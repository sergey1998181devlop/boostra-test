<?php

require_once 'AuthServiceView.php';

/**
 * Модуль контроллер Т-Ид
 */
class TBankIdView extends AuthServiceView
{
    public function auth(): void
    {
        if (!empty($this->request->get('error'))) {
            $_SESSION['t_id_error'] = "Что то пошло не так ;( Попробуйте ещё раз.";
            $this->request->redirect($this->getInitUrl());
        }

        $this->validateState();
        $code = trim($this->request->get('code'));

        $response_token = $this->TBankIdService->getToken($code);

        if (empty($response_token['access_token'])) {
            throw new Exception("Bad request", 400);
        }

        $this->TBankIdService->setUserToken($response_token['access_token']);

        $mainData = $this->TBankIdService->getMainData();
        $inn = $this->TBankIdService->getInn();
        $addresses = $this->TBankIdService->getAddresses();
        $passport_data = $this->TBankIdService->getPassportData();

        $date = date( 'Y-m-d H:i:s' );

        if (!empty($mainData['phone_number'])) {
            $phone_mobile = $this->users->clear_phone($mainData['phone_number']);
            // Авторизуем пользователя если телефон найден
            $this->authUserByPhoneOrRedirect($phone_mobile, TBankId::T_ID_AUTH_OLD_USER_TYPE);
        }

        if(!$this->validateData([$mainData, $passport_data, $addresses])){
            return;
        }

        // Проверим пользователя по паспортным данным в базе
        $this->findUserByPassportOrRedirect($passport_data['serialNumber']);

        // Проверим пользователя в 1С, и сделаем импорт его с авторизацией
        $this->findUserByPhone1CAndAuth($phone_mobile, TBankId::T_ID_AUTH_OLD_USER_TYPE);

        $amount = $_COOKIE['amount'] ?? 30000;
        $period = $_COOKIE['period'] ?? 16;
        $sub = $mainData['sub'];

        $user = new StdClass();
        $user->firstname = $mainData['given_name'];
        $user->lastname = $mainData['family_name'];
        $user->patronymic = $mainData['middle_name'];
        $user->phone_mobile = $phone_mobile;
        $user->birth = (new DateTime($mainData['birthdate'] ?? $mainData['birthDate']))->format('d.m.Y');
        $user->birth_place = $passport_data['birthPlace'];
        $user->gender = $mainData['gender'];

        $user->first_loan_period = min($period, Orders::MAX_PERIOD_FIRST_LOAN);
        $user->first_loan_amount = min($amount, 30000);
        $user->first_loan = 1;

        $user->reg_ip = $_SERVER['REMOTE_ADDR'];
        $user->enabled = 1;

        $user->address_data_added = 0;
        $user->additional_data_added = 0;
        $user->accept_data_added = 0;
        $user->files_added = 0;
        $user->card_added = 0;
        $user->created = date('Y-m-d H:i:s');
        $user->missing_real_date   = date('Y-m-d H:i:s');

        $user->service_sms = 1;
        $user->service_insurance = 0;
        $user->sms = rand(1000, 9999);

        $user->use_b2p = 1;
        $user->utm_source = empty($_COOKIE["utm_source"]) ? 'Boostra' : strip_tags($_COOKIE["utm_source"]);
        $user->utm_medium = empty($_COOKIE["utm_medium"]) ? 'Site' : strip_tags($_COOKIE["utm_medium"]);
        $user->utm_campaign = empty($_COOKIE["utm_campaign"]) ? 'C1_main' : strip_tags($_COOKIE["utm_campaign"]);
        $user->utm_content = empty($_COOKIE["utm_content"]) ? '' : strip_tags($_COOKIE["utm_content"]);
        $user->utm_term = empty($_COOKIE["utm_term"]) ? '' : strip_tags($_COOKIE["utm_term"]);
        $user->webmaster_id = empty($_COOKIE["webmaster_id"]) ? '' : strip_tags($_COOKIE["webmaster_id"]);
        $user->click_hash = empty($_COOKIE["click_hash"]) ? '' : strip_tags($_COOKIE["click_hash"]);

        $user->inn = $inn['inn'] ?? '';

        if (!empty($passport_data['serialNumber'])) {
            $passport = $this->users::splitPassportSerial($passport_data['serialNumber']);

            $user->personal_data_added = 1;
            $user->personal_data_added_date = $date;

            $user->passport_issued = $passport_data['unitName'] ?? '';
            $user->passport_serial = $passport['serial'] . ' ' . $passport['number'];
            $user->subdivision_code = $passport_data['unitCode'] ?? '';
            $user->passport_date = (new DateTime($passport_data['issueDate']))->format('d.m.Y');
        }

        if (!empty($addresses['addresses'])) {
            foreach ($addresses['addresses'] as $address) {
                if ($address['addressType'] === 'REGISTRATION_ADDRESS') {
                    $user->Regindex = $address['zipCode'] ?? '';
                    $user->Regregion = $address['region'] ?? '';
                    $user->Regcity = $address['city'] ?? $address['settlement'] ?? '';
                    $user->Regstreet = $address['street'] ?? '';
                    $user->Reghousing = $address['house'] ?? '';
                    $user->Regbuilding = '';
                    $user->Regroom = $address['apartment'] ?? '';
                    $user->Regregion_shorttype = '';
                    $user->Regcity_shorttype = '';
                    $user->Regstreet_shorttype = '';

                    $validate_registration_address = $this->defaultValidateFieldAddress($address);
                }

                // Фактический адрес, или проживания
                if ($address['addressType'] === 'RESIDENCE_ADDRESS') {
                    $user->Faktindex = $address['zipCode'] ?? '';
                    $user->Faktregion = $address['region'] ?? '';
                    $user->Faktcity = $address['city'] ?? $address['settlement'] ?? '';
                    $user->Faktstreet = $address['street'] ?? '';
                    $user->Fakthousing = $address['house'] ?? '';
                    $user->Faktbuilding = '';
                    $user->Faktroom = $address['apartment'] ?? '';
                    $user->Faktregion_shorttype = '';
                    $user->Faktcity_shorttype = '';
                    $user->Faktstreet_shorttype = '';

                    $validate_residence_address = $this->defaultValidateFieldAddress($address);
                }
            }

            if (!empty($validate_registration_address) && !empty($validate_residence_address)) {
                $user->address_data_added = 1;
                $user->address_data_added_date = $date;
            }
        }

        if ($user_id = $this->users->add_user($user))
        {
            // Инициализация автоподписания для новых пользователей по правилам utm и настроек
            $this->users->initAutoConfirmNewUser($user_id, $user);
            $this->users->initAutoConfirm2NewUser($user_id, $user);

            $this->user_data->set($user_id, $this->user_data::IS_TID_NEW_USER, 1);

            if (!empty($user->address_data_added)) {
                $this->addAddressService($user_id, $user);
            }

            $this->TBankId->saveSubId($user_id, $sub);
            $scorings = array_merge(Scorings::getScoringListAfterPersonalData(), Scorings::getScoringListAfterNewOrder());
            foreach ($scorings as $scoring) {
                $this->scorings->add_scoring(
                    [
                        'user_id' => $user_id,
                        'type' => $scoring,
                    ]
                );
            }

            setcookie('t_id_state', null, time() - 1, '/');
            setcookie('is_tbank_id', '1', time() + 3600);
            setcookie('amount', null);
            setcookie('period', null);
            unset($_SESSION['t_id_error']);

            $this->users->authUserById($user_id);
        }
    }

    public function validateState()
    {
        $state = $this->request->get('state', 'string');
        if (!$this->TBankIdService->validateState($state)) {
            throw new Exception("Bad request", 400);
        }
    }

    public function validateData(array $data): bool
    {
        list($mainData, $passport_data, $addresses) = $data;

        $errors = [];

        if (empty($mainData['family_name']) || empty($mainData['given_name']) || empty($mainData['middle_name'])) {
            $errors['fio'] = "ФИО";
        }

        if (empty($mainData['gender'])) {
            $errors['gender'] = "Пол";
        }

        if (empty($mainData['phone_number'])) {
            $errors['phone'] = "Телефон";
        }

        if (empty($mainData['birthdate']) && empty($mainData['birthDate'])) {
            $errors['birth_date'] = "Дата рождения";
        }

        if (empty($passport_data['serialNumber']) || empty($passport_data['unitName']) || empty($passport_data['unitCode']) || empty($passport_data['issueDate'])) {
            $errors['passport'] = "Паспорт";
        }

        if (empty($passport_data['birthPlace'])) {
            $errors['birth_place'] = "Место рождения";
        }

        if (!empty($addresses['addresses'])) {
            foreach ($addresses['addresses'] as $address) {
                if ((in_array($address['addressType'], ['REGISTRATION_ADDRESS', 'RESIDENCE_ADDRESS'])) && !$this->defaultValidateFieldAddress($address)) {
                    $errors['registration_address'] = "Адрес";
                    break;
                }
            }
        }

        if (!empty($errors)) {
            $this->service_auth_logs->addTidErrorLog($errors);

            $required_filed_errors = array_filter($errors, function ($key) {
                return $key !== 'registration_address';
            }, ARRAY_FILTER_USE_KEY);

            if (!empty($required_filed_errors)) {
                $_SESSION['t_id_error'] = "Данных из сервиса T-ID недостаточно для моментальной регистрации. Пожалуйста, попробуйте снова, отметив обязательные пункты (" . implode(',', $required_filed_errors) . ").";
                return false;
            }
        }

        return true;
    }
}
