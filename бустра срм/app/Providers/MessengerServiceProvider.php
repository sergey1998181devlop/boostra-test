<?php

namespace App\Providers;

use App\Core\Application\Container\ServiceProvider;
use App\Core\Messenger\Middleware\LoggingMiddleware;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransportFactory;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

class MessengerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = config('messenger');
        $serializer = new PhpSerializer();

        $transports = [];
        $redisFactory = new RedisTransportFactory();
        foreach ($config['transports'] as $name => $transportConfig) {
            $transports[$name] = $redisFactory->createTransport($transportConfig['dsn'], $transportConfig['options'] ?? [], $serializer);
        }

        $sendersLocator = new SendersLocator($transports, $config['routing']);

        // --- ШАГ 3: Автообнаружение и СОРТИРОВКА обработчиков ---
        $handlersMap = $this->discoverAndSortHandlers(
            [APP_ROOT . '/app/Modules', APP_ROOT . '/app/Handlers']
        );

        $callableMap = [];
        foreach ($handlersMap as $messageClass => $handlerClasses) {
            $callables = [];
            foreach ($handlerClasses as $handlerClass) {
                if (!$this->container->has($handlerClass)) {
                    $this->container->bind($handlerClass, fn() => new $handlerClass());
                }
                $callables[] = fn($message) => $this->container->make($handlerClass)($message);
            }
            $callableMap[$messageClass] = $callables;
        }
        $handlersLocator = new HandlersLocator($callableMap);

        // --- ШАГ 4: Сборка шины ---
        $messageBus = new MessageBus([
            new LoggingMiddleware(),
            new SendMessageMiddleware($sendersLocator),
            new HandleMessageMiddleware($handlersLocator),
        ]);

        // --- ШАГ 5: Регистрация шины в DI ---
        $this->container->bind(MessageBusInterface::class, fn() => $messageBus);
    }

    /**
     * Сканирует директории, находит все классы обработчиков и сортирует их по приоритету.
     */
    private function discoverAndSortHandlers(array $dirs): array
    {
        $handlers = [];
        $priorities = [];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) continue;

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isDir() || !str_ends_with($file->getFilename(), 'Handler.php')) {
                    continue;
                }

                $path = $file->getPathname();
                $realPath = realpath($path);
                if ($realPath && !in_array($realPath, get_included_files(), true)) {
                    require_once $realPath;
                }

                $class = str_replace([APP_ROOT . '/app/', '/', '.php'], ['App\\', '\\', ''], $path);

                try {
                    $reflector = new ReflectionClass($class);
                    if ($reflector->isInstantiable() && $reflector->implementsInterface('Symfony\Component\Messenger\Handler\MessageHandlerInterface')) {
                        $invokeMethod = $reflector->getMethod('__invoke');
                        $params = $invokeMethod->getParameters();
                        if (count($params) > 0 && ($type = $params[0]->getType()) && !$type->isBuiltin()) {
                            $messageClass = $type->getName();
                            $handlers[$messageClass][] = $class;

                            $handlerInstance = $reflector->newInstanceWithoutConstructor();
                            $priorities[$class] = $handlerInstance->getPriority();
                        }
                    }
                } catch (\ReflectionException $e) {
                    continue;
                }
            }
        }

        foreach ($handlers as $message => &$handlerList) {
            usort($handlerList, function ($classA, $classB) use ($priorities) {
                $priorityA = $priorities[$classA] ?? 0;
                $priorityB = $priorities[$classB] ?? 0;
                return $priorityB <=> $priorityA;
            });
        }

        return $handlers;
    }
}