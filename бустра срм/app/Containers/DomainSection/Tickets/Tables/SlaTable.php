<?php

namespace App\Containers\DomainSection\Tickets\Tables;

use App\Containers\DomainSection\Tickets\DTO\SlaDTO;
use App\Containers\DomainSection\Tickets\Repository\SlaRepository;
use App\Containers\InfrastructureSection\Contracts\DtoInterface;
use App\Containers\InfrastructureSection\Contracts\TableInterface;
use App\Containers\InfrastructureSection\DTO\ResultDTO\ResultDTO;
use App\Containers\InfrastructureSection\DTO\ResultDTO\SelectResultDTO;
use App\Containers\InfrastructureSection\Table\BaseTable;

class SlaTable extends BaseTable implements TableInterface
{
    function getTableName(): string
    {
        return 'ts_sla';
    }

    static function getInstance(): BaseTable
    {
        return new self(new SlaRepository());
    }

    public function getQuarterMap(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Зима'
            ],
            [
                'id' => 2,
                'name' => 'Весна'
            ],
            [
                'id' => 3,
                'name' => 'Лето'
            ],
            [
                'id' => 4,
                'name' => 'Осень'
            ],
        ];
    }

    public function getMonthMap(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Январь',
            ],
            [
                'id' => 2,
                'name' => 'Февраль',
            ],
            [
                'id' => 3,
                'name' => 'Март',
            ],
            [
                'id' => 4,
                'name' => 'Апрель',
            ],
            [
                'id' => 5,
                'name' => 'Май',
            ],
            [
                'id' => 6,
                'name' => 'Июнь',
            ],
            [
                'id' => 7,
                'name' => 'Июль',
            ],
            [
                'id' => 8,
                'name' => 'Август',
            ],
            [
                'id' => 9,
                'name' => 'Сентябрь',
            ],
            [
                'id' => 10,
                'name' => 'Октябрь',
            ],
            [
                'id' => 11,
                'name' => 'Ноябрь',
            ],
            [
                'id' => 12,
                'name' => 'Декабрь',
            ],
        ];
    }

    public function getAll(): array
    {
        return $this->getRepository()->exec('SELECT * FROM ts_sla')->getResult();
    }

    public function getByQuarter(int $quarter, int $year): SlaDTO
    {
        return current($this->getRepository()->exec(
            "SELECT * FROM ts_sla WHERE quarter = $quarter AND year = $year"
        )->getResult()) ?: new SlaDTO();
    }

    public function getByPriorityAndQuarter(int $priorityId, int $quarter, int $year): SlaDTO
    {
        return current($this->getRepository()->exec(
            "SELECT * FROM ts_sla WHERE quarter = $quarter AND year = $year AND priority_id = $priorityId"
        )->getResult()) ?: new SlaDTO();
    }

    public function getAvailableYears(): array
    {
        $result = [];

        foreach ($this->getRepository()->execRaw('SELECT DISTINCT year FROM ts_sla ORDER BY year')->getResult() as $year) {
            $result[] = (int)$year->year;
        }

        return $result;
    }
}