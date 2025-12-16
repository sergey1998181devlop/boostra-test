<?php
    error_reporting(-1);
    ini_set('display_errors', 'On');

    session_start();
    chdir('..');

require_once 'api/Simpla.php';
require_once 'api/Helpers.php';

$simpla = new Simpla();
$response = [];
$action = $simpla->request->get('action');

switch ($action) {
    case 'skip_credit_rating':
        $user_id = (int)$_SESSION['user_id'];
        $response['success'] = $simpla->users->addSkipCreditRating($user_id, 'SKIP');
        break;
    case 'add_statistic_partner_href':
        $simpla->partner_href->addStatistic((int)$_SESSION['user_id'], (int)$_SESSION['partner_item_id'], 'click');
        break;
    case 'has_user_password':
        $phone = $simpla->request->post('phone');
        $user_id = (int)$simpla->users->get_phone_user($phone);

        $clear_phone = $simpla->users->clear_phone($phone);
        $user = $simpla->users->get_user($clear_phone);

        if (!empty($user->blocked)) {
            $response['error'] = 'user_blocked';
        } else {
            $response['success'] = $simpla->users->hasUserPassword($user_id);
        }
        break;
    case 'edit_user_password':
return false;
        $phone = $simpla->request->post('phone');
        $user_id = (int)$simpla->users->get_phone_user($phone);
        $password = $simpla->request->post('password', 'string');

        if (!empty($user_id) && !empty($password)) {
            $password_data = $simpla->helpers::generatePassword($password);
            if($response['success'] = $simpla->users->editPassword($password_data, $user_id))
            {
                //$simpla->notify->sendNewPasswordSms($phone, $password);
            }
        }
        break;
    case 'add_user_password':
return false;
        $phone = $simpla->request->post('phone');
        $user_id = (int)$simpla->users->get_phone_user($phone);
        $password = $simpla->request->post('password', 'string');

        if (!empty($user_id) && !empty($password)) {
            $password_data = $simpla->helpers::generatePassword($password);

            // на всякий случай проверим пароль у пользователя
            if ($simpla->users->hasUserPassword($user_id)) {
                $response['success'] = $simpla->users->editPassword($password_data, $user_id);
            } else {
                $password_data['user_id'] = $user_id;
                $response['success'] = $simpla->users->addPassword($password_data);
            }

            // отправим СМС с паролем
            /*if(!empty($response['success']))
            {
                $simpla->notify->sendNewPasswordSms($phone, $password);
            }*/
        }
        break;
    case 'login_user_password':
return false;
        $phone = $simpla->request->post('phone');
        $user_id = (int)$simpla->users->get_phone_user($phone);
        $password = $simpla->request->post('password', 'string');

        if (!empty($user_id) && !empty($password)) {
            $password_data = $simpla->users->getUserPassword($user_id);
            $incorrect_total = (int)($password_data->incorrect_total ?? 0);
            if ($incorrect_total < $simpla->users::MAX_INCORRECT_PASSWORD) {
                if (!empty($simpla->is_developer) || (!empty($password_data) && $simpla->helpers::validatePassword($password, $password_data))) {
                    if (empty($_SERVER['HTTP_REFERER']))
                    {
                        $response['error'] = 'Обновите страницу и попробуйте еще раз';
                    }
                    elseif (Helpers::isBlockedUserBy1C($simpla, $phone)) {
                        $simpla->users->update_user($user_id, ['blocked' => 1]);
                        $response['error'] = 'Пользователь заблокирован.';
                    }
                    else
                    {
                        $response['user_id'] = $user_id;

                        $response['success'] = true;

                        $user = $simpla->users->get_user($user_id);
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

                        $response['redirect_url'] = $simpla->config->front_url . '/user/login';

                        if ($simpla->request->post('page_action') === 'send_complaint') {
                            $response['redirect_url'] .= '?page_action=open_feedback';
                        }

                        $_SESSION['user_id'] = $user_id;

                        $simpla->users->editPassword(['incorrect_total' => 0], $user_id);
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
                    }
                } else {
                    $incorrect_total++;
                    $simpla->users->editPassword(compact('incorrect_total'), $user_id);
                }
            } else {
                $simpla->users->update_user($user_id, ['blocked' => 1]);
                $response['error'] = 'Пользователь заблокирован.';
            }
        }

        if (empty($response['success']) && empty($response['error'])) {
            $response['error'] = 'Неверный логин или пароль.';
        }
        break;
    case 'login_by_phone':
        $phone = $simpla->request->post('phone');
        $code = $simpla->request->post('code');
        $base_code = $simpla->authcodes->find_code($phone);

        if ($code == $base_code) {


        } else {
            $response['error'] = 'No validate SMS code';
        }
        break;
    case 'blocked_adv_sms':
        $user_id = (int)$_SESSION['user_id'];
        $user = $simpla->users->get_user($user_id);
        $phone = $user->phone_mobile;
        $response['result'] = $simpla->blocked_adv_sms->addItem(
            [
                'user_id' => $user_id,
                'phone' => $phone,
                'sms_type' => 'adv',
            ]
        );
        break;
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");


exit(json_encode($response));
