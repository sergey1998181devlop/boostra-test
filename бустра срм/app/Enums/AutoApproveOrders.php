<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class AutoApproveOrders extends Enum
{
    // Причины для s_auto_approve_nk
    /** @var string Статусы валидации пользователя */
    public const REASON_NO_USER = 'NO_USER';
    public const REASON_USER_HAS_OPEN_ORDER = 'USER_HAS_OPEN_ORDER';
    public const REASON_USER_HAS_EXCESSED_MAX_ORDERS_AMOUNT = 'USER_HAS_EXCESSED_MAX_ORDERS_AMOUNT';
    public const REASON_USER_HAS_MORATORIUM = 'USER_HAS_MORATORIUM';
    public const REASON_USER_IS_BLOCKED = 'USER_IS_BLOCKED';
    public const REASON_USER_IS_BLOCKED_IN_1C = 'USER_IS_BLOCKED_IN_1C';

    /** @var string Статусы валидации заявки */
    public const REASON_NO_ORDER = 'NO_ORDER';
    public const REASON_ORDER_NOT_B2P = 'ORDER_NOT_B2P';
    public const REASON_ORDER_IN_BLACKLIST = 'ORDER_IN_BLACKLIST';
    public const REASON_LAST_ORDER_NOT_CLOSED = 'LAST_ORDER_NOT_CLOSED';
    public const REASON_OLD_LAST_ORDER = 'OLD_LAST_ORDER';

    /** @var string Статусы валидации карты */
    public const REASON_NO_AVAILABLE_CARDS = 'NO_AVAILABLE_CARDS';
    public const REASON_NO_NOT_DELETED_CARDS = 'NO_NOT_DELETED_CARDS';
    public const REASON_NO_NOT_DELETED_SBP = 'NO_NOT_DELETED_SBP';
    public const REASON_INCORRECT_CARD_TYPE = 'INCORRECT_CARD_TYPE';

    /** @var string Статусы валидации скорингов */
    public const REASON_BLACKLIST_SCORING = 'ERROR_BLACKLIST_SCORING';
    public const REASON_FMS_SCORING = 'ERROR_FMS_SCORING';
    public const REASON_FNS_SCORING = 'ERROR_FNS_SCORING';
    public const REASON_EFRSB_SCORING = 'ERROR_EFRSB_SCORING';

    public const REASON_ORDER_NOT_ADDED_TO_1C = 'ORDER_NOT_ADDED_TO_1C';
    public const RESEND_ORDER_TO_1C = 'RESEND_ORDER_TO_1C';
    public const REASON_ORDER_NOT_ADDED_TO_DB = 'ORDER_NOT_ADDED_TO_DB';


    // Причины для s_orders_auto_approve
    public const REASON_TIMEOUT = 'TIMEOUT';
    public const NO_AXI_OR_MAXIMUM_AXI = 'NO_AXI_OR_MAXIMUM_AXI';
    public const REASON_AXI_EMPTY_BODY = 'AXI_EMPTY_BODY';
    public const REASON_AXI_UNKNOWN_REASON = 'AXI_UNKNOWN_REASON';
    public const REASON_AXI_ERROR = 'AXI_ERROR';
    public const REASON_AXI_WAIT = 'AXI_WAIT';
    public const REASON_REPORT_WAIT = 'REPORT_WAIT';
    public const REASON_ORDER_ORG_SWITCH_WAIT = 'ORDER_ORG_SWITCH_WAIT';
    public const REASON_NOT_UPDATED_ORDER_1C_STATUS = 'NOT_UPDATED_ORDER_1C_STATUS';
    public const REASON_GENERATE_ORDER_ERROR = 'GENERATE_ORDER_ERROR';
    public const REASON_UNEXPECTED_ERROR_REASON = 'UNEXPECTED_ERROR_REASON';
    public const REASON_INAPPROPRIATE_STATUS = 'INAPPROPRIATE_STATUS';
}
