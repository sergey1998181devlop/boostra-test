<?php

namespace App\Core\Messenger\Command;

abstract class AbstractCommand
{
    protected array $payload = [];
    protected \DateTime $createdAt;

    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
        $this->createdAt = new \DateTime();
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function validate(): bool
    {
        return true;
    }
}