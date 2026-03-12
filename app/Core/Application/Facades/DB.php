<?php

namespace App\Core\Application\Facades;

class DB extends BaseFacade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'DB';
    }
}
