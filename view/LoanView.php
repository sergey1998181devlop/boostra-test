<?php

require_once 'View.php';

class LoanView extends View
{
    public function __construct()
    {
        parent::__construct();
        
        if (!empty($this->user->id)) {
            $this->request->redirect('/user');
            exit;
        }
    }
    
    public function fetch()
    {
    	if ($this->request->method('post'))
        {

            $user = new StdClass();
            
            $user->firstname = mb_convert_case(trim(strip_tags($this->request->post('firstname'))), MB_CASE_TITLE);
            $user->lastname = mb_convert_case(trim(strip_tags($this->request->post('lastname'))), MB_CASE_TITLE);
            $user->patronymic = mb_convert_case(trim(strip_tags($this->request->post('patronymic'))), MB_CASE_TITLE);
            
            $user->email = (string)trim($this->request->post('email'));
            $user->phone_mobile = trim(strip_tags($_SESSION['user_modal_phone'] ?? $_COOKIE['init_user_phone']));
            $code = trim(strip_tags($this->request->post('code')));
            if ((string)$this->request->post('birthday')) {
                $birthday = (new \DateTime(strip_tags((string)$this->request->post('birthday'))))->format('d.m.Y');
                $user->birth = $birthday;
            }
//            $user->birth = strip_tags((string)$this->request->post('birthday'));

            $user->service_recurent = $this->request->post('service_recurent', 'integer');
            $user->service_sms = $this->request->post('service_sms', 'integer');
            $user->service_insurance = $this->request->post('service_insurance', 'integer');
            $user->service_reason = $this->request->post('service_reason', 'integer');
            $user->service_doctor = $this->request->post('service_doctor', 'integer');

            $user->first_loan_amount = $this->request->post('amount', 'integer');
            $user->first_loan_period = $this->request->post('period', 'integer');

            if ($user->first_loan_period > Orders::MAX_PERIOD_FIRST_LOAN) {
                $user->first_loan_period = Orders::MAX_PERIOD_FIRST_LOAN;
            }
            
           // $accept = $this->request->post('accept', 'integer');
            $accept = 1;
            
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

            $this->design->assign('accept', $accept);

            if (empty($user->firstname))
            {
                $this->design->assign('error', 'empty_firstname');
            }
            elseif (empty($user->lastname))
            {
                $this->design->assign('error', 'empty_lastname');
            }
            elseif (!empty($user->email) && !preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/", $user->email))
            {
                $this->design->assign('error', 'invalid_email');
            }
            elseif (empty($user->patronymic) && empty($this->request->post('not_patronymic')))
            {
                $this->design->assign('error', 'empty_patronymic');
            }
            elseif (empty($user->birth))
            {
                $this->design->assign('error', 'empty_birth');
            }
            elseif (empty($user->phone_mobile))
            {
                $this->design->assign('error', 'empty_phone');
            }
//            elseif (empty($code) || $code != $_SESSION['sms'] || $this->users->clear_phone($user->phone_mobile) != $_SESSION['phone'])
//            {
//                $this->design->assign('error', 'error_code');
//            }
            elseif (empty($accept))
            {
                $this->design->assign('error', 'empty_accept');
            }
            else
            {
                // проверяем и регистрируем
                $soap = $this->soap->get_uid_by_phone($user->phone_mobile);
                if (!empty($soap->result) && !empty($soap->uid))
                {
                    $this->design->assign('error', 'user_exists');
                }
                elseif ($soap->error == 'Множество совпадений')
                {
                    $this->soap->send_doubling_phone($phone);
                    
                    $this->design->assign('error', 'user_blocked');
                }
                elseif ($exist_user = $this->users->get_user((string)$user->phone_mobile))
                {
                    $this->design->assign('error', 'user_exists');
                }
                else
                {
                    $user->sms = $_SESSION['sms'] ?? $_SESSION['init_user_code'];
                    $user->reg_ip = $_SERVER['REMOTE_ADDR'];
                    $user->enabled = 1;
                    $user->first_loan = 1;
                    $user->personal_data_added = 0;
                    $user->additional_data_added = 0;
                    $user->accept_data_added = 0;
                    $user->address_data_added = 0;
                    $user->files_added = 0;
                    $user->card_added = 0;
                    $user->created = date('Y-m-d H:i:s');
                    $user->missing_real_date   = date('Y-m-d H:i:s');

                    $user->utm_source = empty($_COOKIE["utm_source"]) ? 'Boostra' : strip_tags($_COOKIE["utm_source"]);
    				$user->utm_medium = empty($_COOKIE["utm_medium"]) ? 'Site' : strip_tags($_COOKIE["utm_medium"]);
                    $user->utm_campaign = empty($_COOKIE["utm_campaign"]) ? 'C1_main' : strip_tags($_COOKIE["utm_campaign"]);
                    $user->utm_content = empty($_COOKIE["utm_content"]) ? '' : strip_tags($_COOKIE["utm_content"]);
                    $user->utm_term = empty($_COOKIE["utm_term"]) ? '' : strip_tags($_COOKIE["utm_term"]);
                    $user->webmaster_id = empty($_COOKIE["webmaster_id"]) ? '' : strip_tags($_COOKIE["webmaster_id"]);
                    $user->click_hash = empty($_COOKIE["click_hash"]) ? '' : strip_tags($_COOKIE["click_hash"]);
                    
                    if ($this->settings->enable_b2p_for_nk || ($user->utm_source == 'sms' && $user->webmaster_id == '1315'))
                        $user->use_b2p = 1;
                    
                    $user_id = $this->users->add_user($user);

                    // Метчим user_id и _ym_uid
                    if ($_COOKIE["_ym_uid"]) {
                        $this->custom_metric->addYmLog($user_id, $_COOKIE["_ym_uid"]);
                    }

                    if (!empty($user_id)) {
                        $this->users->deleteInitUserPhone($user->phone_mobile);
                        unset($_SESSION['user_modal_phone']);
                        $_SESSION['user_id'] = $user_id;
                        setcookie('user_id', $user_id, time() + 86400 * 365, '/');

                        $isVirtualCardEnabled = $this->settings->vc_enabled
                            && isset($_COOKIE['utm_campaign'])
                            && $_COOKIE['utm_campaign'] === 'vctest'
                            && !empty($_SESSION["virtual_card"]);

                        $this->user_data->set($user_id, "is_virtual_card_consent", $isVirtualCardEnabled ? 1: 0);

                        // Запуск прескорингов для проверки необходимости продажи карты отказного клиента
                        $this->scorings->add_scoring([
                            'user_id' => $user_id,
                            'type' => $this->scorings::TYPE_AGE,
                        ]);

                        unset($_SESSION['virtual_card']);
                    }

                    \api\helpers\UserHelper::getJWTToken($this->config->jwt_secret_key, $user_id, 'auth_jwt_token', $this->config->jwt_expiration_time, true);

                    $this->users->initAutoConfirmNewUser($user_id, $user);
                    $this->users->initAutoConfirm2NewUser($user_id, $user);

                    header('Location: '.$this->config->root_url . '/account');
                }
            }
        }
        else
        {
            
            if (!($amount = $this->request->get('amount', 'integer')))
                $amount = 5000;
            if (!($period = $this->request->get('period', 'integer')))
                $period = 7;

            if (empty($_SESSION['user_modal_phone']) && empty($_COOKIE['init_user_phone'])) {
                $link = '/init_user?'.http_build_query([
                    'amount' => $amount,
                    'period' => $period,
                ]);
                $this->request->redirect($link);
            }

            $this->design->assign('firstname', $_SESSION['user_info']['given_name'] ?? '');
            $this->design->assign('lastname', $_SESSION['user_info']['family_name'] ?? '');
            $this->design->assign('patronymic', $_SESSION['user_info']['middle_name'] ?? '');
            $this->design->assign('birth', isset($_SESSION['user_info']['birthdate']) && $_SESSION['user_info']['birthdate'] !== '' ? date('d.m.Y', strtotime($_SESSION['user_info']['birthdate'])) : '');
            $this->design->assign('amount', $amount);
            $this->design->assign('period', $period);
            $this->design->assign('user_modal_phone', $_SESSION['user_modal_phone'] ?? '');
            $this->design->assign('phone', $_SESSION['phone'] ?? '');
        }

        $this->design->assign('captcha_status', (bool)$this->settings->captcha_status);

        return $this->design->fetch('loan.tpl');
    }
}