<?php

namespace App\Providers;

use App\Core\Application\Container\Container;
use App\Core\Application\Container\ServiceProvider;
use App\Modules\NotificationCenter\Services\NotificationCenterService;
use App\Modules\Referral\Services\ReferralService;
use App\Modules\ShortLink\Services\ShortLinkService;

class ReferralServiceProvider extends ServiceProvider
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
        $c->bind(\Best2pay::class, function() {
            return new \Best2pay();
        });

        $c->bind(\OrderData::class, function() {
            return new \OrderData();
        });

        $c->bind(\UserData::class, function() {
            return new \UserData();
        });

        $c->bind(NotificationCenterService::class, function() {
            return new NotificationCenterService();
        });

        $c->bind(ShortLinkService::class, function() use ($c) {
            return new ShortLinkService(
                $c->make(NotificationCenterService::class),
            );
        });

        $c->bind(ReferralService::class, function () use ($c) {
            return new ReferralService(
                $c->make(\Best2pay::class),
                $c->make(\OrderData::class),
                $c->make(\UserData::class),
                $c->make(ShortLinkService::class)
            );
        });
    }

    public function boot(): void
    {

    }
}