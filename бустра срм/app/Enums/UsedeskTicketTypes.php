<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

final class UsedeskTicketTypes extends Enum
{
    public const QUESTION = 'question';
    public const TASK = 'task';
    public const PROBLEM = 'problem';
    public const INCIDENT = 'incident';
}