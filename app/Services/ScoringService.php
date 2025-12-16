<?php
namespace App\Services;

use App\Repositories\OrderRepository;

class ScoringService
{
    private const MIN_SCORE = 600;

    private OrderRepository $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    /**
     * Проверяет, что скоринг пользователя не ниже порога.
     */
    public function isUserScoreSufficient(int $userId, int $threshold = self::MIN_SCORE): bool
    {
        $score = $this->orderRepo->getActiveOrderByUserId($userId);
        return $score >= $threshold;
    }
}
