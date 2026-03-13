<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class initialPaymentDto
{
    public ?int $amount;

    public function __construct(?int $amount)
    {
        $this->amount = $amount;
    }

    public function isNull(): bool
    {
       return null === $this->amount;
    }
}
