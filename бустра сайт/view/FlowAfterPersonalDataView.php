<?php

require_once 'View.php';

/**
 * Class FlowAfterPersonalDataView
 * This class represents the view handling post-personal data submission process in a loan application flow.
 * It manages multiple steps such as user information processing, personal data validation, and registration handling.
 */
class FlowAfterPersonalDataView extends View
{
    private string $step = '';
    private $amount;
    private $period;
    private string $template = 'loan.tpl';
    const STEP_FIO = 'fio';
    const STEP_PERSONAL_DATA = 'personal-data';
    const STEP_REGISTRATION = 'registration';
    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {
        $this->initStepAction();
        return $this->design->fetch($this->template);
    }

    /**
     * Обработка входных главных параметров
     * @return void
     */
    private function init()
    {
        $this->step = $this->request->get('step', 'string');
        $this->amount = $_COOKIE['calc_amount'] ?: $this->orders::MAX_AMOUNT_FIRST_LOAN;
        $this->period = $_COOKIE['calc_period'] ?: $this->orders::MAX_PERIOD_FIRST_LOAN;
    }

    /**
     * Обработка каждого нового шага
     * @return void
     * @throws Exception
     */
    private function initStepAction()
    {
        $this->validateStep();

        switch ($this->step) {
            case self::STEP_FIO:
                $this->stepFioAction();
                break;
            case self::STEP_PERSONAL_DATA:
                $this->stepPersonalDataAction();
                break;
            case self::STEP_REGISTRATION:
                $this->stepRegistrationAction();
                break;
            default:
                $this->request->redirect($this->config->root_url);
                break;
        }
    }

    /**
     * Шаг с ФИО
     * @return void
     * @throws Exception
     */
    private function stepFioAction()
    {
        $user = new StdClass();
        $user->firstname = mb_convert_case(trim(strip_tags($this->getUserField('firstname'))), MB_CASE_TITLE);
        $user->lastname = mb_convert_case(trim(strip_tags($this->getUserField('lastname'))), MB_CASE_TITLE);
        $user->patronymic = mb_convert_case(trim(strip_tags($this->getUserField('patronymic'))), MB_CASE_TITLE);
        $user->email = trim($this->getUserField('email'));

        if ($this->getUserField('birthday')) {
            $birthday = (new \DateTime(strip_tags($this->getUserField('birthday'))))->format('d.m.Y');
            $user->birth = $user->birthday = $birthday;
        }

        $user->service_recurent = $this->getUserField('service_recurent');
        $user->service_sms = $this->getUserField('service_sms');
        $user->service_insurance = $this->getUserField('service_insurance');
        $user->service_reason = $this->getUserField('service_reason');
        $user->service_doctor = $this->getUserField('service_doctor');
        $user->first_loan_amount = $this->getUserField('amount');
        $user->first_loan_period = $this->getUserField('period');

        if ($user->first_loan_period > Orders::MAX_PERIOD_FIRST_LOAN) {
            $user->first_loan_period = Orders::MAX_PERIOD_FIRST_LOAN;
        }

        $this->design->assign('firstname', $user->firstname);
        $this->design->assign('lastname', $user->lastname);
        $this->design->assign('patronymic', $user->patronymic);
        $this->design->assign('email', $user->email);
        $this->design->assign('phone', $user->phone_mobile);
        $this->design->assign('birth', $user->birth);
        $this->design->assign('amount', $user->first_loan_amount);
        $this->design->assign('period', $user->first_loan_period);
        $this->design->assign('service_sms', $user->service_sms);
        $this->design->assign('service_insurance', $user->service_insurance);
        $this->design->assign('service_reason', $user->service_reason);
        $this->design->assign('service_doctor', $user->service_doctor);

        if ($this->request->method('post')) {
            $this->validateStepFio($user);

            if (!$this->design->get_var('error')) {
                $this->updateUserData($user);
                $this->nextStep(self::STEP_PERSONAL_DATA);
            }
        } else {
            $_SESSION['amount'] = $this->request->get('amount', 'integer');
            $_SESSION['period'] = $this->request->get('period', 'integer');
        }

        $this->template = 'loan.tpl';
    }

    /**
     * Ввод паспортных
     * @return void
     */
    private function stepPersonalDataAction()
    {
        $user = new StdClass();

        $user->passport_serial = $this->getUserField('passportCode');
        $user->passport_date = $this->getUserField('passportDate');
        $user->subdivision_code = $this->getUserField('subdivisionCode');
        $user->passport_issued = mb_convert_case($this->getUserField('passportWho'), MB_CASE_UPPER);
        $user->passport_date = $this->users->tryFormatDate($user->passport_date);
        $user->gender = $this->getUserField('gender');
        $user->birth_place = mb_convert_case($this->getUserField('birth_place'), MB_CASE_UPPER);

        $this->design->assign( 'user', $user );

        if ($this->request->method('post')) {
            $this->validateStepPersonalData($user);

            if (!$this->design->get_var('error')) {
                $this->updateUserData($user);
                $_SESSION['flow_after_personal_data_step_register'] = 1;
                $this->request->redirect($this->config->root_url . '/init_user?' . http_build_query(['period' => $this->period, 'amount' => $this->amount]));
            }
        }

        $this->template = 'account_personal_data.tpl';
    }

    /**
     * Фоновая регистрация пользователя
     * @return void
     * @throws Exception
     */
    private function stepRegistrationAction()
    {
        $user = new StdClass();
        $user->firstname = mb_convert_case(trim(strip_tags($this->getUserField('firstname'))), MB_CASE_TITLE);
        $user->lastname = mb_convert_case(trim(strip_tags($this->getUserField('lastname'))), MB_CASE_TITLE);
        $user->patronymic = mb_convert_case(trim(strip_tags($this->getUserField('patronymic'))), MB_CASE_TITLE);
        $user->email = trim($this->getUserField('email'));

        if ($this->getUserField('birth')) {
            $birthday = (new \DateTime(strip_tags($this->getUserField('birth'))))->format('d.m.Y');
            $user->birth = $birthday;
        }

        $user->service_recurent = $this->getUserField('service_recurent');
        $user->service_sms = $this->getUserField('service_sms');
        $user->service_insurance = $this->getUserField('service_insurance');
        $user->service_reason = $this->getUserField('service_reason');
        $user->service_doctor = $this->getUserField('service_doctor');
        $user->first_loan_amount = $this->getUserField('amount');
        $user->first_loan_period = $this->getUserField('period');
        $user->sms = $_SESSION['sms'] ?? $_SESSION['init_user_code'];
        $user->reg_ip = $_SERVER['REMOTE_ADDR'];
        $user->enabled = 1;
        $user->first_loan = 1;
        $user->additional_data_added = 0;
        $user->accept_data_added = 0;
        $user->address_data_added = 0;
        $user->files_added = 0;
        $user->card_added = 0;
        $user->created = date('Y-m-d H:i:s');

        $user->utm_source = empty($_COOKIE["utm_source"]) ? 'Boostra' : strip_tags($_COOKIE["utm_source"]);
        $user->utm_medium = empty($_COOKIE["utm_medium"]) ? 'Site' : strip_tags($_COOKIE["utm_medium"]);
        $user->utm_campaign = empty($_COOKIE["utm_campaign"]) ? 'C1_main' : strip_tags($_COOKIE["utm_campaign"]);
        $user->utm_content = empty($_COOKIE["utm_content"]) ? '' : strip_tags($_COOKIE["utm_content"]);
        $user->utm_term = empty($_COOKIE["utm_term"]) ? '' : strip_tags($_COOKIE["utm_term"]);
        $user->webmaster_id = empty($_COOKIE["webmaster_id"]) ? '' : strip_tags($_COOKIE["webmaster_id"]);
        $user->click_hash = empty($_COOKIE["click_hash"]) ? '' : strip_tags($_COOKIE["click_hash"]);

        $user->personal_data_added = 1;
        $user->personal_data_added_date = date('Y-m-d H:i:s');
        $user->missing_real_date = date('Y-m-d H:i:s');

        $user->passport_serial = $this->getUserField('passport_serial');
        $user->passport_date = $this->getUserField('passport_date');
        $user->subdivision_code = $this->getUserField('subdivision_code');
        $user->passport_issued = $this->getUserField('passport_issued');
        $user->passport_date = $this->getUserField('passport_date');
        $user->gender = $this->getUserField('gender');
        $user->birth_place = $this->getUserField('birth_place');
        $user->phone_mobile = $this->getUserField('phone_mobile');

        if ($this->settings->enable_b2p_for_nk || ($user->utm_source == 'sms' && $user->webmaster_id == '1315')) {
            $user->use_b2p = 1;
        }

        $user_id = $this->users->add_user($user);

        if (!empty($user_id)) {
            $this->users->deleteInitUserPhone($user->phone_mobile);
            $this->users->clearSessionDataFlowAfterPersonalData();

            $_SESSION['user_id'] = $user_id;
            setcookie('user_id', $user_id, time() + 86400 * 365, '/');

            $this->addScoring((int)$user_id);
            \api\helpers\UserHelper::getJWTToken($this->config->jwt_secret_key, $user_id, 'auth_jwt_token', $this->config->jwt_expiration_time, true);

            $this->request->redirect($this->config->root_url . '/account');
        }

        $this->request->redirect($this->config->root_url . '/init_user?' . http_build_query(['period' => $this->period, 'amount' => $this->amount]));
    }

    /**
     * Валидация формы ФИО
     * @param $user
     * @return void
     */
    private function validateStepFio($user)
    {
        if (empty($user->firstname)) {
            $this->design->assign('error', 'empty_firstname');
        } elseif (empty($user->lastname)) {
            $this->design->assign('error', 'empty_lastname');
        } elseif (empty($user->patronymic)) {
            $this->design->assign('error', 'empty_patronymic');
        } elseif (empty($user->birthday)) {
            $this->design->assign('error', 'empty_birth');
        }
    }

    /**
     * @param $user
     * @return void
     */
    private function validateStepPersonalData($user)
    {
        $passport_user_id = (int)$this->users->get_passport_user($user->passport_serial);
        if (empty($this->is_admin) && !empty($passport_user_id)) {
            // Шифруем телефон
            $existing_user = $this->users->get_user($passport_user_id);
            $existing_user->phone_mobile_obfuscated = preg_replace(
                '@(\d{3})\d*(\d{3})@',
                '+$1****$2',
                $existing_user->phone_mobile
            );

            $this->design->assign('error', 'allready_exists');
            $this->design->assign('existing_user', $existing_user);
        } elseif (empty($user->passport_serial) || !$this->helpers::validatePassport($user->passport_serial)) {
            $this->design->assign('error', 'empty_passportCode');
        } elseif (empty($user->passport_date)) {
            $this->design->assign('error', 'empty_passportDate');
        } elseif (empty($user->subdivision_code)) {
            $this->design->assign('error', 'empty_subdivisionCode');
        } elseif (empty($user->passport_issued)) {
            $this->design->assign('error', 'empty_passportWho');
        } elseif (empty($user->gender)) {
            $this->design->assign('error', 'empty_gender');
        } elseif (empty($user->birth_place)) {
            $this->design->assign('error', 'empty_birth_place');
        }
    }

    /**
     * @param $user
     * @return void
     */
    private function updateUserData($user)
    {
        $userData = $_SESSION['user_info'] ?? [];
        $_SESSION['user_info'] = array_merge($userData, (array)$user);
    }

    /**
     * @param string $key
     * @return bool|int|mixed|string|null
     */
    private function getUserField(string $key)
    {
        return $this->request->post($key) ?: (isset($_SESSION['user_info']) ? ($_SESSION['user_info'][$key] ?? '') : '');
    }

    /**
     * @param string $step
     * @param array $params
     * @return void
     */
    private function nextStep(string $step, array $params = [])
    {
        if (!empty($params)) {
            $postfix = $step . '/?' . http_build_query($params);
        } else {
            $postfix = $step;
        }

        $this->request->redirect($this->config->root_url . '/flow_after_personal_data/' . $postfix);
    }

    /**
     * @param int $user_id
     * @return void
     */
    private function addScoring(int $user_id)
    {
        foreach ($this->getScoringList() as $scoring_type) {
            $this->scorings->add_scoring(
                [
                    'user_id' => $user_id,
                    'type' => $scoring_type,
                ]
            );
        }
    }

    /**
     * @return array
     */
    private function getScoringList(): array
    {
        return array_merge(Scorings::getScoringListAfterPersonalData(), Scorings::getScoringListAfterNewOrder());
    }

    /**
     * Проверяет шаг и делает редирект
     */
    private function validateStep()
    {
        if (!empty($_SESSION['user_id'])) {
            $this->request->redirect($this->config->root_url . '/account');
        }

        if (!empty($_SESSION['user_info']['passport_serial']) && $this->step === self::STEP_PERSONAL_DATA) {
            $this->request->redirect($this->config->root_url . '/init_user?' . http_build_query(['period' => $this->period, 'amount' => $this->amount]));
        }

        if (!empty($_SESSION['user_info']['firstname']) && $this->step === self::STEP_FIO) {
            $this->request->redirect($this->config->root_url . '/flow_after_personal_data/' . self::STEP_PERSONAL_DATA);
        }
    }
}
