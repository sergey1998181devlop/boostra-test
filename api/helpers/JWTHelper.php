<?php

namespace api\helpers;
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper
{
    /**
     * Генерация JWT токена
     * @param string $secret_key
     * @param $user_id
     * @param int $expiration_time
     * @return string
     */
    public static function generateToken(string $secret_key, $user_id, int $expiration_time = 3600): string
    {
        $payload = [
            'sub' => (string)$user_id,
            'exp' => time() + $expiration_time,
        ];

        return JWT::encode($payload, $secret_key, 'HS256');
    }

    /**
     * Валидация и декодирование токена
     * @param string $token
     * @param string $secret_key
     * @return false|\stdClass
     */
    public static function decodeToken(string $token, string $secret_key)
    {
        try {
            // Декодируем токен
            // Если токен валиден, вы можете использовать данные из него
            return JWT::decode($token, new Key($secret_key, 'HS256'));
        } catch (Exception $e) {
            return false;
        }
    }
}
