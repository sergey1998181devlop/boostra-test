<?php

namespace App\Providers;

use Api\Services\UserDataService;
use App\Core\Application\Container\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует все зависимости модуля
     */
    public function register(): void
    {
        $this->container->bind(UserDataService::class, function ($args = []) {
            // If UserData instance is provided as argument, use it; otherwise create new one
            $userData = !empty($args) && $args[0] instanceof \UserData 
                ? $args[0] 
                : new \UserData();
            
            return new UserDataService($userData);
        });
    }

    public function boot()
    {
        //
    }
}