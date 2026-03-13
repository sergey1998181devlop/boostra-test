<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class DocumentDto implements BaseDtoInterface
{
    public ?string $series;
    public ?string $number;
    public ?string $issueDate; //Дата выдачи паспорта в формате YYYY-MM-DD
    public ?string $issueDivisionCode; // Код подразделения, выдавшего паспорт (пример: 770-056)
    public ?string $issuedBy; // Кем выдан паспорт


    public function __construct(?string $series, ?string $number, ?string $issueDate, ?string $issueDivisionCode, ?string $issuedBy)
    {
        $this->series = $series;
        $this->number = $number;
        $this->issueDate = $issueDate;
        $this->issueDivisionCode = $issueDivisionCode;
        $this->issuedBy = $issuedBy;
    }

    public function isNull(): bool
    {
        return $this->series == null &&
               $this->number == null &&
               $this->issueDate == null &&
               $this->issueDivisionCode == null &&
               $this->issuedBy == null;
    }
}
