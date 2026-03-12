<?php

namespace App\Providers;

use App\Core\Application\Container\ServiceProvider;
use App\Modules\OrderData\Repositories\OrderDataRepository;
use App\Modules\OrderData\Services\OrderDataService;
use App\Modules\ShortLink\Repositories\ShortLinkRepository;

class ShortLinkServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы логирования
     */
    public function register(): void
    {
        $c = $this->container;
        $c->bind(ShortLinkRepository::class, function () {
            return new ShortLinkRepository();
        });

        $c->bind(OrderDataRepository::class, function () {
            return new OrderDataRepository();
        });

        $c->bind(OrderDataService::class, function () use ($c) {
            return new OrderDataService(
                $c->make(OrderDataRepository::class)
            );
        });
    }

    public function boot(): void
    {
        // Можно добавить дополнительную логику инициализации
    }
}
