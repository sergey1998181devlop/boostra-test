<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class CommentHandlers extends Enum
{
    public const AVIAR = 'aviar';
    public const OPERATOR = 'operator';
    public const MISSED = 'missed';
}