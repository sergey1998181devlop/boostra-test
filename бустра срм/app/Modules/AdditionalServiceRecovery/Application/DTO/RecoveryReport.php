<?php

namespace App\Modules\AdditionalServiceRecovery\Application\DTO;

use DateTimeInterface;

final class RecoveryReport
{
    private DateTimeInterface $dateFrom;
    private DateTimeInterface $dateTo;
    private float $totalRevenue;
    private float $totalRefunds;
    private float $totalNetRevenue;
    private int $totalReenabled;
    private int $totalPaid;
    private array $details;

    public function __construct(
        DateTimeInterface $dateFrom,
        DateTimeInterface $dateTo,
        float $totalRevenue,
        float $totalRefunds,
        float $totalNetRevenue,
        int $totalReenabled,
        int $totalPaid,
        array $details
    ) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->totalRevenue = $totalRevenue;
        $this->totalRefunds = $totalRefunds;
        $this->totalNetRevenue = $totalNetRevenue;
        $this->totalReenabled = $totalReenabled;
        $this->totalPaid = $totalPaid;
        $this->details = $details;
    }

    public function getDateFrom(): DateTimeInterface
    {
        return $this->dateFrom;
    }

    public function getDateTo(): DateTimeInterface
    {
        return $this->dateTo;
    }

    public function getTotalRevenue(): float
    {
        return $this->totalRevenue;
    }

    public function getTotalRefunds(): float
    {
        return $this->totalRefunds;
    }

    public function getTotalNetRevenue(): float
    {
        return $this->totalNetRevenue;
    }

    public function getTotalReenabled(): int
    {
        return $this->totalReenabled;
    }

    public function getTotalPaid(): int
    {
        return $this->totalPaid;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
