<?php

namespace App\Modules\Clients\Application\Service;

use App\Modules\Clients\Infrastructure\Client\OneCBalanceClient;
use App\Core\Cache\CacheInterface;
use App\Modules\Clients\Infrastructure\Repository\UserBalanceRepository;
use App\Modules\Shared\Repositories\OrganizationRepository;
use App\Modules\Shared\Enums\Organization;

class UserBalanceService
{
    private const CACHE_PREFIX = 'user_balance:';
    private const DEFAULT_TTL = 1800;
    
    private CacheInterface $cache;
    private OneCBalanceClient $oneCClient;
    private UserBalanceRepository $balanceRepository;
    private OrganizationRepository $organizationRepository;
    
    public function __construct(
        CacheInterface $cache,
        OneCBalanceClient $oneCClient,
        UserBalanceRepository $balanceRepository,
        OrganizationRepository $organizationRepository
    ) {
        $this->cache = $cache;
        $this->oneCClient = $oneCClient;
        $this->balanceRepository = $balanceRepository;
        $this->organizationRepository = $organizationRepository;
    }

    public function ensureFreshBalances(string $userUid, int $userId): void
    {
        $cacheKey = $this->getCacheKey($userId);

        try {
            $cached = $this->cache->get($cacheKey);

            if ($cached !== null) {
                return;
            }
        } catch (\Exception $e) {
            logger('cache')->warning('Redis unavailable, proceeding without cache', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
        
        $balances = $this->oneCClient->getUserBalances($userUid);
        
        if ($balances !== null) {
            $ttl = config('cache.ttl.user_balance', self::DEFAULT_TTL);
            
            try {
                $this->cache->set($cacheKey, $balances, $ttl);
            } catch (\Exception $e) {
                logger('cache')->warning('Cache SET FAILED - continuing without cache', [
                    'cache_key' => $cacheKey,
                    'error' => $e->getMessage()
                ]);
            }

            $this->syncToDatabase($userId, $balances);
        } else {
            logger('cache')->warning('1C returned NULL - not caching', ['user_id' => $userId]);
        }
    }
    
    private function syncToDatabase(int $userId, array $balances): void
    {
        $priorityBalance = $this->selectPriorityBalance($balances);
        
        if ($priorityBalance) {
            $this->balanceRepository->updateFromOneCData($userId, $priorityBalance);
        }
    }
    
    private function selectPriorityBalance(array $balances): ?array
    {
        if (empty($balances)) {
            return null;
        }
        
        $rzsInn = $this->organizationRepository->getInnById(Organization::RZS()->getValue());
        
        if ($rzsInn) {
            foreach ($balances as $balance) {
                if (isset($balance['ИНН']) && $balance['ИНН'] === $rzsInn) {
                    return $balance;
                }
            }
        }
        
        return reset($balances) ?: null;
    }
    
    public function invalidateCache(int $userId): void
    {
        try {
            $cacheKey = $this->getCacheKey($userId);
            $this->cache->delete($cacheKey);
        } catch (\Exception $e) {
            logger('cache')->warning('Failed to invalidate cache', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function getCacheKey(int $userId): string
    {
        return self::CACHE_PREFIX . $userId;
    }
}


