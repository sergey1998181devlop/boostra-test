<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class EstateDto implements BaseDtoInterface
{
    public ?int $realEstateKindCode; // Тип недвижимости в собственности: 6 ¬– Квартира 3 – Дом 11 – Нет
    public ?int $carEstateKindCode; // Тип автомобиля в собственности: 1 – Автомобиль иностранный 7 – Автомобиль отечественный 11 – Нет

    public function __construct(?int $realEstateKindCode, ?int $carEstateKindCode)
    {
        $this->realEstateKindCode = $realEstateKindCode;
        $this->carEstateKindCode = $carEstateKindCode;
    }

    public function isNull(): bool
    {
        return null === $this->realEstateKindCode && null === $this->carEstateKindCode;
    }
}
