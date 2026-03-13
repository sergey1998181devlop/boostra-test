<?php

use App\Core\Application\Container\ApplicationContainer;
use App\Http\Middleware\CheckApplicationToken;
use App\Http\Middleware\CheckManagerAuth;
use App\Http\Middleware\CheckManagerOrToken;
use App\Http\Middleware\CheckUsedeskComplaintTicketSecret;
use App\Http\Middleware\CheckUsedeskUserTicketSecret;
use App\Modules\Card\Providers\CardServiceProvider;
use App\Modules\SbpAccount\Providers\SbpAccountServiceProvider;
use App\Modules\TicketAssignment\Providers\TicketAssignmentServiceProvider;
use App\Providers\ClientServiceProvider;
use App\Providers\LoggingServiceProvider;
use App\Providers\OrderServiceProvider;
use App\Providers\ServiceRecoveryServiceProvider;
use App\Providers\UserServiceProvider;

return [
    'env' => env('APP_ENV', 'local'),

    'providers' => [
        ApplicationContainer::class,
        LoggingServiceProvider::class,
        ServiceRecoveryServiceProvider::class,
        ClientServiceProvider::class,
        OrderServiceProvider::class,
        TicketAssignmentServiceProvider::class,
        CardServiceProvider::class,
        SbpAccountServiceProvider::class,
        UserServiceProvider::class
    ],

    'middleware' => [
        'app.token.verify' => CheckApplicationToken::class,
        'app.manager.auth' => CheckManagerAuth::class,
        'app.manager_or_token.auth' => CheckManagerOrToken::class,
        'app.usedesk.complaint_ticket.secret' => CheckUsedeskComplaintTicketSecret::class,
        'app.usedesk.user_ticket.secret' => CheckUsedeskUserTicketSecret::class,
    ]
];
