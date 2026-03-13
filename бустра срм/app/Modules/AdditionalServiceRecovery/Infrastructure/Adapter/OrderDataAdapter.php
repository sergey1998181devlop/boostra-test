<?php

namespace App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter;

use OrderData;

/**
 * Адаптер для legacy OrderData
 */
class OrderDataAdapter
{
    /** @var OrderData */
    private OrderData $orderData;

    /**
     * @param OrderData $orderData
     */
    public function __construct(OrderData $orderData)
    {
        $this->orderData = $orderData;
    }

    /**
     * Устанавливает статус услуги в s_order_data.
     *
     * @param int $orderId
     * @param string $serviceKey
     * @param string $status '0' для включения, '1' для выключения
     * @return void
     */
    public function setServiceStatus(int $orderId, string $serviceKey, string $status): void
    {
        $this->orderData->set($orderId, $serviceKey, $status);
    }
}