<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;


final class LeadInfoDto implements Arrayable, BaseDtoInterface
{
    public ?int $purposeCode;
    public ?int $requestedTermValue;
    public ?int $requestedTermUnitCode;
    public InitialPaymentDto $initialPaymentDto;

    public function __construct(
        ?int $purposeCode,
        ?int $requestedTermValue,
        ?int $requestedTermUnitCode,
        InitialPaymentDto $initialPaymentDto
    ) {
        $this->purposeCode = $purposeCode;
        $this->requestedTermValue = $requestedTermValue;
        $this->requestedTermUnitCode = $requestedTermUnitCode;
        $this->initialPaymentDto = $initialPaymentDto;
    }

    public function isNull(): bool
    {
        return
            null === $this->purposeCode &&
            null === $this->requestedTermValue &&
            null === $this->requestedTermUnitCode &&
            $this->initialPaymentDto->isNull();
    }

    public function toArray(): array
    {
         $response = [];
         if ($this->purposeCode !== null) {
             $response['purposeCode'] = $this->purposeCode;
         }
         if ($this->requestedTermValue !== null) {
             $response['requestedTermValue'] = $this->requestedTermValue;
         }
         if ($this->requestedTermUnitCode !== null) {
             $response['requestedTermUnitCode'] = $this->requestedTermUnitCode;
         }

         if (!$this->initialPaymentDto->isNull()) {
             $response['initialPayment']['amount'] = $this->initialPaymentDto->amount;
         }

         return $response;
    }
}
