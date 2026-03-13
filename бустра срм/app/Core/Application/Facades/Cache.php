<?php

namespace App\Core\Application\Facades;

/**
 * Фасад для работы с кэшем
 * 
 * @method static array|null get(string $key)
 * @method static void set(string $key, array $data, int $ttl = 600)
 * @method static void delete(string $key)
 * @method static bool exists(string $key)
 */
class Cache extends BaseFacade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'cache';
    }
}

