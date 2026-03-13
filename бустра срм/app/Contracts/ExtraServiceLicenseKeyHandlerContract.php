<?php

namespace App\Contracts;

interface ExtraServiceLicenseKeyHandlerContract
{
    /**
     * Получить лицензионный ключ для дополнительной услуги
     *
     * @param string $contractNumber Номер договора
     * @param string $serviceType Тип услуги
     * @return array{success: bool, message: string, license_key?: string, service_name?: string, created?: string}
     */
    public function getLicenseKey(string $contractNumber, string $serviceType): array;
}
