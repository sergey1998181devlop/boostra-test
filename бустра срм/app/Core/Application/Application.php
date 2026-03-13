<?php

namespace App\Core\Application;

use App\Core\Application\Container\Container;
use App\Core\Application\Response\Response;
use App\Core\Application\Traits\Singleton;
use Psr\Log\LoggerInterface;

class Application extends Container {
    use Singleton;

    private Container $container;

    private function __construct() {
        $this->container = new Container ();
        $this->callServiceProviders();
    }

    /**
     * @throws \Exception
     */
    public function run() {
        try {
            $router = $this->container->make('Router');

            $response = $router->dispatch();

            if ($response instanceof Response) {
                $response->send();
            } else {
                throw new \Exception('Response is not instance of Response');
            }

        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function callServiceProviders() {
        $providers = config('app.providers');

        foreach ($providers as $provider) {
            $providerInstance = new $provider($this->container);
            $providerInstance->register();
            $providerInstance->boot();
        }
    }

    public static function getInstance(): Application
    {
        return self::singleton();
    }

    private function handleException(\Throwable $e)
    {
        try {
            $logger = $this->container->make(LoggerInterface::class);

            $context = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ];

            $logger->error($e->getMessage(), $context);
        } catch (\Exception $logException) {
            error_log($e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        }

        Response::json([
            'message' => 'Ошибка',
        ], 500)->send();
    }

    public function make(string $name, ...$args)
    {
        return $this->container->make($name, $args);
    }
}