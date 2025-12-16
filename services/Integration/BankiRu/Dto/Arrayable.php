<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;


interface Arrayable
{
   public function toArray() : array;
}