<?php

require_once 'View.php';

class EmailView extends Simpla
{
    public function fetch()
    {
        $token = $this->request->get("token");
        $base64_and_hmac = explode('.', $token);
        if (count($base64_and_hmac) !== 2) {
            $this->design->assign('type', 'invalid');
            return $this->design->fetch('unsubscribed.tpl');
        }

        if (!hash_equals(hash_hmac('sha256', $base64_and_hmac[0], $this->settings->secret_key), $base64_and_hmac[1])) {
            $this->design->assign('type', 'invalid');
            return $this->design->fetch('unsubscribed.tpl');
        }

        $decoded_user_id = base64_decode(strtr($base64_and_hmac[0], '-_', '+/'));
        $user = $this->users->get_user((int)$decoded_user_id);
        $this->design->assign("email", $user->email);
        $this->user_data->set($user->id, 'email_is_unsubscribed', 1);

        return $this->design->fetch('unsubscribed.tpl');
    }
}