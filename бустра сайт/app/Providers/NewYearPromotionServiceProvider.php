<?php

namespace App\Providers;

use App\Core\Application\Container\Container;
use App\Core\Application\Container\ServiceProvider;
use App\Modules\NewYearPromotion\Services\NewYearPromotionService;

class NewYearPromotionServiceProvider extends ServiceProvider
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct($this->container);
    }

    public function register(): void
    {
        $c = $this->container;
        $c->bind(NewYearPromotionService::class, function () {
            return new NewYearPromotionService();
        });
    }

    public function boot(): void
    {
        //
    }
}

