<?php

namespace App\Contracts;

interface ToggleAutodebitHandlerContract
{
    /**
     * Переключает автодебет для платежных средств пользователя
     *
     * @param int $userId ID пользователя
     * @param int $orderId ID заказа
     * @param int $value 0 (выключить) или 1 (включить)
     * @param int $managerId ID менеджера
     * @return array ['success' => bool, 'message' => string]
     * @throws \Exception
     */
    public function handle(int $userId, int $orderId, int $value, int $managerId): array;
}
