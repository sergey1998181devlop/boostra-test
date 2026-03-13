<?php

namespace App\Containers\DomainSection\Tickets\Repository;

use App\Containers\DomainSection\Tickets\DTO\PriorityDTO;
use App\Containers\InfrastructureSection\Contracts\DtoInterface;
use App\Containers\InfrastructureSection\Contracts\RepositoryInterface;
use App\Containers\InfrastructureSection\DTO\ResultDTO\ResultDTO;
use App\Containers\InfrastructureSection\DTO\ResultDTO\SelectResultDTO;

require_once 'api/Simpla.php';

class PriorityRepository extends \Simpla implements RepositoryInterface
{

    public function getByPrimary(int $id): DtoInterface
    {
        $this->db->query($this->db->placehold('SELECT * FROM s_mytickets_priority WHERE id = ?', $id));
        $priority = $this->db->result();

        return new PriorityDTO(
            (int)$priority->id ?: 0,
            (string)$priority->name ?: '',
            (string)$priority->color ?: $priority
        );
    }

    public function getByName(string $name): DtoInterface
    {
        $this->db->query($this->db->placehold('SELECT * FROM s_mytickets_priority WHERE name = ?', $name));
        $priority = $this->db->result();

        return new PriorityDTO(
            (int)$priority->id ?: 0,
            (string)$priority->name ?: '',
            (string)$priority->color ?: $priority
        );
    }

    public function exec(string $query): ResultDTO
    {
        $this->db->query($this->db->placehold($query));
        $results = $this->db->results();

        if (strtolower(strtok($query, ' ')) === 'select') {
            $result = new SelectResultDTO($query);
            foreach ($results as $priority) {
                $result->pushResult(new PriorityDTO(
                    (int)$priority->id ?: 0,
                    (string)$priority->name ?: '',
                    (string)$priority->color ?: $priority
                ));
            }

            return $result;
        }

        throw new \Exception('Cannot execute query: ' . $query);
    }

    public function getAll(): ResultDTO
    {
        return $this->exec('SELECT * FROM s_mytickets_priority');
    }
}