<?php

namespace App\Contracts;

use App\Dto\TicketCompanyDto;

interface TicketCompanyServiceContract
{
    public function getAll(): array;
    public function create(TicketCompanyDto $dto): int;
    public function update(int $id, TicketCompanyDto $dto): bool;
    public function delete(int $id): string;
}