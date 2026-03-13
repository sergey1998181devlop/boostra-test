<?php

namespace App\Containers\DomainSection\Tickets\Repository;

use App\Containers\DomainSection\Tickets\DTO\ManagerDTO;
use App\Containers\InfrastructureSection\Contracts\DtoInterface;
use App\Containers\InfrastructureSection\Contracts\RepositoryInterface;
use App\Containers\InfrastructureSection\DTO\ResultDTO\ResultDTO;
use App\Containers\InfrastructureSection\DTO\ResultDTO\SelectResultDTO;

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/Managers.php';

class ManagerRepository extends \Managers implements RepositoryInterface
{

    public function getByPrimary(int $id): DtoInterface
    {
        $manager = $this->get_manager($id);
        return new ManagerDTO(
            $manager->id ?? 0,
            $manager->login ?? '',
            $manager->password ?? '',
            $manager->name ?? '',
            $manager->name_1c ?? '',
            $manager->role ?? '',
            $manager->last_ip ?? '',
            $manager->last_visit ?? '',
            $manager->salt ?? '',
            $manager->mango_number ?? 0,
            $manager->avatar ?? '',
            (bool)$manager->blocked,
            (bool)$manager->vox_deleted,
        );
    }

    public function exec(string $query): ResultDTO
    {
        $this->db->query($this->db->placehold($query));
        $results = $this->db->results();

        if (strtolower(strtok($query, ' ')) === 'select') {
            $result = new SelectResultDTO($query);
            foreach ($results as $manager) {
                $result->pushResult(new ManagerDTO(
                    $manager->id ?? 0,
                    $manager->login ?? '',
                    $manager->password ?? '',
                    $manager->name ?? '',
                    $manager->name_1c ?? '',
                    $manager->role ?? '',
                    $manager->last_ip ?? '',
                    $manager->last_visit ?? '',
                    $manager->salt ?? '',
                    $manager->mango_number ?? 0,
                    $manager->avatar ?? '',
                    (bool)$manager->blocked,
                    (bool)$manager->vox_deleted,
                ));
            }

            return $result;
        }

        throw new \Exception('Cannot execute query: ' . $query);
    }
}