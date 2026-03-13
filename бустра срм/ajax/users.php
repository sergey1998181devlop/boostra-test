<?php

header("Content-type: application/json; charset=UTF-8");
header("Pragma: no-cache");
header("Expires: -1");

require dirname(__DIR__) . '/api/Simpla.php';

class AjaxUsers {
    private Simpla $simpla;

    public function __construct()
    {
        error_reporting(E_ERROR);
        ini_set('display_errors', 'Off');
        ini_set("error_log", dirname(__DIR__) . "/logs/php-error_ajax_" . basename(__FILE__) . ".log");

        $this->simpla = new Simpla();
    }

    public function init()
    {
        $action = $this->simpla->request->get('action', 'string');

        if (!method_exists($this, $action . '_action')) {
            http_response_code(404);
            $this->simpla->response->json_output(['error' => 'Action invalid']);
        } else {
            /**
             * @uses has_user_black_list_action
             */
            $this->{$action . '_action'}();
        }
    }

    private function has_user_black_list_action() {
        $phone = $this->simpla->request->post('phone', 'string');
        $user_id = $this->simpla->users->get_phone_user($phone);
        if ($user = $this->simpla->users->get_user($user_id)) {
            $has_black_list_in_table = $this->simpla->blacklist->getOne(['user_id' => $user_id]);
            $has_black_list_from_scoring = false;

            if (!$has_black_list_in_table) {
                $has_black_list_from_scoring = !$this->simpla->blacklist->checkIsUserIn1cBlacklist($user->UID);
            }

            $this->simpla->response->json_output(['success' => $has_black_list_in_table || $has_black_list_from_scoring]);
        } else {
            http_response_code(404);
            $this->simpla->response->json_output(['error' => 'User is not found.']);
        }
    }

    /**
     * Показ скрытие документов доп. услуг у закрытых займов
     * @return void
     */
    private function toggle_show_docs_action()
    {
        $user_id = $this->simpla->request->post('user_id', 'integer');
        $new_value = $this->simpla->request->post('new_value', 'integer');

        if (!$user_id || !in_array($new_value, [0, 1])) {
            http_response_code(400);
            $this->simpla->response->json_output(['error' => 'Invalid request']);
            return;
        }

        $key = UserData::SHOW_EXTRA_DOCS;

        $this->simpla->user_data->set($user_id, $key, $new_value);

        $this->simpla->response->json_output(['success' => true, 'new_value' => $new_value]);
    }

    private function search_action()
    {
        $phone = $this->simpla->request->get('phone', 'string');
        $id = $this->simpla->request->get('id', 'integer');

        $user_id = null;

        if ($id) {
            $user_id = $id;
        } elseif ($phone) {
            $user_id = $this->simpla->users->get_phone_user($phone);
        } else {
            $this->simpla->response->json_output([
                'success' => false,
                'message' => 'Требуется указать телефон или ID пользователя'
            ]);
        }

        $user = $this->simpla->users->get_user($user_id);

        $this->simpla->response->json_output([
            'success' => (bool)$user_id && (bool)$user,
            'user' => $user ? [
                'id' => $user->id,
                'full_name' => $user->firstname . ' ' . $user->lastname . ' ' . $user->patronymic,
                'phone_mobile' => $user->phone_mobile,
                'birth' => $user->birth,
                'email' => $user->email,
                'loans' => $this->simpla->orders->get_short_orders(['user_id' => $user->id]),
            ] : null
        ]);
    }
    
    /**
     * Блокирует / снимает блокировку с отправки рекламных смс
     * @return void
     */
    private function blocked_adv_sms_action()
    {
        $user_id = $this->simpla->request->post('user_id', 'integer');
        $blocked = $this->simpla->request->post('blocked', 'integer');

        if ($blocked) {
            $user = $this->simpla->users->get_user($user_id);
            $sms_type = 'adv';
            $phone = $user->phone_mobile;
            $this->simpla->blocked_adv_sms->addItem(compact('user_id', 'sms_type', 'phone'));
        } else {
            $this->simpla->blocked_adv_sms->deleteItemByUserId($user_id);
        }

        $this->simpla->response->json_output(['success' => true]);
    }
}

(new AjaxUsers())->init();
