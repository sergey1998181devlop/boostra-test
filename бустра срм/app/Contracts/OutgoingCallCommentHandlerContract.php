<?php

namespace App\Contracts;

use App\Core\Application\Response\Response;

interface OutgoingCallCommentHandlerContract
{
    public function handle(array $calls): Response;
}