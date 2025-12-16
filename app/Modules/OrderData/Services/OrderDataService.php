<?php

namespace App\Modules\OrderData\Services;

use App\Modules\OrderData\Repositories\OrderDataRepository;
use OrderData;
use stdClass;

class OrderDataService
{
    private OrderDataRepository $orderDataRepo;

    public function __construct(OrderDataRepository $orderDataRepo)
    {
        $this->orderDataRepo = $orderDataRepo;
    }

    public function getAdditionalData(int $orderId): StdClass
    {
        $result = new StdClass();
        $additionalServicesOrderData = $this->orderDataRepo->getAdditionalDataFields($orderId);
        foreach (OrderData::ADDITIONAL_SERVICES as $service) {
            $additionalServiceItem = $additionalServicesOrderData->where('key', $service)->first();
            $result->$service = ($additionalServiceItem->value ?? 0) ? 0 : OrderData::ADDITIONAL_SERVICE_DEFAULT_VALUE;
        }

        return $result;
    }
}