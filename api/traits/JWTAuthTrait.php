<?php

namespace api\traits;

use api\helpers\JWTHelper;

trait JWTAuthTrait
{
    public function jwtAuthValidate()
    {
        /**
         * @var \Simpla $this
         */
        $jwt = JWTHelper::decodeToken($_COOKIE['auth_jwt_token'] ?? '', $this->config->jwt_secret_key);
        if (!$jwt)
        {
            unset($_SESSION['user_id']);
            header('Location: ' . $this->config->root_url . '/user/login');
            exit;
        } else if(empty($_SESSION['user_id'])) {
            $user_id = (int)$jwt->sub;
            $_SESSION['user_id'] = $user_id;
            if (empty($this->user)) {
                $this->user = $this->users->get_user($user_id);
            }
        }
    }
}
