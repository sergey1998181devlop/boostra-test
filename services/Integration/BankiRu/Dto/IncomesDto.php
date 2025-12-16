<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class IncomesDto implements BaseDtoInterface
{
    public ?int $amount; // Сумма ежемесячного дохода (в рублях)

    /**
     * Код способа подтверждения дохода Клиента:
     * 1 – Справка 2-НДФЛ
     * 2 – Справка по форме Банка
     * 6 – Другой документ
     * 4 – Доход подтвердить невозможно
     */
    public ?int $incomeProffCode;

    public function __construct(?int $amount, ?int $incomeProffCode)
    {
        $this->amount = $amount;
        $this->incomeProffCode = $incomeProffCode;
    }

    public function isNull(): bool
    {
        return null === $this->incomeProffCode && null === $this->amount;
    }
}
