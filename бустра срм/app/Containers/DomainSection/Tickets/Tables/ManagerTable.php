<?php

namespace App\Containers\DomainSection\Tickets\Tables;

use App\Containers\DomainSection\Tickets\Repository\ManagerRepository;
use App\Containers\InfrastructureSection\Contracts\TableInterface;
use App\Containers\InfrastructureSection\Table\BaseTable;

class ManagerTable extends BaseTable implements TableInterface
{
    private const TS_OPERATOR_ROLE = 'ts_operator';

    function getTableName(): string
    {
        return 's_managers';
    }

    static function getInstance(): BaseTable
    {
        return new self(new ManagerRepository());
    }

    public function getTechnicalSupportOperators(): array
    {
        return $this->getRepository()->exec(
            'SELECT * FROM ' . $this->getTableName() . " WHERE role = '" . self::TS_OPERATOR_ROLE . "' ORDER BY id DESC"
        )->getResult();
    }
}
