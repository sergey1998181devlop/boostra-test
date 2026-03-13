<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

final class UsedeskTicketPriorities extends Enum
{
    public const LOW = 'low';
    public const MEDIUM = 'medium';
    public const URGENT = 'urgent';
    public const EXTREME = 'extreme';
}