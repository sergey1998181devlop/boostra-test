<?php

namespace api\helpers;

/**
 * Class UserHelper
 */
class UserHelper
{
    /**
     * Список обязательных полей для входа в ЛК
     */
    const REQUIRE_FIELDS = [
        'personal_data_added' => [
//            'firstname',
//            'lastname',
//            'patronymic',
//            'phone_mobile',
//            'birth',
            'birth_place',
            'gender',

            'passport_serial',
            'subdivision_code',
            'passport_date',
            'passport_issued',
        ],
        'address_data_added' => [
            'Regindex',
            'Regregion',
            'Regcity',
        ],
        'additional_data_added' => [
            'income_base',
            'education',
        ],
        'accept_data_added' => [],
        'files_added' => [],
        'card_added' => [],
    ];

    /**
     * Флоу ФИО + паспорт вводяться До телефона
     */
    public const FLOW_AFTER_PERSONAL_DATA = 1;

    /**
     * Проверка пользователя на наличие просрочки и установки его для снятия допов
     * @param \Simpla $simpla
     * @param object $user
     * @return bool
     * @throws \Exception
     */
    public static function hasNotOverdueLoan(\Simpla $simpla, object $user): bool
    {
        // посчитаем просрочку у последнего закрытого займа
        $loans_closed = array_filter($user->loan_history, function ($loan) {
            return !empty($loan->close_date);
        });
        $end_loan = end($loans_closed);
        $plan_close_date = new \DateTime($end_loan->close_date);
        $interval_close_date = $plan_close_date->diff((new \DateTime($end_loan->plan_close_date)));
        $overdue_last_close_loan = $interval_close_date->days > 0 && $interval_close_date->invert === 1; // если просрочил
        $user_has_overdue_in_table = $simpla->users->hasOverdueHideUserService(
            $user->phone_mobile
        ); // есть пользователь в списке в таблице
        $notOverdueLoan = !$overdue_last_close_loan
            && $user_has_overdue_in_table
            && count($loans_closed) > 3
            && count($loans_closed) < 9; // больше 3 и менее 9 закрытых займов и по крайнему нет просрочки

        if ($user_has_overdue_in_table) {
            if ($notOverdueLoan) {
                $simpla->hide_service->addItem($user->id);
            } else {
                $simpla->hide_service->deleteItem($user->id);
            }
        }

        return $notOverdueLoan;
    }

    /**
     * Генерирует токен и записывает его в coockie
     * @param string $hmac_secret_key
     * @param int $user_id
     * @param string $token_key
     * @param int $expiration_time
     * @param bool $clear_old
     * @return mixed|string
     */
    public static function getJWTToken(string $hmac_secret_key, int $user_id, string $token_key, int $expiration_time = 3600, bool $clear_old = false)
    {
        if ($clear_old) {
            setcookie($token_key, null, time()-1, '/');
            $_COOKIE[$token_key] = null;
        }

        if (!empty($_COOKIE[$token_key])) {
            return $_COOKIE[$token_key];
        } else {
            $token = \api\helpers\JWTHelper::generateToken($hmac_secret_key, $user_id, $expiration_time);
            setcookie($token_key, $token, time() + $expiration_time, '/');
            return $token;
        }
    }

    /**
     * @return mixed|null
     */
    public static function getFlow()
    {
        return $_SESSION['user_flow'] ?? null;
    }

    /**
     * Formats a clean phone number string into a masked phone number format.
     *
     * example +7 (879) 879-87-97
     *
     * @param string $cleanPhone The raw phone number consisting only of digits.
     * @return string|null The formatted phone number as a masked string, or null if the input does not match the expected pattern.
     */
    public static function formatPhoneToMAsk(string $cleanPhone)
    {
        return preg_replace(
            '/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/',
            '+$1 ($2) $3-$4-$5',
            $cleanPhone
        );
    }

    /**
     * Проверка пользователя на наличие $days дней просрочки
     * @param \Simpla $simpla
     * @param int $user_id
     * @param int $days
     * @return bool
     */
    public static function userHasOverduedDays(\Simpla $simpla, int $user_id, int $days): bool
    {
        $simpla->db->query("SELECT 1 has_overdue
                            FROM s_user_balance ub
                            JOIN s_orders o
                                ON o.`1c_id` = ub.zayavka
                                AND o.user_id = ub.user_id
                                AND o.`1c_status` = '5.Выдан'
                                AND o.confirm_date > '1970-01-01'
                            JOIN s_contracts c
                                ON o.id = c.order_id
                                AND c.issuance_date > '1970-01-01'
                            WHERE
                                ub.user_id = ?
                                AND ub.zaim_number <> 'Нет открытых договоров'
                                AND ub.zaim_number <> 'Ошибка'
                                AND ub.zayavka > ''
                                AND ub.payment_date > '1970-01-01'
                                AND DATE(ub.payment_date) <= (CURRENT_DATE() - INTERVAL ? DAY)
                            LIMIT 1", $user_id, $days);
        return (bool)$simpla->db->result('has_overdue');
    }

    /**
     * У пользователя наступает платеж через $days дней
     * @param \Simpla $simpla
     * @param int $user_id
     * @param int $days
     * @return bool
     */
    public static function userHasUpcomingPayment(\Simpla $simpla, int $user_id, int $days): bool
    {
        $simpla->db->query("SELECT 1 has_payment
                            FROM s_user_balance ub
                             JOIN s_orders o
                                ON o.`1c_id` = ub.zayavka
                                AND o.user_id = ub.user_id
                                AND o.`1c_status` = '5.Выдан'
                                AND o.confirm_date > '1970-01-01'
                           WHERE
                                ub.user_id = ?
                                AND ub.zaim_number <> 'Нет открытых договоров'
                                AND ub.zaim_number <> 'Ошибка'
                                AND ub.zayavka > ''
                                AND ub.payment_date > '1970-01-01'
                                AND DATE(ub.payment_date) >= CURRENT_DATE()
                                AND DATE(ub.payment_date) <= CURRENT_DATE() + INTERVAL ? DAY
                            LIMIT 1", $user_id, $days);
        return (bool)$simpla->db->result('has_payment');
    }

    /**
     * Количество займов пользователя на выбранную дату (не включая)
     * @param \Simpla $simpla
     * @param int $user_id
     * @param \DateTime $day
     * @return int
     */
    public static function userLoansCount(\Simpla $simpla, int $user_id, \DateTime $day): int
    {
        $simpla->db->query('SELECT COUNT(*) cnt FROM s_contracts c WHERE c.user_id = ? AND c.issuance_date < ?', $user_id, $day->format('Y-m-d H:i:s'));
        return (int)$simpla->db->result('cnt');
    }
}
