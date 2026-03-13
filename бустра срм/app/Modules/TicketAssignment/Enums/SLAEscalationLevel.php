<?php

namespace App\Modules\TicketAssignment\Enums;

/**
 * Уровни SLA эскалации
 */
class SLAEscalationLevel
{
    public const LEVEL_1 = 1; // Старший специалист
    public const LEVEL_2 = 2; // Руководитель

    /**
     * Получить максимальный уровень
     */
    public static function getMaxLevel(): int
    {
        return self::LEVEL_2;
    }
}
