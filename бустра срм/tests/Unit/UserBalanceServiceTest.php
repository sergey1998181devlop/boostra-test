<?php

namespace Tests\Unit;

use App\Modules\Clients\Application\Service\UserBalanceService;
use App\Core\Cache\CacheInterface;
use App\Modules\Clients\Infrastructure\Client\OneCBalanceClient;
use App\Modules\Clients\Infrastructure\Repository\UserBalanceRepository;
use App\Modules\Shared\Repositories\OrganizationRepository;
use PHPUnit\Framework\TestCase;

class UserBalanceServiceTest extends TestCase
{
    private $cache;
    private $oneCClient;
    private $balanceRepository;
    private $organizationRepository;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cache = $this->createMock(CacheInterface::class);
        $this->oneCClient = $this->createMock(OneCBalanceClient::class);
        $this->balanceRepository = $this->createMock(UserBalanceRepository::class);
        $this->organizationRepository = $this->createMock(OrganizationRepository::class);
        
        $this->service = new UserBalanceService(
            $this->cache,
            $this->oneCClient,
            $this->balanceRepository,
            $this->organizationRepository
        );
    }

    /**
     * @test
     * @testdox Кеш-хит не вызывает обращение к 1C
     */
    public function testCacheHitDoesNotCallOneC()
    {
        $userId = 123;
        $userUid = 'test-uid-123';
        $cachedData = [
            ['НомерЗайма' => 'RZS25-123', 'ОстатокОД' => 1000]
        ];

        $this->cache->expects($this->once())
            ->method('get')
            ->with('user_balance:123')
            ->willReturn($cachedData);

        $this->oneCClient->expects($this->never())
            ->method('getUserBalances');

        $this->balanceRepository->expects($this->never())
            ->method('updateFromOneCData');

        $this->service->ensureFreshBalances($userUid, $userId);
    }

    /**
     * @test
     * @testdox При кеш-миссе получает данные из 1C и обновляет БД
     */
    public function testCacheMissFetchesFromOneCAndUpdatesDatabase()
    {
        $userId = 123;
        $userUid = 'test-uid-123';
        $balances1c = [
            ['НомерЗайма' => 'RZS25-123', 'ОстатокОД' => 1000, 'ИНН' => '7704471939']
        ];

        $this->cache->expects($this->once())
            ->method('get')
            ->with('user_balance:123')
            ->willReturn(null);

        $this->oneCClient->expects($this->once())
            ->method('getUserBalances')
            ->with($userUid)
            ->willReturn($balances1c);

        $this->cache->expects($this->once())
            ->method('set')
            ->with('user_balance:123', $balances1c, $this->anything());

        $this->organizationRepository->expects($this->once())
            ->method('getInnById')
            ->with(13)
            ->willReturn('7704471939');

        $this->balanceRepository->expects($this->once())
            ->method('updateFromOneCData')
            ->with($userId, $balances1c[0]);

        $this->service->ensureFreshBalances($userUid, $userId);
    }

    /**
     * @test
     * @testdox При недоступности Redis продолжает работу без кеша
     */
    public function testRedisUnavailableContinuesWithoutCache()
    {
        $userId = 123;
        $userUid = 'test-uid-123';
        $balances1c = [
            ['НомерЗайма' => 'LORD25-456', 'ОстатокОД' => 5000, 'ИНН' => '9717088848']
        ];

        $this->cache->expects($this->once())
            ->method('get')
            ->willThrowException(new \Exception('Connection refused'));

        $this->oneCClient->expects($this->once())
            ->method('getUserBalances')
            ->with($userUid)
            ->willReturn($balances1c);

        $this->cache->expects($this->once())
            ->method('set')
            ->willThrowException(new \Exception('Connection refused'));

        $this->organizationRepository->expects($this->once())
            ->method('getInnById')
            ->willReturn('7704471939');

        $this->balanceRepository->expects($this->once())
            ->method('updateFromOneCData')
            ->with($userId, $balances1c[0]);

        $this->service->ensureFreshBalances($userUid, $userId);
    }

    /**
     * @test
     * @testdox Выбирает приоритетный баланс РЗС при наличии нескольких организаций
     */
    public function testSelectsPriorityBalanceRZSWhenMultipleOrganizations()
    {
        $userId = 123;
        $userUid = 'test-uid-123';
        $balances1c = [
            ['НомерЗайма' => 'LD25-456', 'ОстатокОД' => 5000, 'ИНН' => '9717088848'],
            ['НомерЗайма' => 'RZS25-123', 'ОстатокОД' => 1000, 'ИНН' => '7704471939'],
            ['НомерЗайма' => 'FL20-789', 'ОстатокОД' => 3000, 'ИНН' => 'XXXXXXXX']
        ];

        $this->cache->method('get')->willReturn(null);
        $this->oneCClient->method('getUserBalances')->willReturn($balances1c);
        $this->cache->method('set');

        $this->organizationRepository->expects($this->once())
            ->method('getInnById')
            ->with(13)
            ->willReturn('7704471939');

        $this->balanceRepository->expects($this->once())
            ->method('updateFromOneCData')
            ->with($userId, $balances1c[1]);

        $this->service->ensureFreshBalances($userUid, $userId);
    }

    /**
     * @test
     * @testdox Выбирает первый баланс когда нет РЗС (старый займ из другой МКК)
     */
    public function testSelectsFirstBalanceWhenNoRZS()
    {
        $userId = 123;
        $userUid = 'test-uid-123';
        $balances1c = [
            ['НомерЗайма' => 'LORD25-456', 'ОстатокОД' => 5000, 'ИНН' => '9717088848'],
            ['НомерЗайма' => 'FINLAB20-789', 'ОстатокОД' => 3000, 'ИНН' => 'XXXXXXXX']
        ];

        $this->cache->method('get')->willReturn(null);
        $this->oneCClient->method('getUserBalances')->willReturn($balances1c);
        $this->cache->method('set');

        $this->organizationRepository->expects($this->once())
            ->method('getInnById')
            ->with(13)
            ->willReturn('7704471939');

        $this->balanceRepository->expects($this->once())
            ->method('updateFromOneCData')
            ->with($userId, $balances1c[0]);

        $this->service->ensureFreshBalances($userUid, $userId);
    }

    /**
     * @test
     * @testdox При NULL ответе от 1C не обновляет БД (у клиента нет счёта)
     */
    public function testOneCReturnsNullDoesNotUpdateDatabase()
    {
        $userId = 123;
        $userUid = 'test-uid-123';

        $this->cache->method('get')->willReturn(null);
        
        $this->oneCClient->expects($this->once())
            ->method('getUserBalances')
            ->with($userUid)
            ->willReturn(null);

        $this->cache->expects($this->never())
            ->method('set');

        $this->balanceRepository->expects($this->never())
            ->method('updateFromOneCData');

        $this->service->ensureFreshBalances($userUid, $userId);
    }

    /**
     * @test
     * @testdox Корректно отображает баланс из 1C с полями НомерЗайма, ОстатокОД, ИНН
     */
    public function testOneCBalanceStructure()
    {
        $userId = 123;
        $userUid = 'test-uid-123';
        $balances1c = [
            [
                'НомерЗайма' => 'RZS25-123',
                'ОстатокОД' => 15000.50,
                'ОстатокПроцентов' => 2500.00,
                'ИНН' => '9717088848',
                'Организация' => 'РУСЗАЙМСЕРВИС'
            ]
        ];

        $this->cache->method('get')->willReturn(null);
        $this->oneCClient->expects($this->once())
            ->method('getUserBalances')
            ->with($userUid)
            ->willReturn($balances1c);

        $this->cache->method('set');
        $this->organizationRepository->method('getInnById')->willReturn('9717088848');

        $this->balanceRepository->expects($this->once())
            ->method('updateFromOneCData')
            ->with($userId, $this->callback(function ($balance) {
                return $balance['НомерЗайма'] === 'RZS25-123' 
                    && $balance['ОстатокОД'] === 15000.50
                    && $balance['ИНН'] === '9717088848';
            }));

        $this->service->ensureFreshBalances($userUid, $userId);
    }

    /**
     * @test
     * @testdox Выбирает займ из Финлаб когда у клиента только старый займ FL
     */
    public function testSelectsFinlabLoanWhenOnlyOldLoan()
    {
        $userId = 456;
        $userUid = 'test-uid-456';
        $balances1c = [
            ['НомерЗайма' => 'FL20-789', 'ОстатокОД' => 3000, 'ИНН' => '6317161167']
        ];

        $this->cache->method('get')->willReturn(null);
        $this->oneCClient->method('getUserBalances')->willReturn($balances1c);
        $this->cache->method('set');

        $this->organizationRepository->expects($this->once())
            ->method('getInnById')
            ->with(13)
            ->willReturn('9717088848');

        $this->balanceRepository->expects($this->once())
            ->method('updateFromOneCData')
            ->with($userId, $balances1c[0]);

        $this->service->ensureFreshBalances($userUid, $userId);
    }

    /**
     * @test
     * @testdox Инвалидация кеша игнорирует ошибки Redis
     */
    public function testInvalidateCacheIgnoresRedisErrors()
    {
        $userId = 123;

        $this->cache->expects($this->once())
            ->method('delete')
            ->with('user_balance:123')
            ->willThrowException(new \Exception('Connection refused'));

        $this->service->invalidateCache($userId);
    }
}

