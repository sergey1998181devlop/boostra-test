<?php

namespace api\enums;

require_once __DIR__  . '/AbstractEnum.php';

class ProfessionEnum extends AbstractEnum
{
    public const SPECIALIST = 'Специалист';
    public const SENIOR_SPECIALIST = 'Старший специалист';
    public const MANAGER = 'Руководитель';

    public const WORK_OFFICIALLY = "работаю официально";
    public const CIVIL_SERVANT = "государственный служащий";
    public const MUNICIPAL_EMPLOYEE = "муниципальный служащий";
    public const SELF_EMPLOYMENT = "самозанятость";
    public const INDIVIDUAL_ENTREPRENEUR = "индивидуальный предприниматель";
    public const BUSINESS_OWNER = "собственник бизнеса";
    public const WORK_UNOFFICIALLY = "работаю неофициально";
    public const STUDENT = "студент";
    public const RETIRED = "пенсионер";
    public const NOT_WORKING = "не работаю";

    /**
     * Retrieves the list of available values.
     *
     * @return array An array of available values.
     */
    public static function getAvailableValues(): array
    {
        return [
            self::WORK_OFFICIALLY,
            self::CIVIL_SERVANT,
            self::MUNICIPAL_EMPLOYEE,
            self::SELF_EMPLOYMENT,
            self::INDIVIDUAL_ENTREPRENEUR,
            self::BUSINESS_OWNER,
            self::WORK_UNOFFICIALLY,
            self::STUDENT,
            self::RETIRED,
            self::NOT_WORKING
        ];
    }

    public static function specialist(): self
    {
        return new self(self::SPECIALIST);
    }

    public static function manager(): self
    {
        return new self(self::MANAGER);
    }

    public static function senior_specialist(): self
    {
        return new self(self::SENIOR_SPECIALIST);
    }
}