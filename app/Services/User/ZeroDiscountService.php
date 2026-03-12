<?php

namespace App\Services\User;

use DateTime;

class ZeroDiscountService
{
    const  APPROVE_AMOUNT = 30_000;

    public static function handle($orders): array
    {
        /*
         * НК
         * 30 тысяч
         */
        $orderConfirmed = null;

        foreach ($orders as $order) {
            if ($order->status_1c === '5.Выдан' && $order->approve_amount == ZeroDiscountService::APPROVE_AMOUNT && $order->have_close_credits != 1) {
                $orderConfirmed = $order;
                break;
            }
        }

        if (!$orderConfirmed) {
            return [
                'show' => false,
            ];
        }

        $date = new DateTime($orderConfirmed->accept_date);
        $isShow = true;

        $now = new DateTime();
        $interval = $now->diff($date);

        if ($interval->days > 4 || $interval->days < 0) {
            $isShow = false;
        }

        $day = 5 - $interval->days;

        $dayText = 'дня';
        if ($day == 1) {
            $dayText = 'день';
        }
        if ($day == 5) {
            $dayText = 'дней';
        }

        return [
            'show' => $isShow,
            'text' => "0% по займу будут действовать еще " . $day . " " . $dayText
        ];
    }
}
