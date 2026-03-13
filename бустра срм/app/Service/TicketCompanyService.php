<?php

namespace App\Service;

use App\Contracts\TicketCompanyServiceContract;
use App\Dto\TicketCompanyDto;
use App\Repositories\TicketCompanyRepository;

class TicketCompanyService implements TicketCompanyServiceContract
{
    private $repo;

    public function __construct()
    {
        $this->repo = new TicketCompanyRepository();
    }

    public function getAll(): array
    {
        return $this->repo->getAll();
    }

    public function create(TicketCompanyDto $dto): int
    {
        return $this->repo->create($dto->getName(), $dto->isActive());
    }

    public function update(int $id, TicketCompanyDto $dto): bool
    {
        return (bool)$this->repo->update($id, $dto->getName(), $dto->isActive());
    }

    public function delete(int $id): string
    {
        if ($this->repo->isUsedInTickets($id)) {
            $company = $this->repo->getById($id);
            $this->repo->update($id, $company->name, 0);
            return 'deactivated';
        }
        $deleted = $this->repo->delete($id);
        return $deleted ? 'deleted' : 'error';
    }

    public function setUseInTickets(int $id, bool $isActive): bool
    {
        return $this->repo->setUseInTickets($id, $isActive);
    }
}