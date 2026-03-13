<?php

namespace App\Contracts;

interface UserTicketHandlerContract
{
    public function handle(array $data): void;
}
