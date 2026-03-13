<?php

namespace App\Models;

use App\Core\Models\BaseModel;
use App\Enums\AdditionalServiceReturnStatus;

abstract class AbstractServiceModel extends BaseModel
{
    public function getByOrderId(int $orderId, array $fields = []): array
    {
        return $this->select($fields, [
            'order_id' => $orderId,
            'status' => 'SUCCESS'
        ])->getData() ?? [];
    }

    public function getActiveByOrderId(int $orderId, array $fields = []): array
    {
        return $this->select($fields, [
            'order_id' => $orderId,
            'status' => 'SUCCESS',
            'OR' => [
                'return_status[!]' => AdditionalServiceReturnStatus::RETURNED,
                'return_transaction_id' => [0, null],
            ],
        ])->getData() ?? [];
    }

    public function getReturnedByOrderId(int $orderId, array $fields = []): array
    {
        return $this->select($fields, [
            'order_id' => $orderId,
            'return_status' => AdditionalServiceReturnStatus::RETURNED,
            'return_transaction_id[!]' => 0
        ])->getData() ?? [];
    }

    public function getReturnedByUserId(int $userId, array $fields = []): array
    {
        return $this->select($fields, [
            'user_id' => $userId,
            'return_status' => AdditionalServiceReturnStatus::RETURNED,
            'return_transaction_id[!]' => 0
        ])->getData() ?? [];
    }

    public function getActiveByUserId(int $userId, array $fields = []): array
    {
        return $this->select($fields, [
            'user_id' => $userId,
            'status' => 'SUCCESS',
            'OR' => [
                'return_status[!]' => AdditionalServiceReturnStatus::RETURNED,
                'return_transaction_id' => [0, null],
            ],
        ])->getData() ?? [];
    }

    public function getAllByUserId(int $userId, array $fields = []): array
    {
        return $this->select($fields, [
            'user_id' => $userId
        ])->getData() ?? [];
    }
}
