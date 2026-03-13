<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class ClientDataDto implements Arrayable, BaseDtoInterface
{
    public AddressesDto $addressesDto;
    public AcceptedConsentsDto $acceptedConsentsDto;

    public PersonProfileDto $personProfileDto;

    public EstateDto $estateDto;

    public JobDto $jobDto;

    public EmailDto $emailDto;

    public ExpensesDto $expensesDto;
    public IncomesDto $incomesDto;

    public DocumentDto $documentDto;

    public RelationPersonDto $relationPersonDto;

    public function __construct(
        AddressesDto $addressesDto,
        AcceptedConsentsDto $acceptedConsentsDto,
        PersonProfileDto $personProfileDto,
        EstateDto $estateDto,
        JobDto $jobDto,
        EmailDto $emailDto,
        ExpensesDto $expensesDto,
        IncomesDto $incomesDto,
        DocumentDto $documentDto,
        RelationPersonDto $relationPersonDto
    ) {
        $this->addressesDto = $addressesDto;
        $this->acceptedConsentsDto = $acceptedConsentsDto;
        $this->personProfileDto = $personProfileDto;
        $this->estateDto = $estateDto;
        $this->jobDto = $jobDto;
        $this->emailDto = $emailDto;
        $this->expensesDto = $expensesDto;
        $this->incomesDto = $incomesDto;
        $this->documentDto = $documentDto;
        $this->relationPersonDto = $relationPersonDto;
    }

    public function toArray(): array
    {
        return [];
    }

    public function isNull(): bool
    {
        return $this->addressesDto->isNull() &&
               $this->acceptedConsentsDto->isNull() &&
               $this->personProfileDto->isNull() &&
               $this->estateDto->isNull() &&
               $this->jobDto->isNull() &&
               $this->emailDto->isNull() &&
               $this->expensesDto->isNull() &&
               $this->incomesDto->isNull() &&
               $this->documentDto->isNull() &&
               $this->relationPersonDto->isNull();
    }
}
