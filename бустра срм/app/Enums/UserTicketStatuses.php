<?php

namespace App\Enums;
use InvalidArgumentException;

final class UserTicketStatuses
{
    public const STATUS_NEW = 'Новое';
    public const STATUS_WORK = 'В работе';
    public const STATUS_WAITING = 'Ожидает ответа';
    public const STATUS_CLOSE = 'Закрыто';

    private const USEDESK_STATUSES_MAP = [
        1 => self::STATUS_NEW,
        2 => self::STATUS_CLOSE,
        3 => self::STATUS_CLOSE,
//        4 => self::STATUS_DELETED,
//        5 => self::STATUS_ON_HOLD,
//        6 => self::STATUS_PENDING,
//        7 => self::STATUS_SPAM,
        8 => self::STATUS_NEW,
//        9 => self::STATUS_MAILING,
//        10 => self::STATUS_MERGED,
    ];

    public static function getStatusName(int $id): string
    {
        if (!isset(self::USEDESK_STATUSES_MAP[$id])) {
            throw new InvalidArgumentException("Unknown status id: $id");
        }

        return self::USEDESK_STATUSES_MAP[$id];
    }
}
