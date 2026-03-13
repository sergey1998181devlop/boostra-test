<?php

namespace App\Service;

use App\Dto\OrderStatusSyncResultDto;
use App\Repositories\OrdersRepository;
use App\Service\OneC\OneCClient;

class OrderStatusSyncService
{
    private OneCClient $oneCClient;
    private OrdersRepository $ordersRepo;
    
    public function __construct(OrdersRepository $ordersRepo)
    {
        $this->oneCClient = new OneCClient();
        $this->ordersRepo = $ordersRepo;
    }
    
    public function syncOrderStatus(int $orderId): OrderStatusSyncResultDto
    {
        $order = $this->ordersRepo->getOrderOneCStatusInfo($orderId);
        
        if (!$order) {
            return OrderStatusSyncResultDto::error('Заявка не найдена');
        }
        
        if (empty($order->id_1c)) {
            return OrderStatusSyncResultDto::error('У заявки отсутствует ID в 1С');
        }
        
        if (empty($order->status_1c)) {
            return OrderStatusSyncResultDto::error('У заявки отсутствует статус 1С');
        }

        $result = $this->checkOrderStatuses([
            [
                'OrderNumber' => $order->id_1c,
                'Status' => $order->status_1c,
            ]
        ]);
        
        if ($result === null) {
            return OrderStatusSyncResultDto::error('Ошибка при обращении к 1С');
        }
        
        if (!($result['Success'] ?? false)) {
            return OrderStatusSyncResultDto::error(
                $result['Error'] ?? 'Неизвестная ошибка при запросе к 1С'
            );
        }
        
        if (empty($result['Data'])) {
            return OrderStatusSyncResultDto::success(
                false,
                $order->status_1c
            );
        }

        $newStatus = $result['Data'][0]['Status'];
        
        $this->ordersRepo->updateOrderStatus($orderId, [
            '1c_status' => $newStatus,
            'modified' => date('Y-m-d H:i:s'),
        ]);
        
        return OrderStatusSyncResultDto::success(
            true,
            $order->status_1c,
            $order->status_1c,
            $newStatus
        );
    }
    
    public function checkOrderStatuses(array $orders): ?array
    {
        return $this->oneCClient->checkOrderStatuses($orders);
    }
}

