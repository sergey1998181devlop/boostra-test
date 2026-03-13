<?php

namespace App\Containers\InfrastructureSection\DTO\ResultDTO;

use App\Containers\InfrastructureSection\Contracts\DtoInterface;
use App\Containers\InfrastructureSection\DTO\ResultDTO\ResultDTO;

class InsertResultDTO extends ResultDTO
{
//    private DtoInterface $dto;
    private int $insertId;

    public function __construct(string $query)
    {
        $this->setQuery($query);
        $this->setType('insert');
    }

    public function setResult(int $insertId): InsertResultDTO
    {
        $this->insertId = $insertId;
        return $this;
    }

    public function getResult(): int
    {
        return $this->insertId;
    }
}