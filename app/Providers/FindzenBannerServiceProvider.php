<?php

namespace App\Providers;

use App\Core\Application\Container\Container;
use App\Core\Application\Container\ServiceProvider;
use App\Modules\Marketing\Services\FindzenBannerService;

/**
 * Регистрация сервиса для финдзен баннера
 */
class FindzenBannerServiceProvider extends ServiceProvider
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct($this->container);
    }

    public function register(): void
    {
        $this->container->bind(FindzenBannerService::class, function () {
            return new FindzenBannerService();
        });
    }

    public function boot(): void
    {
    }
}
