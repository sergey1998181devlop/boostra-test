<?php

namespace App\Containers\InfrastructureSection\DTO\ResultDTO;

use App\Containers\InfrastructureSection\Contracts\DtoInterface;

class SelectResultDTO extends ResultDTO
{
    private array $result = [];

    public function __construct(string $query)
    {
        $this->setQuery($query);
        $this->setType('select');
    }

    public function pushResult(DtoInterface $result): self
    {
        $this->result[] = $result;
        return $this;
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function setResult(array $result): self
    {
        $this->result = $result;
        return $this;
    }
}