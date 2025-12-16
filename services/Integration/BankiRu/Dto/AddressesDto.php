<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class AddressesDto implements BaseDtoInterface
{
    public  $typeCode;
    public  $addressString;
    public  $addressKladrCode;
    public  $dateStart;

    public function __construct(?int $typeCode, ?string $addressString, ?string $addressKladrCode, ?string $dateStart)
    {
        $this->typeCode = $typeCode;
        $this->addressString = $addressString;
        $this->addressKladrCode = $addressKladrCode;
        $this->dateStart = $dateStart;
    }

    public function isNull(): bool
    {
        return null === $this->typeCode &&
               null !== $this->addressString &&
               null !== $this->addressKladrCode &&
               null !== $this->dateStart;
    }
}
