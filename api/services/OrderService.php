<?php

namespace api\services;

use DateTime;
use Exception;

class OrderService
{
    /**
     * Рассчитывает due_days:
     * < 0 — до даты платежа
     * = 0 — сегодня
     * > 0 — просрочка
     *
     * @param string|null $planDate
     * @return int|null
     * @throws Exception
     */
    public static function calculateDueDays(?string $planDate): ?int
    {
        if (empty($planDate)) {
            return null;
        }

        $plan = new DateTime($planDate);
        $today = new DateTime(date('Y-m-d'));

        $diff = date_diff($plan, $today);

        $days = (int)$diff->days;

        if ($diff->invert === 1) {
            return -$days;
        }

        if ($days === 0) {
            return 0;
        }

        return $days;
    }
}
