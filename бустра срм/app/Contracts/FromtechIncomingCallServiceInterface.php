<?php

namespace App\Contracts;

use App\Core\Application\Response\Response;
use App\Dto\FromtechIncomingCallDto;

interface FromtechIncomingCallServiceInterface
{
    public function handle(FromtechIncomingCallDto $dto): Response;
}
