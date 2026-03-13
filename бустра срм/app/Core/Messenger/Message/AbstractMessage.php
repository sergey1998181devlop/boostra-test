<?php

namespace App\Core\Messenger\Message;

abstract class AbstractMessage
{
    protected array $payload = [];
    protected \DateTime $occurredAt;

    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
        $this->occurredAt = new \DateTime();
    }

    public function getEventName(): string
    {
        return static::class;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getOccurredAt(): \DateTime
    {
        return $this->occurredAt;
    }
}