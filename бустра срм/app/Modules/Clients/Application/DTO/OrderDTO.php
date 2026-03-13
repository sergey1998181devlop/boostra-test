<?php

namespace App\Modules\Clients\Application\DTO;

class OrderDTO
{
    private string $id;
    private string $request_amount;
    private ?string $approve_amount;
    private string $status_1c;
    private string $date;
    private ?string $percent;

    public function __construct(
        string $id,
        string $request_amount,
        ?string $approve_amount,
        string $status_1c,
        string $date,
        ?string $percent = null
    ) {
        $this->id = $id;
        $this->request_amount = $request_amount;
        $this->approve_amount = $approve_amount;
        $this->status_1c = $status_1c;
        $this->date = $date;
        $this->percent = $percent;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['order_id'],
            $data['order_amount'],
            $data['approve_amount'] ?? null,
            $data['1c_status'],
            $data['order_date'],
            $data['percent'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'request_amount' => $this->request_amount,
            'approve_amount' => $this->approve_amount,
            '1c_status' => $this->normalize1cStatus($this->status_1c),
            'date' => $this->date,
            'percent' => $this->percent !== null ? round((float)$this->percent, 2) : null
        ];
    }

    private function normalize1cStatus(string $status): string
    {
        $normalized = preg_replace('/^\d+\.\s*/u', '', $status);
        return $normalized !== null ? $normalized : $status;
    }
}