<?php

namespace App\Containers\InfrastructureSection\DTO\ResultDTO;

class UpdateResultDTO extends ResultDTO
{
    public function __construct(string $query)
    {
        $this->setQuery($query);
        $this->setType('update');
    }
}