<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class OrderOrgSwitch extends Enum
{
    // Причины отказа переключения организации
    public const REASON_SETTING_DISABLED = 'SETTING_DISABLED';
    public const REASON_NOT_FIRST_LOAN = 'NOT_FIRST_LOAN';
    public const REASON_CROSS_ORDER = 'CROSS_ORDER';
    public const REASON_INAPPROPRIATE_STATUS = 'INAPPROPRIATE_STATUS';
    public const REASON_INAPPROPRIATE_ORGANIZATION = 'INAPPROPRIATE_ORGANIZATION';
    public const REASON_PING3_ORDER = 'PING3_ORDER';
    public const REASON_ORDER_AMOUNT_EXCEEDED = 'ORDER_AMOUNT_EXCEEDED';
    public const REASON_ALREADY_SWITCHED = 'ALREADY_SWITCHED';
    public const REASON_NOT_TEST_USER = 'NOT_TEST_USER';
    public const REASON_NO_SUCCESS_AXILINK = 'NO_SUCCESS_AXILINK';
    public const REASON_FAILED_UPRID = 'BAD_UPRID';
    public const REASON_NOT_CHANCE = 'NOT_CHANCE'; // Шанс не выпал
    public const REASON_DAY_LIMIT_EXCEEDED = 'DAY_LIMIT_EXCEEDED';
    public const REASON_NO_PDN = 'NO_PDN';
    public const REASON_INCORRECT_PDN = 'INCORRECT_PDN';
    public const REASON_NEW_TERRITORY_PDN = 'NEW_TERRITORY_PDN';
    public const REASON_PDN_EXCEEDED = 'PDN_EXCEEDED';
    public const REASON_CREATE_ORDER_FAILED = 'CREATE_ORDER_FAILED';
    public const REASON_PREVIOUSLY_REJECTED_BY_CHANCE = 'PREVIOUSLY_REJECTED_BY_CHANCE';
    public const REASON_UNKNOWN_ERROR = 'UNKNOWN_ERROR';

    /** @var string Успешное прохождение ручейка со сменой организации */
    public const REASON_SUCCESS_WITH_ORGANIZATION_SWITCH = 'SUCCESS_WITH_ORGANIZATION_SWITCH';

    /** @var string Успешное прохождение ручейка без смены организации */
    public const REASON_SUCCESS_WITHOUT_ORGANIZATION_SWITCH = 'SUCCESS_WITHOUT_ORGANIZATION_SWITCH';

    /** @var string Только одна (исходная) организация входит в МПЛ, поэтому не меняем организацию */
    public const REASON_ONLY_MAIN_ORGANIZATION_IN_MPL = 'ONLY_MAIN_ORGANIZATION_IN_MPL';
    public const REASON_ORDER_NOT_IN_ANY_MPL = 'ORDER_NOT_IN_ANY_MPL'; // Заявка не попала ни в какой МПЛ
}
