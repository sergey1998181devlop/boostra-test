<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

/**
 * Темы тикетов (родительские категории)
 *
 * Примечание: Полный список тем управляется динамически в БД (s_mytickets_subjects).
 * Здесь представлены только родительские темы, используемые в бизнес-логике.
 *
 * @method static TicketSubject COLLECTIONS()
 * @method static TicketSubject ADDITIONAL_SERVICES()
 */
class TicketSubject extends Enum
{
    /** @var int Взыскание (родительская тема) */
    private const COLLECTIONS = 9;

    /** @var int Допы и прочее (родительская тема) */
    private const ADDITIONAL_SERVICES = 10;
}
