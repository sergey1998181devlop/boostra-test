<?php

namespace App\Providers;

use App\Core\Application\Container\ServiceProvider;
use App\Repositories\UserRepository;
use Database;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->bind(\Database::class, function () {
            return new Database();
        });

        $this->container->bind(UserRepository::class, function () {
            return new UserRepository(
                $this->container->make(Database::class),
            );
        });
    }

    public function boot()
    {
        //
    }
}