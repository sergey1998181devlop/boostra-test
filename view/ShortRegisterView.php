<?php

error_reporting(0);
ini_set('display_errors', 'Off');

use api\helpers\UserHelper;
use boostra\services\DadataService;
use boostra\services\RegionService;
use boostra\services\UsersAddressService;

require_once __DIR__ . '/../lib/autoloader.php';
require_once __DIR__ . '/View.php';
require_once __DIR__ . '/../api/Scorings.php';
require_once __DIR__ . '/../api/enums/ProfessionEnum.php';

/**
 * Короткий флоу регистрации через кабутек.
 *
 * `boostra.ru/register`
 *
 * @see https://tracker.yandex.ru/BOOSTRARU-3554 BOOSTRARU-3554
 */
class ShortRegisterView extends View
{
    /** Скан паспорта и прописки в кабутек. Пользователь ещё не создан */
    private const STAGE_PASSPORT_SCAN = 'passport_scan';

    /** Вводит адрес проживания, либо указывает что он совпадает с пропиской */
    private const STAGE_ADDRESS = 'address';

    /** Ручное заполнение данных о работе */
    public const STAGE_WORK = 'work';

    /** Ждём завершения всех запущенных скорингов */
    public const STAGE_TIMER = 'timer';

    /** Привязка реальной карты, далеее переход в ЛК */
    public const STAGE_CARD = 'card';

    /** Не прошёл скоринги, переход на продажу в бонон */
    public const STAGE_BONON = 'bonon';

    /** Регистрация завершена, переадресация в ЛК */
    public const STAGE_FINAL = 'final';

    public const USERDATA_SHORT_FLOW = 'is_short_flow';

    /** @var int[] Значения по-умолчанию для некоторых полей при регистрации нового пользователя */
    private const REGISTER_DEFAULT_VALUES = [
        'enabled' => 1,
        'first_loan' => 1,
        'personal_data_added' => 0,
        'additional_data_added' => 0,
        'accept_data_added' => 0,
        'address_data_added' => 0,
        'files_added' => 0,
        'card_added' => 0,
        'utm_source' => 'Boostra',
        'utm_medium' => 'Site',
        'utm_campaign' => 'C1_main',
    ];

    private const LOG_FILE = 'short_register_flow.txt';

    public function fetch(): string
    {
        $stage = $this->getStage();

        switch ($stage) {

            // 1 этап
            case self::STAGE_PASSPORT_SCAN:
                return $this->passportScanStage();

            // 4 этап
            case self::STAGE_ADDRESS:
                return $this->addressStage();

            // 5 этап
            case self::STAGE_WORK:
                return $this->workStage();

            // 6 этап
            case self::STAGE_TIMER:
                return $this->timerStage();

            // 7 этап
            case self::STAGE_CARD:
                return $this->cardStage();

            case self::STAGE_BONON:
                return $this->bononStage();
        }

        return '';
    }

    /**
     * Получаем текущую стадию прохождения флоу
     *
     * @return string
     */
    private function getStage(): string
    {
        $phone = $_SESSION['phone'];

        if (!$this->short_flow->isShortFlowEnabled() || (empty($phone)) && empty($this->user)) {
            unset($_SESSION['short_flow']);
            $this->request->redirect('/account');
        }

        if (empty($this->user)) {
            $user = $this->users->get_user($phone);
        } else {
            $user = $this->user;
        }

        if (empty($user)) {

            $verification = $this->cyberity->getUserVerification([
                'phone' => $_SESSION['phone']
            ]);

            if (!empty($verification->status) && in_array($verification->status, [$this->cyberity::STATUS_ADDED_SELFIE, $this->cyberity::STATUS_COMPLETED])) {
                return self::STAGE_ADDRESS;
            }

            return self::STAGE_PASSPORT_SCAN;
        }

        $registerStage = $this->short_flow->getRegisterStage((int)$user->id);

        if ($registerStage === self::STAGE_FINAL) {
            $this->request->redirect('/user');
        }

        return $registerStage;
    }

    /**
     * @return string
     * @see self::STAGE_PASSPORT_SCAN
     */
    private function passportScanStage(): string
    {
        $_SESSION['amount'] = $this->request->get('amount', 'integer') ?? 5000;
        $_SESSION['period'] = $this->request->get('period', 'integer') ?? 7;

        $this->design->assign('callbackUrl', $this->config->front_url . '/ajax/register_short_self_verification.php');

        $verification = $this->cyberity->getUserVerification([
            'phone' => $_SESSION['phone']
        ]);

        if (!empty((int)$_SESSION['amount'])) {
            $amount = (int)$_SESSION['amount'];
        } else {
            $amount = 1000;
        }

        $this->design->assign('stage', $verification->status);
        $this->design->assign('header1', 'Проверка паспорта');
        $this->design->assign('header2', 'Вам одобрено ' . $amount . ' рублей. Закончите анкету и получите деньги');
        $this->design->assign('header3', 'Прикрепите фото 2-3 страницы паспорта');
        $this->design->assign('header4', '');

        return $this->design->fetch('short_flow/passport.tpl');
    }

    /**
     * @return string
     * @see self::STAGE_ADDRESS
     */
    private function addressStage(): string
    {
        if ($this->request->method('post')) {
            $phone = $_SESSION['phone'];

            // Добавление пользователя
            $userId = $this->saveUser($phone);

            // Пользователь с таким номером паспорта уже существует
            if ($userId === null) {
                return $this->design->fetch('account_personal_data.tpl');
            }
            // Ошибка при создании пользователя
            elseif ($userId === 0) {
                unset($_SESSION);
                $this->request->redirect('/');
            }

            // Добавление адреса
            $this->save_address_data($userId);

            // Добавление userId в сессию
            $this->setUserIdToSession($userId);

            // Добавление пользователя в $this->user
            $this->user = $this->users->get_user($userId);

            // Вытягиваем id сессии JuicyScore, сохранится в заявку
            $_SESSION['juicescore_session_id'] = $this->request->post('juicescore_session_id');
            if (empty($_SESSION['juicescore_session_id']) && !empty($_COOKIE['juicescore_session_id'])) {
                $_SESSION['juicescore_session_id'] = $_COOKIE['juicescore_session_id'];
            }

            // Добавление заявки
            $orderId = $this->short_flow->addOrder($userId);

            // Добавление скорингов
            $this->short_flow->addScorings($userId, $orderId);

            // Сохранение фото паспорта и скан лица из кабутека
            $this->short_flow->saveFiles($this->user);
            $this->users->update_user((int)$this->user->id, [
                'files_added'       => 1,
                'files_added_date'  => date('Y-m-d H:i:s'),
            ]);

            $verification = $this->cyberity->getUserVerification([
                'phone' => $phone,
            ]);

            $this->cyberity->updateUserVerification((int)$verification->id, [
                'user_id' => $userId,
                'status' => $this->cyberity::STATUS_COMPLETED,
                'date_update' => date('Y-m-d H:i:s'),
            ]);

            $this->short_flow->setRegisterStage($userId, self::STAGE_WORK);

            return $this->getWorkView();
        }

        return $this->getAddressView();
    }

    private function setUserIdToSession(int $userId): void
    {
        $_SESSION['user_id'] = $userId;
        setcookie('user_id', $userId, time() + 86400 * 365, '/');

        UserHelper::getJWTToken($this->config->jwt_secret_key, $userId, 'auth_jwt_token', $this->config->jwt_expiration_time, true);
    }

    public function saveUser(string $phone): ?int
    {
        $verificationResponse = $this->short_flow->getVerificationResponse($phone);

        $passport = new stdClass();
        foreach ($verificationResponse->info->idDocs as $document) {
            if (!empty($document->number)) {
                $passport = $document;
                break;
            }
        }

        $passport_issued = $this->getPassportIssued($passport->issueAuthorityCode);

        $newUser = (object)[
            'firstname' => $verificationResponse->info->firstName,
            'lastname' => $verificationResponse->info->lastName,
            'patronymic' => $verificationResponse->info->middleName ?? '',
            'birth' => date('d.m.Y', strtotime($verificationResponse->info->dob)),
            'birth_place' => $verificationResponse->info->placeOfBirth,
            'phone_mobile' => $phone,
            'first_loan_amount' => $_SESSION['amount'],
            'first_loan_period' => $_SESSION['period'],
            'utm_source' => empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"],
            'utm_medium' => empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"],
            'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"],
            'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
            'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
            'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
            'click_hash' => empty($_COOKIE["click_hash"]) ? '' : $_COOKIE["click_hash"],
            'reg_ip' => $_SERVER['REMOTE_ADDR'],
            'sms' => $_SESSION['sms'] ?? $_SESSION['init_user_code'],
            'subdivision_code' => $passport->issueAuthorityCode,
            'passport_serial' => $passport->number,
            'passport_date' => date('d.m.Y', strtotime($passport->issuedDate)),
            'passport_issued' => $passport_issued,
            'gender' => $verificationResponse->info->gender === 'M' ? 'male' : 'female',
            'personal_data_added' => 1,
            'personal_data_added_date' => date('Y-m-d H:i:s'),
            'additional_data_added' => 1,
            'additional_data_added_date' => date('Y-m-d H:i:s'),
        ];

        if ($this->checkIsUserExist($passport, $newUser)) {
            return null;
        }

        try {
            $userId = $this->addUserToDb($newUser);
        } catch (Exception $e) {
            $error = [
                'Ошибка: ' . $e->getMessage(),
                'Файл: ' . $e->getFile(),
                'Строка: ' . $e->getLine(),
                'Подробности: ' . $e->getTraceAsString()
            ];
            $this->logging(__METHOD__, '', '', ['error' => $error, 'verificationResponse' => $verificationResponse,
            ], self::LOG_FILE);
            return 0;
        }

        return $userId;
    }

    /**
     * Проверка существования пользователя по номеру паспорта
     *
     *
     * @param stdClass|null $passport
     * @param stdClass $newUser
     * @return bool
     */
    private function checkIsUserExist(?stdClass $passport, stdClass $newUser): bool
    {
        $passport_user_id = (int)$this->users->get_passport_user((string)$passport->number);

        if (empty($passport_user_id) || !empty($this->is_developer) || !empty($this->is_admin)) {
            return false;
        }

        $isTestUser = $this->user_data->read($passport_user_id, 'test_user');

        // Тестовый пользователь
        if (!empty($isTestUser)) {
            return false;
        }

        // Получаем маску телефона для фронта
        $existing_user = $this->users->get_user( $passport_user_id );
        $existing_user->phone_mobile_obfuscated = preg_replace(
            '@(\d{3})\d*(\d{3})@',
            '+$1****$2',
            $existing_user->phone_mobile
        );
        $this->design->assign('error', 'allready_exists');
        $this->design->assign('existing_user', $existing_user);
        $this->design->assign('user', $newUser);

        $this->logging(__METHOD__, '',
            'Клиент с такими паспортными данными зарегистрирован по номеру телефона ' . $existing_user->phone_mobile,
            ['error' => true, 'existing_user' => $existing_user, 'new_user' => $newUser], self::LOG_FILE);

        return true;
    }

    public function addUserToDb(stdClass $user_dto): int
    {
        // TODO: Сюда обязательно должна быть передана инфа по допам, см. LoanView CTRL+F service_recurent
        $user = new StdClass();
        foreach ($user_dto as $field => $value) {
            $user->{$field} = $value;
        }

        if (empty($user->first_loan_period) || $user->first_loan_period > Orders::MAX_PERIOD_FIRST_LOAN) {
            $user->first_loan_period = Orders::MAX_PERIOD_FIRST_LOAN;
        }

        foreach (self::REGISTER_DEFAULT_VALUES as $field => $value) {
            if (!isset($user->{$field})) {
                $user->{$field} = $value;
            }
        }

        foreach (['firstname', 'lastname', 'patronymic'] as $field) {
            if (!empty($user->{$field})) {
                $user->{$field} = mb_convert_case(trim(strip_tags($user->{$field})), MB_CASE_TITLE);
            }
        }

        $soap = $this->soap->get_uid_by_phone($user->phone_mobile);
        if (!empty($soap->result) && !empty($soap->uid) || $soap->error == 'Множество совпадений') {
            throw new Exception("Номер телефона уже есть в 1С");
        }

        if ($this->users->get_user($user->phone_mobile)) {
            throw new Exception("Номер телефона уже есть в БД");
        }

        $user->created = date('Y-m-d H:i:s');
        $user->missing_real_date = date('Y-m-d H:i:s');

        if ($this->settings->enable_b2p_for_nk || ($user->utm_source == 'sms' && $user->webmaster_id == '1315'))
            $user->use_b2p = 1;

        $user_id = $this->users->add_user($user);

        if (!empty($user_id)) {
            $this->users->deleteInitUserPhone($user->phone_mobile);
            unset($_SESSION['user_modal_phone']);
        }

        $this->user_data->set($user_id, self::USERDATA_SHORT_FLOW, 1);

        return (int)$user_id;
    }

    private function getPassportIssued($issue_authority_code): string
    {
        if (empty($issue_authority_code)) {
            return '';
        }

        $issued = (new DadataService())->getPassportIssued($issue_authority_code);

        if (empty($issued['name'])) {
            return '';
        }

        return $issued['name'];
    }

    private function getAddressView(): string
    {
        $this->design->assign('equal', 1);

        $this->design->assign('regions', (new RegionService())->getRegions());
        $this->design->assign('factual_region', $_SESSION['user_info']['addresses'][1]['region'] ?? '');
        $this->design->assign('registration_region', $_SESSION['user_info']['addresses'][2]['region'] ?? '');

        $this->design->assign('residence_settlement', $_SESSION['user_info']['addresses'][1]['settlement'] ?? '');
        $this->design->assign('residence_city', $_SESSION['user_info']['addresses'][1]['city'] ?? '');
        $this->design->assign('residence_house', $_SESSION['user_info']['addresses'][1]['house'] ?? '');
        $this->design->assign('residence_street', $_SESSION['user_info']['addresses'][1]['street'] ?? '');
        $this->design->assign('residence_apartment', $_SESSION['user_info']['addresses'][1]['apartment'] ?? '');
        $this->design->assign('residence_building', $_SESSION['user_info']['addresses'][1]['building'] ?? '');
        $this->design->assign('residence_zipCode', $_SESSION['user_info']['addresses'][1]['zipCode'] ?? '');

        $this->design->assign('registration_settlement', $_SESSION['user_info']['addresses'][2]['settlement'] ?? '');
        $this->design->assign('registration_city', $_SESSION['user_info']['addresses'][2]['city'] ?? '');
        $this->design->assign('registration_house', $_SESSION['user_info']['addresses'][2]['house'] ?? '');
        $this->design->assign('registration_street', $_SESSION['user_info']['addresses'][2]['street'] ?? '');
        $this->design->assign('registration_apartment', $_SESSION['user_info']['addresses'][2]['apartment'] ?? '');
        $this->design->assign('registration_building', $_SESSION['user_info']['addresses'][2]['building'] ?? '');
        $this->design->assign('registration_zipCode', $_SESSION['user_info']['addresses'][2]['zipCode'] ?? '');

        return $this->design->fetch('account_address_data.tpl');
    }

    /**
     * Код из AccountView save_address_data()
     *
     * @param int $userId
     * @return void
     */
    private function save_address_data(int $userId)
    {
        $user = new StdClass();

        $user->Regindex = (string)$this->request->post('Regindex');
        $user->Regregion = (string)$this->request->post('Regregion');
        $user->Regcity = (string)$this->request->post('Regcity');
        $user->Regstreet = (string)$this->request->post('Regstreet');
        $user->Reghousing = (string)$this->request->post('Reghousing');
        $user->Regbuilding = (string)$this->request->post('Regbuilding');
        $user->Regroom = (string)$this->request->post('Regroom');
        $user->Regregion_shorttype = (string)$this->request->post('Regregion_shorttype');
        $user->Regcity_shorttype = (string)$this->request->post('Regcity_shorttype');
        $user->Regstreet_shorttype = (string)$this->request->post('Regstreet_shorttype');

        $user->Faktindex = (string)$this->request->post('Faktindex');
        $user->Faktregion = (string)$this->request->post('Faktregion');
        $user->Faktcity = (string)$this->request->post('Faktcity');
        $user->Faktstreet = (string)$this->request->post('Faktstreet');
        $user->Fakthousing = (string)$this->request->post('Fakthousing');
        $user->Faktbuilding = (string)$this->request->post('Faktbuilding');
        $user->Faktroom = (string)$this->request->post('Faktroom');
        $user->Faktregion_shorttype = (string)$this->request->post('Faktregion_shorttype');
        $user->Faktcity_shorttype = (string)$this->request->post('Faktcity_shorttype');
        $user->Faktstreet_shorttype = (string)$this->request->post('Faktstreet_shorttype');

        $equal = $this->request->post('equal', 'integer');
        $this->design->assign('equal', $equal);

        if ($equal) {
            $user->Faktindex = $user->Regindex;
            $user->Faktregion = $user->Regregion;
            $user->Faktcity = $user->Regcity;
            $user->Faktstreet = $user->Regstreet;
            $user->Fakthousing = $user->Reghousing;
            $user->Faktbuilding = $user->Regbuilding;
            $user->Faktroom = $user->Regroom;
            $user->Faktregion_shorttype = $user->Regregion_shorttype;
            $user->Faktcity_shorttype = $user->Regcity_shorttype;
            $user->Faktstreet_shorttype = $user->Regstreet_shorttype;
        }

        $this->design->assign('user', $user);

        if (empty($user->Regregion))
            $this->design->assign('error', 'empty_Regregion');
        elseif (empty($user->Regcity))
            $this->design->assign('error', 'empty_Regcity');
        elseif (empty($user->Faktregion))
            $this->design->assign('error', 'empty_Faktregion');
        elseif (empty($user->Faktcity))
            $this->design->assign('error', 'empty_Faktcity');
        else {
            try {
                $usersAddressService = new UsersAddressService();

                $registrationAddress = $usersAddressService->getRegistrationAddress($this->request);
                $factualAddress = $equal ? $registrationAddress : $usersAddressService->getFactualAddress($this->request);

                $user->registration_address_id = $usersAddressService->saveNewAddress($registrationAddress);
                $user->factual_address_id = $usersAddressService->saveNewAddress($factualAddress);

                $usersAddressService->saveOktmo($userId, $registrationAddress);
            } catch (Throwable $e) {
                $this->logging(json_encode($_SERVER), 'registrationAddress', $e->getMessage() . $e->getTraceAsString() . $e->getFile() . $e->getLine(), [$registrationAddress ?? [], $factualAddress ?? []], 'users_addresses.txt');
            }

            $user->address_data_added = 1;
            $user->address_data_added_date = date('Y-m-d H:i:s');
            $user->missing_real_date = date('Y-m-d H:i:s');

            $user = array_map('strip_tags', (array)$user);
            $this->users->update_user($userId, $user);
        }
    }

    /**
     * @return string
     * @see self::STAGE_WORK
     */
    private function workStage(): string
    {
        if ($this->request->method('post')) {

            // Добавление работы
            $this->saveWork();

            $this->short_flow->setRegisterStage((int)$this->user->id, self::STAGE_TIMER);
            $this->request->redirect('/register');
        }

        return $this->getWorkView();
    }

    /**
     * Обработка отправленной из `account_additional_data.tpl` формы
     * @see workStage
     */
    private function saveWork()
    {
        // Код из AccountView save_additional_data()
        $user = new StdClass();

        $user->profession = (string)$this->request->post('profession');
        $user->work_scope = (string)$this->request->post('work_scope');
        $user->workplace = (string)$this->request->post('workplace');
        $user->work_phone = (string)$this->request->post('work_phone');
        $user->workdirector_name = (string)$this->request->post('workdirector_name');
        $user->income_base = (string)$this->request->post('income_base');
        $user->education = (string)$this->request->post('education');

        $user->has_estate = (int)$this->request->post('has_estate');

        $user->Workindex = (string)$this->request->post('Regindex');
        $user->Workregion = (string)$this->request->post('Regregion');
        $user->Workcity = (string)$this->request->post('Regcity');
        $user->Workstreet = (string)$this->request->post('Regstreet');
        $user->Workhousing = (string)$this->request->post('Reghousing');
        $user->Workbuilding = (string)$this->request->post('Regbuilding');
        $user->Workroom = (string)$this->request->post('Regroom');
        $user->Workregion_shorttype = (string)$this->request->post('Regregion_shorttype');
        $user->Workcity_shorttype = (string)$this->request->post('Regcity_shorttype');
        $user->Workstreet_shorttype = (string)$this->request->post('Regstreet_shorttype');

        $user->additional_data_added = 1;
        $user->additional_data_added_date = date('Y-m-d H:i:s');

        $this->design->assign('user', $user);

        $user = array_map('strip_tags', (array)$user);
        $this->users->update_user($this->user->id, $user);
    }

    private function getWorkView(): string
    {
        $this->design->assign('work_full_address', '');

        $this->design->assign('is_short_flow', true);

        $this->design->assign('professions', \api\enums\ProfessionEnum::getAvailableValues());

//        $is_rejected_nk = $this->user_data->read($this->user->id, 'is_rejected_nk');
//        $is_flow_skipped = $this->user_data->read($this->user->id, 'rejected_nk_skipped');

        // Еще нет решения по бонону ИЛИ есть решение, что клиента нужно продать И клиента НЕ нужно пропускать
        // Отключено, т.к. раз заявка создается лишь после работы, то если бонон сработает на работе, то человека редиректит на прикрепление карты в бонон и заявка не создается, человека перебрасывает в лк без заявки. Поэтому на этапе работы пусть не перебрасывает в бонон
//        if ($is_rejected_nk === null || $is_rejected_nk === '1' && empty($is_flow_skipped)) {
//            $this->design->assign('check_scorings_nk', true);
//        }

        return $this->design->fetch('account_additional_data.tpl');
    }

    /**
     * @return string
     */
    private function cardStage(): string
    {
        $organization_id = $this->organizations->get_base_organization_id(['user_id' => $this->user->id]);
        $this->design->assign('organization_id', $organization_id);
        $b2p_enabled = ($this->settings->b2p_enabled || $this->user->use_b2p) ? 1 : 0;
        if (empty($b2p_enabled)) {
            $this->users->update_user($this->user->id, ['accept_data_added' => 0]);
            header('Location: ' . $this->config->root_url . '/account');
            exit;
        }

        $cards = $this->best2pay->get_cards([
            'user_id' => $this->user->id,
            'organization_id' => $organization_id,
        ]);

        if (!empty($cards)) {
            $reset_card = reset($cards);
            $order_card_id = $reset_card->id;
            $_SESSION['yandex_target'] = 1;

            if ($last_order = $this->orders->get_last_order($this->user->id)) {
                $this->orders->update_order($last_order->id, [
                    'date' => date('Y-m-d H:i:s'),
                    'card_id' => $order_card_id,
                    'b2p' => $b2p_enabled,
                    'organization_id' => $organization_id,
                ]);
            }

            $this->short_flow->setRegisterStage($this->user->id, self::STAGE_FINAL);
            return $this->request->redirect('/register');
        }

        // Отображаем стандартную привязку, но с изменениями под короткое флоу
        $this->design->assign('is_short_flow', true);

        $bki_consent = $this->user_data->read($this->user->id, 'bki_consent');
        $this->design->assign('bki_consent', $bki_consent);

        return $this->design->fetch('account_card_data.tpl');
    }

    /**
     * @return string
     */
    function timerStage(): string
    {
        $bonon_enabled = $this->settings->bonon_enabled;
        $is_rejected_nk = $this->user_data->read((int)$this->user->id, 'is_rejected_nk');
        $is_flow_skipped = $this->user_data->read((int)$this->user->id, 'rejected_nk_skipped');

        // Бонон включен И решено продавать в бонон -> экран прикрепления карты в бонон
        if (!empty($is_rejected_nk) && $is_rejected_nk === '1') {
            $this->short_flow->setRegisterStage($this->user->id, self::STAGE_BONON);
            return $this->request->redirect('/register');
        }

        // Бонон выключен ИЛИ клиента НЕ надо продавать в бонон ИЛИ клиент пропустил проверку бонона -> на экран регистрации карты
        else if (empty($bonon_enabled) || (empty($is_rejected_nk) && $is_rejected_nk === '0') || !empty($is_flow_skipped)) {
            $this->short_flow->setRegisterStage($this->user->id, self::STAGE_CARD);
            return $this->request->redirect('/register');
        }

        // Отображаем стандартный таймер, но с изменениями под короткое флоу
        $this->design->assign('is_short_flow', true);
        return $this->design->fetch('account_wait_scorings.tpl');
    }

    /**
     * @return string
     */
    private function bononStage(): string
    {
        $bonon_enabled = $this->settings->bonon_enabled;
        $is_rejected_nk = $this->user_data->read($this->user->id, 'is_rejected_nk') ?? 0;
        $rejected_nk_visited = $this->user_data->read($this->user->id, 'rejected_nk_visited') ?? 0;
        if (empty($bonon_enabled) || $is_rejected_nk != 1 || $rejected_nk_visited == 1) {
            $this->short_flow->setRegisterStage($this->user->id, self::STAGE_CARD);
            return $this->request->redirect('/register');
        }

        // Отказной НК - отображаем привязку карты к сервису партнёра
        $partner_url = $this->user_data->read($this->user->id, 'rejected_nk_url');
        if (empty($partner_url)) {
            $last_order = $this->orders->get_last_order($this->user->id);
            if (empty($last_order)) {
                // Заявка ещё не создана (Такого не должно быть), возвращаем в стандартный короткий флоу
                $this->user_data->set($this->user->id, 'is_rejected_nk', 0);
                $this->short_flow->setRegisterStage($this->user->id, self::STAGE_CARD);
                return $this->request->redirect('/register');
            }

            $partner_url = $this->bonondo->createClientUrlForOrder($last_order, $this->bonondo::NK_POSTFIX);
            if (empty($partner_url)) {
                // Не удалось создать ссылку, логируем и возвращаем клиента в короткий флоу
                $this->logging('Empty partner url', 'User: ' . $this->user->id, $last_order, '', 'bonondo_page.txt');

                $this->user_data->set($this->user->id, 'is_rejected_nk', 0);
                $this->short_flow->setRegisterStage($this->user->id, self::STAGE_CARD);
                return $this->request->redirect('/register');
            }

            $this->user_data->set($this->user->id, 'rejected_nk_url', $partner_url);
        }

        $this->design->assign('partner_url', $partner_url);
        return $this->design->fetch('account_partner_card_data.tpl');
    }
}
