<?php

namespace App\Models;

use App\Core\Models\BaseModel;
use ReflectionClass;

class OrderData extends BaseModel
{
    public string $table = 's_order_data';

    /** @var string Консьерж при пролонгации,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_MULTIPOLIS = 'additional_service_multipolis';

    /** @var string Вита-мед при пролонгации, 1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_TV_MED = 'additional_service_tv_med';

    /** @var string Доп. услуга на частичном закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_PARTIAL_REPAYMENT = 'additional_service_partial_repayment';

    /** @var string  Доп. услуга на частичном закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT = 'half_additional_service_partial_repayment';

    /** @var string Доп. услуга на закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_REPAYMENT = 'additional_service_repayment';

    /** @var string Доп. услуга при выдаче, 1 отключен, 0 включен */
    public const DISABLE_ADDITIONAL_SERVICE_ON_ISSUE = 'disable_additional_service_on_issue';

    /** @var string Доп. услуга на закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_REPAYMENT = 'half_additional_service_repayment';

    /** @var string Звездный Оракул(SO) на закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_SO_REPAYMENT = 'additional_service_so_repayment';

    /** @var string Звездный Оракул(SO) на закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_SO_REPAYMENT = 'half_additional_service_so_repayment';

    /** @var string Звездный Оракул(SO) на частичном закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT = 'additional_service_so_partial_repayment';

    /** @var string Звездный Оракул(SO) на частичном закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT = 'half_additional_service_so_partial_repayment';

    /** @var string Если активен, то настройки crm приоритетнее на пролонгации */
    public const CONSIDER_PROLONGATION_SERVICES = 'consider_prolongation_services';

    /**
     * Возвращает список констант с приставкой "ADDITIONAL_SERVICE_".
     *
     * @return array
     */
    public static function getAdditionalServiceList(): array
    {
        $reflectionClass = new ReflectionClass(self::class);
        $constants = $reflectionClass->getConstants();

        return array_values(array_filter($constants, function ($value, $name) {
            return strpos($name, 'ADDITIONAL_SERVICE_') === 0;
        }, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Возвращает список констант с приставкой "HALF_ADDITIONAL_SERVICE_".
     *
     * @return array
     */
    public static function getHalfAdditionalServiceList(): array
    {
        $reflectionClass = new ReflectionClass(self::class);
        $constants = $reflectionClass->getConstants();

        return array_values(array_filter($constants, function ($value, $name) {
            return strpos($name, 'HALF_ADDITIONAL_SERVICE_') === 0;
        }, ARRAY_FILTER_USE_BOTH));
    }
}
