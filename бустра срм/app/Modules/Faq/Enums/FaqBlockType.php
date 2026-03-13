<?php

namespace App\Modules\Faq\Enums;

class FaqBlockType
{
    public const PUBLIC = 'public';
    public const AUTHORIZED_NO_LOANS = 'authorized_no_loans';
    public const ACTIVE_LOAN = 'active_loan';
    public const OVERDUE_DEBT = 'overdue_debt';
    public const APPLICATION_PROCESS = 'application_process';
    public const CLOSED_LOANS = 'closed_loans';

    public static function all(): array
    {
        return [
            self::PUBLIC,
            self::AUTHORIZED_NO_LOANS,
            self::ACTIVE_LOAN,
            self::OVERDUE_DEBT,
            self::APPLICATION_PROCESS,
            self::CLOSED_LOANS,
        ];
    }

    public static function isValid(string $type): bool
    {
        return in_array($type, self::all(), true);
    }

    public static function getLabel(string $type): string
    {
        $labels = [
            self::PUBLIC => 'Публичный',
            self::AUTHORIZED_NO_LOANS => 'Авторизован без займов',
            self::ACTIVE_LOAN => 'Активный займ',
            self::OVERDUE_DEBT => 'Просроченная задолженность',
            self::APPLICATION_PROCESS => 'Процесс подачи заявки',
            self::CLOSED_LOANS => 'Закрытые займы',
        ];

        return $labels[$type] ?? $type;
    }
}
