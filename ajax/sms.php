<?php

use api\helpers\UserHelper;

error_reporting(0);
ini_set('display_errors', 'Off');

session_start();
chdir('..');
define('ROOT', dirname(__DIR__));

function clear_phone($phone)
{
    return str_replace(array('-', '+', '(', ')', ' '), '', $phone);
}

require_once 'api/Simpla.php';
require_once 'api/CreditDoctor.php';
require_once 'api/CreditRating.php';

$simpla = new Simpla();
$result = array();

const SMS_DELAY_DEFAULT = 30;
const SMS_DELAY_SECOND_ATTEMPT = 60;
const SMS_DELAY_THIRD_ATTEMPT = 300;

$phone = $simpla->request->get('phone');

$flag = $simpla->request->get('flag');
$forced = $simpla->request->get('forced', 'integer');

/**
 * @return int
 */
function defaultCheckSms(): int
{
    $simpla = $GLOBALS['simpla'];
    $phone = $simpla->users->clear_phone($simpla->request->get('phone'));
    $order_id = $simpla->request->get('order_id');
    $code = $simpla->request->get('code');

    if ($order_id) {
        $condition = $simpla->orders->check_sms($order_id, $code);
    } else {
        $condition = $code == $_SESSION['sms'];
    }

//        $result['phone'] = $_SESSION['phone'];
//        $result['code'] = $_SESSION['sms'];
//        $result['session_phone'] = $phone;
//        $result['session_code'] = $code;
    return (int)(isset($_SESSION['phone'], $_SESSION['sms']) && $phone == $_SESSION['phone'] && $condition);
}


function creteAspAdditionalDoc($user_id, \Simpla $simpla, $sms)
{
    $user = $simpla->users->get_user((int)$user_id);
    $response_balances = $simpla->soap->get_user_balances_array_1c($user->uid);
    foreach ($response_balances as $response) {
        $utc_payment_date = strtotime($response['ПланДата']);
        $utc_now = strtotime(date('Y-m-d 00:00:00'));
        $zaimNumber = $response['НомерЗайма'];
        if ($utc_now > $utc_payment_date && !($simpla->users->getZaimAspStatus($zaimNumber))) {
            $fileName = 'asp_zaim_' . $response['НомерЗайма'] . '.pdf';
            $simpla->users->addZaimToAsp($zaimNumber, (int)$_SESSION['sms'], $user_id,$fileName);
            $simpla->notification_center->signAdditionalCommunicationDoc($zaimNumber);
            $simpla->docs->get_asp_zaim_pdf($user, $zaimNumber);
            $array_soap_asp = [
                'НомерЗайма' => $zaimNumber,
                'АСП' => $sms,
                'ДатаАСП' => date('YmdHis'),
            ];
            $object_soap = $simpla->soap->generateObject($array_soap_asp);
            $simpla->soap->requestSoap($object_soap, 'WebLK', 'DelayExit');
        }
    }
}

/**
 * Преобразует цифры в слова, например:
 *
 * 2191 - два один девять один
 *
 * 8230 - восемь два три ноль
 * @param int|string $number
 * @return string
 */
function numberToWords($number) {
    $digits = [
        '0' => 'ноль',
        '1' => 'один',
        '2' => 'два',
        '3' => 'три',
        '4' => 'четыре',
        '5' => 'пять',
        '6' => 'шесть',
        '7' => 'семь',
        '8' => 'восемь',
        '9' => 'девять'
    ];

    // Преобразуем число в строку и разбиваем на массив цифр
    $chars = str_split((string)$number);

    // Преобразуем каждую цифру в слово
    $words = array_map(function($digit) use ($digits) {
        return $digits[$digit];
    }, $chars);

    // Склеиваем слова в строку через пробел
    return implode(' ', $words);
}

switch ($simpla->request->get('action')):
    
    case 'send':
        $order_id = $simpla->request->get('order_id', 'integer');

        $send_count = $_SESSION['sms_send_count'] ?? 1;
        
        $current_delay = SMS_DELAY_DEFAULT;
        if ($send_count == 2) {
            $current_delay = SMS_DELAY_SECOND_ATTEMPT;
        } elseif ($send_count >= 3) {
            $current_delay = SMS_DELAY_THIRD_ATTEMPT;
        }
        
        if (!$forced && !empty($_SESSION['sms_time']) && ($_SESSION['sms_time'] + $current_delay) > time())
        {
            $result['error'] = 'sms_time';
            $result['time_left'] = $_SESSION['sms_time'] + $current_delay - time();
        }
        else
        {
            if (empty($_SESSION['asp_code_already_sent'])) {
                $rand_code = mt_rand(1000, 9999);

                $_SESSION['sms'] = $rand_code;
                if ($order_id) {
                    $simpla->orders->update_order($order_id, ['accept_sms' => $rand_code]);
                }
            }
            $_SESSION['phone'] = clear_phone($phone);

            if (!empty($simpla->is_developer) || !empty($simpla->is_admin))
            {
                $result['mode'] = 'developer';
                $result['developer_code'] = $_SESSION['sms'];
                
            }
            else
            {
                if (empty($_SESSION['asp_code_already_sent'])) {
                    if ($flag == 'АСП') {
                        //$msg = 'Ваш АСП: ' . $_SESSION['sms'];
                        $msg = 'Код - ' . numberToWords($_SESSION['sms']);
                    } else if ($flag == 'autoconfirm') {
                        $msg = 'Ваш АСП: ' . $_SESSION['sms'];
                    } else {
                        $msg = $_SESSION['sms'];
                    }

                    $msg = iconv('utf8', 'cp1251', $msg);

                    if ($phone != '79051158610') {
                        $result['response'] = $simpla->notify->send_sms($phone, $msg);
                    } else {
                        $result['response'] = false;
                    }
                }

                $result['mode'] = 'standart';
            }

            if ($result['response'] !== false || !empty($simpla->is_developer) || !empty($simpla->is_admin)) {
                if (!isset($_SESSION['sms_send_count'])) {
                    $_SESSION['sms_send_count'] = 1;
                } else {
                    $_SESSION['sms_send_count'] = min($_SESSION['sms_send_count'] + 1, 3);
                }
            }
            
            $_SESSION['sms_time'] = time();

            $result['success'] = true;
            if (empty($_SESSION['sms_time']))
                $result['time_left'] = 0;
            else
                $result['time_left'] = ($_SESSION['sms_time'] + $current_delay) - time();

            if (!empty($_SESSION['asp_code_already_sent'])) {
                unset($_SESSION['asp_code_already_sent']);
            }
        }
        
    break;

    case 'credit_rating_send':
        $user_id = (int)$_SESSION['user_id'];
        $user = $simpla->users->get_user($user_id);
        $result = $simpla->credit_rating->send_credit_rating_sms($user);
    break;

    case 'user_credit_doctor_send':
        $user_id = (int)$_SESSION['user_id'];
        $user = $simpla->users->get_user($user_id);
        $result = $simpla->user_credit_doctor->send_sms($user);
    break;

    case 'user_credit_doctor_check':
        $code = $simpla->request->post('code');
        $sms = $_SESSION[$simpla->user_credit_doctor::SMS_SESSION_KEY];
        $result['success'] = (int)($sms == $code);
    break;

    case 'refinance_send':
        if (empty($_SESSION['user_id'])) {
            $result['success'] = 0;
            $result['error'] = 'Ошибка авторизации';
            break;
        }

        $userId = (int)$_SESSION['user_id'];
        $user = $simpla->users->get_user($userId);

        if (!$user) {
            $result['success'] = 0;
            $result['error'] = 'Пользователь не найден';
            break;
        }

        $result = $simpla->refinance->send_refinance_sms($user);
    break;

    case 'refinance_check':
        $code = $simpla->request->post('code');
        
        // Валидация входящего кода
        if (empty($code) || !is_string($code) || strlen($code) !== 4 || !ctype_digit($code)) {
            $result['success'] = 0;
            $result['error'] = 'Неверный формат кода';
            break;
        }
        
        // Проверка существования SMS в сессии
        if (!isset($_SESSION[$simpla->refinance::SMS_SESSION_KEY])) {
            $result['success'] = 0;
            $result['error'] = 'Код не найден. Запросите новый код';
            break;
        }
        
        $sms = $_SESSION[$simpla->refinance::SMS_SESSION_KEY];
        
        // Инициализация счетчика попыток
        if (!isset($_SESSION['refinance_check_attempts'])) {
            $_SESSION['refinance_check_attempts'] = 0;
        }
        
        // Проверка лимита попыток (максимум 3 попытки)
        $maxAttempts = 3;
        if ($_SESSION['refinance_check_attempts'] >= $maxAttempts) {
            $result['success'] = 0;
            $result['error'] = 'Превышено количество попыток. Запросите новый код';
            // Сброс SMS кода при превышении лимита
            unset($_SESSION[$simpla->refinance::SMS_SESSION_KEY]);
            unset($_SESSION['refinance_check_attempts']);
            break;
        }
        
        // Увеличение счетчика попыток
        $_SESSION['refinance_check_attempts']++;
        
        $result['success'] = $sms == $code;
        if ($result['success']) {
            unset($_SESSION[$simpla->refinance::SMS_SESSION_KEY]);
            unset($_SESSION['refinance_check_attempts']);
        } else {
            $result['success'] = 0;
            $attempts_left = $maxAttempts - $_SESSION['refinance_check_attempts'];
            $result['error'] = 'Код не совпадает. Осталось попыток: ' . $attempts_left;
            
            // Если попытки закончились, очищаем SMS код
            if ($attempts_left <= 0) {
                unset($_SESSION[$simpla->refinance::SMS_SESSION_KEY]);
                unset($_SESSION['refinance_check_attempts']);
                $result['error'] = 'Превышено количество попыток. Запросите новый код';
            }
        }
    break;

    case 'check_login':
        $result['success'] = defaultCheckSms();

        $phone = $simpla->users->clear_phone($simpla->request->get('phone'));
        $result_validate_sms_auth = $simpla->sms_auth_validate_sms->validateSms($phone, $simpla->sms_auth_validate_sms::TYPE_LOGIN);

        if (!$result_validate_sms_auth['success']) {
            $result['success'] = false;
            $result['error'] = $result_validate_sms_auth['error'];
        }

        if ($result['success']) {
            if ($user = $simpla->users->get_user($phone))
            {
                $user_id = $user->id;
                if (empty($_SERVER['HTTP_REFERER']))
                {
                    $result['error'] = 'Обновите страницу и попробуйте еще раз';
                }
                elseif (Helpers::isBlockedUserBy1C($simpla, $phone)) {
                    $simpla->users->update_user($user_id, ['blocked' => 1]);
                    $result['error'] = 'Пользователь заблокирован.';
                }
                else
                {
                    $result['user_id'] = $user_id;

                    $result['success'] = true;

                    if (isset($_SESSION['time']) && isset($_SESSION['user_ip'])) {
                        $simpla->users->updateSessionData($_SESSION['time'],time(),$_SESSION['user_ip']);
                        $_SESSION['time'] = time();
                        $simpla->users->update_loan_funnel_report($_SESSION['time'],$_SESSION['user_ip'],false,[
                            'user_id' => $user_id,
                            'login' => true,
                            'login_date' => date("Y-m-d")
                        ]);
                    }

                    if (!empty($user->uid))
                    {
                        // отправим информацию об входе в ЛК в 1С
                        $simpla->soap->add_client_authorized($user->uid);
                    }

                    if ($simpla->request->post('page_action') === 'send_complaint') {
                        $result['redirect_url'] .= '?page_action=open_feedback';
                    }

                    $jwt = UserHelper::getJWTToken($simpla->config->jwt_secret_key, $user_id, 'auth_jwt_token', $simpla->config->jwt_expiration_time, true);
                    $result['auth_jwt_token'] = $jwt;

                    $_SESSION['user_id'] = $user_id;
                    if ($simpla->request->get('asp_input') == 1) {
                        creteAspAdditionalDoc($user_id, $simpla, $simpla->request->get('code'));
                    }
                    $simpla->users->editPassword(['incorrect_total' => 0], $user_id);

                    if ($flood = $simpla->sms_validate->getRow(NULL, $simpla->users->clear_phone($phone))) {
                        $simpla->sms_validate->updateRow($flood->id, [
                            'total' => 0,
                            'total_unique' => 0,
                        ]);
                    }
                    
                    $update = [];
                    if ($quantity_loans = $simpla->soap->get_quantity_loans($user->uid)) {
                        $update['quantity_loans'] = json_encode($quantity_loans, JSON_UNESCAPED_UNICODE);
                    }
                    if ($credits_history = $simpla->soap->get_user_credits($user->uid)) {
                        $simpla->users->save_loan_history($user->id, $credits_history);
                    }
                    $update['use_b2p'] = $simpla->orders->check_use_b2p($user_id);
                    if (!empty($update)) {
                        $simpla->users->update_user($user->id, $update);
                    }
                    $simpla->sms_auth_validate_sms->delete($phone);
                }
            } else {
                $soap = $simpla->soap->get_uid_by_phone($phone);
                if (!empty($soap->result) && !empty($soap->uid)) {
                    $expl = explode(' ', $soap->client);
                    $lastname = isset($expl[0]) ? mb_convert_case($expl[0], MB_CASE_TITLE) : '';
                    $firstname = isset($expl[1]) ? mb_convert_case($expl[1], MB_CASE_TITLE) : '';
                    $patronymic = isset($expl[2]) ? mb_convert_case($expl[2], MB_CASE_TITLE) : '';

                    $user_id = $simpla->users->add_user(array(
                        'UID' => $soap->uid,
                        'UID_status' => "ok",
                        'phone_mobile' => $simpla->users->clear_phone($phone),
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
                        'sms' => $simpla->request->get('code', 'string'),
                        'last_ip'=>$_SERVER['REMOTE_ADDR'],
                        'use_b2p' => 1,
                    ));

                    $_SESSION['user_id'] = $user_id;

                    $details = $simpla->soap->get_client_details($soap->uid);
                    $simpla->import1c->import_user($user_id, $details);
                }
            }
        }
        break;

    case 'check':
        
        $result['success'] = defaultCheckSms();
        
        if ($result['success']) {
            unset($_SESSION['sms_send_count']);
        }
        
        $max_accept_try = $simpla->orders::MAX_ACCEPT_TRY;
        $check_asp = $simpla->request->get('check_asp', 'integer');
        if (!empty($check_asp))
        {
            $order_id = $simpla->request->get('order_id', 'integer');
            $order = $simpla->orders->get_order($order_id);
            $user = $simpla->users->get_user($order->user_id);
            
            if (empty($order) || !empty($user->blocked) || $order->user_id != $_SESSION['user_id']) {
    			unset($_SESSION['user_id']);
                $result['success'] = 0;
                break;
            }

            if (empty($result['success']))
            {
                $accept_try = $order->accept_try + 1;
                $simpla->orders->update_order($order_id, ['accept_try' => $accept_try]);
                $result['accept_try'] = $max_accept_try - $accept_try;
                if ($accept_try > $max_accept_try)
                {
                    // banned
                    $simpla->users->update_user($order->user_id, ['blocked' => 1]);
        			unset($_SESSION['user_id']);
                }                
            }
            else
            {
                unset($_SESSION['sms'], $_SESSION['phone']);
                $simpla->users->update_loan_funnel_report_issued($order->user_id, $order_id,  [
                    'issued' => true,
                    'issued_date' => date("Y-m-d")
                ]);
            }
        }

    break;

    case 'check_autoconfirm':
        $result['success'] = defaultCheckSms();

        $phone = $simpla->users->clear_phone($simpla->request->get('phone'));
        $result_validate_sms_auth = $simpla->sms_auth_validate_sms->validateSms($phone, $simpla->sms_auth_validate_sms::TYPE_AUTOCONFIRM);

        if (!$result_validate_sms_auth['success']) {
            $result['success'] = false;
            $result['error'] = $result_validate_sms_auth['error'];
        }

        $virtual_card_consent = (string)$simpla->request->get('is_virtual_card_consent');
        $user_id = (int)$_SESSION['user_id'];
	    $order_id = $simpla->request->get('current_order_id', 'integer');

        if (in_array($virtual_card_consent, ["1", "0"])) {
	        $simpla->user_data->set($user_id, "is_virtual_card_consent", $virtual_card_consent);
			if ($virtual_card_consent == '1') {
				$vcService = new \lib\VirtualCard\VirtualCardService($simpla->config, $user_id, (int) $order_id);
				$vcService->registerVirtualCard();
			}
        }

        if (!empty($result['success'])) {
            $sms = $_SESSION['sms'];
            unset($_SESSION['sms'], $_SESSION['phone']);

            // Если это подписание документов из ЛК по кнопке оплатить и погасить новый займ
            if ($flag == 'repeat_new_order' && empty($_COOKIE["autoconfirm_repeat_disabled"])) {
                $simpla->order_data->set($order_id, $simpla->order_data::REPEAT_ORDER_AUTO_CONFIRM_ASP, $sms);
            }

            $hasAutoConfirmFlow = $simpla->user_data->read($user_id, $simpla->user_data::AUTOCONFIRM_FLOW);
            $hasAutoConfirm2Flow = $simpla->user_data->read($user_id, $simpla->user_data::AUTOCONFIRM_2_FLOW);

            // Сценарий для НК автоподписание, и дополнительно проверка на страницу, чтобы не произошло двойного автоподпсиания
            if (($hasAutoConfirmFlow || $hasAutoConfirm2Flow) && $flag != 'repeat_new_order') {
                $simpla->user_data->set($user_id, $simpla->user_data::AUTOCONFIRM_FLOW);
                $simpla->user_data->set($user_id, $simpla->user_data::AUTOCONFIRM_2_FLOW);
                $last_order = $simpla->orders->get_last_order($user_id);
                $user = $simpla->users->get_user($user_id);
                
                // Получаем активный тип автоподписания из пользовательских данных
                $activeAutoConfirmFlow = $simpla->user_data->read($user_id, $simpla->user_data::ACTIVE_AUTOCONFIRM_FLOW);

                if ($activeAutoConfirmFlow !== $simpla->user_data::ACTIVE_AUTOCONFIRM_2_FLOW_VALUE) {
                    $simpla->soap->set_order_complete($last_order->id);
                }

                if ($simpla->autoconfirm->is_enabled_by_flow($user, $activeAutoConfirmFlow) || $simpla->users->isUserOldClientOrOldRegister($user)) {
                    $simpla->order_data->set($last_order->id, $simpla->order_data::AUTOCONFIRM_ASP, $sms);

                    // Если заявка уже одобрена, например автоматически скорингом скористы, то отправляем в очередь на автоподписание
                    if ($last_order->status == Orders::STATUS_APPROVED) {
                        if ($activeAutoConfirmFlow !== $simpla->user_data::ACTIVE_AUTOCONFIRM_2_FLOW_VALUE) {
                            $autoconfirm_amount = $simpla->autoconfirm->getAutoConfirmAmount($user_id, $last_order);
                        } else {
                            $autoconfirm_amount = $simpla->scorings->getApproveAmountScoring($user_id);
                        }

                        $simpla->autoconfirm->set_autoconfirm($autoconfirm_amount, $last_order);
                    }
                }
            }
        } else {
            $result['error'] = 'Код не совпадает';
        }
    break;

    case 'check_credit_doctor_sms':
        $code = $simpla->request->post('code');
        $result['success'] = (int)(isset($_SESSION[CreditDoctor::SMS_SESSION_KEY]) &&
            $_SESSION[CreditDoctor::SMS_SESSION_KEY] == $code);

    break;
    
    case 'check_credit_rating_sms':
        $code = $simpla->request->post('code');
        $sms = $_SESSION[CreditRating::SMS_SESSION_KEY];
        $result['success'] = (int)($sms == $code);
        $result['secure_hash'] = CreditRating::get_payment_hash($sms);

    break;

    // проверка СМС для подписи АСП об иной частоте взаимодействия
    case 'check_asp':
        $result['validate_sms'] = defaultCheckSms();
        if ($result['validate_sms']) {
            $user_id = (int)$_SESSION['user_id'];
            $user_balance = $simpla->users->get_user_balance($user_id);
            $zaim_number = $user_balance->zaim_number;
            // добавим новую АСП для просроченного займа
            $fileName = 'asp_zaim_'.$zaim_number.'.pdf';
            if(!empty($user_balance->zayavka) && $simpla->users->addZaimToAsp($zaim_number, (int)$_SESSION['sms'], $user_id,$fileName))
            {
                $zaim_asp = $simpla->users->getZaimAsp($zaim_number);
                if (!empty($zaim_asp)) {
                    $simpla->notification_center->signAdditionalCommunicationDoc($zaim_number);
                    $user = $simpla->users->get_user($user_id);
                    $simpla->docs->get_asp_zaim_pdf($user, $zaim_number);

                    $array_soap_asp = [
                        'НомерЗайма' => $zaim_asp->zaim_number,
                        'АСП' => $zaim_asp->sms_code,
                        'ДатаАСП' => (new \DateTime($zaim_asp->date_added))->format('YmdHis'),
                    ];

                    // отправим данные об АСП в 1С
                    $object_soap = $simpla->soap->generateObject($array_soap_asp);
                    $response_soap = $simpla->soap->requestSoap($object_soap, 'WebLK', 'DelayExit');
                    if (!empty($response_soap['response']) && $response_soap['response'] == 'ОК') {
                        $result['success'] = true;
                        unset($_SESSION['sms'], $_SESSION['phone']);
                    }
                }
            }
        }
        break;

    // проверка СМС для подписи АСП об арбитражном соглашении
    case 'check_arbitration_agreement':
        $result['validate_sms'] = defaultCheckSms();
        if ($result['validate_sms']) {
            $sms_code = (int)$_SESSION['sms'];
            $user_id = (int)$_SESSION['user_id'];
            $order_id = (int)$_GET['order_id'];

            $user = $simpla->users->get_user($user_id);
            $order = $simpla->orders->get_order($order_id);
            if (empty($order->{'1c_id'})) {
                $result['success'] = false;
                $result['error'] = 'order_1c_id_not_found';
                break;
            }

            $user_balance = $simpla->users->get_user_balance_for_order($user->id, $order->{'1c_id'});
            if (!$user_balance) {
                $result['success'] = false;
                $result['error'] = 'balance_not_found_for_order';
                break;
            }
            $zaim_number = $user_balance->zaim_number;

            if ($user_balance->zayavka && $order_id) {
                try {
                    $simpla->docs->getArbitrationAgreementPdf($user, $order_id, $sms_code, true);
                    $result['success'] = true;
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['error'] = 'arbitration_pdf_error';
                    $result['message'] = $e->getMessage();
                }
            }
        }
        break;

    case 'check_pdn_excess':
        $success = defaultCheckSms();
        if ($success) {
            $user_id = (int)$_SESSION['user_id'];
            $sms = (int)$_SESSION['sms'];
            $simpla->users->applyExcessedPdnNotification($user_id, $sms);
            unset($_SESSION['sms'], $_SESSION['phone']);
        }
        $result['success'] = $success;
        break;

    case 'check_agreement_acceptance':
        $success = defaultCheckSms();
        if ($success) {
            unset($_SESSION['sms'], $_SESSION['phone']);

            $user_id = (int)$_SESSION['user_id'];
            $simpla->users->applyUnnaceptedAgreement($user_id);
        }
        $result['success'] = $success;
        break;
    case 'check_init_user':
        $success = defaultCheckSms();

        $phone = $simpla->users->clear_phone($simpla->request->get('phone'));
        $result_validate_sms_auth = $simpla->sms_auth_validate_sms->validateSms($phone, $simpla->sms_auth_validate_sms::TYPE_REGISTRATION);

        if (!$result_validate_sms_auth['success']) {
            $success = false;
            $result['error'] = $result_validate_sms_auth['error'];
        }

        if ($success) {

            if (Helpers::isBlockedUserBy1C($simpla, $phone)) {
                    $simpla->users->update_user($user_id, ['blocked' => 1]);
                    $result['error'] = 'Пользователь заблокирован.';
            } elseif ($user = $simpla->users->get_user($phone)) {

                $jwt = UserHelper::getJWTToken($simpla->config->jwt_secret_key, $user->id, 'auth_jwt_token', $simpla->config->jwt_expiration_time, true);
                $result['auth_jwt_token'] = $jwt;

                $_SESSION['user_id'] = $user->id;
                $result['redirect_url'] = $simpla->config->front_url . '/user';

            } else {

                $soap = $simpla->soap->get_uid_by_phone($phone);
                if (!empty($soap->result) && !empty($soap->uid)) { // Импорт клиента из 1С
                    $expl = explode(' ', $soap->client);
                    $lastname = isset($expl[0]) ? mb_convert_case($expl[0], MB_CASE_TITLE) : '';
                    $firstname = isset($expl[1]) ? mb_convert_case($expl[1], MB_CASE_TITLE) : '';
                    $patronymic = isset($expl[2]) ? mb_convert_case($expl[2], MB_CASE_TITLE) : '';

                    $user_id = $simpla->users->add_user(array(
                        'UID' => $soap->uid,
                        'UID_status' => "ok",
                        'phone_mobile' => $simpla->users->clear_phone($phone),
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
                        'sms' => $simpla->request->get('code', 'string'),
                        'last_ip'=>$_SERVER['REMOTE_ADDR'],
                        'use_b2p' => 1,
                    ));

                    $_SESSION['user_id'] = $user_id;

                    $details = $simpla->soap->get_client_details($soap->uid);
                    $simpla->import1c->import_user($user_id, $details);
                    
                    $user = $simpla->users->get_user($user_id);
    
                    $_SESSION['user_id'] = $user->id;
                    $result['redirect_url'] = $simpla->config->front_url . '/user';
                    $result['is_new_user'] = 0;

                    $jwt = UserHelper::getJWTToken($simpla->config->jwt_secret_key, $user->id, 'auth_jwt_token', $simpla->config->jwt_expiration_time, true);
                    $result['auth_jwt_token'] = $jwt;

                } elseif ($soap->error == 'Множество совпадений') {
                    $simpla->soap->send_doubling_phone($phone);

                    $result['error'] = 'Пользователь с таким номером уже зарегистрован.<br />С Вами свяжется Клиентский Центр.';
                } else {

                    $simpla->sms_auth_validate_sms->delete($phone);

                    $result['is_new_user'] = 1;

                    $simpla->users->addInitUserPhone(
                        [
                            'phone' => $simpla->users->clear_phone($phone),
                            'sms_code' => $simpla->request->get('code'),
                        ]
                    );

                    $redirect_url = '/neworder?';
                    $utm_source = empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"];

                    if ($simpla->short_flow->isShortFlowEnabled() && $simpla->short_flow->isShortFlowSource($utm_source)) {
                        $redirect_url = '/register?';
                        $_SESSION['short_flow'] = 1;
                    } elseif (UserHelper::getFlow() === UserHelper::FLOW_AFTER_PERSONAL_DATA) {
                        $redirect_url = '/flow_after_personal_data/registration?';
                    } else {
                        unset($_SESSION['short_flow']);
                    }

                    $order_get_params = '';

                    if (!empty($simpla->request->get('calc_period')) && !empty($simpla->request->get('calc_amount'))) {
                        $order_get_params = http_build_query(
                            [
                                'period' => $simpla->request->get('calc_period'),
                                'amount' => $simpla->request->get('calc_amount'),
                            ]
                        );
                    }

                    $result['redirect_url'] = $simpla->config->front_url . $redirect_url . $order_get_params;

                }
            }

            if (!empty($user) && !empty($user->uid)) {
                $update = [];
                if ($quantity_loans = $simpla->soap->get_quantity_loans($user->uid)) {
                    $update['quantity_loans'] = json_encode($quantity_loans, JSON_UNESCAPED_UNICODE);
                }
                if ($credits_history = $simpla->soap->get_user_credits($user->uid)) {
                    $simpla->users->save_loan_history($user->id, $credits_history);
                }
                $update['use_b2p'] = $simpla->orders->check_use_b2p($user->id);
                if (!empty($update)) {
                    $simpla->users->update_user($user->id, $update);
                }                    
            }
        }
        
        $result['success'] = $success;
        break;

endswitch;





header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		

echo json_encode($result);
