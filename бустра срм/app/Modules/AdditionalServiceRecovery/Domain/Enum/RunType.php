<?php

namespace App\Modules\AdditionalServiceRecovery\Domain\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static self MANUAL()
 * @method static self AUTO()
 */
class RunType extends Enum
{
    private const MANUAL = 'manual';
    private const AUTO = 'auto';
}