<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

/**
 * @method static TicketStatus NEW()
 * @method static TicketStatus UNRESOLVED()
 * @method static TicketStatus ON_HOLD()
 * @method static TicketStatus RESOLVED()
 * @method static TicketStatus IN_PROGRESS()
 * @method static TicketStatus QUALITY_REQUEST()
 * @method static TicketStatus DUPLICATE()
 * @method static TicketStatus DISPUTED_COMPLAINT()
 */
class TicketStatus extends Enum
{
    private const NEW = 1;
    private const UNRESOLVED = 2;
    private const ON_HOLD = 3;
    private const RESOLVED = 4;
    private const IN_PROGRESS = 5;
    private const QUALITY_REQUEST = 7;
    private const DUPLICATE = 8;
    private const DISPUTED_COMPLAINT = 9;
}
