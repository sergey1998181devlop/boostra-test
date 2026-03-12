<?php

namespace App\Core\Application\Facades;

class Router extends BaseFacade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Router';
    }
}
