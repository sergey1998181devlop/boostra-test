<?php

use App\Core\Application\Container\ApplicationContainer;
use App\Providers\DatabaseServiceProvider;
use App\Providers\ExtraServiceServiceProvider;
use App\Providers\LoggingServiceProvider;
use App\Providers\ReferralServiceProvider;
use App\Providers\UserServiceProvider;
use App\Providers\ShortLinkServiceProvider;

return [
    'env' => env('APP_ENV', 'local'),
    'main_domain' => env('APP_MAIN_DOMAIN', 'boostra.ru'),

    'providers' => [
        ApplicationContainer::class,
        ExtraServiceServiceProvider::class,
        DatabaseServiceProvider::class,
        LoggingServiceProvider::class,
        ShortLinkServiceProvider::class,
        LoggingServiceProvider::class,
        UserServiceProvider::class,
        ReferralServiceProvider::class,
    ],

    'middleware' => [
        //
    ]
]; 
