<?php

namespace App\Enums;

use InvalidArgumentException;
use MyCLabs\Enum\Enum;

class TicketPriority extends Enum
{
    public const MINIMUM = 1;
    public const MEDIUM = 2;
    public const HIGH = 3;
    public const CRITICAL = 4;

    public static function getByName(string $name): ?int
    {
        $name = strtoupper($name);

        switch ($name) {
            case 'MINIMUM':
                return self::MINIMUM;
            case 'MEDIUM':
                return self::MEDIUM;
            case 'HIGH':
                return self::HIGH;
            case 'CRITICAL':
                return self::CRITICAL;
            default:
                return null;
        }
    }

}





