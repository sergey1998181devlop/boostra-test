<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

/**
 * Причины отклонения платежей по р/с
 */
final class PaymentRejectReasonEnum extends Enum
{
    public const INSUFFICIENT_FUNDS = 'insufficient_funds';
    public const WRONG_REQUISITES = 'wrong_requisites';
    public const WRONG_PHOTO = 'wrong_photo';
    public const DUPLICATE_RECEIPT = 'duplicate_receipt';

    /**
     * Получить человекочитаемое описание причины
     */
    public static function getLabel(string $reason): string
    {
        $labels = [
            self::INSUFFICIENT_FUNDS => 'Недостаточно средств для закрытия договора',
            self::WRONG_REQUISITES => 'Оплата произошла по некорректным реквизитам',
            self::WRONG_PHOTO => 'Приложили иное фото',
            self::DUPLICATE_RECEIPT => 'Дубль чека',
        ];

        return $labels[$reason] ?? 'Неизвестная причина';
    }

    /**
     * Получить все причины с описаниями для select
     */
    public static function getOptionsForSelect(): array
    {
        return [
            self::INSUFFICIENT_FUNDS => self::getLabel(self::INSUFFICIENT_FUNDS),
            self::WRONG_REQUISITES => self::getLabel(self::WRONG_REQUISITES),
            self::WRONG_PHOTO => self::getLabel(self::WRONG_PHOTO),
            self::DUPLICATE_RECEIPT => self::getLabel(self::DUPLICATE_RECEIPT),
        ];
    }
}
