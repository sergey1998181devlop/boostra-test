<?php

namespace App\Contracts;

interface AdditionalServicesHandlerContract
{
    public function getByOrderId(int $orderId, ?string $serviceType = null, bool $isReturned = true): array;

    public function getByUserId(int $userId, bool $isReturned = true): array;
}
