<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class PhoneDto implements BaseDtoInterface
{
    public $countryPrefixCode;
    public $number; // Номер телефона работодателя Формат: 10-ти значное число

    public function __construct(?int $countryPrefixCode = null, ?string $number = null)
    {
        $this->countryPrefixCode = $countryPrefixCode;
        $this->number = $number;
    }

    public function isNull(): bool
    {
        return null === $this->countryPrefixCode && null !== $this->number;
    }
}
