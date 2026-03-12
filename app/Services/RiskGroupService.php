<?php

namespace App\Services;

use App\Repositories\OrderRepository;

class RiskGroupService
{
    private OrderRepository $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    /**
     * @throws \Exception
     */
    public function isInRiskGroup($user): bool
    {
        // женщина
        if (strtolower($user->gender ?? '') !== 'female') {
            return false;
        }

        // пк
        if (empty($user->loan_history)) {
            return false;
        }

        // возраст
        $birthDate = $user->birth ?? null;
        $age = $birthDate ? $this->calculateAge($birthDate) : null;

        if ($age < 41) {
            return false;
        }


        // регион
        $riskyRegions = [
            'Севастополь', 'Крым', 'Карелия', 'Ульяновская', 'Тыва',
            'Рязанская', 'Томская', 'Челябинская', 'Ярославская', 'Хакасия',
            'Северная Осетия', 'Калужская', 'Белгородская'
        ];

        $regRegion  = $user->Regregion ?? '';
        $faktRegion = $user->Faktregion ?? '';

        foreach ($riskyRegions as $region) {
            if (stripos($regRegion, $region) !== false || stripos($faktRegion, $region) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    private function calculateAge(string $birthDate): ?int
    {
        $birth = \DateTime::createFromFormat('d.m.Y', $birthDate);

        if (!$birth) {
            return null;
        }
        return (new \DateTime())->diff($birth)->y;
    }
}
