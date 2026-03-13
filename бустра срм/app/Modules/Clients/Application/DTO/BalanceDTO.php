<?php

namespace App\Modules\Clients\Application\DTO;

use Carbon\Carbon;

class BalanceDTO
{
    private float $accrued_interest;
    private float $penalty;
    private float $clear_debt;
    private float $debt;
    private bool $has_fines;

    public function __construct(
        float $accrued_interest,
        float $penalty,
        float $clear_debt,
        float $debt,
        bool $has_fines
    ) {
        $this->accrued_interest = $accrued_interest;
        $this->penalty = $penalty;
        $this->clear_debt = $clear_debt;
        $this->debt = $debt;
        $this->has_fines = $has_fines;
    }

    public static function createEmpty(): self
    {
        return new self(
            0.0,
            0.0,
            0.0,
            0.0,
            false
        );
    }

    public function toArray(): array
    {
        return [
            'accrued_interest' => number_format($this->accrued_interest,2, '.', ''),
            'penalty' => number_format($this->penalty, 2, '.', ''),
            'clear_debt' => number_format($this->clear_debt,2, '.', ''),
            'debt' => number_format($this->debt, 2, '.', ''),
            'debt_date' => Carbon::now()->format('d.m.Y'),
            'debt_with_date' => Carbon::now()->format('d.m.Y') . '* - ' . number_format($this->debt, 2, '.', ''),
            'debt_note' => 'Сумма указана на дату обращения и может увеличиваться при начислении процентов, согласно вашему договору займа.',
            'has_fines' => $this->has_fines
        ];
    }
}