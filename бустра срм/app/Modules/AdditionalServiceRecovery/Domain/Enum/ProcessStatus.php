<?php

namespace App\Modules\AdditionalServiceRecovery\Domain\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static self RUNNING()
 * @method static self COMPLETED()
 * @method static self FAILED()
 */
class ProcessStatus extends Enum
{
    private const RUNNING = 'running';
    private const COMPLETED = 'completed';
    private const FAILED = 'failed';
}