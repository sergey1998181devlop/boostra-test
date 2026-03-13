<?php

namespace App\Containers\InfrastructureSection\Contracts;

use App\Containers\InfrastructureSection\DTO\ResultDTO\ResultDTO;

interface RepositoryInterface
{
    public function getByPrimary(int $id): DtoInterface;
    public function exec(string $query): ResultDTO;
//    public function create(DtoInterface $dto): ResultDTO;
}