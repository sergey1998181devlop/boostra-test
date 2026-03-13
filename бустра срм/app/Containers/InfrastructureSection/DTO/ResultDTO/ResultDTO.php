<?php

namespace App\Containers\InfrastructureSection\DTO\ResultDTO;

abstract class ResultDTO
{
    private string $query;
    private string $type;

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}