<?php

namespace boostra\codeTemplates;

trait Singleton
{
    /**
     * @var mixed
     */
    private static $instance;
    
    /**
     * Constructor
     *
     * @param array $params Additional parameters to pass in the method initialize()
     *
     * @return mixed|static|\Simpla
     */
    public static function instance( ...$params )
    {
        self::$instance = self::$instance
            ?? $params
                ? new static( ...$params )
                : new static();
    
        return self::$instance;
    }
    
    public static function isInitialized(): bool
    {
        return isset( static::$instance );
    }
}
