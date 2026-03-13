<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class CrossOrder extends Enum
{
    /** При добавлении новой причины добавить ее описание в api/Cross_orders::REASON_MESSAGES */
    public const REASON_SETTING_DISABLED = 'SETTING_DISABLED';
    public const REASON_NO_ORDER = 'NO_ORDER';
    public const REASON_NO_ORDERS = 'NO_ORDERS';
    public const REASON_NO_USER = 'NO_USER';
    public const REASON_INSTALLMENT = 'INSTALLMENT';
    public const REASON_IGNORED_SITE = 'IGNORED_SITE';
    public const REASON_NK_SETTING_DISABLED = 'NK_SETTING_DISABLED';
    public const REASON_NO_CROSS_ORGANIZATIONS = 'NO_CROSS_ORGANIZATIONS';
    public const REASON_INCORRECT_ORGANIZATION = 'INCORRECT_ORGANIZATION';
    public const REASON_ORDER_HAS_CROSS_ORDER = 'HAS_CROSS_ORDER';
    public const REASON_ORDER_NOT_ADDED_TO_1C = 'ORDER_NOT_ADDED_TO_1C';
    public const REASON_ORDER_NOT_ADDED_TO_DB = 'ORDER_NOT_ADDED_TO_DB';
    public const REASON_UNKNOWN_REASON = 'ORDER_UNKNOWN_REASON';
    public const REASON_HAS_ACTIVE_CROSS_ORDER = 'HAS_ACTIVE_CROSS_ORDER';
    public const REASON_NO_SUCCESS_SCORING_WITH_SCORBALL = 'NO_SUCCESS_SCORING_WITH_SCORBALL';
    public const REASON_LOW_SCORISTA_BALL_NK = 'LOW_SCORISTA_BALL_NK';
    public const REASON_LOW_SCORISTA_BALL_PK = 'LOW_SCORISTA_BALL_PK';
}
