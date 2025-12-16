<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class LeadRequestDto
{
    public string $partnerCode;
    public string $firstName;
    public int $requestedAmount;
    public PhoneDto $phoneDto;
    public AddressesDto $addressDto;
    /**
     * Тип согласия (передается каждое полученное от Клиента согласие):
     * 1 - Согласие на обработку персональных данных (обязательно)
     * 2 - Согласие на смс рассылку
     * 3 - Согласие на e-mail рассылку
     * 4 - Согласие на push уведомления
     * 5 - Согласие на передачу данных в БКИ
     */
    public array $acceptedConsents; // [[type => 1], ['type' => 2]...]
    public ?string $transactionId;
    public ?LeadInfoDto $leadInfoDto;
    public ?ClientDataDto $clientDataDto;


    public function __construct(
        string $partnerCode,
        string $firstName,
        int $requestedAmount,
        PhoneDto $phoneDto,
        AddressesDto $addressDto,
        array $acceptedConsents,
        ?string $transactionId,
        ?LeadInfoDto $leadInfoDto,
        ?ClientDataDto $clientDataDto
    ) {
        $this->partnerCode = $partnerCode;
        $this->firstName = $firstName;
        $this->requestedAmount = $requestedAmount;
        $this->phoneDto = $phoneDto;
        $this->addressDto = $addressDto;
        $this->acceptedConsents = $acceptedConsents;
        $this->transactionId = $transactionId;
        $this->leadInfoDto = $leadInfoDto;
        $this->clientDataDto = $clientDataDto;
    }
}
