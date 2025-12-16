<?php

namespace App\Repositories;

use App\Core\Models\BaseModel;

class OrderRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new BaseModel();
        $this->model->table = '__orders';
    }

    /**
     * Получить последний скорбалл клиента.
     */
    public function getActiveOrderByUserId(int $userId): ?int
    {
        $this->model->query(
            "SELECT scorista_ball 
               FROM {$this->model->table} 
              WHERE user_id = ? 
                AND status  = ? 
           ORDER BY date DESC 
              LIMIT 1",
            $userId,
            2
        )->result();

        $record = $this->model->getData();

        if (!$record) {
            return null;
        }

        return isset($record->scorista_ball)
            ? (int)$record->scorista_ball
            : null;
    }
}
