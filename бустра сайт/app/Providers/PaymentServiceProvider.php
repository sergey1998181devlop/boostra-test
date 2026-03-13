<?php

namespace App\Providers;

use App\Core\Application\Container\Container;
use App\Core\Application\Container\ServiceProvider;
use App\Modules\Payment\Services\AddCardService;

class PaymentServiceProvider extends ServiceProvider
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct($this->container);
    }

    public function register(): void
    {
        $this->container->bind(AddCardService::class, function () {
            return new AddCardService();
        });
    }

    public function boot(): void
    {

    }
}
