<?php

namespace App\Providers;

use App\Core\Application\Container\ServiceProvider;
use App\Core\Logger\LoggerFactory;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы логирования
     */
    public function register(): void
    {
        $this->container->bind(LoggerFactory::class, function () {
            return new LoggerFactory();
        });

        $this->container->bind(Logger::class, function () {
            return LoggerFactory::createLogger();
        });

        $this->container->bind(LoggerInterface::class, function () {
            return $this->container->make(Logger::class);
        });

        $this->registerLogChannels();
    }

    /**
     * Выполняется после регистрации всех провайдеров
     */
    public function boot(): void
    {
        // Можно добавить дополнительную логику инициализации
    }

    /**
     * Регистрирует именованные каналы логирования
     */
    private function registerLogChannels(): void
    {
        $channels = config('logging.channels', []);

        foreach ($channels as $channel => $config) {
            // Регистрируем каждый канал как отдельный сервис
            $this->container->bind("logger.{$channel}", function () use ($channel) {
                return LoggerFactory::createLogger($channel);
            });
        }

        // Дополнительные именованные логгеры для удобства
        $this->container->bind('logger.app', function () {
            return LoggerFactory::createLogger('app');
        });

        $this->container->bind('logger.error', function () {
            return LoggerFactory::createLogger('error');
        });

        $this->container->bind('logger.api', function () {
            return LoggerFactory::createLogger('api');
        });

        $this->container->bind('logger.sql', function () {
            return LoggerFactory::createLogger('sql');
        });
    }
}
