<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final  class ExpensesDto implements BaseDtoInterface
{
   public ?int $amount; // Сумма ежемесячных расходов (в рублях)

   public function __construct(?int $amount = null)
   {
       $this->amount = $amount;
   }

    public function isNull(): bool
    {
        return null === $this->amount;
    }
}
