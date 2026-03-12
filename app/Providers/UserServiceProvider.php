<?php

namespace App\Providers;

use App\Core\Application\Container\Container;
use App\Core\Application\Container\ServiceProvider;
use App\Modules\NotificationCenter\Services\NotificationCenterService;
use App\Modules\ShortLink\Services\ShortLinkService;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\UserOrderGift\Services\UserOrderGiftService;

/** Сервис провайдер для UserView */
class UserServiceProvider extends ServiceProvider
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct($this->container);
    }

    public function register(): void
    {
        $this->container->bind(NotificationCenterService::class, function () {
            return new NotificationCenterService();
        });

        $this->container->bind(\OrderData::class, function() {
            return new \OrderData();
        });

        $this->container->bind(ShortLinkService::class, function () {
            return new ShortLinkService(
                app()->make(NotificationCenterService::class),
                app()->make(\OrderData::class),
            );
        });

        $this->container->bind(\Database::class, function() {
            return new \Database();
        });

        $this->container->bind(UserOrderGiftService::class, function() {
            return new UserOrderGiftService(
                app()->make(\Database::class),
            );
        });

        $this->container->bind(UserRepository::class, function () {
            return new UserRepository(
                app()->make(\Database::class),
            );
        });
    }

    public function boot(): void
    {

    }
}
