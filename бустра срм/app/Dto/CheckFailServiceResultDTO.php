<?php

namespace App\Dto;

class CheckFailServiceResultDTO
{
    public bool $hasError;
    public ?bool $isActive = null;
    public ?string $message = null;
    public ?bool $needSendMessage = null;
    public ?string $showAt = null;

    public function __construct(
        bool $hasError,
        ?bool $isActive = null,
        ?string $message = null,
        ?bool $needSendMessage = null,
        ?string $showAt = null
    ) {
        $this->hasError = $hasError;
        $this->isActive = $isActive;
        $this->message = $message;
        $this->needSendMessage = $needSendMessage;
        $this->showAt = $showAt;
    }
}
