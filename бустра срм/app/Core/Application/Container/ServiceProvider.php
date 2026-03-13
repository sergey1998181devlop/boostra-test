<?php

namespace App\Core\Application\Container;

abstract class ServiceProvider
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Регистрация сервисов
     */
    abstract public function register();

    /**
     * Инициализация после регистрации
     */
    public function boot()
    {
        //
    }
} 