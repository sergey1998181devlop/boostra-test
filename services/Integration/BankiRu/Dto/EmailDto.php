<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class EmailDto implements BaseDtoInterface
{
   public ?string $email;

   public function __construct(?string $email)
   {
       $this->email = $email;
   }

    public function isNull(): bool
    {
        return null === $this->email;
    }
}
