<?php

namespace App\Modules\OrderData\Application\Service;

use App\Modules\OrderData\Infrastructure\Repository\OrderDataRepository;

/**
 * Class OrderDataService
 * Сервис для работы с дополнительными данными заказов
 */
class OrderDataService
{
    private OrderDataRepository $repository;

    public function __construct(OrderDataRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Получает referer_id для займа из таблицы s_order_data
     *
     * @param int $orderId ID заказа
     * @return string|null Значение referer_id или null если не найдено
     */
    public function getRefererId(int $orderId): ?string
    {
        return $this->repository->getValueByKey($orderId, 'referer_id');
    }

    /**
     * Получает referer_id для нескольких заказов
     *
     * @param array $orderIds Массив ID заказов
     * @return array Ассоциативный массив order_id => referer_id
     */
    public function getRefererIds(array $orderIds): array
    {
        return $this->repository->getValuesByOrderIds($orderIds, 'referer_id');
    }

    /**
     * Устанавливает referer_id для заказа
     *
     * @param int $orderId ID заказа
     * @param string $refererId Значение referer_id
     * @return bool Результат операции
     */
    public function setRefererId(int $orderId, string $refererId): bool
    {
        return $this->repository->setValue($orderId, 'referer_id', $refererId);
    }

    /**
     * Получает значение по произвольному ключу для заказа
     *
     * @param int $orderId ID заказа
     * @param string $key Ключ данных
     * @return string|null Значение или null если не найдено
     */
    public function getValue(int $orderId, string $key): ?string
    {
        return $this->repository->getValueByKey($orderId, $key);
    }

    /**
     * Получает все данные для заказа
     *
     * @param int $orderId ID заказа
     * @return array Ассоциативный массив ключ => значение
     */
    public function getAllData(int $orderId): array
    {
        return $this->repository->getAllByOrderId($orderId);
    }

    /**
     * Устанавливает значение для заказа
     *
     * @param int $orderId ID заказа
     * @param string $key Ключ данных
     * @param string $value Значение
     * @return bool Результат операции
     */
    public function setValue(int $orderId, string $key, string $value): bool
    {
        return $this->repository->setValue($orderId, $key, $value);
    }

    /**
     * Удаляет значение для заказа
     *
     * @param int $orderId ID заказа
     * @param string $key Ключ данных
     * @return bool Результат операции
     */
    public function deleteValue(int $orderId, string $key): bool
    {
        return $this->repository->deleteValue($orderId, $key);
    }
}