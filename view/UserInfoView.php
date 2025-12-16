<?PHP

require_once('View.php');

class UserInfoView extends View
{
    function fetch()
    {
        if (!empty($this->user)) {
            header('Location: /user');
            exit;
        }

        $userInfo = $_SESSION['user_info'] ?? null;;
        $this->design->assign('user_info', $userInfo);

//        if ($this->users->get_user($this->users->clear_phone($userInfo['phone_number']))) {
//            $_SESSION['user_id'] = $this->users->get_user($this->users->clear_phone($userInfo['phone_number']));
//            header('Location: /user/');
//        } else {
//            header( 'Location: /init_user?amount=9000&period=5');
//        }

        $user = $this->users->get_user((int)$_SESSION['user_id']);
        $this->design->assign('user_in', $user);
//        if ($this->users->clear_phone($userInfo['phone_number']) == $user->phone_mobile) {
//            header('Location: /user/');
//        } else {
//            header( 'Location: /init_user?amount=9000&period=5');
//        }

//        $soap = $this->soap->get_uid_by_phone($user->phone_mobile);
//        if (!empty($soap->result) && !empty($soap->uid)) {
//            $expl = explode(' ', $soap->client);
//            $lastname = isset($expl[0]) ? mb_convert_case($expl[0], MB_CASE_TITLE) : '';
//            $firstname = isset($expl[1]) ? mb_convert_case($expl[1], MB_CASE_TITLE) : '';
//            $patronymic = isset($expl[2]) ? mb_convert_case($expl[2], MB_CASE_TITLE) : '';
//
//            $user_id = $simpla->users->add_user(array(
//                'UID' => $soap->uid,
//                'UID_status' => "ok",
//                'phone_mobile' => $simpla->users->clear_phone($phone),
//                'lastname' => $lastname,
//                'firstname' => $firstname,
//                'patronymic' => $patronymic,
//                'utm_source' => empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"],
//                'utm_medium' => empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"],
//                'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"],
//                'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
//                'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
//                'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
//                'click_hash' => empty($_COOKIE["click_hash"]) ? '' : $_COOKIE["click_hash"],
//                'enabled' => 1,
//                'sms' => $simpla->request->get('code', 'string'),
//                'last_ip'=>$_SERVER['REMOTE_ADDR'],
//                'use_b2p' => 1,
//            ));

            //$details = $this->soap->get_client_details($soap->uid);
            //$this->design->assign('soap', $details);
            //$simpla->import1c->import_user($user_id, $details);
//        }

        //$response_balances = $this->soap->get_user_balances_array_1c($user->uid);

//        if (!empty($user->uid))
//        {
//            // отправим информацию об входе в ЛК в 1С
//            $this->soap->add_client_authorized($user->uid);
//        }

        //return $this->design->fetch('main.tpl');
        return $this->design->fetch('user_info.tpl');
    }
}