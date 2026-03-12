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

    /**
     * Получить заявку по ID.
     */
    public function getOrderById(int $orderId): ?object
    {
        $this->model->query(
            "SELECT *
               FROM {$this->model->table}
              WHERE id = ?
              LIMIT 1",
            $orderId
        )->result();

        return $this->model->getData() ?: null;
    }

    /**
     * Последняя кредитная заявка пользователя
     * Логика совпадает с Orders::get_last_order — единый источник данных по проекту.
     */
    public function getLatestOrderByUserId(int $userId): ?object
    {
        $this->model->query(
            "SELECT *
               FROM {$this->model->table}
              WHERE user_id          = ?
                AND DATE(date)       > '2020-01-01'
                AND is_credit_doctor = 0
           ORDER BY id DESC
              LIMIT 1",
            $userId
        )->result();

        return $this->model->getData() ?: null;
    }
}
