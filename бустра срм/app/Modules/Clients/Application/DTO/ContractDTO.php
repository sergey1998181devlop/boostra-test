<?php

namespace App\Modules\Clients\Application\DTO;

class ContractDTO
{
    private string $order_id;
    private string $number;
    private string $loan_amount;
    private string $period;
    private string $status_1c;
    private string $issued_at;
    private string $return_date;
    private ?string $payment_date;
    private bool $can_prolongation;
    private bool $is_overdue;
    private int $days_overdue;
    private bool $additional_service_on_closure;
    private bool $needs_special_queue;
    private ?string $percent;
    private ?string $sale_info;
    private ?string $buyer;
    private ?string $buyer_phone;
    private ?string $loan_type;
    private array $schedule_payments;
    private array $il_details;
    private bool $is_active;

    public function __construct(
        string $order_id,
        string $number,
        string $loan_amount,
        string $period,
        string $status_1c,
        string $issued_at,
        string $return_date,
        ?string $payment_date,
        bool $can_prolongation,
        bool $is_overdue,
        int $days_overdue,
        bool $additional_service_on_closure,
        bool $needs_special_queue,
        ?string $percent = null,
        ?string $sale_info = null,
        ?string $buyer = null,
        ?string $buyer_phone = null,
        ?string $loan_type = null,
        array $schedule_payments = [],
        array $il_details = [],
        bool $is_active = false
    ) {
        $this->order_id = $order_id;
        $this->number = $number;
        $this->loan_amount = $loan_amount;
        $this->period = $period;
        $this->status_1c = $status_1c;
        $this->issued_at = $issued_at;
        $this->return_date = $return_date;
        $this->payment_date = $payment_date;
        $this->can_prolongation = $can_prolongation;
        $this->is_overdue = $is_overdue;
        $this->days_overdue = $days_overdue;
        $this->additional_service_on_closure = $additional_service_on_closure;
        $this->needs_special_queue = $needs_special_queue;
        $this->percent = $percent;
        $this->sale_info = $sale_info;
        $this->buyer = $buyer;
        $this->buyer_phone = $buyer_phone;
        $this->loan_type = $loan_type;
        $this->schedule_payments = $schedule_payments;
        $this->il_details = $il_details;
        $this->is_active = $is_active;
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->order_id,
            'number' => $this->number,
            'amount' => $this->loan_amount,
            'period' => $this->period,
            '1c_status' => $this->normalize1cStatus($this->status_1c),
            'issued_at' => $this->issued_at,
            'return_date' => $this->return_date,
            'payment_date' => $this->payment_date,
            'can_prolongation' => $this->can_prolongation,
            'is_overdue' => $this->is_overdue,
            'days_overdue' => $this->days_overdue,
            'additional_service_on_closure' => $this->additional_service_on_closure,
            'needs_special_queue' => $this->needs_special_queue,
            'percent' => $this->percent !== null ? round((float)$this->percent, 2) : null,
            'sale_info' => $this->sale_info,
            'buyer' => $this->buyer,
            'buyer_phone' => $this->buyer_phone,
            'loan_type' => $this->loan_type,
            'schedule_payments' => $this->schedule_payments,
            'il_details' => $this->il_details,
            'is_active' => $this->is_active
        ];
    }

    private function normalize1cStatus(string $status): string
    {
        $normalized = preg_replace('/^\d+\.\s*/u', '', $status);
        return $normalized !== null ? $normalized : $status;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}