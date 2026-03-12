<?php

namespace RestApi\Helpers;

use api\helpers\JWTHelper;

/**
 * Независимый класс помощник
 */
final class ServiceHelper
{
    /**
     * Ссылка для авторизации на сайте
     *
     * @param \Simpla $simpla
     * @param int $user_id
     * @param string $partner
     * @param array $params Свободные параметры
     * @return string
     */
    public static function getUserAuthLink(\Simpla $simpla, int $user_id, string $partner, array $params = []): string
    {
        $token = JWTHelper::generateToken($simpla->config->jwt_secret_key, $user_id, 3600 * 24 * 7);
        $params = array_merge($params, ['partner' => $partner, 'utm_term' => $simpla->rest_api_partner::UTM_TERM]);

        return $simpla->config->front_url . "/auth-by-token/" . $token . '?' . http_build_query($params);
    }
}
