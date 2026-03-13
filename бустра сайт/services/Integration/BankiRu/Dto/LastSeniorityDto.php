<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class LastSeniorityDto implements BaseDtoInterface
{
    public ?int $value; //Стаж на последнем месте работы, в месяцах (передается количество месяцев)

    public function __construct(?int $value)
    {
        $this->value = $value;
    }

    public function isNull(): bool
    {
        return null === $this->value;
    }
}
