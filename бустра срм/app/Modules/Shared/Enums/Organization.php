<?php

namespace App\Modules\Shared\Enums;

use MyCLabs\Enum\Enum;

/**
 * Перечисление организаций системы
 *
 * @method static self BOOSTRA()
 * @method static self AKVARIUS()
 * @method static self AKADO()
 * @method static self FINTEHMARKET()
 * @method static self FINLAB()
 * @method static self VIPZAIM()
 * @method static self RZS()
 * @method static self LORD()
 * @method static self MOREDENEG()
 */
class Organization extends Enum
{
    private const BOOSTRA = 1;
    private const AKVARIUS = 6;
    private const AKADO = 7;
    private const FINTEHMARKET = 8;
    private const FINLAB = 11;
    private const VIPZAIM = 12;
    private const RZS = 13;
    private const LORD = 15;
    private const MOREDENEG = 17;
    private const RUBL = 21;

    /**
     * @var array<int, string>
     */
    private const ORGANIZATION_NAMES = [
        self::BOOSTRA => 'Бустра',
        self::AKVARIUS => 'Аквариус',
        self::AKADO => 'Акадо',
        self::FINTEHMARKET => 'Финтех-Маркет',
        self::FINLAB => 'Финлаб',
        self::VIPZAIM => 'ВипЗайм',
        self::RZS => 'РУСЗАЙМСЕРВИС',
        self::LORD => 'Лорд',
        self::MOREDENEG => 'ООО МКК «МореДенег»',
        self::RUBL => 'ООО МКК «Рубль.Ру»',
    ];

    /**
     * Получить название организации
     *
     * @return string
     */
    public function getName(): string
    {
        return self::ORGANIZATION_NAMES[$this->getValue()] ?? 'Unknown';
    }

    public static function getList(): array
    {
        return self::ORGANIZATION_NAMES;
    }

    /**
     * Проверяет, является ли переданное значение валидным ID организации
     *
     * @param int $value
     * @return bool
     */
    public static function isValid($value): bool
    {
        return isset(self::ORGANIZATION_NAMES[$value]);
    }
}
