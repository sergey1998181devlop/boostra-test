<?php

namespace App\Containers\DomainSection\Tickets\Repository;

use App\Containers\DomainSection\Tickets\DTO\SlaDTO;
use App\Containers\InfrastructureSection\Contracts\RepositoryInterface;
use App\Containers\InfrastructureSection\DTO\ResultDTO\ResultDTO;
use App\Containers\InfrastructureSection\DTO\ResultDTO\SelectResultDTO;
use Exception;

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/Simpla.php';

class SlaRepository extends \Simpla implements RepositoryInterface
{
    public function getByPrimary(int $id): SlaDTO
    {
        $this->db->query($this->db->placehold('SELECT * FROM ts_sla WHERE id = ?', $id));
        $sla = $this->db->result();

        if (empty($sla)) {
            throw new Exception('Entity ' . $id . ' not found');
        }

        return new SlaDTO(
            $sla->id ?: 0,
            $sla->name ?: '',
            $sla->quarter ?: 0,
            $sla->year ?: 0,
            $sla->priority_id ?: 0,
            $sla->reaction_minutes ?: 0,
            $sla->reaction_percent ?: 0,
            $sla->resolution_minutes ?: 0,
            $sla->resolution_percent ?: 0,
            $sla->total_reaction_percent ?: 0,
            $sla->total_resolution_percent ?: 0,
        );
    }

    /**
     * @param string $query
     * @return ResultDTO
     * @throws Exception
     */
    public function exec(string $query): ResultDTO
    {
        $this->db->query($this->db->placehold($query));

        $method = strtolower(strtok($query, ' '));

        switch ($method) {
            case 'select':
                $result = new SelectResultDTO($query);
                foreach ($this->db->results() as $sla) {
                    $result->pushResult(new SlaDTO(
                        $sla->id ?: 0,
                        $sla->name ?: '',
                        $sla->quarter ?: 0,
                        $sla->year ?: 0,
                        $sla->priority_id ?: 0,
                        $sla->reaction_minutes ?: 0,
                        $sla->reaction_percent ?: 0,
                        $sla->resolution_minutes ?: 0,
                        $sla->resolution_percent ?: 0,
                        $sla->total_reaction_percent ?: 0,
                        $sla->total_resolution_percent ?: 0,
                    ));
                }

                return $result;
        }

        throw new Exception('Cannot execute query: ' . $query);
    }

    public function execRaw(string $query): ResultDTO
    {
        $this->db->query($this->db->placehold($query));
        $results = $this->db->results();

        if (strtolower(strtok($query, ' ')) === 'select') {
            return (new SelectResultDTO($query))->setResult((array)$results);
        }

        throw new \Exception('Cannot execute query: ' . $query);
    }
}