<?php

namespace App\Modules\BRReport\Dto;

/**
 * DTO для одной строки отчета БР
 */
class BRReportItemDto
{
    /** @var int */
    private $id;

    /** @var string */
    private $createdAt;

    /** @var int|null */
    private $clientId;

    /** @var string */
    private $clientName;

    /** @var string */
    private $companyName;

    /** @var string */
    private $typeActivity;

    /** @var string */
    private $typeProduct;

    /** @var string */
    private $subjectName;

    /** @var string */
    private $eventPeriod;

    /** @var string */
    private $channelName;

    /** @var string */
    private $takeDecision;

    /** @var string */
    private $basisDecision;

    /** @var string */
    private $scopeConsideration;

    /** @var string */
    private $cbrLetterNumber;

    public function __construct(
        int $id,
        string $createdAt,
        ?int $clientId,
        string $clientName,
        string $companyName,
        string $typeActivity,
        string $typeProduct,
        string $subjectName,
        string $eventPeriod,
        string $channelName,
        string $takeDecision,
        string $basisDecision,
        string $scopeConsideration,
        string $cbrLetterNumber = ''
    ) {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->clientId = $clientId;
        $this->clientName = $clientName;
        $this->companyName = $companyName;
        $this->typeActivity = $typeActivity;
        $this->typeProduct = $typeProduct;
        $this->subjectName = $subjectName;
        $this->eventPeriod = $eventPeriod;
        $this->channelName = $channelName;
        $this->takeDecision = $takeDecision;
        $this->basisDecision = $basisDecision;
        $this->scopeConsideration = $scopeConsideration;
        $this->cbrLetterNumber = $cbrLetterNumber;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getTypeActivity(): string
    {
        return $this->typeActivity;
    }

    public function getTypeProduct(): string
    {
        return $this->typeProduct;
    }

    public function getSubjectName(): string
    {
        return $this->subjectName;
    }

    public function getEventPeriod(): string
    {
        return $this->eventPeriod;
    }

    public function getChannelName(): string
    {
        return $this->channelName;
    }

    public function getTakeDecision(): string
    {
        return $this->takeDecision;
    }

    public function getBasisDecision(): string
    {
        return $this->basisDecision;
    }

    public function getScopeConsideration(): string
    {
        return $this->scopeConsideration;
    }

    public function getCbrLetterNumber(): string
    {
        return $this->cbrLetterNumber;
    }

    /**
     * Преобразовать DTO в массив для отображения
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->createdAt,
            'client_id' => $this->clientId,
            'client_name' => $this->clientName,
            'company_name' => $this->companyName,
            'type_activity' => $this->typeActivity,
            'type_product' => $this->typeProduct,
            'subject_name' => $this->subjectName,
            'event_period' => $this->eventPeriod,
            'channel_name' => $this->channelName,
            'take_decision' => $this->takeDecision,
            'basis_decision' => $this->basisDecision,
            'scope_consideration' => $this->scopeConsideration,
            'cbr_letter_number' => $this->cbrLetterNumber,
        ];
    }
}
