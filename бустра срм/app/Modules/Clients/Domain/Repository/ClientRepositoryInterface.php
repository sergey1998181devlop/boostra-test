<?php

namespace App\Modules\Clients\Domain\Repository;

use App\Modules\Clients\Domain\Entity\Client;

interface ClientRepositoryInterface
{
    public function findByPhone(string $phone): ?Client;
    public function findByPhoneAndOrganizationId(string $phone, int $organizationId): ?Client;

    /**
     * @param string $phone
     * @param int[] $organizationIds
     * @return Client|null
     */
    public function findByPhoneAndOrganizationIds(string $phone, array $organizationIds): ?Client;
}