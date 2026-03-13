<?php

namespace App\Modules\AdditionalServiceRecovery\Application\DTO;

use DateTime;

/**
 * DTO для создания или обновления записи в списке исключений.
 */
final class ExclusionRequest
{
    public int $userId;
    public ?int $orderId;
    public ?string $serviceKey;
    public string $reason;
    public int $managerId;
    public ?DateTime $expiresAt;

    public function __construct(
        int $userId,
        ?int $orderId,
        ?string $serviceKey,
        string $reason,
        int $managerId,
        ?DateTime $expiresAt = null
    ) {
        $this->userId = $userId;
        $this->orderId = $orderId;
        $this->serviceKey = $serviceKey;
        $this->reason = $reason;
        $this->managerId = $managerId;
        $this->expiresAt = $expiresAt;
    }
}