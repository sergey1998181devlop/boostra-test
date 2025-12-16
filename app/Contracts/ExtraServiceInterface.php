<?php

namespace App\Contracts;

interface ExtraServiceInterface
{
    /**
     * Проверить видимость сервиса для пользователя
     *
     * @param int $userId ID пользователя
     * @return array Массив с информацией о видимости сервиса
     */
    public function checkVisibility(int $userId): array;

    /**
     * Получить цену сервиса
     *
     * @param int $amount Сумма займа
     * @param bool $isNewClient Новый ли клиент
     * @return object|null Объект с ценой и ID сервиса или null если сервис недоступен
     */
    public function getServicePrice(int $amount, bool $isNewClient = true): ?object;
} 