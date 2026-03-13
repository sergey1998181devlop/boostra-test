<?PHP

use api\helpers\UserHelper;

require_once('View.php');

class AccountLoginView extends View
{
    use \api\traits\AuthButtonsTrait;

    function fetch()
    {
        // проверяем куки лидгид
        if (!empty($_COOKIE['click_hash']))
        {
            $this->db->query("SELECT id FROM __orders WHERE click_hash = ?", (string)$_COOKIE['click_hash']);
            if ($this->db->result('id'))
            {
                setcookie("utm_source", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_medium", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_campaign", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_content", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_term", null, time() - 1, '/', 'boostra.ru');
                setcookie("webmaster_id", null, time() - 1, '/', 'boostra.ru');
                setcookie("click_hash", null, time() - 1, '/', 'boostra.ru');

            }
        }

        // Выход
        if($this->request->get('action') == 'logout')
        {
            unset($_SESSION['user_id']);
            unset($_SESSION['user_info']);
            unset($_SESSION['state']);
            unset($_SESSION[$this->account_contract::SESSION_KEY]);
            unset($_SESSION['passport_user']);
            unset($_SESSION['restricted_mode']);
            unset($_SESSION['restricted_mode_logout_hint']);

            setcookie('auth_jwt_token', null, time()-1, '/');
            header('Location: '.$this->config->root_url);
            exit();
        }
        //
        elseif($this->request->method('post') && $this->request->post('login'))
        {

            $key	= $this->request->post('key');
            $phone	= $this->request->post('real_phone');
            $is_ajax = $this->request->post('ajax', 'integer');

            $this->design->assign('phone', $phone);

            if(isset($_SESSION['check_sms_count'])) {
                $_SESSION['check_sms_count']++;
            } else {
                $_SESSION['check_sms_count'] = 1;
            }

            $base_code = $this->authcodes->find_code($phone);

            /* убрал для тестирования мессенджеров
            if ((strtotime('now') - $_SESSION['sms_date']) > 180 && (empty($this->is_developer) && empty($this->is_admin)))
            {
                $this->design->assign('error', 'login_incorrect');
            }
            else
            */
            if ($_SESSION['check_sms_count'] > 3 && empty($this->is_developer) && empty($this->is_admin))
            {
                $this->design->assign('error', 'Превышен лимит попыток. </br><a href="user/login" style="text-decoration: underline;">получить код</a>');
            }
            elseif (!empty($base_code) && $key == $base_code)
            {
                $user = $this->users->get_user($phone);
                $last_order = $this->orders->get_last_order($user->id);

                if (empty($user))
                {
                    $soap = $this->soap->get_uid_by_phone($phone);


                    if (!empty($soap->result) && !empty($soap->uid))
                    {
                        $expl = explode(' ', $soap->client);
                        $lastname = isset($expl[0]) ? mb_convert_case($expl[0], MB_CASE_TITLE) : '';
                        $firstname = isset($expl[1]) ? mb_convert_case($expl[1], MB_CASE_TITLE) : '';
                        $patronymic = isset($expl[2]) ? mb_convert_case($expl[2], MB_CASE_TITLE) : '';

                        $user_id = $this->users->add_user(array(
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
                            'sms' => $key,
                            'last_ip'=>$_SERVER['REMOTE_ADDR'],
                            'use_b2p' => 1,
                        ));

                        $_SESSION['user_id'] = $user_id;
                        setcookie('user_id', $user_id, time() + 86400 * 365, '/');

                        $details = $this->soap->get_client_details($soap->uid);
                        $this->import1c->import_user($user_id, $details);

                        if (empty($this->is_developer) && empty($this->is_admin))
                            $res = $this->soap->add_client_authorized($soap->uid);

                        $redirect = $this->config->root_url.'/user';

                        if ($this->request->get('page_action') === 'send_complaint') {
                            $redirect .= '?page_action=open_feedback';
                        }

                        UserHelper::getJWTToken($this->config->jwt_secret_key, $user_id, 'auth_jwt_token', $this->config->jwt_expiration_time, true);

                        if ($is_ajax) {
                            $this->request->json_output(compact('redirect'));
                        } else {
                            header('Location: '.$redirect);
                        }

                        exit;
                    }
                    elseif ($soap->error == 'Множество совпадений')
                    {
                        $this->soap->send_doubling_phone($phone);

                        $this->design->assign('error', 'user_blocked');
                    }
                    elseif ($soap->error == 'ЛК удален')
                    {
                        $this->design->assign('error', 'user_disabled');
                    }
                    else
                    {
                        $this->design->assign('error', 'Пользователь не найден');
                    }
                }
                else
                {
					$this->login($user, $is_ajax);
                }
            }
            else
            {
                $this->design->assign('error', 'login_incorrect');
            }

		}
		elseif ($payload = $this->request->get('payload'))
        {
            $payload = json_decode($payload);
            $authResult = $this->vk_api->authHandler($payload);
            if (!is_string($authResult))
            {
                $this->login($authResult);
                return;
            }

            $vk_disabled = false;
            switch ($authResult)
            {
                case $this->vk_api::AUTH_RESULT_NOT_FOUND:
                    $error = 'Пользователь не найден. Возможно, вы ещё не зарегистрированы';
                    $vk_disabled = true;
                    break;

                case $this->vk_api::AUTH_RESULT_CANT_GET_PHONE:
                    $error = 'Пожалуйста, попробуйте другой метод авторизации';
                    $vk_disabled = true;
                    break;

                case $this->vk_api::AUTH_RESULT_BAD_REQUEST:
                    $error = 'Произошла ошибка. Пожалуйста, попробуйте ещё раз или используйте другой метод авторизации';
                    break;
            }
            $this->design->assign('vk_error', $error);
            $this->design->assign('vk_disabled', $vk_disabled);
        }
        elseif ($tgHash = $this->request->get('tg_hash')) {
            if ($tgAuth = $this->users->getTelegramHash($tgHash)) {
                if ($user = $this->users->get_user((int)$tgAuth->user_id)) {
                    $this->login($user);
                }
            }

        }
        else
        {
            if ($phone = $this->request->get('phone'))
            {
                $correct_phone = $this->users->clear_phone($phone);
                $correct_phone = substr($correct_phone, -10, 10);
                $this->design->assign('phone', $correct_phone);
///echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($correct_phone);echo '</pre><hr />';
            }

            setcookie('auth_jwt_token', null, time()-1, '/');
        }

        if (!empty($this->user)) {
            $last_order = $this->orders->get_last_order($this->user->id);
            //переадресация на промежуточную страницу если не прошел шаг покупки или пропуска Кредитного рейтинга
            if (0 && empty($this->user->skip_credit_rating) && $this->user->additional_data_added == 1 && $last_order->status != 2) {
                header('Location: ' . $this->config->root_url . '/user/credit_rating');
            } else {
                $redirectUrl = $this->config->root_url . '/user';

                if ($this->request->get('page_action')) {
                    $redirectUrl .= '?page_action=' . $this->request->get('page_action');
                }

                UserHelper::getJWTToken($this->config->jwt_secret_key, $this->user->id, 'auth_jwt_token', $this->config->jwt_expiration_time, true);

                header('Location: ' . $redirectUrl);
            }
            exit();
        }

        if (!empty($_SESSION['flash_error'])) {
            $this->design->assign('flash_error', $_SESSION['flash_error']);
            unset($_SESSION['flash_error']);
        }

        $this->design->assign('is_developer', $this->is_developer);
        $this->design->assign('page_action', $this->request->get('page_action'));
        $this->design->assign('same_page', in_array($_COOKIE['utm_source'] ?? '&', explode(',', $this->settings->bonon_excluded_utms)));
		$this->design->assign('login_partner_href', $this->partner_href->getActualItem(1, 'bonon-background-login'));
		$this->design->assign('complaint_partner_href', $this->partner_href->getActualItem(0, 'bonon-background'));
        $this->design->assign('client_suffix', ':pk');

        $this->initAuthAllButtons();

        return $this->design->fetch('account_login.tpl');
    }

    private function user_update($user, $update)
    {
        if ($quantity_loans = $this->soap->get_quantity_loans($user->uid))
            $update['quantity_loans'] = json_encode($quantity_loans, JSON_UNESCAPED_UNICODE );

        if ($credits_history = $this->soap->get_user_credits($user->uid))
            $this->users->save_loan_history($user->id, $credits_history);

        return $update;
    }

    /**
     * Завершить авторизацию и залогинить клиента
     * @param $user
     * @param bool $is_ajax
     */
    private function login($user, bool $is_ajax = false)
    {
        $_SESSION['user_id'] = $user->id;
        setcookie('user_id', $user->id, time() + 86400 * 365, '/');

        $update = [
            'last_ip' => $_SERVER['REMOTE_ADDR'],
            'fake_order_error' => 0,
        ];
        $update = $this->user_update($user, $update);
        $update['use_b2p'] = $this->orders->check_use_b2p((int)$user->id);
        $this->users->update_user($user->id, $update);

        if (empty($this->is_developer) && empty($this->is_admin) && !empty($user->uid))
            $this->soap->add_client_authorized($user->uid);

        UserHelper::getJWTToken($this->config->jwt_secret_key, $user->id, 'auth_jwt_token', $this->config->jwt_expiration_time, true);

        $redirect = $this->config->root_url . '/user';
        if ($is_ajax) {
            $this->request->json_output(compact('redirect'));
        } else {
            header('Location: ' . $redirect);
        }

        exit();
    }
}
