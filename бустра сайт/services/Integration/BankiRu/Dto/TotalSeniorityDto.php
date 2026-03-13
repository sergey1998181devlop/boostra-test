<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class TotalSeniorityDto implements BaseDtoInterface
{
    public ?int $value; // Общий трудовой стаж. Передается одно из значений: 3 – стаж менее 6 месяцев 9 – стаж от 6 месяцев до 1 года 24 – стаж от 1 года до 3 лет 48 – стаж от 3 до 5 лет 90 – стаж от 5 до 10 лет 120 - стаж более 10 лет

    public function __construct(?int $value)
    {
        $this->value = $value;
    }

    public function isNull(): bool
    {
        return null === $this->value;
    }
}