<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

/**
 * @method static TicketChannel PHONE()
 * @method static TicketChannel CHAT()
 * @method static TicketChannel EMAIL()
 * @method static TicketChannel MAIL()
 * @method static TicketChannel FORUMS()
 * @method static TicketChannel SITE_RATING()
 * @method static TicketChannel BANK_OF_RUSSIA()
 */
class TicketChannel extends Enum
{
    private const PHONE = 1;
    private const CHAT = 2;
    private const EMAIL = 3;
    private const MAIL = 4;
    private const FORUMS = 5;
    private const SITE_RATING = 6;
    private const BANK_OF_RUSSIA = 8;
}
