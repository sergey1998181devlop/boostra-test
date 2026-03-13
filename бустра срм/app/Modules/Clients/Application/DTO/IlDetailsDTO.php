<?php

namespace App\Modules\Clients\Application\DTO;

/**
 * DTO для детальной информации по займу-инстолменту (IL)
 */
class IlDetailsDTO
{
    private float $balance;
    private float $totalDebtPrincipal;
    private float $totalDebtInterest;
    private float $totalDebt;
    private float $currentDebtPrincipal;
    private float $currentDebtInterest;
    private float $currentDebt;
    private float $overdueDebtPrincipal;
    private float $overdueDebtInterest;
    private float $overdueDebt;
    private ?string $nextPaymentDate;
    private float $nextPaymentAmountPrincipal;
    private float $nextPaymentAmountInterest;
    private float $nextPaymentAmount;
    private float $interestUntilRepayment;
    private float $remainingInterestOnPaymentDate;
    private int $paymentsCount;
    private float $discountedAmount;
    private ?string $overdueStartDate;

    /**
     * Создает DTO из массива данных 1С
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();

        $dto->balance = (float)($data['Баланс'] ?? 0);
        $dto->totalDebtPrincipal = (float)($data['ОбщийДолг_ОД'] ?? 0);
        $dto->totalDebtInterest = (float)($data['ОбщийДолг_Проценты'] ?? 0);
        $dto->totalDebt = (float)($data['ОбщийДолг'] ?? 0);
        $dto->currentDebtPrincipal = (float)($data['ТекущийДолг_ОД'] ?? 0);
        $dto->currentDebtInterest = (float)($data['ТекущийДолг_Проценты'] ?? 0);
        $dto->currentDebt = (float)($data['ТекущийДолг'] ?? 0);
        $dto->overdueDebtPrincipal = (float)($data['ПросроченныйДолг_ОД'] ?? 0);
        $dto->overdueDebtInterest = (float)($data['ПросроченныйДолг_Процент'] ?? 0);
        $dto->overdueDebt = (float)($data['ПросроченныйДолг'] ?? 0);
        $dto->nextPaymentDate = !empty($data['БлижайшийПлатеж_Дата'])
            ? self::formatDate($data['БлижайшийПлатеж_Дата'])
            : null;
        $dto->nextPaymentAmountPrincipal = (float)($data['БлижайшийПлатеж_Сумма_ОД'] ?? 0);
        $dto->nextPaymentAmountInterest = (float)($data['БлижайшийПлатеж_Сумма_Процент'] ?? 0);
        $dto->nextPaymentAmount = (float)($data['БлижайшийПлатеж_Сумма'] ?? 0);
        $dto->interestUntilRepayment = (float)($data['ПроцентыДоПогашения'] ?? 0);
        $dto->remainingInterestOnPaymentDate = (float)($data['ОстатокПроцентовНаДатуПлатежа'] ?? 0);
        $dto->paymentsCount = (int)($data['КоличествоПлатежей'] ?? 0);
        $dto->discountedAmount = (float)($data['СуммаСоСкидкой'] ?? 0);
        $dto->overdueStartDate = !empty($data['ДатаНачалаПросрочки'])
            ? self::formatDate($data['ДатаНачалаПросрочки'])
            : null;

        return $dto;
    }

    /**
     * Преобразует дату из формата 1С в ISO формат
     *
     * @param string $date
     * @return string|null
     */
    private static function formatDate(string $date): ?string
    {
        try {
            $dateTime = new \DateTime($date);
            return $dateTime->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Преобразует DTO в массив
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'balance' => $this->balance,
            'total_debt' => [
                'principal' => $this->totalDebtPrincipal,
                'interest' => $this->totalDebtInterest,
                'total' => $this->totalDebt,
            ],
            'current_debt' => [
                'principal' => $this->currentDebtPrincipal,
                'interest' => $this->currentDebtInterest,
                'total' => $this->currentDebt,
            ],
            'overdue_debt' => [
                'principal' => $this->overdueDebtPrincipal,
                'interest' => $this->overdueDebtInterest,
                'total' => $this->overdueDebt,
            ],
            'next_payment' => [
                'date' => $this->nextPaymentDate,
                'amount_principal' => $this->nextPaymentAmountPrincipal,
                'amount_interest' => $this->nextPaymentAmountInterest,
                'amount_total' => $this->nextPaymentAmount,
            ],
            'interest_until_repayment' => $this->interestUntilRepayment,
            'remaining_interest_on_payment_date' => $this->remainingInterestOnPaymentDate,
            'payments_count' => $this->paymentsCount,
            'discounted_amount' => $this->discountedAmount,
            'overdue_start_date' => $this->overdueStartDate,
        ];
    }

    // Геттеры для доступа к свойствам
    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getTotalDebt(): float
    {
        return $this->totalDebt;
    }

    public function getCurrentDebt(): float
    {
        return $this->currentDebt;
    }

    public function getOverdueDebt(): float
    {
        return $this->overdueDebt;
    }

    public function getNextPaymentDate(): ?string
    {
        return $this->nextPaymentDate;
    }

    public function getNextPaymentAmount(): float
    {
        return $this->nextPaymentAmount;
    }

    public function getPaymentsCount(): int
    {
        return $this->paymentsCount;
    }

    public function getDiscountedAmount(): float
    {
        return $this->discountedAmount;
    }

    public function getOverdueStartDate(): ?string
    {
        return $this->overdueStartDate;
    }
}
