<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

final class OrderStatus extends Enum
{
    private const FILLING = 0;
    private const NEW = 1;
    private const APPROVED = 2;
    private const REJECTED = 3;
    private const REFUSED = 4;
    private const CORRECTION = 5;
    private const CORRECTED = 6;
    private const WAITING = 7;
    private const SIGNED = 8;
    private const READY_FOR_ISSUE = 9;
    private const ISSUED = 10;
    private const FAILED_TO_ISSUE = 11;
    private const CLOSED = 12;
    private const POSTPONED = 13;
    private const PRIOR_APPROVED = 14;

    private const LABELS = [
        self::FILLING => 'Заполнение',
        self::NEW => 'Новая',
        self::APPROVED => 'Одобрена',
        self::REJECTED => 'Отказ',
        self::REFUSED => 'Отказался сам',
        self::CORRECTION => 'На исправлении',
        self::CORRECTED => 'Исправлена',
        self::WAITING => 'Ожидание',
        self::SIGNED => 'Подписан',
        self::READY_FOR_ISSUE => 'Готов к выдаче',
        self::ISSUED => 'Выдан',
        self::FAILED_TO_ISSUE => 'Не удалось выдать',
        self::CLOSED => 'Закрыт',
        self::POSTPONED => 'Отложен',
        self::PRIOR_APPROVED => 'Предварительно одобрена',
    ];

    public function getLabel(): string
    {
        return OrderStatus::LABELS[$this->getValue()];
    }
}
