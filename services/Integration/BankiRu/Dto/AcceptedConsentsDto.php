<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class AcceptedConsentsDto
{
    public ?string $signature;
    public ?string $acceptDate;

    public function __construct(?string $signature = null, ?string $acceptDate = null)
    {
        $this->signature = $signature;
        $this->acceptDate = $acceptDate;
    }

    public function isNull(): bool
    {
        return null === $this->signature && null !== $this->acceptDate;
    }
}
