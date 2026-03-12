<?php


declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class RelationPersonDto implements BaseDtoInterface
{
   /**
    * Ком приходитcя контактное лицо:
    * 1 - Друг
    * 2 - Коллега
    * 4 - Родственник
    * 7 - Мой номер
    */

   public ?int $relationTypeCode;
   public ?string $firstName;
   public ?string $lastName;
   public ?string $middleName;

   public PhoneDto $phone;

   public function __construct(?int $relationTypeCode, ?string $firstName, ?string $lastName, ?string  $middleName, PhoneDto $phone)
   {
       $this->relationTypeCode = $relationTypeCode;
       $this->firstName = $firstName;
       $this->lastName = $lastName;
       $this->middleName = $middleName;
       $this->phone = $phone;
   }

    public function isNull(): bool
    {
        return null === $this->relationTypeCode &&
               null === $this->firstName &&
               null === $this->lastName &&
               null === $this->middleName &&
               null === $this->phone->isNull();
    }
}
