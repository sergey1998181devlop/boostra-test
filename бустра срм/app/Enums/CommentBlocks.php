<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class CommentBlocks extends Enum
{
    public const INCOMING_CALL = 'incomingCall';
    public const OUTGOING_CALL = 'outgoingCall';
    public const FROMTECH_INCOMING_CALL = 'fromtechIncomingCall';

    public const ORDER = 'order';

    public const PERSONAL = 'personal';
}