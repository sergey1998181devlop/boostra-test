<?php

namespace App\Modules\TicketAssignment\Dto;

/**
 * DTO для данных о назначении тикета
 */
class AssignmentDto
{
    /** @var int */
    private $ticketId;
    
    /** @var int */
    private $managerId;
    
    /** @var string */
    private $type;
    
    /** @var int|null */
    private $overdueDays;
    
    /** @var string */
    private $complexityLevel;
    
    /** @var float */
    private $coefficient;

    /** @var string|null */
    private $assignedAt;

    public function __construct(
        int $ticketId,
        int $managerId,
        string $type,
        ?int $overdueDays,
        string $complexityLevel,
        float $coefficient,
        ?string $assignedAt = null
    ) {
        $this->ticketId = $ticketId;
        $this->managerId = $managerId;
        $this->type = $type;
        $this->overdueDays = $overdueDays;
        $this->complexityLevel = $complexityLevel;
        $this->coefficient = $coefficient;
        $this->assignedAt = $assignedAt;
    }

    /**
     * Создать DTO из массива данных
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['ticket_id'],
            $data['manager_id'],
            $data['type'],
            $data['overdue_days'] ?? null,
            $data['complexity_level'],
            $data['coefficient'],
            $data['assigned_at'] ?? null
        );
    }

    /**
     * Преобразовать DTO в массив для сохранения
     */
    public function toArray(): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'manager_id' => $this->managerId,
            'type' => $this->type,
            'overdue_days' => $this->overdueDays,
            'complexity_level' => $this->complexityLevel,
            'coefficient' => $this->coefficient,
            'assigned_at' => $this->assignedAt
        ];
    }

    public function getTicketId(): int
    {
        return $this->ticketId;
    }

    public function getManagerId(): int
    {
        return $this->managerId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOverdueDays(): ?int
    {
        return $this->overdueDays;
    }

    public function getComplexityLevel(): string
    {
        return $this->complexityLevel;
    }

    public function getCoefficient(): float
    {
        return $this->coefficient;
    }

    public function getAssignedAt(): ?string
    {
        return $this->assignedAt;
    }
}