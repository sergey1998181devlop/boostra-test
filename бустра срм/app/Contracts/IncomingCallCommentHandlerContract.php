<?php

namespace App\Contracts;

use App\Core\Application\Response\Response;

interface IncomingCallCommentHandlerContract
{
    public function handle(array $userData, array $callData, string $blockType, ?int $managerId = null): Response;
}