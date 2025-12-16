<?php

namespace App\Core\Application\Facades;

class Flush extends BaseFacade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Flush';
    }
}
