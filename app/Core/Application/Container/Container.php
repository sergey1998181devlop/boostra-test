<?php

namespace App\Core\Application\Container;

use Exception;

class Container {

    /**
     * The container's bindings.
     */
    private static array $bindings = [];

    /**
     * This method helps to bind the container.
     */
    public function bind(string $name, callable $resolver): void {
        static::$bindings[$name] = $resolver;
    }

    /**
     * Resolve specific Container by key.
     * @throws Exception
     */
    public function make(string $name) {
        if (!isset(static::$bindings[$name])) {
            throw new Exception($name . ' not found.');
        }elseif (is_callable(static::$bindings[$name])) {
            return static::$bindings[$name]();
        }
        else{
            return static::$bindings[$name];
        }
    }

    /**
     * Get all container list.
     */
    public function getBindings() {
        return static::$bindings;
    }
}