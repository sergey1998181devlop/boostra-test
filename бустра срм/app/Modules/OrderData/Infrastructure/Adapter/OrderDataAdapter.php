<?php

namespace App\Modules\OrderData\Infrastructure\Adapter;

use App\Modules\OrderData\Application\Service\OrderDataService;

/**
 * Class OrderDataAdapter
 * Адаптер для интеграции OrderDataService с существующим кодом
 */
class OrderDataAdapter
{
    private OrderDataService $orderDataService;

    public function __construct(OrderDataService $orderDataService)
    {
        $this->orderDataService = $orderDataService;
    }

    public function addRefererId(array $orders): array
    {
        if (empty($orders)) {
            return $orders;
        }

        $orderIds = [];
        foreach ($orders as $orderData) {
            if (!empty($orderData->order_id)) {
                $orderIds[] = (int)$orderData->order_id;
            }
        }

        if (empty($orderIds)) {
            return $orders;
        }

        $refererIds = $this->orderDataService->getRefererIds($orderIds);
        foreach ($orders as $orderData) {
            if (!empty($orderData->order_id) && isset($refererIds[$orderData->order_id])) {
                $orderData->referer_id = $refererIds[$orderData->order_id];
            }
        }

        return $orders;
    }
}