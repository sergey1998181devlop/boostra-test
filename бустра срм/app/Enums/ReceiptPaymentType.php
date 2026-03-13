<?php

namespace App\Enums;

class ReceiptPaymentType
{
    public const RETURN_CREDIT_DOCTOR = 'return_credit_doctor';
    public const RETURN_PENALTY_CREDIT_DOCTOR = 'return_penalty_credit_doctor';
    public const RETURN_MULTIPOLIS = 'return_multipolis';
    public const RETURN_TV_MEDICAL = 'return_tv_medical';
    public const RETURN_STAR_ORACLE = 'return_star_oracle';

    public static array $paymentDescriptions = [
        self::RETURN_CREDIT_DOCTOR => 'Возврат за ПО «Финансовый Доктор»',
        self::RETURN_PENALTY_CREDIT_DOCTOR => 'Возврат по услуге Кредитный Доктор',
        self::RETURN_MULTIPOLIS => 'Возврат за ПО «Консьерж сервис»',
        self::RETURN_TV_MEDICAL => 'Возврат за ПО «ВитаМед»',
        self::RETURN_STAR_ORACLE => 'Возврат за «Звездный Оракул»',
    ];

    public static function getPaymentDescription(string $type): string
    {
        return self::$paymentDescriptions[$type] ?? '';
    }

    /**
     * Получить тип возврата для чека по типу доп.услуги
     */
    public static function getReturnTypeByServiceType(string $serviceType, bool $isPenalty = false): string
    {
        switch ($serviceType) {
            case 'credit_doctor':
                return $isPenalty ? self::RETURN_PENALTY_CREDIT_DOCTOR : self::RETURN_CREDIT_DOCTOR;
            case 'star_oracle':
                return self::RETURN_STAR_ORACLE;
            case 'multipolis':
                return self::RETURN_MULTIPOLIS;
            case 'tv_medical':
                return self::RETURN_TV_MEDICAL;
            default:
                return '';
        }
    }
}
