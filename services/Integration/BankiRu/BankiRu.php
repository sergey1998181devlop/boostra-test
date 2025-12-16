<?php

declare(strict_types=1);

namespace services\Integration\BankiRu;

use services\Integration\BankiRu\Auth\Auth;
use services\Integration\BankiRu\CheckDuplicates\CheckDuplicate;
use services\Integration\BankiRu\Leads\Lead;

final class BankiRu
{
   public static function getInstance(): BankiRu
   {
        return new BankiRu();
   }

   public function auth(): Auth
   {
       return new Auth();
   }

   public function leads(string $authToken): Lead
   {
       return new Lead($authToken);
   }

   public function checkDuplicate(string $authToken): CheckDuplicate
   {
       return new CheckDuplicate($authToken);
   }

   public function getConfig(): array
   {
       return (array) include __DIR__ . '/config.php';
   }
}
