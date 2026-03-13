<?php

namespace App\Containers\DomainSection\Tickets\Repository;

use App\Containers\DomainSection\Tickets\DTO\DirectionDTO;
use App\Containers\InfrastructureSection\Contracts\DtoInterface;
use App\Containers\InfrastructureSection\Contracts\RepositoryInterface;
use App\Containers\InfrastructureSection\DTO\ResultDTO\ResultDTO;
use App\Containers\InfrastructureSection\DTO\ResultDTO\SelectResultDTO;

require_once 'api/Simpla.php';

class DirectionRepository extends \Simpla implements RepositoryInterface
{
    public function getByPrimary(int $id): DtoInterface
    {
        $this->db->query($this->db->placehold('SELECT * FROM s_mytickets_directions WHERE id = ?', $id));
        $direction = $this->db->result();

        return new DirectionDTO(
            (int)$direction->id ?: 0,
            (string)$direction->name ?: '',
            (string)$direction->code ?: '',
            (bool)$direction->is_active
        );
    }

    public function exec(string $query): ResultDTO
    {
        $this->db->query($this->db->placehold($query));
        $results = $this->db->results();

        if (strtolower(strtok($query, ' ')) === 'select') {
            $result = new SelectResultDTO($query);
            foreach ($results as $direction) {
                $result->pushResult(new DirectionDTO(
                    (int)$direction->id ?: 0,
                    (string)$direction->name ?: '',
                    (string)$direction->code ?: '',
                    (bool)$direction->is_active
                ));
            }

            return $result;
        }

        throw new \Exception('Cannot execute query: ' . $query);
    }

    public function getAll(): ResultDTO
    {
        return $this->exec('SELECT * FROM s_mytickets_directions');
    }

    public function getByCode(string $code): DtoInterface
    {
        $this->db->query($this->db->placehold('SELECT * FROM s_mytickets_directions WHERE code = ?', $code));
        $direction = $this->db->result();

        return new DirectionDTO(
            (int)$direction->id ?: 0,
            (string)$direction->name ?: '',
            (string)$direction->code ?: '',
            (bool)$direction->is_active
        );
    }
}
