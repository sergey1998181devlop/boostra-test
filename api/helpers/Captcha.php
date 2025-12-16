<?php

namespace api\helpers;

class Captcha
{
    /**
     * Лимит СМС
     */
    const LIMIT_SPAM_SMS = 2;

    /**
     * Проверяет запрос на валидность
     * @param int $sms_total
     * @param string $captcha_token
     * @param int $limit_sms
     * @return array|void
     */
    public static function validateRequest(int $sms_total, string $captcha_token, int $limit_sms = self::LIMIT_SPAM_SMS)
    {
        $result = [];

        if ($sms_total > $limit_sms) {
            if (empty($_SESSION['init_smart_captcha'])) {
                $result['captcha'] = 'init';
                $_SESSION['init_smart_captcha'] = 1;
            } else {
                if (!$captcha_token) {
                    $result['captcha'] = 'empty_token';
                } else {
                    if (\api\YaSmartCaptcha::check_captcha($captcha_token)) {
                        if ($_SESSION['init_smart_captcha'] === 1) {
                            $_SESSION['init_smart_captcha'] = 2;
                        } else {
                            $result['captcha'] = 're_init';
                            $_SESSION['init_smart_captcha'] = 1;
                        }
                    } else {
                        $result['captcha'] = 're_init';
                    }
                }
            }
        }

        return $result;
    }
}
