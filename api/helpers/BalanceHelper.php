<?php

namespace api\helpers;

use DateTime;
use stdClass;

class BalanceHelper
{
    /** Долг клиента на просрочке */
    public static function getDebtInDays($balance): ?int
    {
        if (!$balance) {
            return null;
        }

        $parsed = $balance->payment_date ? date_parse($balance->payment_date) : null;

        if (!$parsed) {
            return null;
        }

        if (!checkdate((int) $parsed['month'], (int) $parsed['day'], (int) $parsed['year'])) {
            return null;
        }

        if ((int) $parsed['year'] <= 1900) {
            return null;
        }

        $currentDate = new DateTime();
        $debtDate = new DateTime($balance->payment_date);
        $debtInDays = $debtDate < $currentDate ? $currentDate->diff($debtDate)->days : null;
        // Если долг в днях равен 0, т.е. дата возвращения долга - текущая дата
        // То нас это НЕ интересует, т.к. нас интересует ТОЛЬКО первый и последующие дни долга
        return $debtInDays ?: null;
    }
}