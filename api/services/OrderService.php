<?php

namespace api\services;

use DateTime;

class OrderService
{
    private const GRACE_PERIOD_DAYS = 2;
    private const MIN_IGNORE_OVERDUE_DAYS = 31;
    private const MAX_IGNORE_OVERDUE_DAYS = 90;

    private const STATUS_TODAY = '-1';
    private const STATUS_NOT_DUE = 'not';

    /**
     * Рассчитывает due_days по плановой дате платежа
     *
     * @param string|null $planDate
     * @return string
     */
    public static function calculateDueDays(?string $planDate): string
    {
        if (empty($planDate)) {
            return self::STATUS_NOT_DUE;
        }

        $diff = date_diff(
            new DateTime($planDate),
            new DateTime(date('Y-m-d 00:00:00'))
        );

        $days = $diff->days ?? 0;
        $isPlannedInFuture = $diff->invert === 1;

        switch (true) {
            case $isPlannedInFuture && $days > self::GRACE_PERIOD_DAYS:
                return self::STATUS_NOT_DUE;

            case $isPlannedInFuture && $days <= self::GRACE_PERIOD_DAYS:
                return '-' . $days;

            case !$isPlannedInFuture && $days === 0:
                return self::STATUS_TODAY;

            case !$isPlannedInFuture &&
                $days >= self::MIN_IGNORE_OVERDUE_DAYS &&
                $days <= self::MAX_IGNORE_OVERDUE_DAYS:
                return self::STATUS_NOT_DUE;

            case !$isPlannedInFuture:
                return (string)$days;

            default:
                return self::STATUS_NOT_DUE;
        }
    }
}
