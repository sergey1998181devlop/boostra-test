<?php

namespace App\Contracts;

interface UserDncHandlerContract
{
    /**
     * Обработка создания DNC-записи
     * 
     * @param string $phone Номер телефона
     * @param int $days Количество дней блокировки
     * @param int|null $managerId ID менеджера (опционально)
     * @return array Результат операции
     */
    public function handle(string $phone, int $days, ?int $managerId = null): array;

    /**
     * Обработка создания DNC-записи до даты следующего платежа
     *
     * @param string $phone Номер телефона
     * @param int|null $managerId ID менеджера (опционально)
     * @return array Результат операции
     */
    public function handleByPaymentDate(string $phone, ?int $managerId = null): array;
}
