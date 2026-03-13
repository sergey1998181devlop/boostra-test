<?php

namespace api;

use Simpla;

/**
 * Класс для работы с ЯД капчей
 *
 * https://yandex.cloud/ru/docs/smartcaptcha/quickstart
 */
class YaSmartCaptcha
{
    /**
     * Проверка капчи
     * @param string $token
     * @param string|null $ip
     * @return bool
     */
    public static function check_captcha(string $token, ?string $ip = null): bool
    {
        $simpla = new Simpla();

        $ch = curl_init();
        $args = http_build_query(
            [
                "secret" => $simpla->config->smart_captcha_server_key,
                "token" => $token,
                "ip" => $ip ?: $_SERVER['REMOTE_ADDR'],
            ]
        );
        curl_setopt($ch, CURLOPT_URL, "https://smartcaptcha.yandexcloud.net/validate?$args");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);

        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode !== 200) {
            return false;
        }

        $resp = json_decode($server_output);
        return $resp->status === "ok";
    }
}
