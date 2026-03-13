<?php

namespace App\Modules\AdditionalServiceRecovery\Domain\Model;

use DateTimeImmutable;

/**
 * Модель кандидата на восстановление услуги.
 * Представляет собой отключенную услугу, которую можно потенциально восстановить.
 */
class ServiceCandidate
{
    /** @var int ID заказа */
    private int $orderId;

    /** @var int ID клиента */
    private int $userId;

    /** @var int ID менеджера, который отключил услугу */
    private int $managerId;

    /** @var string Ключ отключенной услуги */
    private string $serviceKey;

    /** @var DateTimeImmutable Дата и время отключения услуги */
    private DateTimeImmutable $disabledAt;

    /** @var float Сумма займа по заказу */
    private float $loanAmount;

    /**
     * @param int $orderId
     * @param int $userId
     * @param int $managerId
     * @param string $serviceKey
     * @param DateTimeImmutable $disabledAt
     * @param float $loanAmount
     */
    public function __construct(int $orderId, int $userId, int $managerId, string $serviceKey, DateTimeImmutable $disabledAt, float $loanAmount)
    {
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->managerId = $managerId;
        $this->serviceKey = $serviceKey;
        $this->disabledAt = $disabledAt;
        $this->loanAmount = $loanAmount;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getManagerId(): int
    {
        return $this->managerId;
    }

    /**
     * @return string
     */
    public function getServiceKey(): string
    {
        return $this->serviceKey;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDisabledAt(): DateTimeImmutable
    {
        return $this->disabledAt;
    }

    /**
     * @return float
     */
    public function getLoanAmount(): float
    {
        return $this->loanAmount;
    }

    /**
     * Рассчитывает, сколько дней прошло с момента отключения услуги.
     *
     * @return int
     */
    public function getDaysSinceDisabled(): int
    {
        $now = new DateTimeImmutable();
        $interval = $this->disabledAt->diff($now);
        return (int)$interval->format('%a');
    }
}