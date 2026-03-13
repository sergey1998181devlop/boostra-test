<?php

namespace App\Modules\Clients\Domain\Repository;

use App\Modules\Clients\Domain\Entity\ActiveLoan;

interface LoanRepositoryInterface
{
    /**
     * @param int $userId
     * @param int[]|null $organizationIds
     * @return ActiveLoan|null
     */
    public function findActiveByUserId(int $userId, ?array $organizationIds = null): ?ActiveLoan;

    /**
     * @param int $userId
     * @param int[]|null $organizationIds
     * @return ActiveLoan|null
     */
    public function findAllByUserId(int $userId, ?array $organizationIds = null): ?ActiveLoan;
}