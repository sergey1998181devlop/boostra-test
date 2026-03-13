<?php

namespace App\Modules\TicketAssignment\Enums;

/**
 * Уровни компетенции менеджеров
 */
class CompetencyLevel
{
    public const SOFT = 'soft';
    public const MIDDLE = 'middle';
    public const HARD = 'hard';

    /**
     * Получить уровень компетенции по количеству дней просрочки
     */
    public static function getByOverdueDays(int $overdueDays): string
    {
        if ($overdueDays <= 7) {
            return self::SOFT;
        }
        if ($overdueDays <= 30) {
            return self::MIDDLE;
        }
        return self::HARD;
    }

    /**
     * Получить все доступные уровни компетенции
     */
    public static function getAll(): array
    {
        return [
            self::SOFT,
            self::MIDDLE,
            self::HARD
        ];
    }

    /**
     * Проверить существование уровня компетенции
     */
    public static function isValid(string $level): bool
    {
        return in_array($level, self::getAll(), true);
    }
}