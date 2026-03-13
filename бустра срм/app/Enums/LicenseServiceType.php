<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

/**
 * Типы услуг для отправки лицензионных SMS и их маппинги
 */
class LicenseServiceType extends Enum
{
    public const DOCTOR_SMS_KEY = 'doctor-sms-key';
    public const ORACLE_SMS_KEY = 'oracle-sms-key';
    public const VITA_SMS_KEY = 'vita-sms-key';
    public const CONCIERGE_SMS_KEY = 'concierge-sms-key';

    /**
     * Список поддерживаемых типов
     */
    public static function all(): array
    {
        return [
            self::DOCTOR_SMS_KEY,
            self::ORACLE_SMS_KEY,
            self::VITA_SMS_KEY,
            self::CONCIERGE_SMS_KEY,
        ];
    }

    /**
     * Человекочитаемое имя услуги по типу
     */
    public static function getName(string $type): string
    {
        $map = [
            self::DOCTOR_SMS_KEY => 'Финансовый доктор',
            self::ORACLE_SMS_KEY => 'Звёздный оракул',
            self::VITA_SMS_KEY => 'Вита-мед',
            self::CONCIERGE_SMS_KEY => 'Консьерж сервис',
        ];

        return $map[$type] ?? 'неизвестная услуга';
    }

    /**
     * Маппинг типа на тип полиса для поиска документа
     */
    public static function getPolicyType(string $type): ?string
    {
        $map = [
            self::DOCTOR_SMS_KEY => 'CREDIT_DOCTOR_POLICY',
            self::ORACLE_SMS_KEY => 'STAR_ORACLE_POLICY',
            self::VITA_SMS_KEY => 'ACCEPT_TELEMEDICINE',
            self::CONCIERGE_SMS_KEY => 'DOC_MULTIPOLIS',
        ];

        return $map[$type] ?? null;
    }

    /**
     * Таблица покупок услуги по типу
     */
    public static function getServiceTable(string $type): ?string
    {
        $map = [
            self::DOCTOR_SMS_KEY => 's_credit_doctor_to_user',
            self::ORACLE_SMS_KEY => 's_star_oracle',
            self::VITA_SMS_KEY => 's_tv_medical_payments',
            self::CONCIERGE_SMS_KEY => 's_multipolis',
        ];

        return $map[$type] ?? null;
    }

    /**
     * Проверка валидности типа услуги
     */
    public static function validate(string $type): bool
    {
        return in_array($type, self::all(), true);
    }
}


