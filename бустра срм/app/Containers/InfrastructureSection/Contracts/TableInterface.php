<?php

namespace App\Containers\InfrastructureSection\Contracts;

use App\Containers\InfrastructureSection\DTO\ResultDTO\ResultDTO;
use App\Containers\InfrastructureSection\Table\BaseTable;

interface TableInterface
{
    function getTableName(): string;
    static function getInstance(): BaseTable;
    function getByPrimary(int $id): DtoInterface;
//    public function add(DtoInterface $item): ResultDTO;
}